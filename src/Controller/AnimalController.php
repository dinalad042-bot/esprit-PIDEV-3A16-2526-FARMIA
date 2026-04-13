<?php

namespace App\Controller;

use App\Entity\Animal;
use App\Repository\AnimalRepository;
use App\Repository\FermeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/animal')]
class AnimalController extends AbstractController
{
    private string $groqApiKey;

    public function __construct(string $groqApiKey)
    {
        $this->groqApiKey = $groqApiKey;
    }

    /**
     * Liste des animaux avec Recherche et Tri
     */
    #[Route('/', name: 'app_animal_index', methods: ['GET'])]
    public function index(Request $request, AnimalRepository $aRepo, FermeRepository $fRepo): Response
    {
        $search = $request->query->get('search');
        $sort = $request->query->get('sort', 'espece');
        $direction = $request->query->get('direction', 'ASC');

        return $this->render('animal/index.html.twig', [
            'animals' => $aRepo->findBySearchAndSort($search, $sort, $direction),
            'fermes' => $fRepo->findAll(),
            'animal_edit' => null,
            'errors' => [],
            'searchTerm' => $search,
            'currentSort' => $sort,
            'currentDirection' => $direction
        ]);
    }

    /**
     * Consultation IA pour les animaux malades (via Groq API)
     */
    #[Route('/{id_animal}/consultation', name: 'app_animal_consultation', methods: ['GET', 'POST'])]
    public function consultation(int $id_animal, AnimalRepository $repo, Request $request, HttpClientInterface $httpClient): Response
    {
        $animal = $repo->find($id_animal);
        
        if (!$animal) {
            throw $this->createNotFoundException('Animal non trouvé');
        }

        $reponseIA = null;

        if ($request->isMethod('POST')) {
            $description = $request->request->get('description');
            
            // Préparation du prompt pour Groq
            $prompt = "L'animal est un " . $animal->getEspece() . ". Symptômes décrits par l'agriculteur : '" . $description . "'";

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
                                'content' => 'Tu es un expert vétérinaire agricole. Analyse les symptômes et donne un diagnostic possible, des conseils de soin immédiats et précise s\'il faut appeler un vétérinaire d\'urgence. Réponds en français de manière structurée.'
                            ],
                            [
                                'role' => 'user',
                                'content' => $prompt
                            ]
                        ],
                        'temperature' => 0.7,
                    ],
                ]);

                if ($response->getStatusCode() === 200) {
                    $result = $response->toArray();
                    $reponseIA = $result['choices'][0]['message']['content'] ?? "Pas de réponse générée.";
                } else {
                    $reponseIA = "Désolé, l'assistant subit une maintenance technique (Erreur " . $response->getStatusCode() . ").";
                }
            } catch (\Exception $e) {
                $reponseIA = "Une erreur de connexion est survenue : " . $e->getMessage();
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
    public function edit(Animal $animal, Request $request, AnimalRepository $aRepo, FermeRepository $fRepo): Response
    {
        $search = $request->query->get('search');
        $sort = $request->query->get('sort', 'espece');
        $direction = $request->query->get('direction', 'ASC');

        return $this->render('animal/index.html.twig', [
            'animals' => $aRepo->findBySearchAndSort($search, $sort, $direction),
            'fermes' => $fRepo->findAll(),
            'animal_edit' => $animal,
            'errors' => [],
            'searchTerm' => $search,
            'currentSort' => $sort,
            'currentDirection' => $direction
        ]);
    }

    /**
     * Suppression
     */
    #[Route('/delete/{id_animal}', name: 'app_animal_delete', methods: ['POST'])]
    public function delete(Request $request, Animal $animal, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $animal->getIdAnimal(), $request->request->get('_token'))) {
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

    // --- Méthodes privées ---

    private function mapData(Animal $animal, Request $request, FermeRepository $fRepo): void
    {
        $animal->setEspece($request->request->get('espece') ?: null);
        $animal->setEtatSante($request->request->get('etat_sante') ?: null);
        
        $date = $request->request->get('date_naissance');
        try {
            $animal->setDateNaissance($date ? new \DateTime($date) : null);
        } catch (\Exception $e) {
            $animal->setDateNaissance(null);
        }
        
        $idFerme = $request->request->get('id_ferme');
        $animal->setFerme($idFerme ? $fRepo->find($idFerme) : null);
    }

    private function renderErrors($violations, $aRepo, $fRepo, $animal_edit, Request $request): Response
    {
        $errors = [];
        foreach ($violations as $v) { $errors[$v->getPropertyPath()] = $v->getMessage(); }

        return $this->render('animal/index.html.twig', [
            'animals' => $aRepo->findBySearchAndSort($request->query->get('search'), $request->query->get('sort', 'espece'), $request->query->get('direction', 'ASC')),
            'fermes' => $fRepo->findAll(),
            'animal_edit' => $animal_edit,
            'errors' => $errors,
            'searchTerm' => $request->query->get('search'),
            'currentSort' => $request->query->get('sort', 'espece'),
            'currentDirection' => $request->query->get('direction', 'ASC')
        ]);
    }
}