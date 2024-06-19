<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Notification::class, orphanRemoval: true)]
    private $notifications;
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Note", mappedBy="user")
     */
    private $notes;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Messages::class, orphanRemoval: true)]

    private $messages;

    public function __construct()
    {
        $this->notes = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->messages = new ArrayCollection();
    }

    /**
     * @return Collection|Messages[]
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Messages $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setUser($this);
        }

        return $this;
    }

    public function removeMessage(Messages $message): self
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getUser() === $this) {
                $message->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Notification[]
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }
    public function addNotification(Notification $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications[] = $notification;
            $notification->setUser($this);
        }

        return $this;
    }
    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getUser() === $this) {
                $notification->setUser(null);
            }
        }

        return $this;
    }
    /**
     * @return Collection|Note[]
     */
    public function getNotes(): Collection
    {
        return $this->notes ?: new ArrayCollection();
    }



    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private string $username;

    #[ORM\Column(type: 'string')]
    private string $password = '';
    #[ORM\Column(type: 'string')]
    private string $confirm_password = '';

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    #[ORM\Column(type: 'string')]
    private $profilePicturePath;

    public function getProfilePicturePath(): ?string
    {
        return $this->profilePicturePath;
    }

    public function setProfilePicturePath(?string $profilePicturePath): self
    {
        $this->profilePicturePath = $profilePicturePath;
        return $this;
    }
    #[ORM\Column(type: 'json')]
    private array $roles = [];

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getConfirmPassword(): string
    {
        return $this->confirm_password;
    }

    public function setConfirmPassword(string $confirm_password): void
    {
        $this->confirm_password = $confirm_password;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
// Do nothing
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

}
