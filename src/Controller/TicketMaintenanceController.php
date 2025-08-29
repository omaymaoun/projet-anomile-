<?php
namespace App\Controller;

use App\Entity\Ticket;
use App\Form\TicketMaintenanceType;
use App\Repository\TicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TicketMaintenanceController extends AbstractController
{
    #[Route('/ticket/{id}/criticite', name: 'ticket_modifier_criticite')]
    public function modifierCriticite(
        int $id,
        TicketRepository $repo,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $ticket = $repo->find($id);

        if (!$ticket) {
            throw $this->createNotFoundException("Ticket non trouvé");
        }

        $form = $this->createForm(TicketMaintenanceType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Criticité du ticket mise à jour.');
            return $this->redirectToRoute('app_ticket_index'); // ta route d’affichage
        }

        return $this->render('ticket/modifier_criticite.html.twig', [
            'form' => $form->createView(),
            'ticket' => $ticket,
        ]);
    }
}
