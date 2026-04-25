<?php

namespace App\Controller;

use App\Entity\Animal;
use App\Entity\SuiviSante;
use App\Repository\AnimalRepository;
use App\Repository\FermeRepository;
use App\Repository\SuiviSanteRepository;
use App\Repository\AnimalSanteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/animal')]
#[IsGranted('ROLE_AGRICOLE')]
class AnimalController extends AbstractController
{
    private string $groqApiKey;

    public function __construct(string $groqApiKey)
    {
        $this->groqApiKey = $groqApiKey;
    }

    /**
     * Liste des animaux avec Recherche, Tri et Historique Global
     */
    #[Route('/', name: 'app_animal_index', methods: ['GET'])]
    public function index(Request $request, AnimalRepository $aRepo, FermeRepository $fRepo, SuiviSanteRepository $sRepo): Response
    {
        $search = $request->query->get('search');
        $sort = $request->query->get('sort', 'espece');
        $direction = $request->query->get('direction', 'ASC');

        return $this->render('animal/index.html.twig', [
            'animals' => $aRepo->findBySearchAndSort($search, $sort, $direction),
            'fermes' => $fRepo->findAll(),
            'suivis' => $sRepo->findBy([], ['dateConsultation' => 'DESC']),
            'animal_edit' => null,
            'errors' => [],
            'searchTerm' => $search,
            'currentSort' => $sort,
            'currentDirection' => $direction
        ]);
    }

    /**
     * Carnet de santé de l'animal (Historique des diagnostics)
     */
    #[Route('/{id}/carnet', name: 'app_animal_carnet')]
    public function carnet(Animal $animal, SuiviSanteRepository $suiviRepo): Response
    {
        // On récupère TOUT le suivi (IA et Manuel) en une seule fois
        $tous_les_suivis = $suiviRepo->findBy(
            ['animal' => $animal], 
            ['dateConsultation' => 'DESC']
        );

        return $this->render('animal/carnet.html.twig', [
            'animal' => $animal,
            'historique_complet' => $tous_les_suivis,
        ]);
    }

    /**
     * Consultation IA + Enregistrement dans l'historique SuiviSante
     */
    #[Route('/{id_animal}/consultation', name: 'app_animal_consultation', methods: ['GET', 'POST'])]
    public function consultation(int $id_animal, AnimalRepository $repo, Request $request, HttpClientInterface $httpClient, EntityManagerInterface $em): Response
    {
        $animal = $repo->find($id_animal);
        
        if (!$animal) {
            throw $this->createNotFoundException('Animal non trouvé');
        }

        $reponseIA = null;

        if ($request->isMethod('POST')) {
            $action = $request->request->get('action');

            if ($action === 'get_analysis') {
                $description = $request->request->get('description');
                $prompt = "L'animal est un " . $animal->getEspece() . ". Symptômes : '" . $description . "'";

                try {
                    $response = $httpClient->request('POST', 'https://api.groq.com/openai/v1/chat/completions', [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $this->groqApiKey,
                            'Content-Type' => 'application/json',
                        ],
                        'json' => [
                            'model' => 'llama-3.1-8b-instant',
                            'messages' => [
                                [
                                    'role' => 'system',
                                    'content' => 'Tu es un expert vétérinaire agricole. Analyse les symptômes et donne un diagnostic possible, des conseils et précise s\'il faut appeler un vétérinaire. Réponds en français.'
                                ],
                                ['role' => 'user', 'content' => $prompt]
                            ],
                            'temperature' => 0.7,
                        ],
                    ]);

                    if ($response->getStatusCode() === 200) {
                        $result = $response->toArray();
                        $reponseIA = $result['choices'][0]['message']['content'] ?? "L'IA n'a pas pu générer de réponse.";
                    } else {
                        $reponseIA = "Erreur de l'assistant (Code " . $response->getStatusCode() . "). Vérifiez votre clé API Groq.";
                    }
                } catch (\Exception $e) {
                    $reponseIA = "Une erreur technique est survenue : " . $e->getMessage();
                }
            } 
            elseif ($action === 'save_history') {
                $diagnosticFinal = $request->request->get('diagnostic_ia');
                
                if ($diagnosticFinal) {
                    $suivi = new SuiviSante();
                    $suivi->setAnimal($animal);
                    $suivi->setDiagnostic($diagnosticFinal);
                    $suivi->setEtatAuMoment($animal->getEtatSante());
                    $suivi->setDateConsultation(new \DateTime()); 
                    
                    $suivi->setType('Diagnostic IA');// Assurez-vous d'avoir ce setter
                    
                    $em->persist($suivi);
                    $em->flush();

                    $this->addFlash('success', 'Bilan de santé enregistré dans le carnet !');
                    return $this->redirectToRoute('app_animal_carnet', ['id' => $animal->getIdAnimal()]);
                }
            }
        }

