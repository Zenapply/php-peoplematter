<?php

namespace Zenapply\PeopleMatter;

use GuzzleHttp\Client;
use Zenapply\PeopleMatter\Exceptions\PeopleMatterException;
use DateTime;

class PeopleMatter
{
    const V3 = 'v3';

    protected $host;
    protected $version;
    protected $client;
    protected $token;
    protected $authenticated = false;

    /**
     * Creates a PeopleMatter instance that can register and unregister webhooks with the API
     * @param string      $username The Username
     * @param string      $password The Password
     * @param string      $alias    The business alias
     * @param string      $host     The host to connect to
     * @param Client|null $client   The Guzzle client (used for testing)
     */
    public function __construct($username, $password, $alias, $host = "api.peoplematter.com", Client $client = null)
    {
        $this->alias = $alias;
        $this->username = $username;
        $this->password = $password;
        $this->host = $host;
        $this->client = $client;
    }

    public function hire(Person $person, Job $job, BusinessUnit $businessUnit, DateTime $hired_at = null, $timeStatus = "FullTime")
    {
        $this->login();

        if ($hired_at === null) {
            $hired_at = new DateTime('now');
        }

        $url = "https://{$this->host}/api/services/platform/hireemployee";

        return $this->request('POST', $url, [
            'debug' => true,
            'json' => [
                "HireDate" => $hired_at->format("m/d/Y"),
                "Business" => [
                    "Alias" => "cafezupassandbox"
                ],
                "BusinessUnit" => [
                    "UnitNumber" => $businessUnit->UnitNumber
                ],
                "Person" => $person->toArray(),
                "JobPositions" => [
                    [
                        "Business" => [
                            "Alias" => "cafezupassandbox"
                        ],
                        "BusinessUnit" => [
                            "UnitNumber" => $businessUnit->UnitNumber
                        ],
                        "Job" => [
                            "Code" => $job->Code,
                        ],
                        "TimeStatus" => $timeStatus,
                        "Person" => $person->toArray(),
                    ]
                ]
            ]
        ]);
    }

    protected function buildUrl($resource)
    {
        return "https://{$this->host}/api/{$resource}";
    }

    public function getBusinessUnits()
    {
        $this->login();
        $response = $this->request('GET', $this->buildUrl("businessunit?businessalias={$this->alias}"));

        $units = [];
        foreach ($response["Records"] as $unit) {
            $units[] = new BusinessUnit($unit);
        }

        return $units;
    }

    public function getJobs()
    {
        $this->login();
        $response = $this->request('GET', $this->buildUrl("job?businessalias={$this->alias}"));

        $jobs = [];
        foreach ($response["Jobs"] as $unit) {
            $jobs[] = new Job($unit);
        }

        return $jobs;
    }

    protected function login()
    {
        if ($this->authenticated !== true) {
            $url = "https://{$this->host}/api/account/login";
            $this->request('POST', $url, [
                'form_params' => [
                    'email' => $this->username,
                    'password' => $this->password,
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
                'cookies' => true
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
            throw new PeopleMatterException($response->getStatusCode().": ".$response->getReasonPhrase(), 1);
        }

        $json = json_decode($response->getBody(), true);

        if (!empty($json["ErrorMessage"])) {
            throw new PeopleMatterException($json["ErrorMessage"], $json["ErrorCode"]);
        }

        return $json;
    }
}
