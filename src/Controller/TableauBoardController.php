<?php
// src/Controller/DashboardController.php

namespace App\Controller;

use App\Repository\TicketRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TableauBoardController extends AbstractController
{
    #[Route('/interventions', name: 'app_interventions_dashboard')]
    public function index(Request $request, TicketRepository $repo): Response
    {
        // Récupération des filtres depuis la requête GET
        $dateDebut = $request->query->get('dateDebut') 
            ? new \DateTime($request->query->get('dateDebut')) 
            : null;

        $dateFin = $request->query->get('dateFin') 
            ? new \DateTime($request->query->get('dateFin')) 
            : null;

        $statut = $request->query->get('statut');

        // Appel au repository avec les filtres
        $tickets = $repo->findByFilters($dateDebut, $dateFin, $statut);

        // Affichage dans le tableau de bord
        return $this->render('dashboard/tableau_de bord_intervention.html.twig', [
            'tickets'   => $tickets,
            'dateDebut' => $dateDebut,
            'dateFin'   => $dateFin,
            'statut'    => $statut,
        ]);
    }
}
