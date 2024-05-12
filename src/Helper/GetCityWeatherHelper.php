<?php

declare(strict_types=1);

namespace App\Helper;

final class GetCityWeatherHelper
{
    public function getCityWeather($cityName, $country, $weatherMapApi): array
    {
        // Check if the city exist
        $cityExistCheck = $this->checkCityExists($cityName, $country, $weatherMapApi);
        if ($cityExistCheck['statusCode'] !== 200) {
            return [
                'statusCode' => 500,
                'content' => $cityExistCheck['content'],
            ];
        }

        //Get City Weather
        $request = $weatherMapApi->getUKCityWeatherMap($cityName);
        if ($request['statusCode'] != 200) {
            return [
                'statusCode' => 500,
                'content' => $request['content'],
            ];
        }

        return [
            'statusCode' => $request['statusCode'],
            'content' => $request['content']
        ];
    }
    public function checkCityExists($cityName, $country, $weatherMapApi): array
    {

        //Get all cities by country
        $check = $weatherMapApi->getAllCountryCities($country);
        if ($check['statusCode'] != 200) {
            return [
                'statusCode' => $check['statusCode'],
                'content' => $check['content']
            ];
        }

        //Get Suggestion - If any
        $suggestions = $this->getCitiesSuggestions($check, $cityName, $country);

        return [
            'statusCode' => $suggestions['statusCode'],
            'content' => $suggestions['content']
        ];
    }

    public function getCitiesSuggestions($check, $userCityName, $country): array
    {
        $suggestions = [];
        foreach ($check['content'] as $apiCityName) {
            // Check for exact match
            if (strtolower($userCityName) == strtolower($apiCityName)) {
                return [
                    'statusCode' => 200,
                    'content' => $userCityName
                ];
            }

            // Calculate Levenshtein distance between user input and each city name
            $distance = levenshtein(strtolower($userCityName), strtolower($apiCityName));
            // Consider a threshold for similarity
            if ($distance <= 2) {
                $suggestions[] = $apiCityName;
            }
        }

        //Return Suggestions - If any
        if (empty($suggestions)) {
            return [
                'statusCode' => 500,
                'content' => 'City Name does not exist in: ' . $country
            ];
        }

        //Return first Suggestion if no exact match found
        return [
            'statusCode' => 300,
            'content' => 'Do you mean? ' . $suggestions[0]
        ];
    }
}
