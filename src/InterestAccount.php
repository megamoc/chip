<?php


namespace Sebmoc\Interestaccount;

use Exception;

class InterestAccount
{
    private AccountPersistInterface $account_persist;
    private StatisticsEndpointInterface $statistics_endpoint;

    /**
     * InterestAccount constructor.
     * @param AccountPersistInterface $account_persist
     * @param StatisticsEndpointInterface $statistics_endpoint
     */
    public function __construct(AccountPersistInterface $account_persist, StatisticsEndpointInterface $statistics_endpoint)
    {
        // Store the Account Persist and Statistics endpoint as properties
        $this->account_persist = $account_persist;
        $this->statistics_endpoint = $statistics_endpoint;
    }

    /**
     * Open an account
     * @param string $account_id
     * @return bool
     * @throws Exception
     */
    public function openAccount(string $account_id): bool
    {
        // Validate the incoming account id
        if ($this->validate_account_id($account_id) == false) {
            throw new Exception("The account id is invalid");
        }

        // Search for the account and if it already exists throw an error
        $account_data = $this->account_persist->getAccount($account_id);
        if ($account_data !== null) {
            return false;
        }

        // Get the income from the account id by using the API
        $income = $this->statistics_endpoint->getAccountIncome($account_id);

        // Calculate the interest6 rate based on the income
        $rate = 0.5;
        if ($income !== null) {
            $rate = $income < 500000 ? 0.93 : 1.02;
        }

        // Create the account data block to be sent to persist
        $account_data = [
            'id' => $account_id,
            'income' => $income,
            'rate' => $rate,
            'balance'=>0,
            'fraction'=>0,
            'transaction_list' => []
        ];

        // Set the account data using the newly created data block
        $this->account_persist->putAccount($account_id, $account_data);

        // Return success state
        return true;
    }

    /**
     * Add funds into an account
     * @param string $account_id
     * @param int $amount_in_pence
     * @throws Exception
     */
    public function depositFunds(string $account_id, int $amount_in_pence): void
    {
        // Validate the incoming account id
        if ($this->validate_account_id($account_id) == false) {
            throw new Exception("The account id is invalid");
        }
        if ($amount_in_pence <= 0) {
            throw new Exception("A deposit can not be zero or a negative number");
        }
        $account_data = $this->account_persist->getAccount($account_id);
        if ($account_data === null) {
            throw new Exception("The account does not exist");
        }
        $account_data['balance'] += $amount_in_pence;
        $account_data['transaction_list'][] = ['type'=>'Deposit', 'amount_in_pence'=>$amount_in_pence, 'balance'=>$account_data['balance']];
        $this->account_persist->putAccount($account_id, $account_data);
    }

    public function calculateInterest(string $account_id)
    {
        // Validate the incoming account id
        if ($this->validate_account_id($account_id) == false) {
            throw new Exception("The account id is invalid");
        }

        // Load the account data and throw an error if it does not exist
        $account_data = $this->account_persist->getAccount($account_id);
        if ($account_data === null) {
            throw new Exception("The account does not exist");
        }

        // Assuming 365 days in a year (ignore leap years)
        $days_in_year = floatval(365);

        // Calculate the interest to be added taking into account the stored fraction
        $current_balance = floatval($account_data['balance']) + floatval($account_data['fraction']);
        $annual_interest = $current_balance * ($account_data['rate'] / 100);
        $three_days_interest = ($annual_interest / $days_in_year) * 3;
        $amount_to_be_added = intval($three_days_interest);

        // Update the balance
        $account_data['balance'] += $amount_to_be_added;

        // Take the true calculated amount of interest and subtract the amount added to give us a fraction to store
        $account_data['fraction'] = $three_days_interest - $amount_to_be_added;

        // Add the transaction into the list
        $account_data['transaction_list'][] = ['type'=>'Interest', 'amount'=>$amount_to_be_added, 'balance'=>$account_data['balance']];

        // Update the account data
        $this->account_persist->putAccount($account_id, $account_data);
    }

    public function getStatement(string $account_id): array
    {
        // Validate the incoming account id
        if ($this->validate_account_id($account_id) == false) {
            throw new Exception("The account id is invalid");
        }

        // Load the account data and throw an error if it does not exist
        $account_data = $this->account_persist->getAccount($account_id);
        if ($account_data === null) {
            throw new Exception("The account does not exist");
        }

        return $account_data['transaction_list'];
    }


    /**
     * Validates an account id
     * @param string $account_id
     * @return bool
     */
    private function validate_account_id(string $account_id): bool
    {
        return (preg_match("/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i", $account_id) != 0);
    }

}