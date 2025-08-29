<?php

namespace App\Controller;
use App\Service\HistoriqueService;
use App\Entity\Ticket;
use App\Form\TicketType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use App\Form\AssignTechnicienType;
use App\Entity\Technicien;
use App\Repository\TicketRepository;
use App\Repository\HistoriqueTicketRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;






#[Route('/ticket')]
final class TicketController extends AbstractController
{
   #[Route(name: 'app_ticket_index', methods: ['GET'])]
public function index(Request $request, EntityManagerInterface $entityManager): Response
{
    // Récupérer la page depuis la query string, par défaut 1
    $page = max(1, $request->query->getInt('page', 1));
    $limit = 10; // nombre d'éléments par page

    // Construire la requête paginée
    $query = $entityManager->createQuery('SELECT t FROM App\Entity\Ticket t ORDER BY t.id DESC')
        ->setFirstResult(($page - 1) * $limit)
        ->setMaxResults($limit);

    // Utiliser le Paginator Doctrine pour compter le total
    $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query);
    $totalItems = count($paginator);
    $totalPages = (int) ceil($totalItems / $limit);

    // Récupérer les tickets de la page courante
    $tickets = iterator_to_array($paginator);

    return $this->render('ticket/index.html.twig', [
        'tickets' => $tickets,
        'currentPage' => $page,
        'totalPages' => $totalPages,
    ]);
}

#[Route('/new', name: 'app_ticket_new', methods: ['GET', 'POST'])]
public function new(
    Request $request,
    EntityManagerInterface $em,
    SluggerInterface $slugger,
    HistoriqueService $historiqueService
): Response {
    $ticket = new Ticket();
    $form = $this->createForm(TicketType::class, $ticket);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Gestion de la photo
        $photo = $form->get('photo')->getData();
        if ($photo) {
            $originalFilename = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$photo->guessExtension();

            $photo->move(
                $this->getParameter('photos_directory'),
                $newFilename
            );

            $ticket->setPhoto($newFilename);
        }

        // Sauvegarde du ticket
        $em->persist($ticket);
        $em->flush();

        // Ajouter l’historique (avec Users au lieu de Utilisateur)
        $historiqueService->ajouterHistorique(
            $ticket,
            $this->getUser(), // retourne un objet Users
            'Création Ticket',
            'Ticket créé par l’utilisateur'
        );

        $this->addFlash('success', 'Ticket ajouté avec succès !');

        return $this->redirectToRoute('app_ticket_index');
    }

    return $this->render('ticket/new.html.twig', [
        'form' => $form->createView(),
    ]);
}





    #[Route('/delete/{id}', name: 'ticket_delete', methods: ['POST'])]
    public function delete(?Ticket $ticket, EntityManagerInterface $em): Response
    {
        if (!$ticket) {
            throw $this->createNotFoundException('Ticket non trouvé');
        }

        $em->remove($ticket);
        $em->flush();

        // rediriger vers la liste des tickets
        return $this->redirectToRoute('app_ticket_index');
    }





  #[Route('/assign/{technicienId}/{ticketId}', name: 'app_technicien_assign', methods: ['GET'])]
public function assignTechnicienToTicket(
    EntityManagerInterface $em,
    HistoriqueService $historiqueService,
    int $technicienId,
    int $ticketId
): Response
{
    $technicien = $em->getRepository(Technicien::class)->find($technicienId);
    $ticket = $em->getRepository(Ticket::class)->find($ticketId);

    if (!$technicien || !$ticket) {
        throw $this->createNotFoundException('Technicien ou Ticket non trouvé');
    }

    // Assignation du technicien
    $ticket->setTechnicien($technicien);

    // Historique pour l'affectation
    $historiqueService->ajouterHistorique(
        $ticket,
        $this->getUser(), // utilisateur qui fait l'affectation
        'Affectation Ticket',
        'Technicien ' . $technicien->getNom() . ' assigné au ticket'
    );

    // Changement de statut
    $ticket->setStatut('en cours');

    // Historique pour le changement de statut
    $historiqueService->ajouterHistorique(
        $ticket,
        $this->getUser(),
        'Changement de statut',
        'Statut changé à "en cours" après affectation'
    );

    $em->flush();

    $this->addFlash('success', 'Technicien assigné et statut mis à jour avec succès.');

    return $this->redirectToRoute('app_ticket_index');
}






#[Route('/ticket/{id}/intervention', name: 'ticket_intervention')]
public function intervention(Request $request, Ticket $ticket, SluggerInterface $slugger, EntityManagerInterface $em): Response
{
    $form = $this->createForm(TicketInterventionType::class, $ticket);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

        $file = $form->get('photoApresReparation')->getData();

        if ($file) {
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

            try {
                $file->move(
                    $this->getParameter('intervention_photos_directory'),
                    $newFilename
                );
                $ticket->setPhotoApresReparation($newFilename);
            } catch (FileException $e) {
                $this->addFlash('error', 'Erreur lors du téléchargement de la photo.');
            }
        }

        $ticket->setStatut('résolu');
        $em->flush();

        $this->addFlash('success', 'Intervention enregistrée et ticket résolu.');
        return $this->redirectToRoute('ticket_list'); // ou autre route
    }

    return $this->render('ticket/intervention.html.twig', [
        'form' => $form->createView(),
        'ticket' => $ticket,
    ]);
}



#[Route('/tickets-techniciens', name: 'app_ticket_techniciens', methods: ['GET'])]
public function ticketsAvecTechniciens(TicketRepository $ticketRepository): Response
{
    $tickets = $ticketRepository->findAllWithTechnicien(); // Méthode personnalisée avec jointure

    return $this->render('ticket/tickets_techniciens.html.twig', [
        'tickets' => $tickets,
    ]);
}



///////////////////////////////////
#[Route('/tickets/historique', name: 'app_ticket_history')]
public function historique(HistoriqueTicketRepository $historiqueRepository): Response
{
    // Récupération de tous les historiques
    $historiques = $historiqueRepository->findAll();

    return $this->render('ticket/historique.html.twig', [
        'historiques' => $historiques,
    ]);
}





 
   


















    




     

}
