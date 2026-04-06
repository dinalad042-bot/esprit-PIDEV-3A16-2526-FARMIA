<?php

namespace App\Controller;

use App\Entity\Plante;
use App\Entity\Ferme;
use App\Repository\PlanteRepository;
use App\Repository\FermeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/plante')]
class PlanteController extends AbstractController
{
    /**
     * Affiche la liste des plantes et le formulaire d'ajout (vierge)
     */
    #[Route('/', name: 'app_plante_index', methods: ['GET'])]
    public function index(PlanteRepository $pRepo, FermeRepository $fRepo): Response
    {
        return $this->render('plante/index.html.twig', [
            'plantes' => $pRepo->findAll(),
            'fermes' => $fRepo->findAll(),
            'plante_edit' => null,
            'errors' => [] 
        ]);
    }

    /**
     * Crée une nouvelle plante avec contrôle de saisie serveur
     */
    #[Route('/new', name: 'app_plante_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $em, ValidatorInterface $validator, PlanteRepository $pRepo, FermeRepository $fRepo): Response
    {
        $plante = new Plante();
        
        // Extraction et sécurisation des données
        $nomEspece = $request->request->get('nom_espece');
        $cycleVie = $request->request->get('cycle_vie');
        $quantiteRaw = $request->request->get('quantite');
        $idFermeRaw = $request->request->get('id_ferme');

        // Mapping vers l'objet (accepte null grâce aux modifs de l'entité)
        $plante->setNomEspece($nomEspece !== "" ? $nomEspece : null);
        $plante->setCycleVie($cycleVie !== "" ? $cycleVie : null);
        $plante->setQuantite($quantiteRaw !== "" ? (int)$quantiteRaw : null);
        $plante->setIdFerme($idFermeRaw !== "" ? (int)$idFermeRaw : null);

        // Validation via les Assert de l'Entité
        $violations = $validator->validate($plante);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }

            return $this->render('plante/index.html.twig', [
                'plantes' => $pRepo->findAll(),
                'fermes' => $fRepo->findAll(),
                'plante_edit' => null,
                'errors' => $errors
            ]);
        }

        $em->persist($plante);
        $em->flush();
        
        $this->addFlash('success', 'Plante ajoutée avec succès !');
        return $this->redirectToRoute('app_plante_index');
    }

    /**
     * Charge une plante existante dans le formulaire pour modification
     */
    #[Route('/{id_plante}/edit', name: 'app_plante_edit', methods: ['GET'])]
    public function edit(Plante $plante, PlanteRepository $pRepo, FermeRepository $fRepo): Response
    {
        return $this->render('plante/index.html.twig', [
            'plantes' => $pRepo->findAll(),
            'fermes' => $fRepo->findAll(),
            'plante_edit' => $plante,
            'errors' => []
        ]);
    }

    /**
     * Enregistre les modifications avec contrôle de saisie serveur
     */
    #[Route('/{id_plante}/update', name: 'app_plante_update', methods: ['POST'])]
    public function update(Request $request, Plante $plante, EntityManagerInterface $em, ValidatorInterface $validator, PlanteRepository $pRepo, FermeRepository $fRepo): Response
    {
        $nomEspece = $request->request->get('nom_espece');
        $cycleVie = $request->request->get('cycle_vie');
        $quantiteRaw = $request->request->get('quantite');
        $idFermeRaw = $request->request->get('id_ferme');

        $plante->setNomEspece($nomEspece !== "" ? $nomEspece : null);
        $plante->setCycleVie($cycleVie !== "" ? $cycleVie : null);
        $plante->setQuantite($quantiteRaw !== "" ? (int)$quantiteRaw : null);
        $plante->setIdFerme($idFermeRaw !== "" ? (int)$idFermeRaw : null);

        $violations = $validator->validate($plante);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }

            return $this->render('plante/index.html.twig', [
                'plantes' => $pRepo->findAll(),
                'fermes' => $fRepo->findAll(),
                'plante_edit' => $plante, // On garde l'objet en cours d'édition
                'errors' => $errors
            ]);
        }

        $em->flush();
        
        $this->addFlash('success', 'Plante mise à jour avec succès !');
        return $this->redirectToRoute('app_plante_index');
    }

    /**
     * Supprime une plante via le bouton poubelle
     */
    #[Route('/delete/{id_plante}', name: 'app_plante_delete', methods: ['POST'])]
    public function delete(Request $request, Plante $plante, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$plante->getIdPlante(), $request->request->get('_token'))) {
            $em->remove($plante);
            $em->flush();
            $this->addFlash('danger', 'Plante supprimée de l\'exploitation.');
        }
        
        return $this->redirectToRoute('app_plante_index');
    }
}