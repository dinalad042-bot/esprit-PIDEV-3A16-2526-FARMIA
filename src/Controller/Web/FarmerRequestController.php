<?php

namespace App\Controller\Web;

use App\Entity\Analyse;
use App\Repository\AnalyseRepository;
use App\Repository\AnimalRepository;
use App\Repository\PlanteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/agricole')]
#[IsGranted('ROLE_AGRICOLE')]
class FarmerRequestController extends AbstractController
{
    public function __construct(
        private AnalyseRepository $analyseRepo,
        private AnimalRepository $animalRepo,
        private PlanteRepository $planteRepo,
        private SluggerInterface $slugger
    ) {}

    #[Route('/nouvelle-demande', name: 'farmer_new_request')]
    public function newRequest(Request $request): Response
    {
        $user = $this->getUser();
        $ferme = $user->getFermes()->first();

        if (!$ferme) {
            $this->addFlash('warning', 'Vous devez d\'abord créer une ferme avant de faire une demande d\'analyse.');
            return $this->redirectToRoute('app_ferme_new');
        }

        // Get animals and plants from the farmer's farm
        $animals = $this->animalRepo->findByFerme($ferme->getId());
        $plantes = $this->planteRepo->findByFerme($ferme->getId());

        if ($request->isMethod('POST')) {
            $description = $request->request->get('description');
            $animalId = $request->request->get('animal');
            $planteId = $request->request->get('plante');

            $analyse = new Analyse();
            $analyse->setDemandeur($user);
            $analyse->setFerme($ferme);
            $analyse->setDescriptionDemande($description);
            $analyse->setStatut('en_attente');

            // Set target animal or plant
            if ($animalId) {
                $animal = $this->animalRepo->find($animalId);
                if ($animal && $animal->getFerme() === $ferme) {
                    $analyse->setAnimalCible($animal);
                }
            }

            if ($planteId) {
                $plante = $this->planteRepo->find($planteId);
                if ($plante && $plante->getFerme() === $ferme) {
                    $analyse->setPlanteCible($plante);
                }
            }

            // Handle image upload
            /** @var UploadedFile $imageFile */
            $imageFile = $request->files->get('image');
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/analyses',
                        $newFilename
                    );
                    $analyse->setImageUrl('/uploads/analyses/' . $newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
                }
            }

            $this->analyseRepo->save($analyse, true);

            $this->addFlash('success', 'Votre demande d\'analyse a été soumise avec succès. Un expert la prendra en charge bientôt.');
            return $this->redirectToRoute('farmer_my_requests');
        }

        return $this->render('portal/agricole/new_request.html.twig', [
            'animals' => $animals,
            'plantes' => $plantes,
            'ferme' => $ferme,
        ]);
    }

    #[Route('/mes-demandes', name: 'farmer_my_requests')]
    public function myRequests(): Response
    {
        $user = $this->getUser();
        $requests = $this->analyseRepo->findByDemandeur($user->getId());

        return $this->render('portal/agricole/my_requests.html.twig', [
            'requests' => $requests,
        ]);
    }
}
