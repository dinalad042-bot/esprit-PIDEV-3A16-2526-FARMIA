<?php

namespace App\Service;

use App\Entity\Analyse;
use App\Entity\Notification;
use App\Entity\User;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;

class NotificationService
{
    public function __construct(
        private EntityManagerInterface $em,
        private NotificationRepository $notificationRepository
    ) {}

    /**
     * Create a notification for a user
     */
    public function createNotification(
        User $user,
        string $message,
        string $type = 'info',
        ?string $link = null
    ): Notification {
        $notification = new Notification();
        $notification->setUser($user);
        $notification->setMessage($message);
        $notification->setType($type);
        $notification->setLink($link);

        $this->em->persist($notification);
        $this->em->flush();

        return $notification;
    }

    /**
     * Notify farmer when a new diagnosis is completed
     */
    public function notifyFarmerOfDiagnosis(Analyse $analyse, string $diagnosisMode): Notification
    {
        $farmer = $analyse->getDemandeur();
        $ferme = $analyse->getFerme();
        
        $modeLabel = $diagnosisMode === 'VISION' ? 'visuel' : 'textuel';
        $message = sprintf(
            'Une nouvelle analyse IA (%s) a été effectuée pour votre ferme "%s". Confiance: %s',
            $modeLabel,
            $ferme ? $ferme->getNomFerme() : 'Inconnue',
            $analyse->getAiConfidenceScore() ?? 'N/A'
        );

        $link = '/agricole/analyse/' . $analyse->getId();

        return $this->createNotification($farmer, $message, 'diagnosis', $link);
    }

    /**
     * Notify expert when a new analysis request is created
     */
    public function notifyExpertsOfNewRequest(Analyse $analyse): void
    {
        // Find all users with ROLE_EXPERT
        $experts = $this->em->getRepository(User::class)->findByRole('ROLE_EXPERT');
        
        $ferme = $analyse->getFerme();
        $message = sprintf(
            'Nouvelle demande d\'analyse pour la ferme "%s"',
            $ferme ? $ferme->getNomFerme() : 'Inconnue'
        );

        $link = '/expert/demandes-en-attente';

        foreach ($experts as $expert) {
            $this->createNotification($expert, $message, 'request', $link);
        }
    }

    /**
     * Get unread notifications for a user
     */
    public function getUnreadNotifications(User $user, ?int $limit = null): array
    {
        return $this->notificationRepository->findUnreadByUser($user, $limit);
    }

    /**
     * Count unread notifications for a user
     */
    public function countUnreadNotifications(User $user): int
    {
        return $this->notificationRepository->countUnreadByUser($user);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead(Notification $notification): void
    {
        $notification->markAsRead();
        $this->em->flush();
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead(User $user): void
    {
        $this->notificationRepository->markAllAsReadForUser($user);
    }
}
