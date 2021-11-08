<?php
namespace App\Services;


class FlightsApi
{
    private $api_address;

    function __construct($api_address)
    {
        $this->api_address = $api_address;
    }

    public function getFlights()
    {
        $route = '/api/flights';
        $curlUrl = curl_init($this->api_address.$route);

        curl_setopt_array(
            $curlUrl,
            [
                CURLOPT_RETURNTRANSFER => true,
            ]
        );

        $api_response = curl_exec($curlUrl);

        return $api_response;
    }

}
?>