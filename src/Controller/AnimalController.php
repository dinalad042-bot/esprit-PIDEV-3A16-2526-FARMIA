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
     * Action de mise à jour en base
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

    /**
     * Mapping des données du formulaire vers l'entité
     */
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
        if ($idFerme) {
            $ferme = $fRepo->find($idFerme);
            $animal->setFerme($ferme);
        } else {
            $animal->setFerme(null);
        }
    }

    /**
     * Gestion centralisée des erreurs de validation
     */
    private function renderErrors($violations, $aRepo, $fRepo, $animal_edit, Request $request): Response
    {
        $errors = [];
        foreach ($violations as $v) {
            $errors[$v->getPropertyPath()] = $v->getMessage();
        }

        $search = $request->query->get('search');
        $sort = $request->query->get('sort', 'espece');
        $direction = $request->query->get('direction', 'ASC');

        return $this->render('animal/index.html.twig', [
            'animals' => $aRepo->findBySearchAndSort($search, $sort, $direction),
            'fermes' => $fRepo->findAll(),
            'animal_edit' => $animal_edit,
            'errors' => $errors,
            'searchTerm' => $search,
            'currentSort' => $sort,
            'currentDirection' => $direction
        ]);
    }
}
