<?php

namespace App\Controller\Web;

use App\Repository\UserRepository;
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
        ValidatorInterface $validator,
        UserRepository $userRepository
    ): Response {
        /** @var \App\Entity\User $user */
        $user   = $this->getUser();
        $errors = [];

        // ── Collect & sanitize inputs ────────────────────────────────────────
        $nom     = trim((string) $request->request->get('nom'));
        $prenom  = trim((string) $request->request->get('prenom'));
        $email   = strtolower(trim((string) $request->request->get('email')));
        $cin     = str_replace(' ', '', trim((string) $request->request->get('cin')));
        $tel     = str_replace(' ', '', trim((string) $request->request->get('telephone')));
        $adresse = trim((string) $request->request->get('adresse'));
        $password = $request->request->get('password');

        // ── Field-level validation ───────────────────────────────────────────
        if (empty($nom))     $errors['nom']     = 'Le nom est obligatoire.';
        if (empty($prenom))  $errors['prenom']  = 'Le prénom est obligatoire.';
        if (empty($adresse)) $errors['adresse'] = "L'adresse est obligatoire.";

        if (empty($email)) {
            $errors['email'] = "L'email est obligatoire.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Veuillez saisir une adresse email valide.';
        } else {
            $existing = $userRepository->findOneBy(['email' => $email]);
            if ($existing && $existing->getId() !== $user->getId()) {
                $errors['email'] = 'Cet email est déjà utilisé par un autre compte.';
            }
        }

        if (empty($cin)) {
            $errors['cin'] = 'Le CIN est obligatoire.';
        } elseif (!preg_match('/^\d{8}$/', $cin)) {
            $errors['cin'] = 'Le CIN doit contenir exactement 8 chiffres.';
        } else {
            $existing = $userRepository->findOneBy(['cin' => $cin]);
            if ($existing && $existing->getId() !== $user->getId()) {
                $errors['cin'] = 'Ce CIN est déjà utilisé par un autre compte.';
            }
        }

        if (empty($tel)) {
            $errors['telephone'] = 'Le téléphone est obligatoire.';
        } elseif (!preg_match('/^\d{8}$/', $tel)) {
            $errors['telephone'] = 'Le téléphone doit contenir exactement 8 chiffres.';
        } else {
            $existing = $userRepository->findOneBy(['telephone' => $tel]);
            if ($existing && $existing->getId() !== $user->getId()) {
                $errors['telephone'] = 'Ce numéro est déjà utilisé par un autre compte.';
            }
        }

        if (!empty($password) && strlen($password) < 6) {
            $errors['password'] = 'Le mot de passe doit contenir au moins 6 caractères.';
        }

        if (!empty($errors)) {
            return new JsonResponse(['success' => false, 'errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        // ── Apply values to entity ───────────────────────────────────────────
        $user->setNom($nom);
        $user->setPrenom($prenom);
        $user->setEmail($email);
        $user->setCin($cin);
        $user->setTelephone($tel);
        $user->setAdresse($adresse);

        if (!empty($password)) {
            $user->setPassword($passwordHasher->hashPassword($user, $password));
        }

        // Role (Admin only)
        if ($this->isGranted('ROLE_ADMIN') && $request->request->get('role')) {
            $user->setRole(str_replace('ROLE_', '', $request->request->get('role')));
        }

        // ── Photo upload ─────────────────────────────────────────────────────
        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $photo */
        $photo = $request->files->get('photo');
        if ($photo) {
            $imageConstraints = new Assert\Image([
                'maxSize'          => '5M',
                'mimeTypes'        => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
                'mimeTypesMessage' => 'Veuillez uploader une image valide (JPEG, PNG, GIF, WEBP).',
            ]);
            $photoViolations = $validator->validate($photo, $imageConstraints);

            if (count($photoViolations) > 0) {
                $photoErrors = [];
                foreach ($photoViolations as $v) { $photoErrors[] = $v->getMessage(); }
                return new JsonResponse(['success' => false, 'errors' => ['photo' => implode(' ', $photoErrors)]], Response::HTTP_BAD_REQUEST);
            }

            $destination = $this->getParameter('kernel.project_dir') . '/public/uploads/avatars';
            if (!file_exists($destination)) { mkdir($destination, 0777, true); }
            $newFilename = uniqid() . '.' . $photo->guessExtension();
            try {
                $photo->move($destination, $newFilename);
                $user->setImageUrl('uploads/avatars/' . $newFilename);
            } catch (\Exception $e) {
                return new JsonResponse(['success' => false, 'errors' => ['photo' => "Erreur lors de l'upload de la photo."]], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        $em->flush();

        $this->addFlash('success', 'Profil mis à jour avec succès.');
        return new JsonResponse(['success' => true]);
    }
}
