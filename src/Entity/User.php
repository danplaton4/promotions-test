<?php

namespace App\Entity;

use App\Model\Constants\UserRole;
use App\Repository\UserRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Gedmo\Mapping\Annotation\Timestampable;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
class User implements UserInterface, PasswordAuthenticatedUserInterface {
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

  #[Timestampable(on: 'update')]
  #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
  private ?DateTimeInterface $dateUpdated = null;

  #[Timestampable(on: 'create')]
  #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
  private ?DateTimeInterface $dateCreated = null;

  /**
   * @return int|null
   */
  public function getId(): ?int {
    return $this->id;
  }

  /**
   * @return string|null
   */
  public function getEmail(): ?string {
    return $this->email;
  }

  /**
   * @param string $email
   * @return $this
   */
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

  /**
   * @see UserInterface
   */
  public function getRoles(): array {
    $roles = $this->roles;

    // Guarantee every user at least has ROLE_USER
    $roles[] = UserRole::ROLE_USER;

    return array_unique($roles);
  }

  /**
   * @param array $roles
   * @return $this
   */
  public function setRoles(array $roles): self {
    $this->roles = $roles;

    return $this;
  }

  /**
   * @return bool
   */
  public function isEnabled(): bool {
    return $this->enabled;
  }

  /**
   * @param bool $enabled
   * @return $this
   */
  public function setEnabled(bool $enabled): self {
    $this->enabled = $enabled;

    return $this;
  }

  /**
   * @return string the hashed password for this user
   */
  public function getPassword(): string {
    return $this->password;
  }

  /**
   * @param string $password
   * @return $this
   */
  public function setPassword(string $password): self {
    $this->password = $password;

    return $this;
  }

  /**
   * @return UserProfile|null
   */
  public function getUserProfile(): ?UserProfile {
    return $this->userProfile;
  }

  /**
   * @param UserProfile $userProfile
   * @return $this
   */
  public function setUserProfile(UserProfile $userProfile): self {
    // Set the owning side of the relation if necessary
    if ($userProfile->getUser() !== $this) {
      $userProfile->setUser($this);
    }

    $this->userProfile = $userProfile;

    return $this;
  }

  /**
   * @return DateTimeInterface|null
   */
  public function getDateUpdated(): ?DateTimeInterface {
    return $this->dateUpdated;
  }

  /**
   * @param DateTimeInterface|null $dateUpdated
   * @return $this
   */
  public function setDateUpdated(?DateTimeInterface $dateUpdated): self {
    $this->dateUpdated = $dateUpdated;

    return $this;
  }

  /**
   * @return DateTimeInterface|null
   */
  public function getDateCreated(): ?DateTimeInterface {
    return $this->dateCreated;
  }

  /**
   * @param DateTimeInterface|null $dateCreated
   * @return $this
   */
  public function setDateCreated(?DateTimeInterface $dateCreated): self {
    $this->dateCreated = $dateCreated;

    return $this;
  }

  /**
   * @return void
   */
  public function eraseCredentials(): void {
    // TODO: Implement eraseCredentials() method.
  }

  /**
   * @param $role
   * @return bool
   */
  public function hasRole($role): bool {
    return in_array(strtoupper($role), $this->getRoles(), true);
  }
}
