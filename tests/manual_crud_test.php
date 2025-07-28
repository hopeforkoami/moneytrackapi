<?php

/**
 * Manual CRUD Test Script for Account API
 * 
 * This script can be run to manually test all Account CRUD operations.
 * Make sure your Symfony server is running before executing this script.
 * 
 * Usage: php tests/manual_crud_test.php
 */

class AccountCrudTester
{
    private $baseUrl;
    private $createdAccountId;

    public function __construct($baseUrl = 'http://localhost:8000')
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function runAllTests()
    {
        echo "=== Account CRUD API Test Suite ===\n\n";

        $tests = [
            'testCreateAccount',
            'testCreateAccountWithMissingName',
            'testCreateAccountWithInvalidBalance',
            'testGetAccountsList',
            'testGetAccountsListWithPagination',
            'testGetAccountsListWithFilters',
            'testGetSingleAccount',
            'testGetNonExistentAccount',
            'testUpdateAccount',
            'testUpdateAccountWithInvalidData',
            'testUpdateNonExistentAccount',
            'testSearchAccounts',
            'testSearchAccountsWithoutQuery',
            'testGetAccountStats',
            'testSoftDeleteAccount',
            'testDeleteNonExistentAccount',
            'testRestoreAccount',
            'testRestoreNonDeletedAccount',
            'testRestoreNonExistentAccount'
        ];

        $passed = 0;
        $failed = 0;

        foreach ($tests as $test) {
            echo "Running: $test... ";
            try {
                $result = $this->$test();
                if ($result) {
                    echo "✅ PASSED\n";
                    $passed++;
                } else {
                    echo "❌ FAILED\n";
                    $failed++;
                }
            } catch (Exception $e) {
                echo "❌ ERROR: " . $e->getMessage() . "\n";
                $failed++;
            }
        }

        echo "\n=== Test Results ===\n";
        echo "Passed: $passed\n";
        echo "Failed: $failed\n";
        echo "Total: " . ($passed + $failed) . "\n";
    }

