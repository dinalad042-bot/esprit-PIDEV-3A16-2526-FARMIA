<?php

namespace App\Controller\Web;

use App\Entity\Conseil;
use App\Enum\Priorite;
use App\Form\ConseilType;
use App\Repository\ConseilRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/expert')]
#[IsGranted('ROLE_EXPERT')]
class ExpertConseilController extends AbstractController
{
    public function __construct(
        private ConseilRepository $conseilRepo,
        private EntityManagerInterface $em,
    ) {}

    #[Route('/conseils', name: 'expert_conseils_list')]
    public function list(Request $request): Response
    {
        $user = $this->getUser();
        $search = $request->query->get('search', '');
        $priorite = $request->query->get('priorite', '');

        // Fetch conseils related to analyses where the expert is the technicien
        $conseils = $this->conseilRepo->findByExpert($user->getId(), $search, $priorite);

        return $this->render('portal/expert/conseils.html.twig', [
            'conseils' => $conseils,
            'search' => $search,
            'priorite' => $priorite,
            'priorites' => Priorite::cases(),
        ]);
    }

    #[Route('/conseil/{id}', name: 'expert_conseil_show', requirements: ['id' => '\d+'])]
    public function show(Conseil $conseil): Response
    {
        // Security check: ensure the expert is the technicien for the related analysis
        if ($conseil->getAnalyse()->getTechnicien() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à voir ce conseil.');
        }

        return $this->render('portal/expert/conseil_show.html.twig', [
            'conseil' => $conseil,
        ]);
    }

    #[Route('/conseil/new', name: 'expert_conseil_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $conseil = new Conseil();
        $form = $this->createForm(ConseilType::class, $conseil);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($conseil);
            $this->em->flush();
            $this->addFlash('success', 'Conseil créé avec succès.');
            return $this->redirectToRoute('expert_conseils_list');
        }

        return $this->render('portal/expert/conseil_new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
