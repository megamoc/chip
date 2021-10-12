<?php


namespace Sebmoc\Interestaccount;


interface StatisticsEndpointInterface
{
    public function getAccountIncome(string $account_id): ?int;
}