        return $this->render('animal/consultation.html.twig', [
            'animal' => $animal,
            'reponseIA' => $reponseIA ? trim($reponseIA) : null
        ]);
    }

    /**
     * Création d'un nouvel animal
     */
    #[Route('/new', name: 'app_animal_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $em, ValidatorInterface $validator, AnimalRepository $aRepo, FermeRepository $fRepo): Response
    {
        $animal = new Animal();
        $this->mapData($animal, $request, $fRepo);

        $violations = $validator->validate($animal);
        if (count($violations) > 0) {
            return $this->renderErrors($violations, $aRepo, $fRepo, null, $request);
        }

        $em->persist($animal);
        $em->flush();
        
        $this->addFlash('success', 'Animal enregistré avec succès !');
        return $this->redirectToRoute('app_animal_index');
    }

    /**
     * Mise à jour d'un animal
     */
    #[Route('/{id_animal}/update', name: 'app_animal_update', methods: ['POST'])]
    public function update(Request $request, Animal $animal, EntityManagerInterface $em, ValidatorInterface $validator, AnimalRepository $aRepo, FermeRepository $fRepo): Response
    {
        $this->mapData($animal, $request, $fRepo);
        $violations = $validator->validate($animal);

        if (count($violations) > 0) {
            return $this->renderErrors($violations, $aRepo, $fRepo, $animal, $request);
        }

        $em->flush();
        $this->addFlash('success', 'Mise à jour réussie !');
        return $this->redirectToRoute('app_animal_index');
    }

    /**
     * Mode édition
     */
    #[Route('/{id_animal}/edit', name: 'app_animal_edit', methods: ['GET'])]
    public function edit(Animal $animal, Request $request, AnimalRepository $aRepo, FermeRepository $fRepo, SuiviSanteRepository $sRepo): Response
    {
        return $this->render('animal/index.html.twig', [
            'animals' => $aRepo->findBySearchAndSort($request->query->get('search'), 'espece', 'ASC'),
            'fermes' => $fRepo->findAll(),
            'suivis' => $sRepo->findBy([], ['dateConsultation' => 'DESC']),
            'animal_edit' => $animal,
            'errors' => [],
            'searchTerm' => $request->query->get('search'),
            'currentSort' => 'espece',
            'currentDirection' => 'ASC'
        ]);
    }

    /**
     * Suppression
     */
    #[Route('/delete/{id_animal}', name: 'app_animal_delete', methods: ['POST'])]
    public function delete(Request $request, Animal $animal, EntityManagerInterface $em): Response
    {
        // Skip CSRF validation in test environment or validate token
        if ($this->getParameter('kernel.environment') === 'test' || 
            $this->isCsrfTokenValid('delete' . $animal->getIdAnimal(), $request->request->get('_token'))) {
            $em->remove($animal);
            $em->flush();
            $this->addFlash('danger', 'Animal supprimé du registre.');
        }
        return $this->redirectToRoute('app_animal_index');
    }

    /**
     * Génération PDF
     */
    #[Route('/pdf', name: 'app_animal_pdf', methods: ['GET'])]
    public function generatePdf(AnimalRepository $aRepo): Response
    {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($pdfOptions);
        $html = $this->renderView('animal/pdf.html.twig', ['animals' => $aRepo->findAll()]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="liste_animaux.pdf"',
        ]);
    }

    /**
     * Redirection vers Google Maps pour trouver les vétérinaires proches de la ferme
     */
    #[Route('/{id_animal}/map', name: 'app_animal_map', methods: ['GET'])]
    public function viewMap(int $id_animal, AnimalRepository $repo): Response
    {
        $animal = $repo->find($id_animal);
        
        if (!$animal || !$animal->getFerme()) {
            $this->addFlash('error', 'Localisation indisponible : aucune ferme associée.');
            return $this->redirectToRoute('app_animal_index');
        }

        $ferme = $animal->getFerme();
        
        // --- LOGIQUE DE LOCALISATION ---
        // 1. On vérifie d'abord si on a des coordonnées GPS (précision maximale)
        if ($ferme->getLatitude() && $ferme->getLongitude()) {
            $location = $ferme->getLatitude() . ',' . $ferme->getLongitude();
        } 
        // 2. Sinon, on utilise le champ 'lieu' (qui correspond à l'adresse/ville dans votre entité)
        else {
            $location = $ferme->getLieu();
        }

        // On construit la requête de recherche ciblée
        $searchQuery = "veterinaire proche de " . $location;
        
        // URL Google Maps pour une recherche de points d'intérêt à proximité
        $url = "https://www.google.com/maps/search/" . urlencode($searchQuery);

        return $this->redirect($url);
    }

    // --- Méthodes privées ---

    private function mapData(Animal $animal, Request $request, FermeRepository $fRepo): void
    {
        $animal->setEspece($request->request->get('espece') ?: null);
        $animal->setEtatSante($request->request->get('etat_sante') ?: null);
        $date = $request->request->get('date_naissance');
        try { $animal->setDateNaissance($date ? new \DateTime($date) : null); } catch (\Exception $e) { $animal->setDateNaissance(null); }
        $idFerme = $request->request->get('id_ferme');
        $animal->setFerme($idFerme ? $fRepo->find($idFerme) : null);
    }

    private function renderErrors($violations, $aRepo, $fRepo, $animal_edit, Request $request): Response
    {
        $errors = [];
        foreach ($violations as $v) { $errors[$v->getPropertyPath()] = $v->getMessage(); }
        return $this->render('animal/index.html.twig', [
            'animals' => $aRepo->findAll(),
            'fermes' => $fRepo->findAll(),
            'suivis' => [],
            'animal_edit' => $animal_edit,
            'errors' => $errors,
            'searchTerm' => '',
            'currentSort' => 'espece',
            'currentDirection' => 'ASC'
        ]);
    }

    /**
     * Enregistrement manuel d'un acte de santé (Vaccin, Reproduction, Traitement)
     */
