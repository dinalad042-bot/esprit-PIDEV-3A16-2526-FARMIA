<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\UserFace;
use App\Repository\UserFaceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Service dédié à la gestion des données biométriques (visages).
 *
 * Responsabilités :
 *  - Enrôler un visage (appel API Python + sauvegarde BDD)
 *  - Vérifier si un utilisateur a un visage enregistré
 *  - Désactiver / supprimer un visage
 */
class FaceEnrollmentService
{
    public function __construct(
        private readonly EntityManagerInterface    $em,
        private readonly UserFaceRepository        $userFaceRepository,
        private readonly HttpClientInterface        $httpClient,
        private readonly LoggerInterface            $logger,
        private readonly PythonFaceRecognitionService $pythonService,
        private readonly string $pythonApiUrl = 'http://127.0.0.1:5000',
        private readonly string $datasetBasePath = '',
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // Enrôlement
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Enrôle le visage d'un utilisateur.
     *
     * 1. Envoie l'image à l'API Python pour détecter + enregistrer le visage.
     * 2. Sauvegarde / met à jour l'entité UserFace en base de données.
     *
     * @param User   $user        L'utilisateur à enrôler
     * @param string $imageBase64 L'image capturée (data URI base64)
     *
     * @return array{success: bool, message: string, face?: UserFace}
     */
    public function enrollFace(User $user, string $imageBase64): array
    {
        // 1. S'assurer que le serveur Python est démarré
        if (!$this->pythonService->ensureServerIsRunning()) {
            return [
                'success' => false,
                'message' => "L'API de reconnaissance faciale est momentanément indisponible.",
            ];
        }

        try {
            // 2. Appel à l'API Python Flask
            $response = $this->httpClient->request('POST', rtrim($this->pythonApiUrl, '/') . '/api/enroll', [
                'json' => [
                    'user_id' => (string) $user->getId(),
                    'image'   => $imageBase64,
                ],
                'timeout' => 15,
            ]);

            $result = $response->toArray(false);

            if (!isset($result['success']) || $result['success'] !== true) {
                $msg = $result['message'] ?? 'Erreur inconnue de l\'API Python.';
                $this->logger->warning('[FaceEnrollment] Échec côté API Python.', ['message' => $msg, 'user_id' => $user->getId()]);
                return ['success' => false, 'message' => $msg];
            }

            // 3. Calculer le chemin du dataset pour cet utilisateur
            $imagePath = 'dataset/' . $user->getId() . '/';

            // 4. Récupérer ou créer l'entité UserFace en BDD
            $userFace = $this->userFaceRepository->findActiveByUser($user);

            if ($userFace === null) {
                // Premier enrôlement : on crée une nouvelle entrée
                $userFace = new UserFace();
                $userFace->setUser($user);
                $this->em->persist($userFace);
            }

            // 5. Mettre à jour les métadonnées
            $userFace->setImagePath($imagePath);
            $userFace->setSamplesCount((int) ($result['samples_count'] ?? 1));
            $userFace->setIsActive(true);

            $this->em->flush();

            $this->logger->info('[FaceEnrollment] Visage enregistré en base.', [
                'user_id'       => $user->getId(),
                'face_id'       => $userFace->getId(),
                'samples_count' => $userFace->getSamplesCount(),
            ]);

            return [
                'success' => true,
                'message' => 'Visage enregistré avec succès.',
                'face'    => $userFace,
            ];

        } catch (TransportExceptionInterface $e) {
            $this->logger->error('[FaceEnrollment] Erreur de transport HTTP.', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Impossible de joindre l\'API Python : ' . $e->getMessage()];
        } catch (\Exception $e) {
            $this->logger->error('[FaceEnrollment] Erreur inattendue.', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Erreur interne : ' . $e->getMessage()];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Lecture
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Retourne le visage actif d'un utilisateur, ou null.
     */
    public function getActiveFace(User $user): ?UserFace
    {
        return $this->userFaceRepository->findActiveByUser($user);
    }

    /**
     * Vérifie si l'utilisateur a un visage enregistré et actif.
     */
    public function hasFaceAuth(User $user): bool
    {
        return $this->userFaceRepository->findActiveByUser($user) !== null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Suppression / Désactivation
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Désactive le visage d'un utilisateur (sans le supprimer de la BDD ni du disque).
     * Permet de réactiver plus tard si besoin.
     */
    public function deactivateFace(User $user): bool
    {
        $userFace = $this->userFaceRepository->findActiveByUser($user);
        if ($userFace === null) {
            return false;
        }

        $userFace->setIsActive(false);
        $this->em->flush();

        $this->logger->info('[FaceEnrollment] Visage désactivé.', ['user_id' => $user->getId()]);
        return true;
    }

    /**
     * Supprime définitivement les données faciales d'un utilisateur.
     * - Supprime l'entité UserFace en BDD
     * - Appelle l'API Python pour supprimer les images du dataset
     */
    public function deleteFace(User $user): array
    {
        $faces = $this->userFaceRepository->findAllByUser($user);

        if (empty($faces)) {
            return ['success' => false, 'message' => 'Aucun visage trouvé pour cet utilisateur.'];
        }

        // Supprimer en BDD (cascade supprimera les enregistrements)
        foreach ($faces as $face) {
            $this->em->remove($face);
        }
        $this->em->flush();

        // Appeler l'API Python pour supprimer les images du dataset
        try {
            if ($this->pythonService->isHealthy()) {
                $this->httpClient->request('DELETE', rtrim($this->pythonApiUrl, '/') . '/api/enroll/' . $user->getId(), [
                    'timeout' => 5,
                ]);
            }
        } catch (\Exception $e) {
            // On ne bloque pas si l'API Python est indisponible — BDD déjà nettoyée
            $this->logger->warning('[FaceEnrollment] Impossible de supprimer les images côté Python.', ['error' => $e->getMessage()]);
        }

        $this->logger->info('[FaceEnrollment] Données faciales supprimées.', ['user_id' => $user->getId()]);
        return ['success' => true, 'message' => 'Données faciales supprimées.'];
    }
}
