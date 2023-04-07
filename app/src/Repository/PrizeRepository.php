<?php

namespace App\Repository;

use App\Entity\Prize;
use App\Entity\Promotion;
use App\Entity\Winning;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Prize>
 *
 * @method Prize|null find($id, $lockMode = null, $lockVersion = null)
 * @method Prize|null findOneBy(array $criteria, array $orderBy = null)
 * @method Prize[]    findAll()
 * @method Prize[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PrizeRepository extends ServiceEntityRepository {
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, Prize::class);
  }

  public function save(Prize $entity, bool $flush = false): void {
    $this->getEntityManager()->persist($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  public function remove(Prize $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  public function getAvailablePrizes(Promotion $promotion, int $limit, int $offset): array {
    $prizesQueryBuilder = $this->createQueryBuilder('prize')
      ->where('prize.promotion = :promotion')
      ->andWhere('prize.isWon = :isWon')
      ->setParameter('promotion', $promotion)
      ->setParameter('isWon', false)
      ->setMaxResults($limit)
      ->setFirstResult($offset);

    $prizes = $prizesQueryBuilder->getQuery()->getResult();

    // Query to get all winnings for the prizes
    $winningsQueryBuilder = $this->getEntityManager()->createQueryBuilder()
      ->select('winning')
      ->from(Winning::class, 'winning')
      ->join('winning.prize', 'prize')
      ->andWhere('prize IN (:prizes)')
      ->setParameter('prizes', $prizes);

    $winnings = $winningsQueryBuilder->getQuery()->getResult();

    // Filter out the prizes that were won
    return array_filter($prizes, function ($prize) use ($winnings) {
      foreach ($winnings as $winning) {
        if ($winning->getPrize() === $prize) {
          return false;
        }
      }

      return true;
    });
  }
}
