<?php

namespace App\Controller\Web;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProfileController extends AbstractController
{
    #[Route('/profile/update', name: 'app_profile_update', methods: ['POST'])]
    public function updateProfile(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException();
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
            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);
        }

        // Rôle (Protégé : seul l'Admin peut le modifier)
        if ($this->isGranted('ROLE_ADMIN') && $request->request->get('role')) {
            $user->setRole(str_replace('ROLE_', '', $request->request->get('role')));
        }

        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $photo */
        $photo = $request->files->get('photo');
        if ($photo) {
            $destination = $this->getParameter('kernel.project_dir') . '/public/uploads/avatars';
            if (!file_exists($destination)) {
                mkdir($destination, 0777, true);
            }
            $newFilename = uniqid() . '.' . $photo->guessExtension();
            try {
                $photo->move($destination, $newFilename);
                $user->setImageUrl('uploads/avatars/' . $newFilename);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de l\'upload de la photo.');
            }
        }

        $em->flush();

        $this->addFlash('success', 'Profil mis à jour avec succès.');
        return $this->redirect($request->headers->get('referer'));
    }
}
