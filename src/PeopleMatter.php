<?php

namespace Zenapply\PeopleMatter;

use GuzzleHttp\Client;

class PeopleMatter
{
    const V3 = 'v3';

    protected $host;
    protected $version;
    protected $client;
    protected $token;

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

    public function hire()
    {

    }

    protected function login()
    {
        $url = "https://{$this->host}/api/account/login";
        return $this->request('POST', $url, [
            'username' => $this->username,
            'password' => $this->password,
        ]);
    }

    /**
     * Returns the Client instance
     * @return Client
     */
    protected function getClient()
    {
        $client = $this->client;
        if (!$client instanceof Client) {
            $client = new Client();
        }
        return $client;
    }

    /**
     * Executes a request to the PeopleMatter API
     * @param  string $url    The URL to send to
     * @param string $method
     * @return mixed          The response data
     */
    protected function request($method, $url, $data)
    {
        $client = $this->getClient();
        $response = $client->request($method, $url, ["data" => $data]);
        return $response->getBody();
    }
}
