<?php

namespace App\Entity;

use App\Repository\InscriptionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=InscriptionRepository::class)
 */
class Inscription
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Etudiant::class, inversedBy="inscriptions")
     */
    private $etudiant;

    /**
     * @ORM\ManyToOne(targetEntity=Filiere::class, inversedBy="inscriptions")
     */
    private $filiere;

    /**
     * @ORM\ManyToOne(targetEntity=Niveau::class, inversedBy="inscriptions")
     */
    private $niveau;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity=NotesEtudiant::class, mappedBy="inscription")
     */
    private $notesEtudiants;

    public function __construct()
    {
        $this->notesEtudiants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEtudiant(): ?Etudiant
    {
        return $this->etudiant;
    }

    public function setEtudiant(?Etudiant $etudiant): self
    {
        $this->etudiant = $etudiant;

        return $this;
    }

    public function getFiliere(): ?Filiere
    {
        return $this->filiere;
    }

    public function setFiliere(?Filiere $filiere): self
    {
        $this->filiere = $filiere;

        return $this;
    }

    public function getNiveau(): ?Niveau
    {
        return $this->niveau;
    }

    public function setNiveau(?Niveau $niveau): self
    {
        $this->niveau = $niveau;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection|NotesEtudiant[]
     */
    public function getNotesEtudiants(): Collection
    {
        return $this->notesEtudiants;
    }

    public function addNotesEtudiant(NotesEtudiant $notesEtudiant): self
    {
        if (!$this->notesEtudiants->contains($notesEtudiant)) {
            $this->notesEtudiants[] = $notesEtudiant;
            $notesEtudiant->setInscription($this);
        }

        return $this;
    }

    public function removeNotesEtudiant(NotesEtudiant $notesEtudiant): self
    {
        if ($this->notesEtudiants->removeElement($notesEtudiant)) {
            // set the owning side to null (unless already changed)
            if ($notesEtudiant->getInscription() === $this) {
                $notesEtudiant->setInscription(null);
            }
        }

        return $this;
    }
}
