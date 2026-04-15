<?php
namespace App\Controller\Admin;

use App\Entity\UserLog;
use App\Repository\UserLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/audit')]
#[IsGranted('ROLE_ADMIN')]
class AuditController extends AbstractController
{
    #[Route('/', name: 'admin_audit_logs')]
    public function index(UserLogRepository $userLogRepository): Response
    {
        $logs = $userLogRepository->findBy([], ['timestamp' => 'DESC']);

        return $this->render('admin/audit/index.html.twig', [
            'logs' => $logs
        ]);
    }

    #[Route('/data', name: 'admin_audit_logs_data')]
    public function data(UserLogRepository $userLogRepository): JsonResponse
    {
        $logs = $userLogRepository->findBy([], ['timestamp' => 'DESC']);
        $formattedLogs = [];

        foreach ($logs as $log) {
            $formattedLogs[] = [
                'id' => $log->getId(),
                'action' => $log->getActionType(),
                'color' => $log->getActionType() === 'CREATE' ? 'green' : ($log->getActionType() === 'DELETE' ? 'red' : 'orange'),
                'email' => $log->getPerformedBy() ? $log->getPerformedBy()->getEmail() : 'System/Guest',
                'target' => $log->getUser() ? $log->getUser()->getEmail() : 'N/A',
                'date' => $log->getTimestamp() ? $log->getTimestamp()->format('d/m/Y') : '',
                'time' => $log->getTimestamp() ? $log->getTimestamp()->format('H:i:s') : '',
                'detail' => $log->getDescription(),
            ];
        }

        return new JsonResponse(['logs' => $formattedLogs]);
    }
}
