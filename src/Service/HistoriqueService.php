<?php

namespace App\Service;

use App\Entity\HistoriqueTicket;
use App\Entity\Ticket;
use App\Entity\Users; // On utilise bien Users
use Doctrine\ORM\EntityManagerInterface;

class HistoriqueService
{
    public function __construct(private EntityManagerInterface $em) {}

    /**
     * Ajouter une action dans l’historique
     */
    public function ajouterHistorique(Ticket $ticket, Users $utilisateur, string $action, ?string $details = null): void
    {
        $historique = new HistoriqueTicket();
        $historique->setTicket($ticket);
        $historique->setUtilisateur($utilisateur); // Assurez-vous que setUtilisateur attend bien un Users
        $historique->setAction($action);
        $historique->setDetails($details);

         $historique->setDateAction(new \DateTime());

        $this->em->persist($historique);
        $this->em->flush();
    }

    /**
     * Récupérer l’historique d’un ticket (du plus récent au plus ancien)
     */
    public function getHistoriqueParTicket(Ticket $ticket): array
    {
        return $this->em->getRepository(HistoriqueTicket::class)
            ->findBy(['ticket' => $ticket], ['dateAction' => 'DESC']);
    }

    /**
     * Supprimer une entrée de l’historique
     */
    public function supprimerHistorique(HistoriqueTicket $historique): void
    {
        $this->em->remove($historique);
        $this->em->flush();
    }
}
