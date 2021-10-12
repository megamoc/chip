<?php

namespace Sebmoc\Interestaccount\Mocks;

use Sebmoc\Interestaccount\StatisticsEndpointInterface;

class StatisticsEndpointMock implements StatisticsEndpointInterface
{
    private array $mock_data;

    public function __construct()
    {
        $this->mock_data = [
            '88224979-0001-4e32-9458-55836e4e1f95' => 675699,
            '88224979-0002-4e32-9458-55836e4e1f95' => 305634,
            '88224979-0003-4e32-9458-55836e4e1f95' => 500000,
        ];
    }

    public function getAccountIncome(string $account_id): ?int
    {
        if (array_key_exists($account_id, $this->mock_data) == true) {
            return floatval($this->mock_data[$account_id]);
        }
        return null;
    }
}