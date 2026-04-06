<?php

namespace App\Controller;

use App\Entity\Ferme;
use App\Repository\FermeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/ferme')]
class FermeController extends AbstractController
{
    #[Route('/', name: 'app_ferme_index', methods: ['GET'])]
    public function index(FermeRepository $repo): Response
    {
        return $this->render('ferme/index.html.twig', [
            'fermes' => $repo->findAll(),
            'ferme_edit' => null,
            'errors' => []
        ]);
    }

    #[Route('/new', name: 'app_ferme_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $em, ValidatorInterface $validator, FermeRepository $repo): Response
    {
        $ferme = new Ferme();
        $this->mapData($ferme, $request);

        $violations = $validator->validate($ferme);

        if (count($violations) > 0) {
            return $this->renderWithErrors($violations, $repo, null);
        }

        $em->persist($ferme);
        $em->flush();
        
        $this->addFlash('success', 'Ferme créée avec succès !');
        return $this->redirectToRoute('app_ferme_index');
    }

    #[Route('/{id_ferme}/edit', name: 'app_ferme_edit', methods: ['GET'])]
    public function edit(Ferme $ferme, FermeRepository $repo): Response
    {
        return $this->render('ferme/index.html.twig', [
            'fermes' => $repo->findAll(),
            'ferme_edit' => $ferme,
            'errors' => []
        ]);
    }

    #[Route('/{id_ferme}/update', name: 'app_ferme_update', methods: ['POST'])]
    public function update(Request $request, Ferme $ferme, EntityManagerInterface $em, ValidatorInterface $validator, FermeRepository $repo): Response
    {
        $this->mapData($ferme, $request);
        $violations = $validator->validate($ferme);

        if (count($violations) > 0) {
            return $this->renderWithErrors($violations, $repo, $ferme);
        }

        $em->flush();
        $this->addFlash('success', 'Ferme mise à jour !');
        return $this->redirectToRoute('app_ferme_index');
    }

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

    private function mapData(Ferme $ferme, Request $request): void
    {
        $ferme->setNomFerme($request->request->get('nom_ferme') ?: null);
        $ferme->setLieu($request->request->get('lieu') ?: null);
        $surface = $request->request->get('surface');
        $ferme->setSurface($surface !== "" ? (float)$surface : null);
    }

    private function renderWithErrors($violations, $repo, $ferme_edit): Response
    {
        $errors = [];
        foreach ($violations as $v) {
            $errors[$v->getPropertyPath()] = $v->getMessage();
        }
        return $this->render('ferme/index.html.twig', [
            'fermes' => $repo->findAll(),
            'ferme_edit' => $ferme_edit,
            'errors' => $errors
        ]);
    }
}