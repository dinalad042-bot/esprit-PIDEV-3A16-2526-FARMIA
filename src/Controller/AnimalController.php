<?php

namespace App\Controller;

use App\Entity\Animal;
use App\Form\AnimalType;
use App\Repository\AnimalRepository;
use App\Repository\FermeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/animal')]
class AnimalController extends AbstractController
{
    /**
     * Liste des animaux avec Recherche et Tri
     */
    #[Route('/', name: 'app_animal_index', methods: ['GET'])]
    public function index(Request $request, AnimalRepository $aRepo, FermeRepository $fRepo): Response
    {
        // Récupération des paramètres de recherche et tri
        $search = $request->query->get('search');
        $sort = $request->query->get('sort', 'espece'); // Tri par défaut sur l'espèce
        $direction = $request->query->get('direction', 'ASC');

        return $this->render('animal/index.html.twig', [
            'animals' => $aRepo->findBySearchAndSort($search, $sort, $direction),
            'fermes' => $fRepo->findAll(),
            'animal_edit' => null,
            'errors' => [],
            'search' => $search,
            'currentSort' => $sort,
            'currentDirection' => $direction
        ]);
    }

    /**
     * Création d'un nouvel animal
     */
    #[Route('/new', name: 'app_animal_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, AnimalRepository $aRepo, FermeRepository $fRepo): Response
    {
        $animal = new Animal();
        $form = $this->createForm(AnimalType::class, $animal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($animal);
            $em->flush();
            
            $this->addFlash('success', 'Animal enregistré avec succès !');
            return $this->redirectToRoute('app_animal_index');
        }

        // En cas d'erreurs ou GET, afficher le formulaire
        return $this->render('animal/new.html.twig', [
            'form' => $form->createView(),
            'fermes' => $fRepo->findAll(),
        ]);
    }

    /**
     * Génération du catalogue PDF
     */
    #[Route('/pdf', name: 'app_animal_pdf', methods: ['GET'])]
    public function generatePdf(AnimalRepository $aRepo): Response
    {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($pdfOptions);

        $html = $this->renderView('animal/pdf.html.twig', [
            'animals' => $aRepo->findAll()
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="liste_animaux.pdf"',
        ]);
    }

    /**
     * Mode édition : charge l'animal dans le formulaire
     */
    #[Route('/{id_animal}/edit', name: 'app_animal_edit', methods: ['GET', 'POST'])]
    public function edit(Animal $animal, Request $request, EntityManagerInterface $em, AnimalRepository $aRepo, FermeRepository $fRepo): Response
    {
        $form = $this->createForm(AnimalType::class, $animal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Mise à jour réussie !');
            return $this->redirectToRoute('app_animal_index');
        }

        $search = $request->query->get('search');
        $sort = $request->query->get('sort', 'espece');
        $direction = $request->query->get('direction', 'ASC');

        return $this->render('animal/edit.html.twig', [
            'form' => $form->createView(),
            'animal' => $animal,
            'fermes' => $fRepo->findAll(),
            'animals' => $aRepo->findBySearchAndSort($search, $sort, $direction),
            'search' => $search,
            'currentSort' => $sort,
            'currentDirection' => $direction
        ]);
    }

    /**
     * Suppression d'un animal
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
}
