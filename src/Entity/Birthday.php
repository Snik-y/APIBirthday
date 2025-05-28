<?php

namespace App\Entity;

use App\Repository\BirthdayRepository;
use Doctrine\ORM\Mapping as ORM;
/*use App\Entity\User;*/
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
/**
 * @ORM\Entity(repositoryClass=BirthdayRepository::class)
 */
class Birthday
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
    private $name;

    /**
     * @ORM\Column(type="date_immutable")
     */
    #[JMS\SerializedName("birthdate")]
    private $date;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): self
    {
        $this->date = $date;

        return $this;
    }
    //*
    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="birthdays")
     * @ORM\JoinColumn(nullable=false)
     
    private $user;

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }
    */
}