<?php

namespace Zenapply\HRIS\PeopleMatter;

use DateTime;
use Exception;
use GuzzleHttp\Client;
use Zenapply\HRIS\PeopleMatter\Exceptions\PeopleMatterException;
use Zenapply\HRIS\PeopleMatter\Models\BusinessUnit;
use Zenapply\HRIS\PeopleMatter\Models\BusinessUnitEmployee;
use Zenapply\HRIS\PeopleMatter\Models\UnitEmployeeRecord;
use Zenapply\HRIS\PeopleMatter\Models\Job;
use Zenapply\HRIS\PeopleMatter\Models\Person;
use Zenapply\HRIS\PeopleMatter\Models\Employee;

class PeopleMatter
{
    protected $business_id;
    protected $authenticated = false;
    protected $client;
    protected $host;
    protected $password;
    protected $username;

    /**
     * Creates a PeopleMatter instance that can register and unregister webhooks with the API
     * @param string      $username The Username
     * @param string      $password The Password
     * @param string      $business_id    The business business_id
     * @param string      $host     The host to connect to
     * @param Client|null $client   The Guzzle client (used for testing)
     */
    public function __construct($username, $password, $business_id, $host = "api.peoplematter.com", Client $client = null)
    {
        $this->business_id = $business_id;
        $this->client = $client;
        $this->host = $host;
        $this->password = $password;
        $this->username = $username;
    }

    public function hire(Person $person, Job $job, BusinessUnit $businessUnit, $timeStatus, DateTime $hired_at = null)
    {
        $this->login();

        if ($hired_at === null) {
            $hired_at = new DateTime("now");
        }

        if (!in_array($timeStatus, ["FullTime", "PartTime"])) {
            throw new Exception("{$timeStatus} is invalid! Please use FullTime or PartTime");
        }

        $url = "https://{$this->host}/api/services/platform/hireemployee";

        return $this->request("POST", $url, [
            "json" => [
                "HireDate" => $hired_at->format("m/d/Y"),
                "Business" => [
                    "Id" => $this->business_id,
                ],
                "BusinessUnit" => [
                    "Id" => $businessUnit->Id
                ],
                "Person" => $person->toArray(),
                "JobPositions" => [
                    [
                        "Business" => [
                            "Id" => $this->business_id,
                        ],
                        "BusinessUnit" => [
                            "Id" => $businessUnit->Id
                        ],
                        "Job" => [
                            "Id" => $job->Id,
                        ],
                        "TimeStatus" => $timeStatus,
                        "Person" => $person->toArray(),
                    ]
                ]
            ]
        ]);
    }

    /**
     * @return string
     */
    protected function buildUrl($resource)
    {
        return "https://{$this->host}/api/{$resource}";
    }

    public function getBusinessUnits()
    {
        $this->login();
        
        $units = [];
        $totalPages = 0;
        $page = 0;
        $i = 0;

        do {
            $i++;
            $query = [
                "BusinessId" => $this->business_id,
                "Page" => $page++,
            ];

            $response = $this->request("GET", $this->buildUrl("businessunit"), [
                "query" => $query,
            ]);

            foreach ($response["Records"] as $unit) {
                $units[] = new BusinessUnit($unit);
            }

            $totalPages = intval($response["TotalPages"]);
        } while ($page < $totalPages);

        return $units;
    }

    public function getJobs()
    {
        $this->login();
        
        $jobs = [];
        $totalPages = 0;
        $page = 0;
        $i = 0;

        do {
            $i++;
            $response = $this->request("GET", $this->buildUrl("job"), [
                "query" => [
                    "BusinessId" => $this->business_id,
                    "Page" => $page++,
                ]
            ]);

            foreach ($response["Jobs"] as $unit) {
                $jobs[] = new Job($unit);
            }

            $totalPages = intval($response["TotalPages"]);
        } while ($page < $totalPages);

        return $jobs;
    }

    public function getPerson($id)
    {
        if (empty($id)) {
            throw new Exception("Id is invalid!");
        }
        $this->login();
        $items = [];
        $response = $this->request("GET", $this->buildUrl("person/{$id}"), [
            "query" => [
                "BusinessId" => $this->business_id,
            ]
        ]);

        $items[] = new Person($response);

        return count($items) > 0 ? $items[0] : null;
    }


    public function getEmployee($id)
    {
        if (empty($id)) {
            throw new Exception("Id is invalid!");
        }
        $this->login();
        $employees = [];
        $response = $this->request("GET", $this->buildUrl("businessunitemployee/{$id}"), [
            "query" => [
                "BusinessId" => $this->business_id,
            ]
        ]);

        return new BusinessUnitEmployee($response);
    }

    
    public function getEmployees(BusinessUnit $unit = null)
    {
        $this->login();

        $items = [];
        $totalPages = 0;
        $page = 0;
        $i = 0;

        do {
            $i++;

            $query = [
                "BusinessId" => $this->business_id,
                "SortBy" => "Person.HireDate",
                "SortAscending" => "False",
                "Page" => ++$page,
            ];
            
            if ($unit) {
                $query["BusinessUnitId"] = $unit->Id;
            }

            $response = $this->request("GET", $this->buildUrl("unitemployeerecord"), [
                "query" => $query
            ]);

            foreach ($response["Records"] as $item) {
                $items[] = new UnitEmployeeRecord($item);
            }

            $totalPages = intval($response["TotalPages"]);
        } while ($page < $totalPages);

        return $items;
    }

    protected function login()
    {
        if ($this->authenticated !== true) {
            $url = "https://{$this->host}/api/account/login";
            $this->request("POST", $url, [
                "form_params" => [
                    "email" => $this->username,
                    "password" => $this->password,
                ]
            ]);
            $this->authenticated = true;
        }

        return $this->authenticated;
    }

    /**
     * Returns the Client instance
     * @return Client
     */
    public function getClient()
    {
        if (!$this->client instanceof Client) {
            $this->client = new Client([
                "cookies" => true
            ]);
        }
        return $this->client;
    }
    
    /**
     * Executes a request to the PeopleMatter API
     * @param  string $method  The request type
     * @param  string $url     The url to request
     * @param  array  $options An array of options for the request
     * @return array           The response as an array
     */
    protected function request($method, $url, $options = [])
    {
        $client = $this->getClient();
        try {
            $response = $client->request($method, $url, $options);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            throw new PeopleMatterException($response->getStatusCode().": ".$response->getReasonPhrase(), 1, $e);
        }

        $body = $response->getBody();
        if (!is_array($body)) {
            $json = json_decode($body, true);
        } else {
            $json = $body;
        }

        if (!empty($json["ErrorMessage"])) {
            throw new PeopleMatterException($json["ErrorMessage"], $json["ErrorCode"]);
        }

        return $json;
    }
}
