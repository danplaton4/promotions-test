<?php

namespace App\Entity;

use App\Repository\PromotionRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;

#[ORM\Entity(repositoryClass: PromotionRepository::class)]
#[ORM\Table(name: 'promotions')]
class Promotion implements TimestampableInterface {
  use TimestampableTrait;

  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 255)]
  private ?string $name = null;

  #[ORM\Column(type: Types::DATETIME_MUTABLE)]
  private ?DateTime $startDate = null;

  #[ORM\OneToMany(mappedBy: 'promotion', targetEntity: Prize::class, orphanRemoval: true)]
  private Collection $prizes;

  public function __construct() {
    $this->prizes = new ArrayCollection();
  }

  public function getId(): int {
    return $this->id;
  }

  public function getName(): string {
    return $this->name;
  }

  public function setName(string $name): self {
    $this->name = $name;

    return $this;
  }

  public function getStartDate(): DateTime {
    return $this->startDate;
  }

  public function setStartDate(DateTime $startDate): self {
    $this->startDate = $startDate;

    return $this;
  }

  /**
   * @return Collection<int, Prize>
   */
  public function getPrizes(): Collection {
    return $this->prizes;
  }

  public function addPrize(Prize $prize): self {
    if (!$this->prizes->contains($prize)) {
      $this->prizes->add($prize);
      $prize->setPromotion($this);
    }

    return $this;
  }

  public function removePrize(Prize $prize): self {
    if ($this->prizes->removeElement($prize)) {
      // set the owning side to null (unless already changed)
      if ($prize->getPromotion() === $this) {
        $prize->setPromotion(null);
      }
    }

    return $this;
  }
}
