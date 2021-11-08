<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\FlightsApi;
use App\Models\FlightGrouping;


class FlightGroupingController extends Controller
{
    function index()
    {
        try {

            $address = env('123MILHAS_API_ADDRESS');

            $api = new FlightsApi($address);
    
            $flights = json_decode($api->getFlights());
    
            return response()->json($flights);
            
        } catch (\Exception $e) {
            $result = ['result' => 'error', 'message' => 'Failed to request', 'status_code' => '500'];
            $status = 500;
        }
  
    }

    function grouping()
    {
     

        $address = env('123MILHAS_API_ADDRESS');
     
        $api_123miles = new FlightsApi($address);

        $flights = json_decode($api_123miles->getFlights());

        $flight_grouping  = new FlightGrouping();
   /*      try{ */
        $result = $flight_grouping->makeGroups($flights);
        $status = 200;
        
    /*     }catch (\Exception $e)
        
        {
            $result = ['result' => 'error', 'message' => 'Failed to group flights', 'status_code' => '500'];
            $status = 500;
        } */

        return response($result, $status);
    }

}
