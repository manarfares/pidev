<?php

namespace App\Entity;

use App\Repository\ParticipantRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ParticipantRepository::class)]
#[ORM\Table(
    name: 'participant',
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: 'unique_participation', columns: ['passager_id', 'covoiturage_id']),
    ],
)]
class Participant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'passager_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?User $passager = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'covoiturage_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Covoiturage $covoiturage = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPassager(): ?User
    {
        return $this->passager;
    }

    public function setPassager(?User $passager): static
    {
        $this->passager = $passager;

        return $this;
    }

    public function getCovoiturage(): ?Covoiturage
    {
        return $this->covoiturage;
    }

    public function setCovoiturage(?Covoiturage $covoiturage): static
    {
        $this->covoiturage = $covoiturage;

        return $this;
    }
}
