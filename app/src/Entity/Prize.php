<?php

namespace App\Entity;

use App\Repository\PrizeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Timestampable;

#[ORM\Entity(repositoryClass: PrizeRepository::class)]
class Prize {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 255)]
  private ?string $partnerCode = null;

  #[ORM\Column(length: 255)]
  private ?string $name = null;

  #[ORM\Column(type: Types::TEXT)]
  private ?string $description = null;

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

  public function getPartnerCode(): ?string {
    return $this->partnerCode;
  }

  public function setPartnerCode(string $partnerCode): self {
    $this->partnerCode = $partnerCode;

    return $this;
  }

  public function getName(): ?string {
    return $this->name;
  }

  public function setName(string $name): self {
    $this->name = $name;

    return $this;
  }

  public function getDescription(): ?string {
    return $this->description;
  }

  public function setDescription(string $description): self {
    $this->description = $description;

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
