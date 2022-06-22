<?php

namespace Zenapply\HRIS\PeopleMatter\Tests;

use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Zenapply\HRIS\PeopleMatter\Models\BusinessUnit;
use Zenapply\HRIS\PeopleMatter\Models\Job;
use Zenapply\HRIS\PeopleMatter\Models\Person;
use Zenapply\HRIS\PeopleMatter\Models\Employee;
use Zenapply\HRIS\PeopleMatter\PeopleMatter;

class PeopleMatterTest extends TestCase
{
    protected $request;

    public function testItCreatesAnInstanceOfHttpRequest()
    {
        $x = new PeopleMatter("username", "password", "alias");
        $this->assertInstanceOf(PeopleMatter::class, $x);
    }

    public function testGetPerson()
    {
        $x = $this->getInstance([
            $this->getLoginResponse(),
            $this->getResponse([
                "Records" => [[
                    "FirstName" => "Test",
                    "LastName" => "Name",
                    "EmailAddress" => "test@dev.com",
                    "Username" => "foobar",
                    "Id" => "7f860497-56e6-4033-8fb9-a6af01737795",
                ]],
                "TotalPages"=>1
            ])
        ]);
        $response = $x->getEmployeeByEmail("test@dev.com");
        $this->assertInstanceOf(Employee::class, $response);
        $this->assertInstanceOf(Person::class, $response);
        $this->assertEquals("test@dev.com", $response->EmailAddress);
    }

    public function testGetBusinessUnits()
    {
        $x = $this->getInstance([
            $this->getLoginResponse(),
            $this->getResponse([
                "Records" => [
                    [
                        "Id" => "a1c01c85-fa26-4662-925c-a63b00dd7747",
                        "Business" => [
                            "Name" => "Company Name",
                            "Alias" => "alias",
                            "Id" => "77806413-6c3c-40f6-a375-a63b00dd6c62",
                            "URI" => "https://sandbox.peoplematter.com/api/business/77806413-6c3c-40f6-a375-a63b00dd6c62",
                        ],
                        "Name" => "AmericanFork",
                        "UnitNumber" => 105,
                        "Status" => 0,
                        "ActivationDate" => "2016-06-29",
                        "DeactivationDate" => null,
                        "Address" => [
                            "StreetAddress1" => "500 West Main Street",
                            "StreetAddress2" => null,
                            "City" => "City",
                            "State" => "UT",
                            "ZipCode" => 55555,
                            "Country" => "US",
                        ],
                    ]
                ],
                "TotalPages"=>1
            ])
        ]);
        $response = $x->getBusinessUnits();
        $this->assertInstanceOf(BusinessUnit::class, $response[0]);
    }

    public function testGetJobs()
    {
        $x = $this->getInstance([
            $this->getLoginResponse(),
            $this->getResponse([
                "Jobs" => [
                    [
                        "Id" => "fafe0f49-d8a9-4c89-bf02-a63b00dd8e97",
                        "Business" => [
                            "Name" => "Company Name",
                            "Alias" => "alias",
                            "Id" => "77806413-6c3c-40f6-a375-a63b00dd6c62",
                            "URI" => "https://sandbox.peoplematter.com/api/business/77806413-6c3c-40f6-a375-a63b00dd6c62",
                        ],
                        "Title" => "AM Dishwasher",
                        "Description" => "This is the job description for AM Dishwasher",
                    ]
                ],
                "TotalPages"=>1
            ])
        ]);
        $response = $x->getJobs();
        $this->assertInstanceOf(Job::class, $response[0]);
    }

    public function testHiring()
    {
        $x = $this->getInstance([
            $this->getLoginResponse(),
            $this->getResponse([
                "ErrorCount" => 0,
                "Id" => "766ba311-e1b8-4ac5-b2b3-a6b000123456",
                "EmployeeRecordURI" => "https://sandbox.peoplematter.com/alias/Employees/Details?personId=766ba311-e1b8-4ac5-b2b3-a6b0004d92b5",
                "BusinessUnitEmployeeId" => "d0972a42-2b8e-4260-b326-a6b000123456",
                "PersonCreationStatus" => [
                  "StatusCode" => 0,
                  "StatusDescription" => "No match was found. Created a new Person.",
                ]
            ])
        ]);
        $b = new BusinessUnit(["Id" => "asdflkjasdf-asdf-asdf-asdfasd"]);
        $j = new Job(["Id" => "asdfasg-asdaf-qweef-asdf"]);
        $p = new Person(["FirstName" => "Cafe", "LastName" => "Zupas", "EmailAddress" => "test2@dev.com"]);
        $response = $x->hire($p, $j, $b, "PartTime", new DateTime('NOW'));
        $this->assertEquals(0, $response["ErrorCount"]);
        $this->assertEquals("766ba311-e1b8-4ac5-b2b3-a6b000123456", $response["Id"]);
    }

    protected function getInstance($responses = [])
    {
        return new PeopleMatter("email", "password", "business-id", "sandbox.peoplematter.com", $this->getClientMock($responses));
    }

    protected function getClientMock($responses = [])
    {
        // return new Client(['verify' => false, 'cookies' => true]);
        // Create a mock and queue two responses.
        // [
        //     new Response(200, ['X-Foo' => 'Bar']),
        //     new Response(202, ['Content-Length' => 0]),
        //     new RequestException("Error Communicating with Server", new Request('GET', 'test'))
        // ]

        if (count($responses) > 0) {
            $mock = new MockHandler($responses);
            $handler = HandlerStack::create($mock);
            return new Client(['verify' => false, 'cookies' => true, 'handler' => $handler]);
        }

        return new Client(['verify' => false, 'cookies' => true]);
    }

    protected function getLoginResponse()
    {
        return new Response(200, [
            "Success" => 1,
            "ErrorMessage" => null,
            "ErrorCode" => 0,
        ]);
    }

    protected function getResponse($data)
    {
        return new Response(200, [], json_encode($data));
    }
}
