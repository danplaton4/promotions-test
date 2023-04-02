<?php

namespace App\Entity;

use App\Repository\MediaRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Timestampable;

#[ORM\Entity(repositoryClass: MediaRepository::class)]
class Media {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 255)]
  private ?string $name = null;

  #[ORM\Column(length: 255)]
  private ?string $url = null;

  #[ORM\Column(length: 255)]
  private ?string $type = null;

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
  public function getName(): ?string {
    return $this->name;
  }

  /**
   * @param string $name
   * @return $this
   */
  public function setName(string $name): self {
    $this->name = $name;

    return $this;
  }

  /**
   * @return string|null
   */
  public function getUrl(): ?string {
    return $this->url;
  }

  /**
   * @param string $url
   * @return $this
   */
  public function setUrl(string $url): self {
    $this->url = $url;

    return $this;
  }

  /**
   * @return string|null
   */
  public function getType(): ?string {
    return $this->type;
  }

  /**
   * @param string $type
   * @return $this
   */
  public function setType(string $type): self {
    $this->type = $type;

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
