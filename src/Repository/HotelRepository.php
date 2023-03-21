<?php

namespace App\Repository;

use App\Entity\Hotel;
use App\Entity\RequestCustomer;
use App\Entity\Announce;
use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Hotel>
 *
 * @method Hotel|null find($id, $lockMode = null, $lockVersion = null)
 * @method Hotel|null findOneBy(array $criteria, array $orderBy = null)
 * @method Hotel[]    findAll()
 * @method Hotel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HotelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hotel::class);
    }

    public function save(Hotel $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Hotel $entity, bool $flush = false): void
    {
        $announces = $entity->getAnnounces();

        foreach ($announces as $announce) {
            $this
                ->getEntityManager()
                ->getRepository(Announce::class)
                ->remove($announce, true);
        }

        $comments = $entity->getComments();
        foreach ($comments as $comment) {
            $this
                ->getEntityManager()
                ->getRepository(Comment::class)
                ->remove($comment, true);
        }

        $requests = $this
            ->getEntityManager()
            ->getRepository(RequestCustomer::class)
            ->findBy(['hotel' => $entity]);

        foreach ($requests as $request) {
            $this
                ->getEntityManager()
                ->getRepository(RequestCustomer::class)
                ->remove($request, true);
        }

        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function count(array $criteria = []): int
    {
        $qb = $this->createQueryBuilder('h');

        $qb->select('COUNT(h.id)');

        foreach ($criteria as $key => $value) {
            $qb->andWhere("h.$key = :$key");
            $qb->setParameter($key, $value);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    //Create a search method to search hotels by input
    public function search(string $query): array
    {
        $qb = $this->createQueryBuilder('h');
        $qb->where('h.name LIKE :query')
            ->orWhere('h.adresse LIKE :query')
            ->orWhere('h.ville LIKE :query')
            ->orWhere('h.country LIKE :query')
            ->setParameter('query', "%$query%");

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Hotel[] Returns an array of Hotel objects
     */
    public function sortByRating(): array
    {
        //sort by note and return the 3 best hotels
       return $this->createQueryBuilder('h')
            ->orderBy('h.note', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOneByUserId(int $id)
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.user_id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult()
        ;
    }
}
