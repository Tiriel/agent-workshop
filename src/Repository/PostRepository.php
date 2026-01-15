<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function save(Post $post, bool $flush = true): void
    {
        $this->getEntityManager()->persist($post);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Post $post, bool $flush = true): void
    {
        $this->getEntityManager()->remove($post);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Count posts without tags
     */
    public function countWithoutTags(): int
    {
        $qb = $this->createQueryBuilder('p');
        $qb->select('COUNT(p.id)')
            ->leftJoin('p.tags', 't')
            ->where('t.id IS NULL');

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function getAverageTagsPerPost(): float
    {
        $totalPosts = $this->count([]);
        if ($totalPosts === 0) {
            return 0;
        }

        $qb = $this->createQueryBuilder('p');
        $qb->select('p.id, COUNT(t.id) as tagCount')
            ->leftJoin('p.tags', 't')
            ->groupBy('p.id');

        $postsWithTagCounts = $qb->getQuery()->getResult();
        $totalTagCount = array_sum(array_column($postsWithTagCounts, 'tagCount'));

        return round($totalTagCount / $totalPosts, 2);
    }

    /**
     * Find posts without tags.
     *
     * @return Post[]
     */
    public function findWithoutTags(?int $limit = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.tags', 't')
            ->where('t.id IS NULL');

        if (\is_int($limit)) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()
            ->getResult();
    }

    //    /**
//     * @return Post[] Returns an array of Post objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

    //    public function findOneBySomeField($value): ?Post
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
