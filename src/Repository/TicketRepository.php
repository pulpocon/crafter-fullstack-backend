<?php

namespace App\Repository;

use App\Entity\Ticket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ticket>
 *
 * @method Ticket|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ticket|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ticket[]    findAll()
 * @method Ticket[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);
    }

    public function add(Ticket $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Ticket $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function salesPerDay() : array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT COUNT(*) AS total, DATE_FORMAT(end_date, \'%Y-%m-%d\') AS date
            FROM ticket WHERE end_date IS NOT NULL GROUP BY date';

        return $conn->prepare($sql)->executeQuery()->fetchAllAssociative();
    }

    public function ofEmailInvoice(string $invoiceEmail) : array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.emailInvoice = :invoiceEmail')
            ->andWhere('t.invoice IS NULL')
            ->andWhere('t.revoked = 0')
            ->andWhere('t.endDate IS NOT NULL')
            ->setParameter('invoiceEmail', $invoiceEmail)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }
}
