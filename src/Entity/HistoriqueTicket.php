<?php

namespace App\Entity;

use App\Repository\HistoriqueTicketRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HistoriqueTicketRepository::class)]
class HistoriqueTicket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Ticket::class, inversedBy: 'historiques')]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Ticket $ticket = null;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Users $utilisateur = null;

    #[ORM\Column(length: 255)]
    private ?string $action = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $details = null;

    #[ORM\Column(type: "datetime")]
    private ?\DateTimeInterface $dateAction = null;

    public function __construct()
    {
        $this->dateAction = new \DateTime();
    }

    // ---- Getters & Setters ----

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTicket(): ?Ticket
    {
        return $this->ticket;
    }

    public function setTicket(?Ticket $ticket): static
    {
        $this->ticket = $ticket;
        return $this;
    }

    public function getUtilisateur(): ?Users
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Users $utilisateur): static
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action): static
    {
        $this->action = $action;
        return $this;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(?string $details): static
    {
        $this->details = $details;
        return $this;
    }

    public function getDateAction(): ?\DateTimeInterface
    {
        return $this->dateAction;
    }

    public function setDateAction(\DateTimeInterface $dateAction): static
    {
        $this->dateAction = $dateAction;
        return $this;
    }
}
