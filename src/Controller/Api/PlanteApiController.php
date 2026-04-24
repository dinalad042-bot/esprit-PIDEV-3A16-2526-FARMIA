<?php

namespace App\Controller\Api;

use App\Entity\Plante;
use App\Entity\Arrosage;
use App\Repository\ArrosageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlanteApiController extends AbstractController
{
    #[Route('/api/plante/{id}/arrosage/{date}', name: 'api_toggle_arrosage', methods: ['POST'])]
    public function toggleArrosage(Plante $plante, string $date, ArrosageRepository $arrosageRepo, EntityManagerInterface $em): Response
    {
        $dateObj = new \DateTime($date);
        
        // On vérifie si un arrosage existe déjà pour cette plante à cette date
        $arrosage = $arrosageRepo->findOneBy([
            'plante' => $plante,
            'dateArrosage' => $dateObj
        ]);

        if ($arrosage) {
            $em->remove($arrosage);
            $message = "Arrosage supprimé";
        } else {
            $newArrosage = new Arrosage();
            $newArrosage->setPlante($plante);
            $newArrosage->setDateArrosage($dateObj);
            $em->persist($newArrosage);
            $message = "Arrosage enregistré";
        }

        $em->flush();
        return $this->json(['message' => $message], 200);
    }

    #[Route('/api/plante/{id}/recolte', name: 'api_mark_recolte', methods: ['PATCH'])]
    public function markRecolte(Plante $plante, EntityManagerInterface $em): Response
    {
        // On suppose que ton entité Plante a un champ 'statut'
        $plante->setStatut('Récoltée');
        $em->flush();

        return $this->json(['message' => 'Plante marquée comme récoltée'], 200);
    }
}