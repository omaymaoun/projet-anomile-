<?php

namespace App\Repository;

use App\Entity\HistoriqueTicket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class HistoriqueTicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HistoriqueTicket::class);
    }

    // Ajoute tes méthodes personnalisées ici si nécessaire
}
