<?php

namespace App\Repository;

use App\Entity\Card;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Card>
 *
 * @method Card|null find($id, $lockMode = null, $lockVersion = null)
 * @method Card|null findOneBy(array $criteria, array $orderBy = null)
 * @method Card[]    findAll()
 * @method Card[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Card::class);
    }

    public function getAllUuids(): array
    {
        $result =  $this->createQueryBuilder('c')
            ->select('c.uuid')
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_ARRAY)
        ;
        return array_column($result, 'uuid');
    }

    public function getAllSetCodes(): array
    {
        $result = $this->createQueryBuilder('c')
            ->select('DISTINCT c.setCode')
            ->where('c.setCode IS NOT NULL')
            ->orderBy('c.setCode', 'ASC')
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_ARRAY);

        return array_column($result, 'setCode');
    }

    public function findPaginated(int $page, int $limit = 100, ?string $setCode = null): array
    {
        $qb = $this->createQueryBuilder('c');

        if ($setCode !== null) {
            $qb->where('c.setCode = :setCode')
               ->setParameter('setCode', $setCode);
        }

        $total = (int) (clone $qb)
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $items = $qb
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return [
            'items' => $items,
            'page' => $page,
            'totalPages' => (int) ceil($total / $limit),
            'total' => $total,
        ];
    }

    /**
     * @return Card[]
     */
    public function searchByName(string $name, ?string $setCode = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.name LIKE :name')
            ->setParameter('name', '%' . $name . '%')
            ->setMaxResults(20);

        if ($setCode !== null) {
            $qb->andWhere('c.setCode = :setCode')
               ->setParameter('setCode', $setCode);
        }

        return $qb->getQuery()->getResult();
    }
}
