<?php

namespace App\Entity;

use App\Repository\SemestreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SemestreRepository::class)
 */
class Semestre
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\OneToMany(targetEntity=Ue::class, mappedBy="semestre")
     */
    private $ues;

    /**
     * @ORM\OneToMany(targetEntity=NotesEtudiant::class, mappedBy="semestre")
     */
    private $notesEtudiants;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="semestres")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function __construct()
    {
        $this->ues = new ArrayCollection();
        $this->notesEtudiants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * @return Collection|Ue[]
     */
    public function getUes(): Collection
    {
        return $this->ues;
    }

    public function addUe(Ue $ue): self
    {
        if (!$this->ues->contains($ue)) {
            $this->ues[] = $ue;
            $ue->setSemestre($this);
        }

        return $this;
    }

    public function removeUe(Ue $ue): self
    {
        if ($this->ues->removeElement($ue)) {
            // set the owning side to null (unless already changed)
            if ($ue->getSemestre() === $this) {
                $ue->setSemestre(null);
            }
        }

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
            $notesEtudiant->setSemestre($this);
        }

        return $this;
    }

    public function removeNotesEtudiant(NotesEtudiant $notesEtudiant): self
    {
        if ($this->notesEtudiants->removeElement($notesEtudiant)) {
            // set the owning side to null (unless already changed)
            if ($notesEtudiant->getSemestre() === $this) {
                $notesEtudiant->setSemestre(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

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
}
