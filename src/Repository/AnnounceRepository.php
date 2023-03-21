<?php

namespace App\Repository;

use App\Entity\Announce;
use App\Entity\Reservation;
use App\Entity\Payment;
/* use App\Repository\ReservationRepository; */
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Announce>
 *
 * @method Announce|null find($id, $lockMode = null, $lockVersion = null)
 * @method Announce|null findOneBy(array $criteria, array $orderBy = null)
 * @method Announce[]    findAll()
 * @method Announce[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnnounceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Announce::class);
    }

    public function save(Announce $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Announce $entity, bool $flush = false): void
    {
        $reservations = $this
            ->getEntityManager()
            ->getRepository(Reservation::class)
            ->findBy(['announce' => $entity]);

        foreach ($reservations as $reservation) {
            $this
                ->getEntityManager()
                ->getRepository(Reservation::class)
                ->remove($reservation, true);
        }

        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function search(string $query): array
    {
        $qb = $this->createQueryBuilder('a');
        $qb->where('a.title LIKE :query')
            ->setParameter('query', "%$query%");
        return $qb->getQuery()->getResult();
    }
}
