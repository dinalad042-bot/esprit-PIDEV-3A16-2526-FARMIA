<?php

namespace App\Controller;

use App\Entity\Plante;
use App\Repository\PlanteRepository;
use App\Repository\FermeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/plante')]
class PlanteController extends AbstractController
{
    /**
     * Affiche la liste des plantes et le formulaire d'ajout
     */
    #[Route('/', name: 'app_plante_index', methods: ['GET'])]
    public function index(PlanteRepository $pRepo, FermeRepository $fRepo): Response
    {
        return $this->render('plante/index.html.twig', [
            'plantes' => $pRepo->findAll(),
            'fermes' => $fRepo->findAll(),
            'plante_edit' => null
        ]);
    }

    /**
     * Crée une nouvelle plante
     */
    #[Route('/new', name: 'app_plante_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $plante = new Plante();
        $this->saveData($plante, $request, $em);
        
        $this->addFlash('success', 'Plante ajoutée avec succès !');
        return $this->redirectToRoute('app_plante_index');
    }

    /**
     * Charge une plante dans le formulaire pour modification
     */
    #[Route('/{id_plante}/edit', name: 'app_plante_edit', methods: ['GET'])]
    public function edit(Plante $plante, PlanteRepository $pRepo, FermeRepository $fRepo): Response
    {
        return $this->render('plante/index.html.twig', [
            'plantes' => $pRepo->findAll(),
            'fermes' => $fRepo->findAll(),
            'plante_edit' => $plante
        ]);
    }

    /**
     * Enregistre les modifications d'une plante
     */
    #[Route('/{id_plante}/update', name: 'app_plante_update', methods: ['POST'])]
    public function update(Request $request, Plante $plante, EntityManagerInterface $em): Response
    {
        $this->saveData($plante, $request, $em);
        
        $this->addFlash('success', 'Plante mise à jour !');
        return $this->redirectToRoute('app_plante_index');
    }

    /**
     * Supprime une plante
     */
    #[Route('/delete/{id_plante}', name: 'app_plante_delete', methods: ['POST'])]
    public function delete(Request $request, Plante $plante, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$plante->getIdPlante(), $request->request->get('_token'))) {
            $em->remove($plante);
            $em->flush();
            $this->addFlash('danger', 'Plante supprimée.');
        }
        
        return $this->redirectToRoute('app_plante_index');
    }

    /**
     * Méthode privée pour centraliser l'enregistrement des données
     */
    private function saveData(Plante $plante, Request $request, EntityManagerInterface $em): void
    {
        $plante->setNomEspece($request->request->get('nom_espece'));
        $plante->setCycleVie($request->request->get('cycle_vie'));
        $plante->setQuantite((int)$request->request->get('quantite'));
        $plante->setIdFerme((int)$request->request->get('id_ferme'));
        
        $em->persist($plante);
        $em->flush();
    }
}