    private function makeRequest($method, $endpoint, $data = null)
    {
        $url = $this->baseUrl . $endpoint;
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);

        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'status_code' => $httpCode,
            'body' => json_decode($response, true)
        ];
    }

    public function testCreateAccount()
    {
        $accountData = [
            'name' => 'Test Account',
            'balance' => '1000.00',
            'description' => 'Test account for CRUD operations',
            'contact' => 'test@example.com',
            'emplacement' => 'Test Location'
        ];

        $response = $this->makeRequest('POST', '/api/accounts', $accountData);

        if ($response['status_code'] !== 200) {
            return false;
        }

        $data = $response['body'];
        if ($data['statut'] !== 'success' || $data['message'] !== 'Account created successfully') {
            return false;
        }

        if (!isset($data['data']['id']) || $data['data']['name'] !== 'Test Account') {
            return false;
        }

        $this->createdAccountId = $data['data']['id'];
        return true;
    }

    public function testCreateAccountWithMissingName()
    {
        $accountData = [
            'balance' => '1000.00',
            'description' => 'Test account without name'
        ];

        $response = $this->makeRequest('POST', '/api/accounts', $accountData);
        $data = $response['body'];

        return $data['statut'] === 'error' && strpos($data['message'], 'name is required') !== false;
    }

    public function testCreateAccountWithInvalidBalance()
    {
        $accountData = [
            'name' => 'Test Account',
            'balance' => 'invalid_balance',
            'description' => 'Test account with invalid balance'
        ];

        $response = $this->makeRequest('POST', '/api/accounts', $accountData);
        $data = $response['body'];

        return $data['statut'] === 'error' && strpos($data['message'], 'balance') !== false;
    }

    public function testGetAccountsList()
    {
        $response = $this->makeRequest('GET', '/api/accounts');

        if ($response['status_code'] !== 200) {
            return false;
        }

        $data = $response['body'];
        return $data['statut'] === 'success' && 
               isset($data['data']['accounts']) && 
               isset($data['data']['pagination']) &&
               is_array($data['data']['accounts']);
    }

    public function testGetAccountsListWithPagination()
    {
        $response = $this->makeRequest('GET', '/api/accounts?page=1&limit=5');

        if ($response['status_code'] !== 200) {
            return false;
        }

        $data = $response['body'];
        return $data['statut'] === 'success' && 
               $data['data']['pagination']['current_page'] === 1 &&
               $data['data']['pagination']['items_per_page'] === 5;
    }

    public function testGetAccountsListWithFilters()
    {
        $response = $this->makeRequest('GET', '/api/accounts?synced=false&search=Test');

        if ($response['status_code'] !== 200) {
            return false;
        }

        $data = $response['body'];
        return $data['statut'] === 'success' && is_array($data['data']['accounts']);
    }

    public function testGetSingleAccount()
    {
        if (!$this->createdAccountId) {
            $this->testCreateAccount();
        }

        $response = $this->makeRequest('GET', '/api/accounts/' . $this->createdAccountId);

        if ($response['status_code'] !== 200) {
            return false;
        }

        $data = $response['body'];
        return $data['statut'] === 'success' && 
               $data['data']['id'] == $this->createdAccountId &&
               isset($data['data']['online_transactions_count']) &&
               isset($data['data']['offline_transactions_count']);
    }

    public function testGetNonExistentAccount()
    {
        $response = $this->makeRequest('GET', '/api/accounts/99999');
        $data = $response['body'];

        return $data['statut'] === 'error' && strpos($data['message'], 'not found') !== false;
    }

    public function testUpdateAccount()
    {
        if (!$this->createdAccountId) {
            $this->testCreateAccount();
        }

        $updateData = [
            'name' => 'Updated Test Account',
            'balance' => '1500.00',
            'description' => 'Updated description',
            'synced' => true
        ];

        $response = $this->makeRequest('PUT', '/api/accounts/' . $this->createdAccountId, $updateData);

        if ($response['status_code'] !== 200) {
            return false;
        }

        $data = $response['body'];
        return $data['statut'] === 'success' && 
               $data['data']['name'] === 'Updated Test Account' &&
               $data['data']['balance'] === '1500.00' &&
               $data['data']['synced'] === true;
    }

    public function testUpdateAccountWithInvalidData()
    {
        if (!$this->createdAccountId) {
            $this->testCreateAccount();
        }

        $updateData = [
            'name' => '', // Empty name should fail
            'balance' => 'invalid'
        ];

        $response = $this->makeRequest('PUT', '/api/accounts/' . $this->createdAccountId, $updateData);
        $data = $response['body'];

        return $data['statut'] === 'error';
    }

    public function testUpdateNonExistentAccount()
    {
        $updateData = ['name' => 'Updated Name'];
        $response = $this->makeRequest('PUT', '/api/accounts/99999', $updateData);
        $data = $response['body'];

        return $data['statut'] === 'error' && strpos($data['message'], 'not found') !== false;
    }

    public function testSearchAccounts()
    {
        $response = $this->makeRequest('GET', '/api/accounts/search?q=Test');

        if ($response['status_code'] !== 200) {
            return false;
        }

        $data = $response['body'];
        return $data['statut'] === 'success' && 
               $data['data']['search_term'] === 'Test' &&
               is_array($data['data']['accounts']) &&
               isset($data['data']['results_count']);
    }

    public function testSearchAccountsWithoutQuery()
    {
        $response = $this->makeRequest('GET', '/api/accounts/search');
        $data = $response['body'];

        return $data['statut'] === 'error' && strpos($data['message'], 'Search term is required') !== false;
    }

    public function testGetAccountStats()
    {
        $response = $this->makeRequest('GET', '/api/accounts/stats');

        if ($response['status_code'] !== 200) {
            return false;
        }

        $data = $response['body'];
        return $data['statut'] === 'success' && 
               isset($data['data']['accounts']) &&
               isset($data['data']['total_accounts']) &&
               is_array($data['data']['accounts']);
    }

    public function testSoftDeleteAccount()
    {
        if (!$this->createdAccountId) {
            $this->testCreateAccount();
        }

        $response = $this->makeRequest('DELETE', '/api/accounts/' . $this->createdAccountId);

        if ($response['status_code'] !== 200) {
            return false;
        }

        $data = $response['body'];
        if ($data['statut'] !== 'success' || $data['message'] !== 'Account deleted successfully') {
            return false;
        }

        // Verify account is no longer accessible
        $getResponse = $this->makeRequest('GET', '/api/accounts/' . $this->createdAccountId);
        $getData = $getResponse['body'];

        return $getData['statut'] === 'error';
    }

    public function testDeleteNonExistentAccount()
    {
        $response = $this->makeRequest('DELETE', '/api/accounts/99999');
        $data = $response['body'];

        return $data['statut'] === 'error' && strpos($data['message'], 'not found') !== false;
    }

    public function testRestoreAccount()
    {
        // Create a new account for this test
        $this->testCreateAccount();
        $accountId = $this->createdAccountId;

        // Delete it first
        $this->makeRequest('DELETE', '/api/accounts/' . $accountId);

        // Now restore it
        $response = $this->makeRequest('POST', '/api/accounts/' . $accountId . '/restore');

        if ($response['status_code'] !== 200) {
            return false;
        }

        $data = $response['body'];
        if ($data['statut'] !== 'success' || $data['message'] !== 'Account restored successfully') {
            return false;
        }

        // Verify account is accessible again
        $getResponse = $this->makeRequest('GET', '/api/accounts/' . $accountId);
        $getData = $getResponse['body'];

        return $getData['statut'] === 'success';
    }

    public function testRestoreNonDeletedAccount()
    {
        if (!$this->createdAccountId) {
            $this->testCreateAccount();
        }

        $response = $this->makeRequest('POST', '/api/accounts/' . $this->createdAccountId . '/restore');
        $data = $response['body'];

        return $data['statut'] === 'error' && strpos($data['message'], 'not deleted') !== false;
    }

    public function testRestoreNonExistentAccount()
    {
        $response = $this->makeRequest('POST', '/api/accounts/99999/restore');
        $data = $response['body'];

        return $data['statut'] === 'error' && strpos($data['message'], 'not found') !== false;
    }
}

// Run the tests if this script is executed directly
if (php_sapi_name() === 'cli') {
    $tester = new AccountCrudTester();
    $tester->runAllTests();
}