<?php

namespace App\Controller;

use App\Entity\Ferme;
use App\Repository\FermeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ferme')]
class FermeController extends AbstractController
{
    /**
     * Affiche la liste des fermes et le formulaire d'ajout
     */
    #[Route('/', name: 'app_ferme_index', methods: ['GET'])]
    public function index(FermeRepository $repo): Response
    {
        return $this->render('ferme/index.html.twig', [
            'fermes' => $repo->findAll(),
            'ferme_edit' => null
        ]);
    }

    /**
     * Création d'une nouvelle ferme
     */
    #[Route('/new', name: 'app_ferme_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $ferme = new Ferme();
        $this->saveData($ferme, $request, $em);
        
        $this->addFlash('success', 'Ferme créée avec succès !');
        return $this->redirectToRoute('app_ferme_index');
    }

    /**
     * Charge une ferme pour modification
     */
    #[Route('/{id_ferme}/edit', name: 'app_ferme_edit', methods: ['GET'])]
    public function edit(Ferme $ferme, FermeRepository $repo): Response
    {
        return $this->render('ferme/index.html.twig', [
            'fermes' => $repo->findAll(),
            'ferme_edit' => $ferme
        ]);
    }

    /**
     * Enregistre les modifications d'une ferme
     */
    #[Route('/{id_ferme}/update', name: 'app_ferme_update', methods: ['POST'])]
    public function update(Request $request, Ferme $ferme, EntityManagerInterface $em): Response
    {
        $this->saveData($ferme, $request, $em);
        
        $this->addFlash('success', 'Ferme mise à jour !');
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
            $this->addFlash('danger', 'Ferme supprimée.');
        }
        
        return $this->redirectToRoute('app_ferme_index');
    }

    /**
     * Logique d'enregistrement centralisée
     */
    private function saveData(Ferme $ferme, Request $request, EntityManagerInterface $em): void
    {
        $ferme->setNomFerme($request->request->get('nom_ferme'));
        $ferme->setLieu($request->request->get('lieu'));
        $ferme->setSurface((float)$request->request->get('surface'));
        
        $em->persist($ferme);
        $em->flush();
    }
}