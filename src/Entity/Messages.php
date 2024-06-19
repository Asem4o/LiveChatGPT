<?php

namespace App\Entity;

use App\Repository\MessageRepository;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Messages
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $room = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;


    #[ORM\Column(type: 'text')]
    private ?string $message = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $timestamp = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoom(): ?string
    {
        return $this->room;
    }

    public function setRoom(string $room): self
    {
        $this->room = $room;
        return $this;
    }


    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function getTimestamp(): ?\DateTimeInterface
    {
        return $this->timestamp;
    }

    public function setTimestamp(\DateTimeInterface $timestamp): self
    {
        $this->timestamp = $timestamp;
        return $this;
    }
}
