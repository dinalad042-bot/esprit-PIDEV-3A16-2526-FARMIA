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
    #[Route('/', name: 'app_ferme_index', methods: ['GET', 'POST'])]
    public function index(FermeRepository $fermeRepository, Request $request, EntityManagerInterface $em, ValidatorInterface $validator): Response
    {
        $search = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'id_ferme');
        $direction = $request->query->get('direction', 'ASC');

        // Sécurisation du tri pour éviter les injections ou erreurs SQL
        $allowedSorts = ['id_ferme', 'nom_ferme', 'lieu', 'surface'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'id_ferme';
        }

        if ($request->isMethod('POST')) {
            $ferme = new Ferme();
            $this->mapData($ferme, $request);
            
            $violations = $validator->validate($ferme);
            if (count($violations) > 0) {
                return $this->renderWithErrors($violations, $fermeRepository, null, $request);
            }

            $em->persist($ferme);
            $em->flush();

            $this->addFlash('success', 'Ferme ajoutée avec succès !');
            return $this->redirectToRoute('app_ferme_index');
        }

        // PERFORMANCE : Application systématique d'une limite
        $limit = 20;
        if (!empty($search) && method_exists($fermeRepository, 'findBySearchAndSort')) {
            $fermes = $fermeRepository->findBySearchAndSort($search, $sort, $direction);
        } else {
            $fermes = $fermeRepository->findBy([], [$sort => $direction], $limit);
        }

        return $this->render('ferme/index.html.twig', [
            'fermes' => $fermes,
            'searchTerm' => $search,
            'currentSort' => $sort,
            'currentDirection' => $direction,
            'errors' => [],
            'ferme_edit' => null
        ]);
    }

    #[Route('/pdf', name: 'app_ferme_pdf', methods: ['GET'])]
    public function generatePdf(FermeRepository $repo): Response
    {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isHtml5ParserEnabled', true);
        
        $dompdf = new Dompdf($pdfOptions);

        // PERFORMANCE : Limite de sécurité pour la génération PDF
        $html = $this->renderView('ferme/pdf.html.twig', [
            'fermes' => $repo->findBy([], ['nom_ferme' => 'ASC'], 50)
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="liste_fermes.pdf"'
        ]);
    }

    #[Route('/{id_ferme}/edit', name: 'app_ferme_edit', methods: ['GET'])]
    public function edit(Ferme $ferme, Request $request, FermeRepository $repo): Response
    {
        $sort = $request->query->get('sort', 'id_ferme');
        $direction = $request->query->get('direction', 'ASC');

        return $this->render('ferme/index.html.twig', [
            'fermes' => $repo->findBy([], [$sort => $direction], 20), // LIMITE PERFORMANCE
            'ferme_edit' => $ferme,
            'errors' => [],
            'searchTerm' => $request->query->get('search', ''),
            'currentSort' => $sort,
            'currentDirection' => $direction
        ]);
    }

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
        
        if ($this->getUser()) {
            $ferme->setUser($this->getUser());
        }
    }

    private function renderWithErrors($violations, FermeRepository $repo, ?Ferme $ferme_edit, Request $request): Response
    {
        $errors = [];
        foreach ($violations as $v) { 
            $errors[$v->getPropertyPath()] = $v->getMessage(); 
        }

        return $this->render('ferme/index.html.twig', [
            'fermes' => $repo->findBy([], ['id_ferme' => 'DESC'], 20), // LIMITE PERFORMANCE
            'ferme_edit' => $ferme_edit,
            'errors' => $errors,
            'searchTerm' => $request->query->get('search'),
            'currentSort' => $request->query->get('sort', 'id_ferme'),
            'currentDirection' => $request->query->get('direction', 'ASC')
        ]);
    }

    #[Route('/ferme/{id}/weather', name: 'app_ferme_weather')]
    public function weather(int $id, FermeRepository $repo, WeatherService $weatherService): Response
    {
        $ferme = $repo->find($id);
        if (!$ferme) {
            throw $this->createNotFoundException('Ferme introuvable');
        }
        
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

        $planExpert = $predictor->generateFullPlan($ferme);

        return $this->render('ferme/prediction.html.twig', [
            'ferme' => $ferme,
            'plan' => $planExpert
        ]);
    }
}