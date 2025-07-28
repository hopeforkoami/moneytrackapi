<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AccountCrudTest extends WebTestCase
{
    private $client;
    private $createdAccountId;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testCreateAccount(): void
    {
        $accountData = [
            'name' => 'Test Account',
            'balance' => '1000.00',
            'description' => 'Test account for CRUD operations',
            'contact' => 'test@example.com',
            'emplacement' => 'Test Location'
        ];

        $this->client->request(
            'POST',
            '/api/accounts',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($accountData)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['statut']);
        $this->assertEquals('Account created successfully', $responseData['message']);
        $this->assertNotNull($responseData['data']['id']);
        $this->assertEquals('Test Account', $responseData['data']['name']);
        $this->assertEquals('1000.00', $responseData['data']['balance']);

        // Store the created account ID for other tests
        $this->createdAccountId = $responseData['data']['id'];
    }

    public function testCreateAccountWithMissingName(): void
    {
        $accountData = [
            'balance' => '1000.00',
            'description' => 'Test account without name'
        ];

        $this->client->request(
            'POST',
            '/api/accounts',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($accountData)
        );

        $response = $this->client->getResponse();
        $responseData = json_decode($response->getContent(), true);
        
        $this->assertEquals('error', $responseData['statut']);
        $this->assertStringContains('name is required', $responseData['message']);
    }

    public function testCreateAccountWithInvalidBalance(): void
    {
        $accountData = [
            'name' => 'Test Account',
            'balance' => 'invalid_balance',
            'description' => 'Test account with invalid balance'
        ];

        $this->client->request(
            'POST',
            '/api/accounts',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($accountData)
        );

        $response = $this->client->getResponse();
        $responseData = json_decode($response->getContent(), true);
        
        $this->assertEquals('error', $responseData['statut']);
        $this->assertStringContains('balance', $responseData['message']);
    }

    /**
     * @depends testCreateAccount
     */
    public function testGetAccountsList(): void
    {
        $this->client->request('GET', '/api/accounts');

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['statut']);
        $this->assertArrayHasKey('accounts', $responseData['data']);
        $this->assertArrayHasKey('pagination', $responseData['data']);
        $this->assertIsArray($responseData['data']['accounts']);
    }

    /**
     * @depends testCreateAccount
     */
    public function testGetAccountsListWithPagination(): void
    {
        $this->client->request('GET', '/api/accounts?page=1&limit=5');

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['statut']);
        $this->assertArrayHasKey('pagination', $responseData['data']);
        $this->assertEquals(1, $responseData['data']['pagination']['current_page']);
        $this->assertEquals(5, $responseData['data']['pagination']['items_per_page']);
    }

    /**
     * @depends testCreateAccount
     */
    public function testGetAccountsListWithFilters(): void
    {
        $this->client->request('GET', '/api/accounts?synced=false&search=Test');

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['statut']);
        $this->assertIsArray($responseData['data']['accounts']);
    }

    /**
     * @depends testCreateAccount
     */
    public function testGetSingleAccount(): void
    {
        // First create an account to get its ID
        $this->testCreateAccount();
        
        $this->client->request('GET', '/api/accounts/' . $this->createdAccountId);

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['statut']);
        $this->assertEquals($this->createdAccountId, $responseData['data']['id']);
        $this->assertArrayHasKey('online_transactions_count', $responseData['data']);
        $this->assertArrayHasKey('offline_transactions_count', $responseData['data']);
    }

    public function testGetNonExistentAccount(): void
    {
        $this->client->request('GET', '/api/accounts/99999');

        $response = $this->client->getResponse();
        $responseData = json_decode($response->getContent(), true);
        
        $this->assertEquals('error', $responseData['statut']);
        $this->assertStringContains('not found', $responseData['message']);
    }

    /**
     * @depends testCreateAccount
     */
    public function testUpdateAccount(): void
    {
        // First create an account
        $this->testCreateAccount();

        $updateData = [
            'name' => 'Updated Test Account',
            'balance' => '1500.00',
            'description' => 'Updated description',
            'synced' => true
        ];

        $this->client->request(
            'PUT',
            '/api/accounts/' . $this->createdAccountId,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['statut']);
        $this->assertEquals('Updated Test Account', $responseData['data']['name']);
        $this->assertEquals('1500.00', $responseData['data']['balance']);
        $this->assertTrue($responseData['data']['synced']);
    }

    /**
     * @depends testCreateAccount
     */
    public function testUpdateAccountWithInvalidData(): void
    {
        // First create an account
        $this->testCreateAccount();

        $updateData = [
            'name' => '', // Empty name should fail
            'balance' => 'invalid'
        ];

        $this->client->request(
            'PUT',
            '/api/accounts/' . $this->createdAccountId,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );

        $response = $this->client->getResponse();
        $responseData = json_decode($response->getContent(), true);
        
        $this->assertEquals('error', $responseData['statut']);
    }

    public function testUpdateNonExistentAccount(): void
    {
        $updateData = [
            'name' => 'Updated Name'
        ];

        $this->client->request(
            'PUT',
            '/api/accounts/99999',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );

        $response = $this->client->getResponse();
        $responseData = json_decode($response->getContent(), true);
        
        $this->assertEquals('error', $responseData['statut']);
        $this->assertStringContains('not found', $responseData['message']);
    }

    /**
     * @depends testCreateAccount
     */
    public function testSearchAccounts(): void
    {
        // First create an account
        $this->testCreateAccount();

        $this->client->request('GET', '/api/accounts/search?q=Test');

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['statut']);
        $this->assertEquals('Test', $responseData['data']['search_term']);
        $this->assertIsArray($responseData['data']['accounts']);
        $this->assertGreaterThanOrEqual(0, $responseData['data']['results_count']);
    }

    public function testSearchAccountsWithoutQuery(): void
    {
        $this->client->request('GET', '/api/accounts/search');

        $response = $this->client->getResponse();
        $responseData = json_decode($response->getContent(), true);
        
        $this->assertEquals('error', $responseData['statut']);
        $this->assertStringContains('Search term is required', $responseData['message']);
    }

    /**
     * @depends testCreateAccount
     */
    public function testGetAccountStats(): void
    {
        $this->client->request('GET', '/api/accounts/stats');

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['statut']);
        $this->assertArrayHasKey('accounts', $responseData['data']);
        $this->assertArrayHasKey('total_accounts', $responseData['data']);
        $this->assertIsArray($responseData['data']['accounts']);
    }

    /**
     * @depends testCreateAccount
     */
    public function testSoftDeleteAccount(): void
    {
        // First create an account
        $this->testCreateAccount();

        $this->client->request('DELETE', '/api/accounts/' . $this->createdAccountId);

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['statut']);
        $this->assertEquals('Account deleted successfully', $responseData['message']);
        $this->assertNotNull($responseData['data']['deleted_at']);

        // Verify account is no longer accessible via normal GET
        $this->client->request('GET', '/api/accounts/' . $this->createdAccountId);
        $getResponse = $this->client->getResponse();
        $getResponseData = json_decode($getResponse->getContent(), true);
        $this->assertEquals('error', $getResponseData['statut']);
    }

    public function testDeleteNonExistentAccount(): void
    {
        $this->client->request('DELETE', '/api/accounts/99999');

        $response = $this->client->getResponse();
        $responseData = json_decode($response->getContent(), true);
        
        $this->assertEquals('error', $responseData['statut']);
        $this->assertStringContains('not found', $responseData['message']);
    }

    /**
     * @depends testSoftDeleteAccount
     */
    public function testRestoreAccount(): void
    {
        // First create and delete an account
        $this->testCreateAccount();
        $this->client->request('DELETE', '/api/accounts/' . $this->createdAccountId);

        // Now restore it
        $this->client->request('POST', '/api/accounts/' . $this->createdAccountId . '/restore');

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['statut']);
        $this->assertEquals('Account restored successfully', $responseData['message']);
        $this->assertNull($responseData['data']['deleted_at']);

        // Verify account is accessible again
        $this->client->request('GET', '/api/accounts/' . $this->createdAccountId);
        $getResponse = $this->client->getResponse();
        $getResponseData = json_decode($getResponse->getContent(), true);
        $this->assertEquals('success', $getResponseData['statut']);
    }

    public function testRestoreNonDeletedAccount(): void
    {
        // First create an account (not deleted)
        $this->testCreateAccount();

        $this->client->request('POST', '/api/accounts/' . $this->createdAccountId . '/restore');

        $response = $this->client->getResponse();
        $responseData = json_decode($response->getContent(), true);
        
        $this->assertEquals('error', $responseData['statut']);
        $this->assertStringContains('not deleted', $responseData['message']);
    }

    public function testRestoreNonExistentAccount(): void
    {
        $this->client->request('POST', '/api/accounts/99999/restore');

        $response = $this->client->getResponse();
        $responseData = json_decode($response->getContent(), true);
        
        $this->assertEquals('error', $responseData['statut']);
        $this->assertStringContains('not found', $responseData['message']);
    }
}