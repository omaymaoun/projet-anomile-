<?php

namespace App\Repository;

use App\Entity\Ticket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);
    }

    /**
     * Récupère tous les tickets avec leur technicien associé (jointure LEFT JOIN)
     *
     * @return Ticket[]
     */
    public function findAllWithTechnicien(): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.technicien', 'tech')
            ->addSelect('tech')
            ->orderBy('t.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Temps moyen de traitement en heures
     *
     * @return float|null
     */
    public function getTempsMoyenTraitement(): ?float
{
    $conn = $this->getEntityManager()->getConnection();

    $sql = 'SELECT AVG(TIMESTAMPDIFF(HOUR, date, datefin)) as avg_time FROM ticket WHERE datefin IS NOT NULL';
    $stmt = $conn->prepare($sql);
    $result = $stmt->executeQuery();

    return (float) $result->fetchOne();
}

    /**
     * Nombre de pannes par localisation
     *
     * @return array
     */
    public function getPannesParLieu(): array
    {
        return $this->createQueryBuilder('t')
            ->select('t.localisation as lieu, COUNT(t.id) as total')
            ->groupBy('t.localisation')
            ->getQuery()
            ->getResult();
    }

    /**
     * Top 5 causes de pannes (si la colonne "cause" existe dans Ticket)
     *
     * @return array
     */
   /* public function getTop5Causes(): array
    {
        return $this->createQueryBuilder('t')
           ->select('t.cause as cause, COUNT(t.id) as total')
            ->groupBy('t.cause')
            ->orderBy('total', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();
    }*/

/**
 * Nombre de tickets par statut
 *
 * @return array
 */
public function getTicketsParStatut(): array
{
    return $this->createQueryBuilder('t')
        ->select('t.statut as statut, COUNT(t.id) as total')
        ->groupBy('t.statut')
        ->getQuery()
        ->getResult();
}




  // src/Repository/TicketRepository.php

public function findByFilters(?\DateTime $dateDebut = null, ?\DateTime $dateFin = null, ?string $statut = null): array
{
    $qb = $this->createQueryBuilder('t');

    // Filtre par date de début (date)
    if ($dateDebut) {
        $qb->andWhere('t.date >= :dateDebut')
           ->setParameter('dateDebut', $dateDebut->format('Y-m-d 00:00:00'));
    }

    // Filtre par date de fin (dateFin)
    if ($dateFin) {
        $qb->andWhere('t.dateFin <= :dateFin')
           ->setParameter('dateFin', $dateFin->format('Y-m-d 23:59:59'));
    }

    // Filtre par statut
    if ($statut) {
        $qb->andWhere('t.statut = :statut')
           ->setParameter('statut', $statut);
    }

    return $qb->getQuery()->getResult();
}







}
