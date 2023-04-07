<?php

namespace App\Entity;

use App\Repository\PrizeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;

#[ORM\Entity(repositoryClass: PrizeRepository::class)]
#[ORM\Table(name: 'prizes')]
class Prize implements TimestampableInterface, TranslatableInterface {
  use TimestampableTrait, TranslatableTrait;

  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 255, unique: true)]
  private ?string $code = null;

  #[ORM\Column(length: 255)]
  private ?string $partnerCode = null;

  #[ORM\ManyToOne(inversedBy: 'prizes')]
  #[ORM\JoinColumn(nullable: false)]
  private ?Promotion $promotion = null;

  #[ORM\Column]
  private bool $isWon = false;

  public function getId(): int {
    return $this->id;
  }

  public function getCode(): string {
    return $this->code;
  }

  public function setCode(string $code): self {
    $this->code = $code;

    return $this;
  }

  public function getPartnerCode(): string {
    return $this->partnerCode;
  }

  public function setPartnerCode(string $partnerCode): self {
    $this->partnerCode = $partnerCode;

    return $this;
  }

  public function getPromotion(): Promotion {
    return $this->promotion;
  }

  public function setPromotion(Promotion $promotion): self {
    $this->promotion = $promotion;

    return $this;
  }

  public function isIsWon(): bool {
    return $this->isWon;
  }

  public function setIsWon(bool $isWon): self {
    $this->isWon = $isWon;

    return $this;
  }
}
