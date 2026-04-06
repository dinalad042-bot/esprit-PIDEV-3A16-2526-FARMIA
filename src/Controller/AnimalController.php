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

#[Route('/animal')]
class AnimalController extends AbstractController
{
    #[Route('/', name: 'app_animal_index', methods: ['GET'])]
    public function index(AnimalRepository $aRepo, FermeRepository $fRepo): Response
    {
        return $this->render('animal/index.html.twig', [
            'animals' => $aRepo->findAll(),
            'fermes' => $fRepo->findAll(),
            'animal_edit' => null,
            'errors' => []
        ]);
    }

    #[Route('/new', name: 'app_animal_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $em, ValidatorInterface $validator, AnimalRepository $aRepo, FermeRepository $fRepo): Response
    {
        $animal = new Animal();
        $this->mapData($animal, $request);

        $violations = $validator->validate($animal);

        if (count($violations) > 0) {
            return $this->renderErrors($violations, $aRepo, $fRepo, null);
        }

        $em->persist($animal);
        $em->flush();
        $this->addFlash('success', 'Animal enregistré !');
        return $this->redirectToRoute('app_animal_index');
    }

    #[Route('/{id_animal}/edit', name: 'app_animal_edit', methods: ['GET'])]
    public function edit(Animal $animal, AnimalRepository $aRepo, FermeRepository $fRepo): Response
    {
        return $this->render('animal/index.html.twig', [
            'animals' => $aRepo->findAll(),
            'fermes' => $fRepo->findAll(),
            'animal_edit' => $animal,
            'errors' => []
        ]);
    }

    #[Route('/{id_animal}/update', name: 'app_animal_update', methods: ['POST'])]
    public function update(Request $request, Animal $animal, EntityManagerInterface $em, ValidatorInterface $validator, AnimalRepository $aRepo, FermeRepository $fRepo): Response
    {
        $this->mapData($animal, $request);
        $violations = $validator->validate($animal);

        if (count($violations) > 0) {
            return $this->renderErrors($violations, $aRepo, $fRepo, $animal);
        }

        $em->flush();
        $this->addFlash('success', 'Mise à jour réussie !');
        return $this->redirectToRoute('app_animal_index');
    }

    #[Route('/delete/{id_animal}', name: 'app_animal_delete', methods: ['POST'])]
    public function delete(Request $request, Animal $animal, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $animal->getIdAnimal(), $request->request->get('_token'))) {
            $em->remove($animal);
            $em->flush();
        }
        return $this->redirectToRoute('app_animal_index');
    }

    private function mapData(Animal $animal, Request $request): void
    {
        $animal->setEspece($request->request->get('espece') ?: null);
        $animal->setEtatSante($request->request->get('etat_sante') ?: null);
        
        $date = $request->request->get('date_naissance');
        $animal->setDateNaissance($date ? new \DateTime($date) : null);
        
        $idFerme = $request->request->get('id_ferme');
        $animal->setIdFerme($idFerme ? (int)$idFerme : null);
    }

    private function renderErrors($violations, $aRepo, $fRepo, $animal_edit): Response
    {
        $errors = [];
        foreach ($violations as $v) { $errors[$v->getPropertyPath()] = $v->getMessage(); }
        return $this->render('animal/index.html.twig', [
            'animals' => $aRepo->findAll(),
            'fermes' => $fRepo->findAll(),
            'animal_edit' => $animal_edit,
            'errors' => $errors
        ]);
    }
}