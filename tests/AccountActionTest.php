<?php


use PHPUnit\Framework\TestCase;
use Sebmoc\Interestaccount\InterestAccount;
use Sebmoc\Interestaccount\Mocks\AccountPersistMock;
use Sebmoc\Interestaccount\Mocks\StatisticsEndpointMock;

class AccountActionTest extends TestCase
{
    /**
     * Test the library initializes ok
     */
    public function testObjectsInitialize()
    {
        try {
            // Create a mock Statistics API class
            $statistics_endpoint = new StatisticsEndpointMock();
            $this->assertInstanceOf('Sebmoc\\Interestaccount\\StatisticsEndpointInterface', $statistics_endpoint, 'Statistics Mock is not an instance of StatisticsEndpointInterface');
            // Create a mock Account Persist class
            $account_persist = new AccountPersistMock();
            $this->assertInstanceOf('Sebmoc\\Interestaccount\\AccountPersistInterface', $account_persist, 'Account Persist Mock is not an instance of AccountPersistInterface');
            // Create the interest account object
            $interest_account = new InterestAccount($account_persist, $statistics_endpoint);
            $this->assertInstanceOf('Sebmoc\\Interestaccount\\InterestAccount', $interest_account, 'The main interest account class did not initialize');
        } catch (Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }

    /**
     * Test the opening of accounts and that duplicates are not created
     */
    public function testCreate()
    {
        try {
            $statistics_endpoint = new StatisticsEndpointMock();
            $account_persist = new AccountPersistMock();
            $interest_account = new InterestAccount($account_persist, $statistics_endpoint);

            // Attempt to open an account using the first test id
            $result = $interest_account->openAccount("88224979-0001-4e32-9458-55836e4e1f95");
            $this->assertTrue($result, 'The account failed to create even though the account does not already exist');

            // Attempt to open an account using the second test id
            $result = $interest_account->openAccount("88224979-0002-4e32-9458-55836e4e1f95");
            $this->assertTrue($result, 'The account failed to create even though the account does not already exist');

            // Attempt to open an account using the second test id (this should fail
            $result = $interest_account->openAccount("88224979-0002-4e32-9458-55836e4e1f95");
            $this->assertFalse($result, 'The account was created even though it already exists');
        } catch (Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }

    public function testInterestRates()
    {
        try {
            $statistics_endpoint = new StatisticsEndpointMock();
            $account_persist = new AccountPersistMock();
            $interest_account = new InterestAccount($account_persist, $statistics_endpoint);

            // Open the first test account which has an income of 6756.99 so should have a rate of 1.02
            $result = $interest_account->openAccount("88224979-0001-4e32-9458-55836e4e1f95");
            $this->assertTrue($result, 'The account failed to create even though the account does not already exist');
            $account_data = $account_persist->getAccount('88224979-0001-4e32-9458-55836e4e1f95');
            $this->assertIsArray($account_data, 'we were expecting an array here');
            $this->assertArrayHasKey('rate', $account_data, 'The account rate is not present');
            $this->assertEquals(1.02, $account_data['rate']);

            // Open the second test account which has an income of 3056.34 so should have a rate of 0.93
            $result = $interest_account->openAccount("88224979-0002-4e32-9458-55836e4e1f95");
            $this->assertTrue($result, 'The account failed to create even though the account does not already exist');
            $account_data = $account_persist->getAccount('88224979-0002-4e32-9458-55836e4e1f95');
            $this->assertIsArray($account_data, 'we were expecting an array here');
            $this->assertArrayHasKey('rate', $account_data, 'The account rate is not present');
            $this->assertEquals(0.93, $account_data['rate']);

            // Open the third test account which has an income of 5000 so should have a rate of 1.02
            $result = $interest_account->openAccount("88224979-0003-4e32-9458-55836e4e1f95");
            $this->assertTrue($result, 'The account failed to create even though the account does not already exist');
            $account_data = $account_persist->getAccount('88224979-0003-4e32-9458-55836e4e1f95');
            $this->assertIsArray($account_data, 'we were expecting an array here');
            $this->assertArrayHasKey('rate', $account_data, 'The account rate is not present');
            $this->assertEquals(1.02, $account_data['rate']);

            // Open the forth test account which does not exists in stats so should have a rate of 0.5
            $result = $interest_account->openAccount("88224979-0004-4e32-9458-55836e4e1f95");
            $this->assertTrue($result, 'The account failed to create even though the account does not already exist');
            $account_data = $account_persist->getAccount('88224979-0004-4e32-9458-55836e4e1f95');
            $this->assertIsArray($account_data, 'we were expecting an array here');
            $this->assertArrayHasKey('rate', $account_data, 'The account rate is not present');
            $this->assertEquals(0.5, $account_data['rate']);
        } catch (Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }

    /**
     * Test depositing funds into an account and track the balance
     */
    public function testDeposit()
    {
        try {
            // Set up our main classes
            $statistics_endpoint = new StatisticsEndpointMock();
            $account_persist = new AccountPersistMock();
            $interest_account = new InterestAccount($account_persist, $statistics_endpoint);

            // Open two accounts to test with
            $result = $interest_account->openAccount("88224979-0001-4e32-9458-55836e4e1f95");
            $this->assertTrue($result, 'The account failed to create even though the account does not already exist');
            $result = $interest_account->openAccount("88224979-0002-4e32-9458-55836e4e1f95");
            $this->assertTrue($result, 'The account failed to create even though the account does not already exist');

            // Add 500 pounds to account 1 and test the total
            $interest_account->depositFunds('88224979-0001-4e32-9458-55836e4e1f95', 50000);
            $account_data = $account_persist->getAccount('88224979-0001-4e32-9458-55836e4e1f95');
            $this->assertEquals(50000, $account_data['balance']);

            // Add 100 pounds to account 1 and test the total
            $interest_account->depositFunds('88224979-0001-4e32-9458-55836e4e1f95', 10000);
            $account_data = $account_persist->getAccount('88224979-0001-4e32-9458-55836e4e1f95');
            $this->assertEquals(60000, $account_data['balance']);

            // Add 300 pounds to account 2 and test the total
            $interest_account->depositFunds('88224979-0002-4e32-9458-55836e4e1f95', 30000);
            $account_data = $account_persist->getAccount('88224979-0002-4e32-9458-55836e4e1f95');
            $this->assertEquals(30000, $account_data['balance']);

            // Add 200 pounds to account 1 and test the total
            $interest_account->depositFunds('88224979-0001-4e32-9458-55836e4e1f95', 20000);
            $account_data = $account_persist->getAccount('88224979-0001-4e32-9458-55836e4e1f95');
            $this->assertEquals(80000, $account_data['balance']);

            // Add 200 pounds to account 2 and test the total
            $interest_account->depositFunds('88224979-0002-4e32-9458-55836e4e1f95', 20000);
            $account_data = $account_persist->getAccount('88224979-0002-4e32-9458-55836e4e1f95');
            $this->assertEquals(50000, $account_data['balance']);
        } catch (Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }

    /**
     * Test an exception is thrown if an attempt to deposit money into an account that does not exist
     */
    public function testDepositNoAccount()
    {
        try {
            $statistics_endpoint = new StatisticsEndpointMock();
            $account_persist = new AccountPersistMock();
            $interest_account = new InterestAccount($account_persist, $statistics_endpoint);
            try {
                // Open test account 1 so we have at least one assert so we dont get a warning from phpunit
                $result = $interest_account->openAccount("88224979-0001-4e32-9458-55836e4e1f95");
                $this->assertTrue($result, 'The account failed to create even though the account does not already exist');

                // Attempt to add funds into an account that does not exist
                $interest_account->depositFunds('88224979-0006-4e32-9458-55836e4e1f95', 20000);

                // If we have got to this point then the test has failed as an exception should have been raised
                $this->fail("This account does nopt exist so an exception should have been thrown");
            } catch (Exception) {
                // Create a pass assert because if we are here the test has passed
                $this->assertTrue(true);
            }
        } catch(Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }

    /**
     * Test that an exception is thrown when zero or a negative amount is passed to depost
     */
    public function testDepositInvalidAmount()
    {
        try {
            $statistics_endpoint = new StatisticsEndpointMock();
            $account_persist = new AccountPersistMock();
            $interest_account = new InterestAccount($account_persist, $statistics_endpoint);

            // Open an account to run the test
            $result = $interest_account->openAccount("88224979-0001-4e32-9458-55836e4e1f95");
            $this->assertTrue($result, 'The account failed to create even though the account does not already exist');

            try {
                $interest_account->depositFunds('88224979-0001-4e32-9458-55836e4e1f95', -200);
                $this->fail("An exception should have been thrown, deposits can not be negative");
            } catch (Exception $exception) {
                $this->assertTrue(true);
            }

            try {
                $interest_account->depositFunds('88224979-0001-4e32-9458-55836e4e1f95', 0);
                $this->fail("An exception should have been thrown, deposits can not be zero");
            } catch (Exception $exception) {
                $this->assertTrue(true);
            }
        } catch (Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }

    /**
     * Tests the result of interest calculations using a data source
     */
    public function testInterestCalculation()
    {
        try {
            $statistics_endpoint = new StatisticsEndpointMock();
            $account_persist = new AccountPersistMock();
            $interest_account = new InterestAccount($account_persist, $statistics_endpoint);

            // Create the four accounts we will be using to test all the interest rates and a fringe case
            $result = $interest_account->openAccount("88224979-0001-4e32-9458-55836e4e1f95");
            $this->assertTrue($result, 'The account failed to create even though the account does not already exist');
            $result = $interest_account->openAccount("88224979-0002-4e32-9458-55836e4e1f95");
            $this->assertTrue($result, 'The account failed to create even though the account does not already exist');
            $result = $interest_account->openAccount("88224979-0003-4e32-9458-55836e4e1f95");
            $this->assertTrue($result, 'The account failed to create even though the account does not already exist');
            $result = $interest_account->openAccount("88224979-0004-4e32-9458-55836e4e1f95");
            $this->assertTrue($result, 'The account failed to create even though the account does not already exist');

            // Deposit some funds into each account to get us started
            $interest_account->depositFunds('88224979-0001-4e32-9458-55836e4e1f95', 245654);
            $interest_account->depositFunds('88224979-0002-4e32-9458-55836e4e1f95', 245654);
            $interest_account->depositFunds('88224979-0003-4e32-9458-55836e4e1f95', 245654);
            $interest_account->depositFunds('88224979-0004-4e32-9458-55836e4e1f95', 245654);

            // Calculate the interest of each account
            $interest_account->calculateInterest('88224979-0001-4e32-9458-55836e4e1f95');
            $interest_account->calculateInterest('88224979-0002-4e32-9458-55836e4e1f95');
            $interest_account->calculateInterest('88224979-0003-4e32-9458-55836e4e1f95');
            $interest_account->calculateInterest('88224979-0004-4e32-9458-55836e4e1f95');

            // Test all of the balances are as expected
            $account_data = $account_persist->getAccount('88224979-0001-4e32-9458-55836e4e1f95');
            $this->assertEquals(245674, $account_data['balance']);
            $account_data = $account_persist->getAccount('88224979-0002-4e32-9458-55836e4e1f95');
            $this->assertEquals(245672, $account_data['balance']);
            $account_data = $account_persist->getAccount('88224979-0003-4e32-9458-55836e4e1f95');
            $this->assertEquals(245674, $account_data['balance']);
            $account_data = $account_persist->getAccount('88224979-0004-4e32-9458-55836e4e1f95');
            $this->assertEquals(245664, $account_data['balance']);

            // Now we loop through and calculate the interest 30 times to test the accumulation
            for($cycle = 0; $cycle < 30; $cycle++) {
                $interest_account->calculateInterest('88224979-0001-4e32-9458-55836e4e1f95');
                $interest_account->calculateInterest('88224979-0002-4e32-9458-55836e4e1f95');
                $interest_account->calculateInterest('88224979-0003-4e32-9458-55836e4e1f95');
                $interest_account->calculateInterest('88224979-0004-4e32-9458-55836e4e1f95');
            }
            $account_data = $account_persist->getAccount('88224979-0001-4e32-9458-55836e4e1f95');
            $this->assertEquals(246274, $account_data['balance']);
            $account_data = $account_persist->getAccount('88224979-0002-4e32-9458-55836e4e1f95');
            $this->assertEquals(246212, $account_data['balance']);
            $account_data = $account_persist->getAccount('88224979-0003-4e32-9458-55836e4e1f95');
            $this->assertEquals(246274, $account_data['balance']);
            $account_data = $account_persist->getAccount('88224979-0004-4e32-9458-55836e4e1f95');
            $this->assertEquals(245964, $account_data['balance']);
        } catch (Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }

    /**
     * Test the statement output
     */
    public function testTransactionList()
    {
        try {
            $statistics_endpoint = new StatisticsEndpointMock();
            $account_persist = new AccountPersistMock();
            $interest_account = new InterestAccount($account_persist, $statistics_endpoint);

            // Create the four accounts we will be using to test all the interest rates and a fringe case
            $result = $interest_account->openAccount("88224979-0001-4e32-9458-55836e4e1f95");
            $this->assertTrue($result, 'The account failed to create even though the account does not already exist');
            $interest_account->depositFunds('88224979-0001-4e32-9458-55836e4e1f95', 245654);
            $interest_account->calculateInterest('88224979-0001-4e32-9458-55836e4e1f95');
            $interest_account->calculateInterest('88224979-0001-4e32-9458-55836e4e1f95');
            $transaction_list = $interest_account->getStatement('88224979-0001-4e32-9458-55836e4e1f95');
            $this->assertIsArray($transaction_list, 'We were expecting an array here');
            $this->assertEquals(3, sizeof($transaction_list), 'This array should have 3 items');
            $test_hash = sha1(json_encode($transaction_list));
            $this->assertEquals('20133bdad947a85fd72f672c5672f8a92fcac863', $test_hash, 'The return hash does not match the expected output');
        } catch (Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }



}