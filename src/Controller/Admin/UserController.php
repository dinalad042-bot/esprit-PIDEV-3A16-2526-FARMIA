<?php
namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserLogService;
use App\Form\UserAdminType;

#[Route('/admin/users')]
#[IsGranted('ROLE_ADMIN')]
class UserController extends AbstractController
{
    #[Route('/', name: 'admin_users_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        $form = $this->createForm(UserAdminType::class);
        return $this->render('admin/users/index.html.twig', [
            'users' => $userRepository->findAll(),
            'form'  => $form->createView(),
            'open_modal' => false,
        ]);
    }

    #[Route('/save', name: 'admin_users_save', methods: ['POST'])]
    public function save(Request $request, UserRepository $userRepository, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, UserLogService $userLogService): Response
    {
        $id = $request->request->get('id');
        $isNew = !$id;
        $user = $id ? $userRepository->find($id) : new User();

        if (!$isNew && !$user) {
            $this->addFlash('error', 'Utilisateur introuvable.');
            return $this->redirectToRoute('admin_users_index');
        }

        $form = $this->createForm(UserAdminType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword) {
                $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            } elseif ($isNew) {
                $this->addFlash('error', 'Mot de passe obligatoire pour un nouvel utilisateur.');
                return $this->render('admin/users/index.html.twig', [
                    'users' => $userRepository->findAll(),
                    'form'  => $form->createView(),
                    'open_modal' => true,
                    'is_edit'    => false,
                    'user_id'    => null,
                ]);
            }

            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $photo */
            $photo = $request->files->get('photo');
            if ($photo) {
                $destination = $this->getParameter('kernel.project_dir') . '/public/uploads/avatars';
                if (!file_exists($destination)) { mkdir($destination, 0777, true); }
                $newFilename = uniqid() . '.' . $photo->guessExtension();
                try {
                    $photo->move($destination, $newFilename);
                    $user->setImageUrl('uploads/avatars/' . $newFilename);
                } catch (\Exception $e) {}
            }

            if ($isNew) {
                $em->persist($user);
            }
            $em->flush();

            // Enregistrer dans l'audit log
            $action = $isNew ? 'CREATE' : 'UPDATE';
            $description = $isNew ? 'User registered: ' . $user->getEmail() : 'User updated: ' . $user->getEmail();
            $userLogService->log($user, $action, $description);

            $this->addFlash('success', 'Utilisateur enregistré avec succès.');
            return $this->redirectToRoute('admin_users_index');
        }

        // Validation failed
        return $this->render('admin/users/index.html.twig', [
            'users' => $userRepository->findAll(),
            'form'  => $form->createView(),
            'open_modal' => true,
            'is_edit'    => !$isNew,
            'user_id'    => $id,
        ]);
    }

    #[Route('/delete/{id}', name: 'admin_users_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $em, UserLogService $userLogService): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            
            // Enregistrer dans l'audit log avant de supprimer
            $userLogService->log($user, 'DELETE', 'User deleted: ' . $user->getEmail());
            
            $em->remove($user);
            $em->flush();
            $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        }

        return $this->redirectToRoute('admin_users_index');
    }
}
