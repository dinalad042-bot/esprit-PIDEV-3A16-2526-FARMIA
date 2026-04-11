<?php

namespace App\Controller\Web;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
class ProfileController extends AbstractController
{
    #[Route('/profile/update', name: 'app_profile_update', methods: ['POST'])]
    public function updateProfile(
        Request $request, 
        EntityManagerInterface $em, 
        UserPasswordHasherInterface $passwordHasher, 
        ValidatorInterface $validator
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if (!$request->request->get('agreeTerms')) {
            return new JsonResponse(['success' => false, 'errors' => ['Vous devez accepter les conditions d\'utilisation pour modifier votre profil.']], Response::HTTP_BAD_REQUEST);
        }

        // Base fields
        $user->setNom($request->request->get('nom'));
        $user->setPrenom($request->request->get('prenom'));
        $user->setCin($request->request->get('cin'));
        $user->setTelephone($request->request->get('telephone'));
        $user->setAdresse($request->request->get('adresse'));
        $user->setEmail($request->request->get('email'));

        // Handle Password update if provided
        $newPassword = $request->request->get('password');
        if (!empty($newPassword)) {
            if (strlen($newPassword) < 6) {
                return new JsonResponse(['success' => false, 'errors' => ['Le mot de passe doit contenir au moins 6 caractères.']], Response::HTTP_BAD_REQUEST);
            }
            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);
        }

        // Rôle (Protégé : seul l'Admin peut le modifier)
        if ($this->isGranted('ROLE_ADMIN') && $request->request->get('role')) {
            $user->setRole(str_replace('ROLE_', '', $request->request->get('role')));
        }

        // Photo file validation
        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $photo */
        $photo = $request->files->get('photo');
        if ($photo) {
            $imageConstraints = new Assert\Image([
                'maxSize' => '5M',
                'mimeTypes' => [
                    'image/jpeg',
                    'image/png',
                    'image/gif',
                    'image/webp'
                ],
                'mimeTypesMessage' => 'Veuillez uploader une image valide (JPEG, PNG, GIF, WEBP).',
            ]);
            $photoViolations = $validator->validate($photo, $imageConstraints);
            
            if (count($photoViolations) > 0) {
                $errors = [];
                foreach ($photoViolations as $violation) {
                    $errors[] = $violation->getMessage();
                }
                return new JsonResponse(['success' => false, 'errors' => $errors], Response::HTTP_BAD_REQUEST);
            }

            $destination = $this->getParameter('kernel.project_dir') . '/public/uploads/avatars';
            if (!file_exists($destination)) {
                mkdir($destination, 0777, true);
            }
            $newFilename = uniqid() . '.' . $photo->guessExtension();
            try {
                $photo->move($destination, $newFilename);
                $user->setImageUrl('uploads/avatars/' . $newFilename);
            } catch (\Exception $e) {
                return new JsonResponse(['success' => false, 'errors' => ['Erreur lors de l\'upload de la photo.']], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        // Validate entity constraints
        $violations = $validator->validate($user);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }
            return new JsonResponse(['success' => false, 'errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        $em->flush();

        $this->addFlash('success', 'Profil mis à jour avec succès.');
        return new JsonResponse(['success' => true]);
    }
}