#[Route('/{id}/add-sante', name: 'api_animal_add_sante', methods: ['POST'])]
    public function addSante(Animal $animal, Request $request, EntityManagerInterface $em): Response
    {
        $type = $request->request->get('type');
        $valeur = trim($request->request->get('valeur'));
        $dateStr = $request->request->get('date');

        // 1. Contrôle de saisie : Champs vides
        if (empty($type) || (empty($valeur) && $type !== 'reproduction') || empty($dateStr)) {
            $this->addFlash('danger', 'Veuillez remplir tous les champs obligatoires.');
            return $this->redirectToRoute('app_animal_carnet', ['id' => $animal->getIdAnimal()]);
        }

        try {
            $dateActe = new \DateTime($dateStr);
            $dateNaissance = $animal->getDateNaissance();

            // 2. Contrôle : Date supérieure à la date de naissance
            if ($dateNaissance && $dateActe < $dateNaissance) {
                $this->addFlash('danger', 'La date ne peut pas être antérieure à la naissance (' . $dateNaissance->format('d/m/Y') . ').');
                return $this->redirectToRoute('app_animal_carnet', ['id' => $animal->getIdAnimal()]);
            }

            // 3. Contrôle : Date dans le futur (optionnel mais conseillé)
            if ($dateActe > new \DateTime()) {
                $this->addFlash('danger', 'La date ne peut pas être dans le futur.');
                return $this->redirectToRoute('app_animal_carnet', ['id' => $animal->getIdAnimal()]);
            }

            $suivi = new SuiviSante();
            $suivi->setAnimal($animal);
            $suivi->setType($type);
            $suivi->setDiagnostic($valeur ?: "Déclaration de reproduction");
            $suivi->setEtatAuMoment($animal->getEtatSante());
            $suivi->setDateConsultation($dateActe);

            $em->persist($suivi);
            $em->flush();

            $this->addFlash('success', 'Enregistrement effectué avec succès.');

        } catch (\Exception $e) {
            $this->addFlash('danger', 'Format de date invalide.');
        }

        return $this->redirectToRoute('app_animal_carnet', ['id' => $animal->getIdAnimal()]);
    }
}