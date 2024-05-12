<?php

declare(strict_types=1);

namespace App\Controller;

use App\API\WeatherMapApi;
use App\Form\CityInputForm;
use App\Helper\GetCityWeatherHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{

    #[Route('/', name: 'app_index')]
    public function uploadImage(Request $request, SessionInterface $session, WeatherMapApi $weatherMapApi, GetCityWeatherHelper $helper): ?Response
    {
        $session->set('confirmation', []);

        $form = $this->createForm(CityInputForm::class);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                $error = (string) $form->getErrors(true);
                $this->addFlash('error', $error);
                return $this->redirect($this->generateUrl('app_index'));
            }

            $cityName = $form['city']->getData();

            //Get City Weather function
            $getWeatherRequest = $helper->getCityWeather($cityName, 'united kingdom', $weatherMapApi);

            //If any errors
            if ($getWeatherRequest['statusCode'] !== 200) {
                $this->addFlash('error', $getWeatherRequest['content']);
                return $this->redirect($this->generateUrl('app_index'));
            }

            // Return Confirmation to user with weather details
            $session->set('confirmation', $getWeatherRequest['content']);
            return $this->redirect($this->generateUrl('app_get_confirmation'));
        }

        return $this->render('base.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/confirmation', name: 'app_get_confirmation')]
    public function weatherConfirmation(SessionInterface $session): ?Response
    {
        //Get details from session array
        $weatherDetails = $session->get('confirmation', []);

        return $this->render('city-weather.html.twig', [
            'weatherDetails' => json_decode(json_encode($weatherDetails), true)
        ]);
    }

    #[Route('/jquery/autocomplete/cities', name: 'autocomplete_cities')]
    public function autocompleteCities(Request $request, WeatherMapApi $weatherMapApi): JsonResponse
    {
        $query = $request->query->get('q');

        //Get all cities by country
        $ukCities = $weatherMapApi->getAllCountryCities('united kingdom');
        if ($ukCities['statusCode'] != 200) {
            return new JsonResponse(500);
        }

        $suggestions = [];
        //Suggest 5 cities max
        foreach ($ukCities['content'] as $city) {
            // Get Similar city strings
            if (stripos(strtolower($city), strtolower($query)) !== false && count($suggestions) <= 5) {
                $suggestions[] = $city;
            }
        }

        return new JsonResponse($suggestions);
    }
}
