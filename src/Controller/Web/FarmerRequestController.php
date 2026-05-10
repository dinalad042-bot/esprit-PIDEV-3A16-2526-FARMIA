<?php

namespace App\Controller\Web;

use App\Entity\Analyse;
use App\Entity\Animal;
use App\Entity\Ferme;
use App\Entity\Plante;
use App\Repository\AnalyseRepository;
use App\Repository\AnimalRepository;
use App\Repository\PlanteRepository;
use Doctrine\ORM\EntityManagerInterface;
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
        private SluggerInterface $slugger,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/nouvelle-demande', name: 'farmer_new_request')]
    public function newRequest(Request $request): Response
    {
        $user = $this->getUser();
        $fermes = $user->getFermes();

        if ($fermes->isEmpty()) {
            $this->addFlash('warning', 'Vous devez d\'abord créer une ferme avant de faire une demande d\'analyse.');
            return $this->redirectToRoute('app_ferme_index');
        }

        // Handle farm selection from POST or use first farm as default
        $fermeId = $request->request->get('ferme');
        $ferme = null;

        if ($fermeId) {
            // Find the selected farm and validate it belongs to user
            $ferme = $fermes->filter(fn($f) => $f->getIdFerme() == $fermeId)->first();
        }

        // If no valid farm selected, use first farm
        if (!$ferme) {
            $ferme = $fermes->first();
        }

        // Force load the farm with its collections using DQL
        $farmId = $ferme->getIdFerme();

        // Direct query using repository to get animals and plants
        $conn = $this->entityManager->getConnection();
        $stmt = $conn->prepare("SELECT id_animal, espece, etat_sante, date_naissance, id_ferme FROM animal WHERE id_ferme = ?");
        $animalResult = $stmt->executeQuery([$farmId])->fetchAllAssociative();

        $stmt = $conn->prepare("SELECT id_plante, nom_espece, cycle_vie, quantite, id_ferme FROM plante WHERE id_ferme = ?");
        $planteResult = $stmt->executeQuery([$farmId])->fetchAllAssociative();

        // Build animal objects manually
        $animals = [];
        foreach ($animalResult as $row) {
            $animal = new \App\Entity\Animal();
            $animal->setId($row['id_animal']);
            $animal->setEspece($row['espece']);
            $animal->setEtatSante($row['etat_sante']);
            $animals[] = $animal;
        }

        // Build plante objects manually
        $plantes = [];
        foreach ($planteResult as $row) {
            $plante = new \App\Entity\Plante();
            $plante->setId($row['id_plante']);
            $plante->setNomEspece($row['nom_espece']);
            $plante->setCycleVie($row['cycle_vie']);
            $plante->setQuantite($row['quantite']);
            $plantes[] = $plante;
        }

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
            'fermes' => $fermes,
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
