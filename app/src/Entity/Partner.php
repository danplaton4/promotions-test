<?php

namespace App\Entity;

use App\Repository\PartnerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Timestampable;

#[ORM\Entity(repositoryClass: PartnerRepository::class)]
class Partner {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 255)]
  private ?string $name = null;

  #[ORM\Column(length: 255)]
  private ?string $url = null;

  #[ORM\Column(length: 255)]
  private ?string $code = null;

  #[Timestampable(on: 'update')]
  #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
  private ?\DateTimeInterface $dateUpdated = null;

  #[Timestampable(on: 'create')]
  #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
  private ?\DateTimeInterface $dateCreated = null;

  public function getId(): ?int {
    return $this->id;
  }

  public function getName(): ?string {
    return $this->name;
  }

  public function setName(string $name): self {
    $this->name = $name;

    return $this;
  }

  public function getUrl(): ?string {
    return $this->url;
  }

  public function setUrl(string $url): self {
    $this->url = $url;

    return $this;
  }

  public function getCode(): ?string {
    return $this->code;
  }

  public function setCode(string $code): self {
    $this->code = $code;

    return $this;
  }

  public function getDateUpdated(): ?\DateTimeInterface {
    return $this->dateUpdated;
  }

  public function setDateUpdated(?\DateTimeInterface $dateUpdated): self {
    $this->dateUpdated = $dateUpdated;

    return $this;
  }

  public function getDateCreated(): ?\DateTimeInterface {
    return $this->dateCreated;
  }

  public function setDateCreated(?\DateTimeInterface $dateCreated): self {
    $this->dateCreated = $dateCreated;

    return $this;
  }
}
