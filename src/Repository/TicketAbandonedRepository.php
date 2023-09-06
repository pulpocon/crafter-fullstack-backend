<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\TicketAbandoned;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TicketAbandoned>
 *
 * @method TicketAbandoned|null find($id, $lockMode = null, $lockVersion = null)
 * @method TicketAbandoned|null findOneBy(array $criteria, array $orderBy = null)
 * @method TicketAbandoned[]    findAll()
 * @method TicketAbandoned[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TicketAbandonedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TicketAbandoned::class);
    }

    public function add(TicketAbandoned $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TicketAbandoned $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
