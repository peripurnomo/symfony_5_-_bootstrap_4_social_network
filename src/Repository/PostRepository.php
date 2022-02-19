<?php

namespace App\Repository;

use App\Entity\Post;
use App\Pagination\Paginator;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @todo Create query builder for method findByFollowing()
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function findByFollowing(string $username): ?array
    {
        $conn = $this->getEntityManager()->getConnection();
        
        $sql = "SELECT
                `p`.*,
                `f`.`username`,
                `f`.`follower`

            FROM
                `post` `p`

            LEFT JOIN
                `follow` `f`

            USING
                (`username`)

            WHERE
                `p`.`username`=`f`.`username`
                AND `f`.`follower`=:username

            ORDER BY
                `p`.`id` DESC

            LIMIT 25";

        $stmt = $conn->prepare($sql);
        $stmt->execute(['username' => $username]);
        return $stmt->fetchAll();
    }

    public function findOneById(int $id): ?Post
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findByUsername(int $page = 1, string $username): ?Paginator
    {
        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.username = :username')
            ->orderBy('p.id', 'DESC')
            ->setParameter('username', $username)
        ;
        
        return (new Paginator($qb))->paginate($page);
    }

    /**
     * @return User[]
     */
    public function findBySearchQuery(string $query): ?array
    {
        $searchTerms = $this->extractSearchTerms($query);

        if (0 === \count($searchTerms)) {
            return [];
        }

        $queryBuilder = $this->createQueryBuilder('p');

        foreach ($searchTerms as $key => $term) {
            $queryBuilder
                ->orWhere('p.body LIKE :t_'.$key)
                ->setParameter('t_'.$key, '%'.$term.'%')
            ;
        }

        return $queryBuilder
            ->orderBy('p.id', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Transforms the search string into an array of search terms.
     */
    private function extractSearchTerms(string $searchQuery): ?array
    {
        $searchQuery = trim(preg_replace('/[[:space:]]+/', ' ', $searchQuery));
        $terms = array_unique(explode(' ', $searchQuery));

        # Ignore the search terms that are too short.
        return array_filter($terms, function ($term) {
            return 5 <= mb_strlen($term);
        });
    }
}
