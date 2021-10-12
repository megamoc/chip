<?php


namespace Sebmoc\Interestaccount\Mocks;

use Sebmoc\Interestaccount\AccountPersistInterface;

class AccountPersistMock implements AccountPersistInterface
{
    private array $mock_data;

    public function __construct()
    {
        $this->mock_data = [];
    }

    public function getAccount(string $account_id): ?array
    {
        return array_key_exists($account_id, $this->mock_data) ? $this->mock_data[$account_id] : null;
    }

    public function putAccount(string $account_id, array $account_data): void
    {
        $this->mock_data[$account_id] = $account_data;
    }
}