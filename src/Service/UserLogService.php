<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\UserLog;
use App\Repository\UserLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class UserLogService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserLogRepository $userLogRepository,
        private readonly Security $security,
    ) {}

    /**
     * Record an action in user_log.
     *
     * @param string $action  e.g. SIGNUP, LOGIN, CREATE, UPDATE, DELETE
     * @param string $statut  e.g. SUCCESS, FAILURE
     */
    public function log(User $user, string $action, string $statut = 'SUCCESS'): void
    {
        $log = new UserLog();
        $log->setUser($user);
        $log->setActionType($action);
        $log->setTimestamp(new \DateTime());
        $log->setDescription($statut);

        $admin = $this->security->getUser();
        if ($admin instanceof User) {
            $log->setPerformedBy($admin);
        }

        $this->em->persist($log);
        $this->em->flush();
    }

    /**
     * Retrieve all logs for a given user.
     */
    public function getLogsForUser(int $userId): array
    {
        return $this->userLogRepository->findByUserId($userId);
    }
}
