<?php

namespace App\Entity;

use App\Repository\UserProfileRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Timestampable;

#[ORM\Entity(repositoryClass: UserProfileRepository::class)]
#[ORM\Table(name: 'user_profiles')]
class UserProfile {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 255)]
  private ?string $firstName = null;

  #[ORM\Column(length: 255)]
  private ?string $lastName = null;

  #[ORM\Column(length: 255, nullable: true)]
  private ?string $avatar = null;

  #[ORM\OneToOne(inversedBy: 'userProfile', cascade: ['persist', 'remove'])]
  #[ORM\JoinColumn(nullable: false)]
  private ?User $user = null;

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
  public function getFirstName(): ?string {
    return $this->firstName;
  }

  /**
   * @param string $firstName
   * @return $this
   */
  public function setFirstName(string $firstName): self {
    $this->firstName = $firstName;

    return $this;
  }

  /**
   * @return string|null
   */
  public function getLastName(): ?string {
    return $this->lastName;
  }

  /**
   * @param string $lastName
   * @return $this
   */
  public function setLastName(string $lastName): self {
    $this->lastName = $lastName;

    return $this;
  }

  /**
   * @return string|null
   */
  public function getAvatar(): ?string {
    return $this->avatar;
  }

  /**
   * @param string|null $avatar
   * @return $this
   */
  public function setAvatar(?string $avatar): self {
    $this->avatar = $avatar;

    return $this;
  }

  /**
   * @return User|null
   */
  public function getUser(): ?User {
    return $this->user;
  }

  /**
   * @param User $user
   * @return $this
   */
  public function setUser(User $user): self {
    $this->user = $user;

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
}
