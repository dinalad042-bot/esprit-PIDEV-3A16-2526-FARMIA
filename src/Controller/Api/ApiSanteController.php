<?php

namespace App\Controller\Api;

use App\Entity\Plante;
use App\Entity\Arrosage;
use App\Entity\Animal;
use App\Repository\ArrosageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class ApiSanteController extends AbstractController
{
    /**
     * Gère l'arrosage des plantes (Calendrier)
     * Correction de l'erreur 500 vue sur la capture
     */
    #[Route('/plante/{id}/arrosage/{date}', name: 'api_plante_arrosage', methods: ['POST'])]
    public function toggleArrosage(
        Plante $plante, 
        string $date, 
        EntityManagerInterface $em, 
        ArrosageRepository $repo
    ): JsonResponse {
        try {
            $dateTime = new \DateTime($date);
            
            // On cherche si un arrosage existe déjà pour cette plante à cette date
            $arrosageExist = $repo->findOneBy([
                'plante' => $plante, 
                'dateArrosage' => $dateTime
            ]);

            if ($arrosageExist) {
                $em->remove($arrosageExist);
                $action = 'removed';
            } else {
                $arrosage = new Arrosage();
                $arrosage->setPlante($plante);
                $arrosage->setDateArrosage($dateTime);
                $em->persist($arrosage);
                $action = 'added';
            }

            $em->flush();

            return new JsonResponse([
                'status' => 'success', 
                'action' => $action,
                'date' => $date
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error', 
                'message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Gère le carnet de santé des animaux
     * Correction de l'erreur 404 vue sur la capture du Canard
     */
    #[Route('/animal/{id}/sante', name: 'api_animal_sante', methods: ['POST'])]
    public function addSante(
        Animal $animal, 
        Request $request, 
        EntityManagerInterface $em
    ): JsonResponse {
        // Récupération des données JSON envoyées par fetch()
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse([
                'status' => 'error', 
                'message' => 'Données JSON invalides'
            ], Response::HTTP_BAD_REQUEST);
        }

        /* LOGIQUE DE SAUVEGARDE :
           Si tu as une entité 'SanteAnimale', décommente les lignes suivantes 
           après avoir créé l'entité avec php bin/console make:entity
        */

        /*
        $sante = new SanteAnimale();
        $sante->setAnimal($animal);
        $sante->setType($data['type']); // VACCIN, REPRODUCTION, etc.
        $sante->setValeur($data['valeur']);
        $sante->setDateEvenement(new \DateTime($data['date']));
        $em->persist($sante);
        $em->flush();
        */

        return new JsonResponse([
            'status' => 'success',
            'message' => 'L\'événement pour le ' . $animal->getEspece() . ' a été reçu.',
            'details' => [
                'type' => $data['type'] ?? 'Inconnu',
                'id_animal' => $animal->getIdAnimal()
            ]
        ], Response::HTTP_CREATED);
    }
}