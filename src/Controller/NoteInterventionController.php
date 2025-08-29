<?php

namespace App\Controller;
use App\Entity\HistoriqueIntervention;
use App\Entity\NoteIntervention;
use App\Entity\Ticket;
use App\Entity\Technicien;
use App\Form\NoteInterventionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\NoteInterventionRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Security;
use App\Service\HistoriqueService;



class NoteInterventionController extends AbstractController

{
   #[Route('/note-intervention/new/{id}', name: 'note_intervention_new', methods: ['GET', 'POST'])]
public function addNoteIntervention(
    Ticket $ticket,
    Request $request,
    EntityManagerInterface $entityManager,
    HistoriqueService $historiqueService
): Response {
    $note = new NoteIntervention();

    $form = $this->createForm(NoteInterventionType::class, $note);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Associer le ticket à la note
        $note->setTicket($ticket);

        // Récupérer l'utilisateur connecté
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException("Aucun utilisateur connecté.");
        }

        // Trouver le technicien associé à cet utilisateur
        $technicien = $entityManager->getRepository(Technicien::class)->findOneBy(['user' => $user]);
        if (!$technicien) {
            throw $this->createNotFoundException("Technicien non trouvé pour l'utilisateur connecté.");
        }
        $note->setTechnicien($technicien);

        // Changer le statut du ticket en "terminé"
         $ticket->setDateFin(new \DateTime());
        $ticket->setStatut('terminé');
        $entityManager->persist($ticket);

        // Définir la date actuelle
        $note->setDateIntervention(new \DateTime());

        // Persister la note d'intervention
        $entityManager->persist($note);

        // **Ajouter l'action dans l'historique**
        $historiqueService->ajouterHistorique(
            $ticket,
            $user,
            'Clôture Ticket / Note d\'intervention',
            'Ticket clôturé et note ajoutée par le technicien ' . $technicien->getNom()
        );

        // Sauvegarder en base
        $entityManager->flush();

        $this->addFlash('success', 'Note d\'intervention ajoutée et ticket terminé.');

        return $this->redirectToRoute('app_technicien_mes_tickets');
    }

    return $this->render('note_intervention/new.html.twig', [
        'form' => $form->createView(),
        'ticket' => $ticket,
    ]);
}




   #[Route('/notes', name: 'note_intervention_index')]
public function index(NoteInterventionRepository $repo): Response
{
    $notes = $repo->findAll();
    return $this->render('note_intervention/show.html.twig', [
        'notes' => $notes,
    ]);
}



#[Route('/note/{id}/delete', name: 'note_delete', methods: ['POST'])]
public function delete(Request $request, ?NoteIntervention $note, EntityManagerInterface $em): RedirectResponse
{
    if (!$note) {
        $this->addFlash('error', 'Note d\'intervention introuvable.');
        return $this->redirectToRoute('note_intervention_index');
    }

    if ($this->isCsrfTokenValid('delete' . $note->getId(), $request->request->get('_token'))) {
        $em->remove($note);
        $em->flush();
        $this->addFlash('success', 'Note supprimée avec succès.');
    } else {
        $this->addFlash('error', 'Jeton CSRF invalide.');
    }

    return $this->redirectToRoute('note_intervention_index');
}




   


#[Route('/intervention/{id}/terminer', name: 'intervention_terminer', methods: ['POST'])]
    public function terminerIntervention(Request $request, EntityManagerInterface $em, int $id): Response
    {
        $intervention = $em->getRepository(NoteIntervention::class)->find($id);

        if (!$intervention) {
            throw $this->createNotFoundException("Intervention non trouvée");
        }

        // Changer le statut de l'intervention
        $intervention->setStatut('terminée');
        $em->persist($intervention);

        // Créer un nouvel historique
        $historique = new HistoriqueIntervention();
        $historique->setIntervention($intervention);
        $historique->setUtilisateur($this->getUser());
        $historique->setDateDebut($intervention->getDateDebut());
        $historique->setDateFin(new \DateTime());
        $historique->setDescription("Intervention terminée avec succès.");
        $historique->setStatut('terminée');
        
        // Calculer la durée de l'intervention (méthode dans l'entité)
        $historique->calculerDuree();

        $em->persist($historique);
        $em->flush();

        $this->addFlash('success', 'Intervention terminée et ajoutée à l’historique.');

        return $this->redirectToRoute('historique_intervention_index');






    }












}


