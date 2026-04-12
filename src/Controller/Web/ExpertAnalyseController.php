<?php

namespace App\Controller\Web;

use App\Entity\Analyse;
use App\Repository\AnalyseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/expert')]
#[IsGranted('ROLE_EXPERT')]
class ExpertAnalyseController extends AbstractController
{
    public function __construct(
        private AnalyseRepository $analyseRepo
    ) {}

    #[Route('/analyses', name: 'expert_analyses_list')]
    public function list(Request $request): Response
    {
        $user = $this->getUser();
        $search = $request->query->get('search', '');

        // Fetch analyses where the expert is the technicien
        if ($search) {
            $analyses = $this->analyseRepo->searchByTechnicien($user->getId(), $search);
        } else {
            $analyses = $this->analyseRepo->findByTechnicienId($user->getId());
        }

        return $this->render('portal/expert/analyses.html.twig', [
            'analyses' => $analyses,
            'search' => $search,
        ]);
    }

    #[Route('/analyse/{id}', name: 'expert_analyse_show', requirements: ['id' => '\d+'])]
    public function show(Analyse $analyse): Response
    {
        // Security check: ensure the expert is the technicien for this analysis
        if ($analyse->getTechnicien() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à voir cette analyse.');
        }

        return $this->render('portal/expert/analyse_show.html.twig', [
            'analyse' => $analyse,
        ]);
    }

    #[Route('/demandes-en-attente', name: 'expert_pending_requests')]
    public function pendingRequests(): Response
    {
        $pendingRequests = $this->analyseRepo->findPendingRequests();

        return $this->render('portal/expert/pending_requests.html.twig', [
            'requests' => $pendingRequests,
        ]);
    }

    #[Route('/demande/{id}/prendre-en-charge', name: 'expert_take_request', requirements: ['id' => '\d+'])]
    public function takeRequest(Analyse $analyse): Response
    {
        // Check if request is still pending
        if ($analyse->getStatut() !== 'en_attente') {
            $this->addFlash('error', 'Cette demande a déjà été prise en charge.');
            return $this->redirectToRoute('expert_pending_requests');
        }

        // Assign the expert to this analysis
        $analyse->setTechnicien($this->getUser());
        $analyse->setStatut('en_cours');

        $this->analyseRepo->save($analyse, true);

        $this->addFlash('success', 'Demande prise en charge avec succès.');
        return $this->redirectToRoute('expert_analyse_show', ['id' => $analyse->getId()]);
    }
}
