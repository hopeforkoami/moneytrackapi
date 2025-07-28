<?php

namespace App\Controller;

use App\Entity\Account;
use App\Repository\AccountRepository;
use App\Modele\NogSystemResponse;
use App\Modele\NogCustomedFunctions;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/accounts', name: 'api_accounts_')]
class AccountController extends AbstractController
{
    private $accountRepository;
    private $entityManager;
    private $nogFunctions;

    public function __construct(
        AccountRepository $accountRepository,
        EntityManagerInterface $entityManager,
        NogCustomedFunctions $nogFunctions
    ) {
        $this->accountRepository = $accountRepository;
        $this->entityManager = $entityManager;
        $this->nogFunctions = $nogFunctions;
    }

    /**
     * Create a new account
     */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            // Validate required fields
            if (empty($data['name'])) {
                $response = new NogSystemResponse('error', 'Account name is required', null);
                return $response->getSystemHttpResponse();
            }

            if (!isset($data['balance']) || !is_numeric($data['balance'])) {
                $response = new NogSystemResponse('error', 'Valid balance is required', null);
                return $response->getSystemHttpResponse();
            }

            // Create new account
            $account = new Account();
            $account->setName($data['name']);
            $account->setBalance((string)$data['balance']);
            $account->setDescription($data['description'] ?? null);
            $account->setContact($data['contact'] ?? null);
            $account->setEmplacement($data['emplacement'] ?? null);
            
            // Set default values
            $account->setSynced(false);
            $account->setOnlineID($this->nogFunctions->generateID('ACC'));
            $account->setCreateAt(new \DateTimeImmutable());
            $account->setUpdatedAt(new \DateTimeImmutable());

            // Persist to database
            $this->entityManager->persist($account);
            $this->entityManager->flush();

            $response = new NogSystemResponse('success', 'Account created successfully', [
                'id' => $account->getId(),
                'onlineID' => $account->getOnlineID(),
                'name' => $account->getName(),
                'balance' => $account->getBalance(),
                'description' => $account->getDescription(),
                'contact' => $account->getContact(),
                'emplacement' => $account->getEmplacement(),
                'synced' => $account->isSynced(),
                'created_at' => $account->getCreateAt()->format('Y-m-d H:i:s'),
                'updated_at' => $account->getUpdatedAt()->format('Y-m-d H:i:s')
            ]);

