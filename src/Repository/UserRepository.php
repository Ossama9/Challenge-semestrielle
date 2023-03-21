<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Reservation;
use App\Entity\Hotel;
use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function save(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $reservations = $this
            ->getEntityManager()
            ->getRepository(Reservation::class)
            ->findBy(['user' => $entity]);

        foreach ($reservations as $reservation)
            $this
                ->getEntityManager()
                ->getRepository(Reservation::class)
                ->remove($reservation, true);

        $hotels = $entity->getHotels();
        foreach ($hotels as $hotel)
            $this
                ->getEntityManager()
                ->getRepository(Hotel::class)
                ->remove($hotel, true);


        $comments = $this
            ->getEntityManager()
            ->getRepository(Comment::class)
            ->findBy(['user' => $entity]);

        foreach ($comments as $comment)
            $this
                ->getEntityManager()
                ->getRepository(Comment::class)
                ->remove($comment, true);

        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function count(array $criteria = []): int
    {
        $qb = $this->createQueryBuilder('u');

        $qb->select('COUNT(u.id)');

        foreach ($criteria as $key => $value) {
            $qb->andWhere("u.$key = :$key");
            $qb->setParameter($key, $value);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function findByRole(string $role): array
    {
        $qb = $this->createQueryBuilder('u');

        $qb->select('u');
        $qb->where('u.roles LIKE :role');
        $qb->setParameter('role', "%$role%");

        return $qb->getQuery()->getResult();
    }

//    /**
//     * @return User[] Returns an array of User objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?User
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
