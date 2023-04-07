<?php

namespace App\Entity;

use App\Model\Constant\UserRole;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
class User implements UserInterface, PasswordAuthenticatedUserInterface, TimestampableInterface {
  use TimestampableTrait;

  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(type: 'string', length: 180, unique: true)]
  private string $email;

  #[ORM\Column(type: 'json')]
  private array $roles = [];

  #[ORM\Column(type: 'boolean')]
  private bool $enabled = true;

  #[ORM\Column(type: 'string')]
  private string $password;

  #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
  private ?UserProfile $userProfile = null;

  #[ORM\OneToMany(mappedBy: 'user', targetEntity: Winning::class, orphanRemoval: true)]
  private Collection $winnings;

  public function __construct() {
    $this->winnings = new ArrayCollection();
  }

  public function getId(): int {
    return $this->id;
  }

  public function getEmail(): string {
    return $this->email;
  }

  public function setEmail(string $email): self {
    $this->email = $email;

    return $this;
  }

  /**
   * The public representation of the user (e.g. a username, an email address, etc.)
   *
   * @see UserInterface
   */
  public function getUserIdentifier(): string {
    return $this->email;
  }

  public function getRoles(): array {
    $roles = $this->roles;

    // Guarantee every user at least has ROLE_USER
    $roles[] = UserRole::ROLE_USER;

    return array_unique($roles);
  }

  public function setRoles(array $roles): self {
    $this->roles = $roles;

    return $this;
  }

  public function isEnabled(): bool {
    return $this->enabled;
  }

  public function setEnabled(bool $enabled): self {
    $this->enabled = $enabled;

    return $this;
  }

  public function getPassword(): string {
    return $this->password;
  }

  public function setPassword(string $password): self {
    $this->password = $password;

    return $this;
  }

  public function getUserProfile(): UserProfile {
    return $this->userProfile;
  }

  public function setUserProfile(UserProfile $userProfile): self {
    // Set the owning side of the relation if necessary
    if ($userProfile->getUser() !== $this) {
      $userProfile->setUser($this);
    }

    $this->userProfile = $userProfile;

    return $this;
  }

  public function eraseCredentials(): void {
    // TODO: Implement eraseCredentials() method.
  }

  public function hasRole($role): bool {
    return in_array(strtoupper($role), $this->getRoles(), true);
  }

  public function getWinnings(): Collection {
    return $this->winnings;
  }

  public function addWinning(Winning $winning): self {
    if (!$this->winnings->contains($winning)) {
      $this->winnings->add($winning);
      $winning->setUser($this);
    }

    return $this;
  }

  public function removeWinning(Winning $winning): self {
    if ($this->winnings->removeElement($winning)) {
      // set the owning side to null (unless already changed)
      if ($winning->getUser() === $this) {
        $winning->setUser(null);
      }
    }

    return $this;
  }
}
