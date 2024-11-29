<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Comment>
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    /**
     * Retourne tous les commentaires associés à un post spécifique
     *
     * @param int $postId
     * @return Comment[]
     */
    public function findByPost(int $postId): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.post = :postId')
            ->setParameter('postId', $postId)
            ->orderBy('c.createdAt', 'DESC') // Optionnel : tri par date de création
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche les commentaires créés à partir d'une certaine date
     *
     * @param \DateTime $date
     * @return Comment[]
     */
    public function findByCreatedAt(\DateTime $date): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.createdAt >= :date')
            ->setParameter('date', $date)
            ->orderBy('c.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne les commentaires d'un utilisateur spécifique
     *
     * @param int $userId
     * @return Comment[]
     */
    public function findByUser(int $userId): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne un commentaire par son id
     *
     * @param int $id
     * @return Comment|null
     */
    public function findOneById(int $id): ?Comment
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
