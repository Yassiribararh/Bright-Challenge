<?php

declare(strict_types=1);

namespace App\API;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class WeatherMapApi
{
    private const API_ENDPOINT = 'https://api.openweathermap.org/data/2.5/weather';
    private const API_TOKEN = '9c3486ac9c58b4dc202be43bde21793c';

    public function getUKCityWeatherMap($cityName): bool|array
    {
        try {
            $client = HttpClient::create();
            $response = $client->request('GET', self::API_ENDPOINT, [
                'query' => [
                    'q' => $cityName . ",UK", // LONDON
                    'appid' => self::API_TOKEN,
                    'units' => 'metric' // metric = Celsius temperature
                ]
            ]);

            //Check if the upload was successful (status code 200 indicates success)
            if ($response->getStatusCode() === 200) {
                return [
                    'statusCode' => 200,
                    'content' => json_decode($response->getContent())
                ];
            } else {
                return [
                    'statusCode' => $response->getStatusCode(),
                    'content' => $response->getContent()
                ];
            }

        } catch (TransportExceptionInterface|ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface $e) {
            // Handle exceptions
            return [
                'statusCode' => $e->getCode(),
                'content' => $e->getMessage()
            ];
        }
    }


    public function getAllCountryCities($countryName): bool|array
    {
        // Final endpoint
        $url = 'https://countriesnow.space/api/v0.1/countries/cities';

        try {
            // Initialize HttpClient
            $client = HttpClient::create();

            // Send request to get all cities
            $response = $client->request('POST', $url, [
                'json' => [
                    'country' => $countryName// "united kingdom"
                ]
            ]);
            $content = json_decode($response->getContent())->data;

            //Check if the upload was successful (status code 200 indicates success)
            if ($response->getStatusCode() === 200) {
                // Return the list of the cities
                return [
                    'statusCode' => $response->getStatusCode(),
                    'content' => $content
                ];
            } else {
                return [
                    'statusCode' => $response->getStatusCode(),
                    'content' => $response->getContent()
                ];
            }
        } catch (TransportExceptionInterface|ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface $e) {
            // Handle exceptions
            return [
                'statusCode' => $e->getCode(),
                'content' => $e->getMessage()
            ];
        }
    }
}