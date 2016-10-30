<?php

namespace Zenapply\PeopleMatter\Tests;

use Zenapply\PeopleMatter\PeopleMatter;
use Zenapply\PeopleMatter\BusinessUnit;
use Zenapply\PeopleMatter\Job;
use Zenapply\PeopleMatter\Person;
use GuzzleHttp\Client;
use DateTime;

class PeopleMatterTest extends TestCase
{
    protected $request;

    public function testItCreatesAnInstanceOfHttpRequest()
    {
        $x = new PeopleMatter("username", "password", "alias");
        $this->assertInstanceOf(PeopleMatter::class, $x);
    }

    // public function testGetPerson()
    // {
    //     $x = $this->getInstance();
    //     $response = $x->getPerson("test@dev.com");
    //     $this->assertInstanceOf(Person::class, $response[0]);
    // }

    public function testGetBusinessUnits()
    {
        $x = $this->getInstance();
        $response = $x->getBusinessUnits();
        $this->assertInstanceOf(BusinessUnit::class, $response[0]);
    }

    public function testGetJobs()
    {
        $x = $this->getInstance();
        $response = $x->getJobs();
        $this->assertInstanceOf(Job::class, $response[0]);
    }

    public function testHiring()
    {
        $x = $this->getInstance();
        $b = new BusinessUnit([
            "UnitNumber" => 105
        ]);
        $j = new Job([
            "Code" => 210
        ]);
        $p = new Person([
            "FirstName" => "Cafe",
            "LastName" => "Zupas",
            "EmailAddress" => "test1@dev.com",
            "Username" => "",
            "Id" => "",
            "URI" => ""
        ]);
        $response = $x->hire($p, $j, $b, new DateTime('NOW'), "PartTime");
        var_dump($response);
    }

    protected function getInstance()
    {
        return new PeopleMatter("CafeZupasAPIUser@dev.com", "Mri54NNNDaLo9NevXU1u", "cafezupassandbox", "sandbox.peoplematter.com", $this->getClientMock());
    }

    protected function getClientMock()
    {
        return new Client([
            'verify' => false,
            'cookies' => true
        ]);
    }
}
