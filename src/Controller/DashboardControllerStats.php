<?php

namespace App\Controller;

use App\Repository\TicketRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardControllerStats extends AbstractController
{
    #[Route('/statistiques', name: 'app_statistiques')]
    public function statistiques(TicketRepository $ticketRepo): Response
    {
        $tickets = $ticketRepo->findAll();

        // --- 1️⃣ Temps moyen de traitement ---
        $totalMinutes = 0;
        $count = 0;
        foreach ($tickets as $ticket) {
            $dateDebut = $ticket->getDate();
            $dateFin = $ticket->getDateFin();
            if ($dateDebut && $dateFin) {
                $interval = $dateDebut->diff($dateFin);
                $minutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
                $totalMinutes += $minutes;
                $count++;
            }
        }
        $tempsMoyenMinutes = $count > 0 ? $totalMinutes / $count : 0;
        $heures = floor($tempsMoyenMinutes / 60);
        $minutes = round($tempsMoyenMinutes % 60);

        // --- 2️⃣ Tickets par statut ---
        $ticketsParStatut = $ticketRepo->getTicketsParStatut();
        $labelsStatut = [];
        $dataStatut = [];
        foreach ($ticketsParStatut as $item) {
            $labelsStatut[] = $item['statut'];
            $dataStatut[] = $item['total'];
        }

        // --- 3️⃣ Tickets par localisation ---
        $ticketsParLieu = $ticketRepo->getPannesParLieu();
        $labelsLieu = [];
        $dataLieu = [];
        foreach ($ticketsParLieu as $item) {
            $labelsLieu[] = $item['lieu'];
            $dataLieu[] = $item['total'];
        }

        return $this->render('dashboard/statistiques.html.twig', [
            'heures' => $heures,
            'minutes' => $minutes,
            'labelsStatut' => $labelsStatut,
            'dataStatut' => $dataStatut,
            'labelsLieu' => $labelsLieu,
            'dataLieu' => $dataLieu,
        ]);
    }
}
