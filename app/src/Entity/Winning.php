<?php

namespace App\Entity;

use App\Repository\WinningRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;

#[ORM\Entity(repositoryClass: WinningRepository::class)]
#[ORM\Table(name: 'winnings')]
class Winning implements TimestampableInterface {
  use TimestampableTrait;

  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\ManyToOne(inversedBy: 'winnings')]
  #[ORM\JoinColumn(nullable: false)]
  private ?User $user = null;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: false)]
  private ?Prize $prize = null;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: false)]
  private ?Promotion $promotion = null;

  #[ORM\Column(type: Types::DATE_MUTABLE)]
  private ?DateTimeInterface $date = null;

  public function getId(): int {
    return $this->id;
  }

  public function getUser(): User {
    return $this->user;
  }

  public function setUser(User $user): self {
    $this->user = $user;

    return $this;
  }

  public function getPrize(): Prize {
    return $this->prize;
  }

  public function setPrize(Prize $prize): self {
    $this->prize = $prize;

    return $this;
  }

  public function getPromotion(): Promotion {
    return $this->promotion;
  }

  public function setPromotion(Promotion $promotion): self {
    $this->promotion = $promotion;

    return $this;
  }

  public function getDate(): DateTimeInterface {
    return $this->date;
  }

  public function setDate(DateTimeInterface $date): self {
    $this->date = $date;

    return $this;
  }
}
