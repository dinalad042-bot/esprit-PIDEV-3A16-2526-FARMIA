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

#[Route('/animal')]
final class AnimalController extends AbstractController
{
    /**
     * Affiche la liste du bétail et le formulaire (nécessite FermeRepository pour le select)
     */
    #[Route('/', name: 'app_animal_index', methods: ['GET'])]
    public function index(AnimalRepository $animalRepository, FermeRepository $fermeRepository): Response
    {
        return $this->render('animal/index.html.twig', [
            'animals' => $animalRepository->findAll(),
            'fermes' => $fermeRepository->findAll(),
            'animal_edit' => null
        ]);
    }

    /**
     * Ajout d'un nouvel animal
     */
    #[Route('/new', name: 'app_animal_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $animal = new Animal();
        $this->saveAnimalData($animal, $request, $entityManager);
        
        $this->addFlash('success', 'Animal ajouté avec succès !');
        return $this->redirectToRoute('app_animal_index');
    }

    /**
     * Mode édition : remplit le formulaire avec les données de l'animal
     */
    #[Route('/{id_animal}/edit', name: 'app_animal_edit', methods: ['GET'])]
    public function edit(Animal $animal, AnimalRepository $animalRepository, FermeRepository $fermeRepository): Response
    {
        return $this->render('animal/index.html.twig', [
            'animals' => $animalRepository->findAll(),
            'fermes' => $fermeRepository->findAll(),
            'animal_edit' => $animal 
        ]);
    }

    /**
     * Enregistre les modifications
     */
    #[Route('/{id_animal}/update', name: 'app_animal_update', methods: ['POST'])]
    public function update(Request $request, Animal $animal, EntityManagerInterface $entityManager): Response
    {
        $this->saveAnimalData($animal, $request, $entityManager);
        
        $this->addFlash('success', 'Animal mis à jour !');
        return $this->redirectToRoute('app_animal_index');
    }

    /**
     * Suppression d'un animal
     */
    #[Route('/delete/{id_animal}', name: 'app_animal_delete', methods: ['POST'])]
    public function delete(Request $request, Animal $animal, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $animal->getIdAnimal(), $request->request->get('_token'))) {
            $entityManager->remove($animal);
            $entityManager->flush();
            $this->addFlash('danger', 'Animal supprimé.');
        }

        return $this->redirectToRoute('app_animal_index');
    }

    /**
     * Logique d'enregistrement centralisée (Gestion des dates et des IDs)
     */
    private function saveAnimalData(Animal $animal, Request $request, EntityManagerInterface $em): void
    {
        $animal->setEspece($request->request->get('espece'));
        $animal->setEtatSante($request->request->get('etat_sante'));
        
        if ($dateString = $request->request->get('date_naissance')) {
            try {
                $animal->setDateNaissance(new \DateTime($dateString));
            } catch (\Exception $e) {
                // En cas de format de date invalide, on peut gérer l'erreur ici
            }
        }
        
        $animal->setIdFerme((int)$request->request->get('id_ferme'));

        $em->persist($animal);
        $em->flush();
    }
}