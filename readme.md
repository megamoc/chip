#Interest Account Assignment
#### Sebastian Moc

## Introduction

The library is packaged as a composer library and can be used by requiring it in to another project (assuming you have a satis server or using packagist.org). The project has been completed under the namespace 'Sebmoc\Interestaccount' and currently contains one class and 2 interfaces.

## Instantiation
To create an instance of the InterestAccount class 2 parameters must be passed into the constructor and these must implement 'AccountPersistInterface' and 'StatisticsEndpointInterface'. The two implmentations are then stored as properties for the main class to access as long as the instance exists.

## Testing
For testing there are mock classes for both interfaces, these are named 'AccountPersistMock' and 'StatisticsEndpointMock' with the namespace 'Sebmoc\Interestaccount\Mocks'. 

The account persist mock simply keeps an array of account data using the methods getAccount and putAccount, these methods simply assign an array key with the data supplied in the parameter.

The statistics endpoint only contains one method 'getAccountIncome' which returns the income for a given account id (this is null if the account does not exist), the data is held in the class as a private property.

### Statistics endpoint data

* 88224979-0001-4e32-9458-55836e4e1f95 = 675699
* 88224979-0002-4e32-9458-55836e4e1f95 = 305634
* 88224979-0003-4e32-9458-55836e4e1f95 = 500000

A forth account is also used to test if the defrault rate of 0.5 is used

### How to run the tests

1 Extract the bundle 
2 Run composer install
3 Run PHPUnit using the config file 'phpunit.xml'

I have not included PHPUnit and composer phar files as its not good practice putting them in a project repository. but you can get them easily by going to the following pages.

* https://getcomposer.org/download/
* https://phar.phpunit.de/phpunit-9.5.phar

When running the tests you should see 50 assertions

## Assumptions

* I have set the days in a year to be 365 days
* The package has been done as a composer library with no main program just the tests
* I have used PHP 8.0

