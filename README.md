# People Matter
[![Latest Version](https://img.shields.io/github/release/zenapply/php-peoplematter.svg?style=flat-square)](https://github.com/zenapply/php-peoplematter/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![HHVM Status](http://hhvm.h4cc.de/badge/zenapply/php-peoplematter.svg?style=flat-square)](http://hhvm.h4cc.de/package/zenapply/php-peoplematter)
[![Build Status](https://travis-ci.org/zenapply/php-peoplematter.svg?branch=master)](https://travis-ci.org/zenapply/php-peoplematter)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/zenapply/php-peoplematter/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/zenapply/php-peoplematter/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/zenapply/php-peoplematter/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/zenapply/php-peoplematter/?branch=master)
[![Dependency Status](https://www.versioneye.com/user/projects/56f3252c35630e0029db0187/badge.svg?style=flat)](https://www.versioneye.com/user/projects/56f3252c35630e0029db0187)
[![Total Downloads](https://img.shields.io/packagist/dt/zenapply/php-peoplematter.svg?style=flat-square)](https://packagist.org/packages/zenapply/php-peoplematter) 

## Installation

Install via [composer](https://getcomposer.org/) - In the terminal:
```bash
composer require zenapply/php-peoplematter
```

## Usage

Create it
```php
use Zenapply\PeopleMatter\PeopleMatter;
$client = new PeopleMatter("email", "password", "businessalias");
```

Get all Business Units
```php
$units = $client->businessUnits(); // Returns an array of Zenapply\PeopleMatter\BusinessUnit Objects
print_r($units[0]);
# Zenapply\PeopleMatter\BusinessUnit Object
# (
#     [Id] => a1c01c85-fa26-4662-925c-a63b00123456
#     [Business] => Array
#         (
#             [Name] => Company Name
#             [Alias] => businessalias
#             [Id] => 77806413-6c3c-40f6-a375-a63b00123456
#             [URI] => https://sandbox.peoplematter.com/api/business/77806413-6c3c-40f6-a375-a63b0123456
#         )
# 
#     [Name] => Name of Unit
#     [UnitNumber] => 105
#     [Status] => 0
#     [ActivationDate] => 2016-06-29
#     [DeactivationDate] =>
#     [Address] => Array
#         (
#             [StreetAddress1] => 599 West Main Street
#             [StreetAddress2] =>
#             [City] => City
#             [State] => State
#             [ZipCode] => 55555
#             [Country] => US
#         )
# 
#     [TimeZone] => (UTC-07:00) Mountain Time (US & Canada)
#     [PhoneNumber] => (555) 555-5555
#     [TaxIdentificationNumber] => 00-0000000
#     [EmailAddress] => name@email.com
#     [EverifyEnabled] =>
#     [I9EmployerName] =>
#     [BrandNameJobBoards] =>
#     [IntegrationAttributes] => Array
#         (
#             [CustomField1] =>
#             [CustomField2] =>
#             [CustomField3] =>
#         )
# 
#     [BusinessUnitGroup] =>
#     [AssignAllActiveJobs] =>
# )

```
