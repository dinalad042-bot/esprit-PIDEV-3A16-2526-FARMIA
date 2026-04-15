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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Dompdf\Dompdf;
use Dompdf\Options;

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

    #[Route('/export/pdf', name: 'admin_users_export_pdf', methods: ['GET'])]
    public function exportPdf(Request $request, UserRepository $userRepository): Response
    {
        $users = $this->getFilteredUsers($request, $userRepository);
        $html = $this->renderView('admin/users/export_pdf.html.twig', [
            'users' => $users
        ]);

        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $pdfContent = $dompdf->output();

        return new Response(
            $pdfContent,
            200,
            [
                'Content-Type'          => 'application/pdf',
                'Content-Disposition'   => 'attachment; filename="utilisateurs_farmia.pdf"'
            ]
        );
    }

    #[Route('/export/excel', name: 'admin_users_export_excel', methods: ['GET'])]
    public function exportExcel(Request $request, UserRepository $userRepository): Response
    {
        $users = $this->getFilteredUsers($request, $userRepository);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'Nom');
        $sheet->setCellValue('B1', 'Prénom');
        $sheet->setCellValue('C1', 'Email');
        $sheet->setCellValue('D1', 'CIN');
        $sheet->setCellValue('E1', 'Téléphone');
        $sheet->setCellValue('F1', 'Adresse');
        $sheet->setCellValue('G1', 'Rôle');

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF43A047']]
        ];
        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
        
        $row = 2;
        foreach ($users as $user) {
            $sheet->setCellValue('A' . $row, $user->getNom());
            $sheet->setCellValue('B' . $row, $user->getPrenom());
            $sheet->setCellValue('C' . $row, $user->getEmail());
            $sheet->setCellValueExplicit('D' . $row, $user->getCin(), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('E' . $row, $user->getTelephone(), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('F' . $row, $user->getAdresse());
            $sheet->setCellValue('G' . $row, strtoupper(str_replace('ROLE_', '', $user->getRole() ?? 'USER')));
            $row++;
        }

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $temp_file = tempnam(sys_get_temp_dir(), 'excel');
        $writer->save($temp_file);

        return $this->file($temp_file, 'utilisateurs_farmia.xlsx', ResponseHeaderBag::DISPOSITION_INLINE);
    }

    private function getFilteredUsers(Request $request, UserRepository $userRepository): array
    {
        $search = strtolower($request->query->get('search', ''));
        $role = strtoupper($request->query->get('role', ''));
        $users = $userRepository->findAll();

        if ($search || $role) {
            $users = array_filter($users, function(User $u) use ($search, $role) {
                $rawRole = strtoupper(str_replace('ROLE_', '', $u->getRole() ?? 'USER'));
                $matchName = false;
                $fullName = strtolower((string)$u->getPrenom() . ' ' . (string)$u->getNom());
                if (!$search || str_contains($fullName, $search) || str_contains(strtolower((string)$u->getEmail()), $search)) {
                    $matchName = true;
                }
                $matchRole = false;
                if (!$role || $rawRole === $role) {
                    $matchRole = true;
                }
                return $matchName && $matchRole;
            });
        }
        return $users;
    }
}
