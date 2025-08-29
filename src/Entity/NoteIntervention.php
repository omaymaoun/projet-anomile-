<?php

namespace App\Entity;

use App\Repository\NoteInterventionRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Technicien;
use App\Entity\Ticket;

#[ORM\Entity(repositoryClass: NoteInterventionRepository::class)]
class NoteIntervention
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type:"integer")]
    private ?int $id = null;

    #[ORM\Column(type:"text")]
    private string $contenu;

    #[ORM\Column(type:"string", length:255, nullable:true)]
    private ?string $photo = null;

    #[ORM\Column(type:"datetime")]
    private \DateTimeInterface $dateIntervention;

    #[ORM\ManyToOne(targetEntity: Technicien::class, inversedBy: "notesIntervention")]
    #[ORM\JoinColumn(nullable:false)]
   private ?Technicien $technicien = null;

    #[ORM\ManyToOne(targetEntity: Ticket::class, inversedBy: "notesIntervention")]
    #[ORM\JoinColumn(nullable:false)]
    private ?Ticket $ticket = null;

    // --- Getters et setters ---

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): self
    {
        $this->contenu = $contenu;
        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;
        return $this;
    }

    public function getDateIntervention(): ?\DateTimeInterface
    {
        return $this->dateIntervention;
    }

    public function setDateIntervention(\DateTimeInterface $dateIntervention): self
    {
        $this->dateIntervention = $dateIntervention;
        return $this;
    }

    public function getTechnicien(): ?Technicien
    {
        return $this->technicien;
    }

    public function setTechnicien(Technicien $technicien): self
    {
        $this->technicien = $technicien;
        return $this;
    }

    public function getTicket(): ?Ticket
    {
        return $this->ticket;
    }

    public function setTicket(Ticket $ticket): self
    {
        $this->ticket = $ticket;
        return $this;
    }
}
