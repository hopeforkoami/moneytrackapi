<?php

namespace App\Repository;

use App\Entity\Account;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Account>
 *
 * @method Account|null find($id, $lockMode = null, $lockVersion = null)
 * @method Account|null findOneBy(array $criteria, array $orderBy = null)
 * @method Account[]    findAll()
 * @method Account[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Account::class);
    }

    /**
     * Find active (non-deleted) accounts
     *
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return Account[]
     */
    public function findActiveAccounts(array $criteria = [], array $orderBy = null, $limit = null, $offset = null): array
    {
        $qb = $this->createQueryBuilder('a')
            ->andWhere('a.deleted_at IS NULL');

        // Apply additional criteria
        foreach ($criteria as $field => $value) {
            $qb->andWhere("a.$field = :$field")
               ->setParameter($field, $value);
        }

        // Apply ordering
        if ($orderBy) {
            foreach ($orderBy as $field => $direction) {
                $qb->addOrderBy("a.$field", $direction);
            }
        } else {
            $qb->orderBy('a.create_at', 'DESC');
        }

        // Apply pagination
        if ($limit) {
            $qb->setMaxResults($limit);
        }
        if ($offset) {
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Find accounts with pagination and filters
     *
     * @param int $page
     * @param int $limit
     * @param array $filters
     * @return array
     */
    public function findWithPagination(int $page = 1, int $limit = 10, array $filters = []): array
    {
        $qb = $this->createQueryBuilder('a');

        // Apply filters
        $this->applyFilters($qb, $filters);

        // Count total results
        $countQb = clone $qb;
        $totalItems = $countQb->select('COUNT(a.id)')
                             ->getQuery()
                             ->getSingleScalarResult();

        // Apply pagination
        $offset = ($page - 1) * $limit;
        $qb->setFirstResult($offset)
           ->setMaxResults($limit)
           ->orderBy('a.create_at', 'DESC');

        $accounts = $qb->getQuery()->getResult();

        return [
            'data' => $accounts,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($totalItems / $limit),
                'total_items' => (int)$totalItems,
                'items_per_page' => $limit,
                'has_next' => $page < ceil($totalItems / $limit),
                'has_previous' => $page > 1
            ]
        ];
    }

    /**
     * Search accounts by name and description
     *
     * @param string $searchTerm
     * @param bool $includeDeleted
     * @return Account[]
     */
    public function searchAccounts(string $searchTerm, bool $includeDeleted = false): array
    {
        $qb = $this->createQueryBuilder('a')
            ->andWhere('a.name LIKE :search OR a.description LIKE :search')
            ->setParameter('search', '%' . $searchTerm . '%');

        if (!$includeDeleted) {
            $qb->andWhere('a.deleted_at IS NULL');
        }

        return $qb->orderBy('a.create_at', 'DESC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * Soft delete an account
     *
     * @param Account $account
     * @return void
     */
    public function softDelete(Account $account): void
    {
        $account->setDeletedAt(new \DateTimeImmutable());
        $account->setUpdatedAt(new \DateTimeImmutable());
        
        $this->getEntityManager()->persist($account);
        $this->getEntityManager()->flush();
    }

    /**
     * Restore a soft-deleted account
     *
     * @param Account $account
     * @return void
     */
    public function restore(Account $account): void
    {
        $account->setDeletedAt(null);
        $account->setUpdatedAt(new \DateTimeImmutable());
        
        $this->getEntityManager()->persist($account);
        $this->getEntityManager()->flush();
    }

    /**
     * Find accounts by advanced filters
     *
     * @param array $filters
     * @return Account[]
     */
    public function findByFilters(array $filters): array
    {
        $qb = $this->createQueryBuilder('a');
        
        $this->applyFilters($qb, $filters);
        
        return $qb->orderBy('a.create_at', 'DESC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * Get accounts with their transaction counts
     *
     * @param bool $includeDeleted
     * @return array
     */
    public function getAccountsWithTransactionCount(bool $includeDeleted = false): array
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a',
                'COUNT(DISTINCT ot.id) as online_transaction_count',
                'COUNT(DISTINCT oft.id) as offline_transaction_count',
                '(COUNT(DISTINCT ot.id) + COUNT(DISTINCT oft.id)) as total_transaction_count'
            )
            ->leftJoin('a.onlineTransactions', 'ot')
            ->leftJoin('a.offlineTransactions', 'oft')
            ->groupBy('a.id');

        if (!$includeDeleted) {
            $qb->andWhere('a.deleted_at IS NULL');
        }

        return $qb->orderBy('a.create_at', 'DESC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * Find account by ID (active only)
     *
     * @param int $id
     * @return Account|null
     */
    public function findActiveById(int $id): ?Account
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.id = :id')
            ->andWhere('a.deleted_at IS NULL')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Apply filters to query builder
     *
     * @param QueryBuilder $qb
     * @param array $filters
     * @return void
     */
    private function applyFilters(QueryBuilder $qb, array $filters): void
    {
        // Filter by deleted status
        if (isset($filters['deleted'])) {
            if ($filters['deleted'] === 'true' || $filters['deleted'] === true) {
                $qb->andWhere('a.deleted_at IS NOT NULL');
            } elseif ($filters['deleted'] === 'false' || $filters['deleted'] === false) {
                $qb->andWhere('a.deleted_at IS NULL');
            }
            // If 'all', don't add any deleted_at filter
        } else {
            // Default: only active accounts
            $qb->andWhere('a.deleted_at IS NULL');
        }

        // Filter by synced status
        if (isset($filters['synced'])) {
            $qb->andWhere('a.synced = :synced')
               ->setParameter('synced', $filters['synced'] === 'true' || $filters['synced'] === true);
        }

        // Search filter
        if (isset($filters['search']) && !empty($filters['search'])) {
            $qb->andWhere('a.name LIKE :search OR a.description LIKE :search')
               ->setParameter('search', '%' . $filters['search'] . '%');
        }

        // Balance range filters
        if (isset($filters['balance_min'])) {
            $qb->andWhere('a.balance >= :balance_min')
               ->setParameter('balance_min', $filters['balance_min']);
        }

        if (isset($filters['balance_max'])) {
            $qb->andWhere('a.balance <= :balance_max')
               ->setParameter('balance_max', $filters['balance_max']);
        }

        // Date range filters
        if (isset($filters['created_after'])) {
            $qb->andWhere('a.create_at >= :created_after')
               ->setParameter('created_after', new \DateTimeImmutable($filters['created_after']));
        }

        if (isset($filters['created_before'])) {
            $qb->andWhere('a.create_at <= :created_before')
               ->setParameter('created_before', new \DateTimeImmutable($filters['created_before']));
        }

        // Sorting
        if (isset($filters['sort'])) {
            $sortField = $filters['sort'];
            $sortOrder = $filters['order'] ?? 'DESC';
            
            $allowedSortFields = ['name', 'balance', 'create_at', 'updated_at'];
            if (in_array($sortField, $allowedSortFields)) {
                $qb->orderBy("a.$sortField", strtoupper($sortOrder));
            }
        }
    }
}
