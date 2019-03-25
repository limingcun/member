<?php

namespace App\Services;
use GuzzleHttp\Client;

class VkaServer
{
    const VKAURL = 'https://api.vi-ni.com/api/istore/card/';
    const GOURL = 'https://api.vi-ni.com/api/istore/go/';
    
    private $clientId;
    
    public function __construct()
    {
        $this->clientId = '5a84203fe6a8de299c39bf97570a8e861cf435';
    }

    /**
     * 
     */
    public function getUpgrade($url, $pwd)
    {
        $client = new Client();
        $response = $client->request('GET', $url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'clientId'     => $this->clientId,
                'password'      => $pwd
            ]
        ]);
        if ($response->getStatusCode() == 200) {
            $result = json_decode($response->getBody(), true);
            return $result;
        }
        return false;
    }
}