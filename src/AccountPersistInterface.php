<?php


namespace Sebmoc\Interestaccount;


interface AccountPersistInterface
{
    public function getAccount(string $account_id): ?array;
    public function putAccount(string $account_id, array $account_data): void;
}