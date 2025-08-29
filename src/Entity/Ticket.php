<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Technicien;
use App\Entity\NoteIntervention;

#[ORM\Entity]
class Ticket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le titre est obligatoire.")]
    #[Assert\Length(
        min: 5,
        max: 255,
        minMessage: "Le titre doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le titre ne peut pas dépasser {{ limit }} caractères."
    )]
    private string $titre;

    #[ORM\Column(type: "text")]
    #[Assert\NotBlank(message: "La description est obligatoire.")]
    #[Assert\Length(min: 10, minMessage: "La description doit contenir au moins {{ limit }} caractères.")]
    private string $description;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "La localisation est obligatoire.")]
    #[Assert\Length(max: 255, maxMessage: "La localisation ne peut pas dépasser {{ limit }} caractères.")]
    #[Assert\Regex(
        pattern: '/^Bâtiment\s+[A-Za-z0-9]+,\s+Salle\s+\d+$/',
        message: 'La localisation doit être au format "Bâtiment X, Salle Y".'
    )]
    private ?string $localisation = null;

    #[ORM\Column(type: "string", length: 20)]
    #[Assert\Choice(
        choices: ['en attente', 'en cours', 'terminé'],
        message: "Le statut doit être 'en attente', 'en cours' ou 'terminé'."
    )]
    private string $statut = 'en attente';
    

    #[ORM\Column(type: "datetime")]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\ManyToOne(targetEntity: Technicien::class, inversedBy: "tickets")]
    #[ORM\JoinColumn(nullable: true)]
    private ?Technicien $technicien = null;

    #[ORM\OneToMany(mappedBy: "ticket", targetEntity: NoteIntervention::class, orphanRemoval: true)]
    private Collection $notesIntervention;

    public function __construct()
    {
        $this->date = new \DateTimeImmutable();
        $this->notesIntervention = new ArrayCollection();
    }

    // --- Getters & Setters ---

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
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

    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    public function setLocalisation(string $localisation): self
    {
        $this->localisation = $localisation;
        return $this;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTimeInterface $dateFin): self
    {
        $this->dateFin = $dateFin;

        // Si on met une date de fin, on met automatiquement le statut à "terminé"
        if ($dateFin !== null) {
            $this->statut = 'terminé';
        }

        return $this;
    }

    public function getTechnicien(): ?Technicien
    {
        return $this->technicien;
    }

    public function setTechnicien(?Technicien $technicien): self
    {
        $this->technicien = $technicien;

        // Ne change le statut que s’il n’est pas déjà "terminé"
        if ($this->statut !== 'terminé') {
            if ($technicien !== null) {
                $this->statut = 'en cours';
            } else {
                $this->statut = 'en attente';
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, NoteIntervention>
     */
    public function getNotesIntervention(): Collection
    {
        return $this->notesIntervention;
    }

    public function addNoteIntervention(NoteIntervention $note): self
    {
        if (!$this->notesIntervention->contains($note)) {
            $this->notesIntervention[] = $note;
            $note->setTicket($this);
        }

        return $this;
    }

    public function removeNoteIntervention(NoteIntervention $note): self
    {
        if ($this->notesIntervention->removeElement($note)) {
            if ($note->getTicket() === $this) {
                $note->setTicket(null);
            }
        }

        return $this;
    }



#[ORM\Column(type: 'string', enumType: null, length: 50)]
private ?string $categorie = null;

public function getCategorie(): ?string
{
    return $this->categorie;
}

public function setCategorie(string $categorie): self
{
    $this->categorie = $categorie;

    return $this;
}



#[ORM\Column(type: 'string', length: 20, nullable: true)]
private ?string $criticite = 'Mineure';

public function getCriticite(): ?string
{
    return $this->criticite;
}

public function setCriticite(?string $criticite): self
{
    $this->criticite = $criticite;
    return $this;
}






    
}