            return $response->getSystemHttpResponse();

        } catch (\Exception $e) {
            $response = new NogSystemResponse('error', 'Failed to create account: ' . $e->getMessage(), null);
            return $response->getSystemHttpResponse();
        }
    }

    /**
     * Get all accounts with pagination and filters
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): Response
    {
        try {
            $page = (int)$request->query->get('page', 1);
            $limit = (int)$request->query->get('limit', 10);
            
            // Prepare filters
            $filters = [];
            if ($request->query->has('synced')) {
                $filters['synced'] = $request->query->get('synced');
            }
            if ($request->query->has('deleted')) {
                $filters['deleted'] = $request->query->get('deleted');
            }
            if ($request->query->has('search')) {
                $filters['search'] = $request->query->get('search');
            }
            if ($request->query->has('balance_min')) {
                $filters['balance_min'] = $request->query->get('balance_min');
            }
            if ($request->query->has('balance_max')) {
                $filters['balance_max'] = $request->query->get('balance_max');
            }
            if ($request->query->has('created_after')) {
                $filters['created_after'] = $request->query->get('created_after');
            }
            if ($request->query->has('created_before')) {
                $filters['created_before'] = $request->query->get('created_before');
            }
            if ($request->query->has('sort')) {
                $filters['sort'] = $request->query->get('sort');
                $filters['order'] = $request->query->get('order', 'DESC');
            }

            $result = $this->accountRepository->findWithPagination($page, $limit, $filters);

            // Format accounts data
            $accountsData = array_map(function (Account $account) {
                return $this->formatAccountData($account);
            }, $result['data']);

            $response = new NogSystemResponse('success', 'Accounts retrieved successfully', [
                'accounts' => $accountsData,
                'pagination' => $result['pagination']
            ]);

            return $response->getSystemHttpResponse();

        } catch (\Exception $e) {
            $response = new NogSystemResponse('error', 'Failed to retrieve accounts: ' . $e->getMessage(), null);
            return $response->getSystemHttpResponse();
        }
    }

    /**
     * Get a single account by ID
     */
    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        try {
            $account = $this->accountRepository->findActiveById($id);

            if (!$account) {
                $response = new NogSystemResponse('error', 'Account not found', null);
                return $response->getSystemHttpResponse();
            }

            $response = new NogSystemResponse('success', 'Account retrieved successfully',
                $this->formatAccountData($account, true)
            );

            return $response->getSystemHttpResponse();

        } catch (\Exception $e) {
            $response = new NogSystemResponse('error', 'Failed to retrieve account: ' . $e->getMessage(), null);
            return $response->getSystemHttpResponse();
        }
    }

    /**
     * Update an existing account
     */
    #[Route('/{id}', name: 'update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): Response
    {
        try {
            $account = $this->accountRepository->findActiveById($id);

            if (!$account) {
                $response = new NogSystemResponse('error', 'Account not found', null);
                return $response->getSystemHttpResponse();
            }

            $data = json_decode($request->getContent(), true);

            // Update fields if provided
            if (isset($data['name'])) {
                if (empty($data['name'])) {
                    $response = new NogSystemResponse('error', 'Account name cannot be empty', null);
                    return $response->getSystemHttpResponse();
                }
                $account->setName($data['name']);
            }

            if (isset($data['balance'])) {
                if (!is_numeric($data['balance'])) {
                    $response = new NogSystemResponse('error', 'Balance must be numeric', null);
                    return $response->getSystemHttpResponse();
                }
                $account->setBalance((string)$data['balance']);
            }

            if (isset($data['description'])) {
                $account->setDescription($data['description']);
            }

            if (isset($data['contact'])) {
                $account->setContact($data['contact']);
            }

            if (isset($data['emplacement'])) {
                $account->setEmplacement($data['emplacement']);
            }

            if (isset($data['synced'])) {
                $account->setSynced((bool)$data['synced']);
            }

            // Update timestamp
            $account->setUpdatedAt(new \DateTimeImmutable());

            // Persist changes
            $this->entityManager->persist($account);
            $this->entityManager->flush();

            $response = new NogSystemResponse('success', 'Account updated successfully',
                $this->formatAccountData($account)
            );

            return $response->getSystemHttpResponse();

        } catch (\Exception $e) {
            $response = new NogSystemResponse('error', 'Failed to update account: ' . $e->getMessage(), null);
            return $response->getSystemHttpResponse();
        }
    }

    /**
     * Soft delete an account
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): Response
    {
        try {
            $account = $this->accountRepository->findActiveById($id);

            if (!$account) {
                $response = new NogSystemResponse('error', 'Account not found', null);
                return $response->getSystemHttpResponse();
            }

            // Perform soft delete
            $this->accountRepository->softDelete($account);

            $response = new NogSystemResponse('success', 'Account deleted successfully', [
                'id' => $account->getId(),
                'name' => $account->getName(),
                'deleted_at' => $account->getDeletedAt()->format('Y-m-d H:i:s')
            ]);

            return $response->getSystemHttpResponse();

        } catch (\Exception $e) {
            $response = new NogSystemResponse('error', 'Failed to delete account: ' . $e->getMessage(), null);
            return $response->getSystemHttpResponse();
        }
    }

    /**
     * Search accounts
     */
    #[Route('/search', name: 'search', methods: ['GET'])]
    public function search(Request $request): Response
    {
        try {
            $searchTerm = $request->query->get('q', '');
            $includeDeleted = $request->query->get('include_deleted', 'false') === 'true';

            if (empty($searchTerm)) {
                $response = new NogSystemResponse('error', 'Search term is required', null);
                return $response->getSystemHttpResponse();
            }

            $accounts = $this->accountRepository->searchAccounts($searchTerm, $includeDeleted);

            $accountsData = array_map(function (Account $account) {
                return $this->formatAccountData($account);
            }, $accounts);

            $response = new NogSystemResponse('success', 'Search completed successfully', [
                'search_term' => $searchTerm,
                'results_count' => count($accountsData),
                'accounts' => $accountsData
            ]);

            return $response->getSystemHttpResponse();

        } catch (\Exception $e) {
            $response = new NogSystemResponse('error', 'Search failed: ' . $e->getMessage(), null);
            return $response->getSystemHttpResponse();
        }
    }

    /**
     * Get accounts with transaction counts
     */
    #[Route('/stats', name: 'stats', methods: ['GET'])]
    public function stats(Request $request): Response
    {
        try {
            $includeDeleted = $request->query->get('include_deleted', 'false') === 'true';
            
            $accountsWithCounts = $this->accountRepository->getAccountsWithTransactionCount($includeDeleted);

            $statsData = array_map(function ($result) {
                $account = $result[0]; // The Account entity
                return [
                    'id' => $account->getId(),
                    'name' => $account->getName(),
                    'balance' => $account->getBalance(),
                    'online_transaction_count' => (int)$result['online_transaction_count'],
                    'offline_transaction_count' => (int)$result['offline_transaction_count'],
                    'total_transaction_count' => (int)$result['total_transaction_count'],
                    'synced' => $account->isSynced(),
                    'created_at' => $account->getCreateAt()->format('Y-m-d H:i:s')
                ];
            }, $accountsWithCounts);

            $response = new NogSystemResponse('success', 'Account statistics retrieved successfully', [
                'accounts' => $statsData,
                'total_accounts' => count($statsData)
            ]);

            return $response->getSystemHttpResponse();

        } catch (\Exception $e) {
            $response = new NogSystemResponse('error', 'Failed to retrieve statistics: ' . $e->getMessage(), null);
            return $response->getSystemHttpResponse();
        }
    }

    /**
     * Restore a soft-deleted account
     */
    #[Route('/{id}/restore', name: 'restore', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function restore(int $id): Response
    {
        try {
            // Find account including deleted ones
            $account = $this->accountRepository->find($id);

            if (!$account) {
                $response = new NogSystemResponse('error', 'Account not found', null);
                return $response->getSystemHttpResponse();
            }

            if (!$account->getDeletedAt()) {
                $response = new NogSystemResponse('error', 'Account is not deleted', null);
                return $response->getSystemHttpResponse();
            }

            // Restore the account
            $this->accountRepository->restore($account);

            $response = new NogSystemResponse('success', 'Account restored successfully',
                $this->formatAccountData($account)
            );

            return $response->getSystemHttpResponse();

        } catch (\Exception $e) {
            $response = new NogSystemResponse('error', 'Failed to restore account: ' . $e->getMessage(), null);
            return $response->getSystemHttpResponse();
        }
    }

    /**
     * Format account data for API response
     */
    private function formatAccountData(Account $account, bool $includeTransactions = false): array
    {
        $data = [
            'id' => $account->getId(),
            'onlineID' => $account->getOnlineID(),
            'name' => $account->getName(),
            'balance' => $account->getBalance(),
            'description' => $account->getDescription(),
            'contact' => $account->getContact(),
            'emplacement' => $account->getEmplacement(),
            'synced' => $account->isSynced(),
            'created_at' => $account->getCreateAt()->format('Y-m-d H:i:s'),
            'updated_at' => $account->getUpdatedAt()->format('Y-m-d H:i:s'),
            'deleted_at' => $account->getDeletedAt() ? $account->getDeletedAt()->format('Y-m-d H:i:s') : null
        ];

        if ($includeTransactions) {
            $data['online_transactions_count'] = $account->getOnlineTransactions()->count();
            $data['offline_transactions_count'] = $account->getOfflineTransactions()->count();
            $data['total_transactions_count'] = $data['online_transactions_count'] + $data['offline_transactions_count'];
        }

        return $data;
    }
}
