<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\FermeRepository; // IMPORTANT : Ne pas oublier cet import
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
    public function index(UserRepository $userRepository, Request $request): Response
    {
        $role = $request->query->get('role');
        
        if ($role) {
            $cleanRole = str_replace('ROLE_', '', $role);

            $users = $userRepository->createQueryBuilder('u')
                ->where('u.role LIKE :role')
                ->orWhere('u.role LIKE :cleanRole')
                ->setParameter('role', '%' . $role . '%')
                ->setParameter('cleanRole', '%' . $cleanRole . '%')
                ->getQuery()
                ->getResult();
        } else {
            $users = $userRepository->findAll();
        }

        $form = $this->createForm(UserAdminType::class);

        return $this->render('admin/users/index.html.twig', [
            'users' => $users,
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
            }

            if ($isNew) {
                $em->persist($user);
            }
            
            $em->flush();

            $action = $isNew ? 'CREATE' : 'UPDATE';
            $userLogService->log($user, $action, ($isNew ? 'User registered: ' : 'User updated: ') . $user->getEmail());

            $this->addFlash('success', 'Utilisateur enregistré avec succès.');
            return $this->redirectToRoute('admin_users_index');
        }

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
            try {
                $userLogService->log($user, 'DELETE', 'User deleted: ' . $user->getEmail());
                $em->remove($user);
                $em->flush();
                $this->addFlash('success', 'Utilisateur supprimé avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Impossible de supprimer cet utilisateur (il possède des logs actifs).');
            }
        }
        return $this->redirectToRoute('admin_users_index');
    }

    /**
     * MÉTHODE CORRIGÉE POUR LA CARTE
     */
    #[Route('/agriculteur/{id}/map', name: 'admin_agriculteur_map', methods: ['GET'])]
    public function viewMap(User $user, FermeRepository $fermeRepo): Response
    {
        // 1. On vérifie le rôle (on accepte "AGRICOLE" ou "ROLE_AGRICOLE")
        $role = is_array($user->getRoles()) ? implode(',', $user->getRoles()) : $user->getRole();
        
        if (!str_contains($role, 'AGRICOLE')) {
            $this->addFlash('error', "Cet utilisateur n'est pas un agriculteur.");
            return $this->redirectToRoute('admin_users_index');
        }

        // 2. On récupère les fermes liées à cet utilisateur précis
        // On suppose que dans ton entité Ferme, la propriété s'appelle 'user'
        $fermes = $fermeRepo->findBy(['user' => $user]);

        // 3. On envoie les données à la vue
        return $this->render('admin/users/map.html.twig', [
            'user' => $user,
            'fermes' => $fermes, // La variable est maintenant définie
        ]);
    }
}