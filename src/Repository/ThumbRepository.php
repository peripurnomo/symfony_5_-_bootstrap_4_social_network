<?php

namespace App\Repository;

use App\Entity\Thumb;
// use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\AST\Join;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Thumb|null find($id, $lockMode = null, $lockVersion = null)
 * @method Thumb|null findOneBy(array $criteria, array $orderBy = null)
 * @method Thumb[]    findAll()
 * @method Thumb[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ThumbRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Thumb::class);
    }

    /**
     * @param string $username username.
     * @param int $id postid.
     * @return Thumb object.
     */
    public function findOneByLiking(string $username, int $id): ?Thumb
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.post', 'p')
            ->where('t.liker = :username')
            ->andWhere('t.post = :id')
            ->setParameters([
                'username' => $username,
                'id' => $id
            ])
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function countLike(int $id)
    {
        return $this->createQueryBuilder('t')
            ->select('COUNT(p)')
            ->leftJoin('t.post', 'p')
            ->andWhere('t.post = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult()[1]
        ;
    }
}
