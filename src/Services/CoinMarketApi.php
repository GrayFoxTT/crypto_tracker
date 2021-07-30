<?php

//src/Services/CoinMarketApi

namespace App\Services;

use Symfony\Contracts\HttpClient\HttpClientInterface;


//Se connecter et récupérer les données de l'API Coin Market
class CoinMarketApi
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function getApiCoinMarket()
    {

        //Connexion à l'API de Coin Market
        $response = $this->client->request(
            'GET',
            'https://pro-api.coinmarketcap.com/v1/cryptocurrency/quotes/latest?slug=bitcoin,ethereum,ripple&convert=eur', [
            'headers' => [
                'Accept' => 'application/json',
                'X-CMC_PRO_API_KEY: dc532d5c-c9f0-41de-a840-e5145b7b1694',
            ]   
        ]);

        $statusCode = $response->getStatusCode();

        if($statusCode == 200)
        {
            $content = $response->toArray();
        }
        else
        {
            $content = false;
        }

        return $content;
    }
}