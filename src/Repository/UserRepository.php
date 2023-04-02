<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserProfile;
use App\Model\Helpers\SearchQueryHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface {
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, User::class);
  }

  public function save(User $entity, bool $flush = false): void {
    $this->getEntityManager()->persist($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  public function remove(User $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  /**
   * Used to upgrade (rehash) the user's password automatically over time.
   */
  public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void {
    if (!$user instanceof User) {
      throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
    }

    $user->setPassword($newHashedPassword);

    $this->save($user, true);
  }

  public function getCount(
    bool   $enabled,
    string $q = null
  ): int {
    $qb = $this->createQueryBuilder('user');

    $qb->select('count(userProfile.id)');
    $qb->leftJoin(UserProfile::class, 'userProfile', 'WITH', 'user.id = userProfile.user');
    $qb->where('user.enabled = :enabled');
    $qb->setParameter('enabled', $enabled);

    if ($q) {
      SearchQueryHelper::addQueryStringConditionMultiple([
        'userProfile.firstName',
        'userProfile.lastName',
        'user.email'
      ], $q, $qb);
    }

    return (int)$qb->getQuery()->getSingleScalarResult();
  }

  public function getAll(
    int    $page,
    int    $noRecords,
    string $sortField,
    string $sortType,
    bool   $enabled,
    string $q = null
  ): array {
    $qb = $this->createQueryBuilder('user');

    $qb->select(
      'user.id AS id',
      'user.email AS email',
      'user.enabled AS enabled',
      'user.roles as roles',
      'userProfile.firstName as firstName',
      'userProfile.lastName as lastName',
      'userProfile.avatar as avatar',
    );

    $qb->leftJoin(UserProfile::class, 'userProfile', 'WITH', 'user.id = userProfile.user');
    $qb->where('user.enabled = :enabled');
    $qb->setParameter('enabled', $enabled);

    if ($q) {
      SearchQueryHelper::addQueryStringConditionMultiple([
        'userProfile.firstName',
        'userProfile.lastName',
        'user.email'
      ], $q, $qb);
    }

    if ($sortField === 'name') {
      $qb->orderBy('firstName', $sortType);
      $qb->addOrderBy('lastName', $sortType);
    } else {
      $qb->orderBy($sortField, $sortType);
    }

    $qb->setMaxResults($noRecords);
    $qb->setFirstResult($page * $noRecords);

    return $qb->getQuery()->getArrayResult();
  }

}
