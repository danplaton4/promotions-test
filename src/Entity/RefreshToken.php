<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Gedmo\Mapping\Annotation\Timestampable;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshToken as BaseRefreshToken;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'refresh_tokens')]
class RefreshToken extends BaseRefreshToken {

  #[Timestampable(on: 'update')]
  #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
  private ?DateTimeInterface $dateUpdated = null;

  #[Timestampable(on: 'create')]
  #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
  private ?DateTimeInterface $dateCreated = null;

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
