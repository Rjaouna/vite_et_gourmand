<?php

namespace App\Controller;

use App\Repository\OpeningHourRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class OpeningHoursController extends AbstractController
{
    #[Route('/opening-hours', name: 'app_opening_hours_json', methods: ['GET'])]
    public function openingHours(OpeningHourRepository $repo): JsonResponse
    {
        $hours = $repo->findBy([], ['dayOfWeek' => 'ASC']); // 1..7

        $days = [
            1 => 'Lundi',
            2 => 'Mardi',
            3 => 'Mercredi',
            4 => 'Jeudi',
            5 => 'Vendredi',
            6 => 'Samedi',
            7 => 'Dimanche',
        ];

        $data = [];
        foreach ($hours as $h) {
            $open  = $h->getOpenTime();
            $close = $h->getCloseTime();

            $data[] = [
                'day' => $h->getDayOfWeek(),
                'label' => $days[$h->getDayOfWeek()] ?? ('Jour ' . $h->getDayOfWeek()),
                'closed' => (bool) $h->isClosed(),
                'open' => $open ? $open->format('H:i') : null,
                'close' => $close ? $close->format('H:i') : null,
            ];
        }

        // ✅ ici tu peux utiliser le helper json() d'AbstractController
        return $this->json([
            'ok' => true,
            'items' => $data,
        ]);
    }
}
