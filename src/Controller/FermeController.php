<?php

namespace App\Controller;

use App\Entity\Ferme;
use App\Form\FermeType;
use App\Repository\FermeRepository;
use App\Service\WeatherService;
use App\Service\FarmPredictor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/ferme')]
class FermeController extends AbstractController
{
    /**
     * PAGE PRINCIPALE : Affiche la liste ET gère l'ajout (POST)
     */
    #[Route('/', name: 'app_ferme_index', methods: ['GET', 'POST'])]
    public function index(FermeRepository $fermeRepository, Request $request, EntityManagerInterface $em, ValidatorInterface $validator): Response
    {
        $search = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'idFerme');
        $direction = $request->query->get('direction', 'ASC');

        // --- PARTIE AJOUT (POST) ---
        if ($request->isMethod('POST')) {
            $ferme = new Ferme();
            $this->mapData($ferme, $request); // Utilise la fonction de mapping existante
            
            // Validation
            $violations = $validator->validate($ferme);
            if (count($violations) > 0) {
                return $this->renderWithErrors($violations, $fermeRepository, null, $request);
            }

            $em->persist($ferme);
            $em->flush();

            $this->addFlash('success', 'Ferme ajoutée avec succès !');
            return $this->redirectToRoute('app_ferme_index');
        }

        // --- PARTIE AFFICHAGE (GET) ---
        // Utilisation de findBySearchAndSort si elle existe dans ton repo, sinon findAll()
        $fermes = method_exists($fermeRepository, 'findBySearchAndSort') 
            ? $fermeRepository->findBySearchAndSort($search, $sort, $direction)
            : $fermeRepository->findAll();

        return $this->render('ferme/index.html.twig', [
            'fermes' => $fermes,
            'searchTerm' => $search,
            'currentSort' => $sort,
            'currentDirection' => $direction,
            'errors' => [],
            'ferme_edit' => null // Mode ajout par défaut
        ]);
    }

    /**
     * Génération du catalogue PDF
     */
    #[Route('/pdf', name: 'app_ferme_pdf', methods: ['GET'])]
    public function generatePdf(FermeRepository $repo): Response
    {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isHtml5ParserEnabled', true);
        
        $dompdf = new Dompdf($pdfOptions);

        $html = $this->renderView('ferme/pdf.html.twig', [
            'fermes' => $repo->findAll()
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="liste_fermes.pdf"'
        ]);
    }

    /**
     * Mode édition : recharge la page index avec les données de la ferme choisie
     */
    #[Route('/{id_ferme}/edit', name: 'app_ferme_edit', methods: ['GET'])]
    public function edit(Ferme $ferme, Request $request, FermeRepository $repo): Response
    {
        $search = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'idFerme');
        $direction = $request->query->get('direction', 'ASC');

        return $this->render('ferme/index.html.twig', [
            'fermes' => $repo->findAll(), // Ou findBySearchAndSort
            'ferme_edit' => $ferme,
            'errors' => [],
            'searchTerm' => $search,
            'currentSort' => $sort,
            'currentDirection' => $direction
        ]);
    }

    /**
     * Mise à jour effective en base
     */
    #[Route('/{id_ferme}/update', name: 'app_ferme_update', methods: ['POST'])]
    public function update(Request $request, Ferme $ferme, EntityManagerInterface $em, ValidatorInterface $validator, FermeRepository $repo): Response
    {
        $this->mapData($ferme, $request);
        
        $violations = $validator->validate($ferme);
        if (count($violations) > 0) {
            return $this->renderWithErrors($violations, $repo, $ferme, $request);
        }

        $em->flush();
        $this->addFlash('success', 'La ferme a été mise à jour.');
        
        return $this->redirectToRoute('app_ferme_index');
    }

    /**
     * Suppression d'une ferme
     */
    #[Route('/delete/{id_ferme}', name: 'app_ferme_delete', methods: ['POST'])]
    public function delete(Request $request, Ferme $ferme, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ferme->getIdFerme(), $request->request->get('_token'))) {
            $em->remove($ferme);
            $em->flush();
            $this->addFlash('success', 'Ferme supprimée.');
        }
        
        return $this->redirectToRoute('app_ferme_index');
    }

    /**
     * Mapping manuel des données (Évite d'utiliser FermeType pour rester sur l'index)
     */
    private function mapData(Ferme $ferme, Request $request): void
    {
        $ferme->setNomFerme($request->request->get('nom_ferme'));
        $ferme->setLieu($request->request->get('lieu'));
        
        $surface = $request->request->get('surface');
        $ferme->setSurface($surface !== null ? (float)$surface : 0.0);

        $lat = $request->request->get('latitude');
        $lng = $request->request->get('longitude');

        $ferme->setLatitude($lat !== null && $lat !== '' ? (float)$lat : null);
        $ferme->setLongitude($lng !== null && $lng !== '' ? (float)$lng : null);
        
        // On lie l'utilisateur connecté
        if ($this->getUser()) {
            $ferme->setUser($this->getUser());
        }
    }

    /**
     * Centralisation de l'affichage en cas d'erreurs
     */
    private function renderWithErrors($violations, FermeRepository $repo, ?Ferme $ferme_edit, Request $request): Response
    {
        $errors = [];
        foreach ($violations as $v) { 
            $errors[$v->getPropertyPath()] = $v->getMessage(); 
        }

        return $this->render('ferme/index.html.twig', [
            'fermes' => $repo->findAll(),
            'ferme_edit' => $ferme_edit,
            'errors' => $errors,
            'searchTerm' => $request->query->get('search'),
            'currentSort' => $request->query->get('sort', 'idFerme'),
            'currentDirection' => $request->query->get('direction', 'ASC')
        ]);
    }
    #[Route('/ferme/{id}/weather', name: 'app_ferme_weather')]
public function weather(int $id, FermeRepository $repo, WeatherService $weatherService): Response
{
    $ferme = $repo->find($id);
    
    // On récupère la météo en utilisant le lieu (ville) de la ferme
    $weatherData = $weatherService->getWeather($ferme->getLieu());

    return $this->render('ferme/weather.html.twig', [
        'ferme' => $ferme,
        'weather' => $weatherData
    ]);
}
#[Route('/{id_ferme}/prediction', name: 'app_ferme_prediction', methods: ['GET'])]
public function prediction(int $id_ferme, FermeRepository $repo, FarmPredictor $predictor): Response
{
    $ferme = $repo->find($id_ferme);
    
    if (!$ferme) {
        throw $this->createNotFoundException('Ferme non trouvée');
    }

    // Appel au service que nous avons créé précédemment
    $planExpert = $predictor->generateFullPlan($ferme);

    return $this->render('ferme/prediction.html.twig', [
        'ferme' => $ferme,
        'plan' => $planExpert
    ]);
}
}