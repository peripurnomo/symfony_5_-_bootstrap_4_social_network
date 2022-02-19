<?php

namespace App\Repository;

use App\Entity\Follow;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Follow|null find($id, $lockMode = null, $lockVersion = null)
 * @method Follow|null findOneBy(array $criteria, array $orderBy = null)
 * @method Follow[]    findAll()
 * @method Follow[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FollowRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Follow::class);
    }
    
    public function findOneByFollowing(int $u, int $p): ?Follow
    {
        return $this->createQueryBuilder('f')
            ->leftJoin('f.user', 'u')
            ->where('f.follower = :u')
            ->andWhere('f.user = :p')
            ->setParameters([
                'u' => $u,
                'p' => $p
            ])
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findByUsername(int $u): ?array
    {
        return $this->createQueryBuilder('f')
            ->leftJoin('f.user', 'u')
            ->andWhere('f.user = :u')
            ->setParameter('u', $u)
            ->orderBy('f.follower', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByFollower(int $u): ?array
    {
        return $this->createQueryBuilder('f')
            ->leftJoin('f.user', 'u')
            ->andWhere('f.follower = :u')
            ->setParameter('u', $u)
            ->orderBy('f.user', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
}