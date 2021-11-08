<?php
namespace App\Models;
use App\Models\Group;


class FlightGrouping
{
    public function makeGroups($flights)
    {

        $flights_collection = collect($flights);

       /* SEPARA VOOS POR TARIFAS */
        $flights_fares = $flights_collection->groupBy('fare');
        
        /* SEPARA VOOS POR TIPO */
        $flights_fares = $this->groupByInboundOrOutbound($flights_fares);

        /* COMBINA VOOS COM PREÇOS IGUAIS */
        $combined_flight = collect();

        foreach($flights_fares as $flights_fare)
        {
            $grouped_outbonds = $this->findEqualItems($flights_fare[0], 'price');
            $grouped_inbounds = $this->findEqualItems($flights_fare[1], 'price');

            $combined_flight->push($this->combineFlights($grouped_outbonds, $grouped_inbounds));
        }

        $combined_flights_output = $this->formatOutput($combined_flight);

        $combined_flights_output['flights'] = $flights_collection;
        $combined_flights_output['groups'] = $combined_flights_output['groups']->sortBy('totalPrice');

        return $combined_flights_output;
    }

    function findEqualItems($items, $field)
    {
        $equal_items = collect();

        foreach($items as $item)
        {
            $equal = $items->where($field,$item->{$field});
            if($equal->isNotEmpty())
            {
                $equal_items ->push($equal);

                foreach($equal as $item)
                    $items = $items->reject($item, function($collection_item,$item){
                    return $collection_item->{$field} == $item->{$field};
                });

                $equal = null;
            }
        }

        return $equal_items;
    }

    function combineFlights($goings, $turns)
    {
        $groups = collect();
        
        foreach($goings as $key_goings => $going)
        {
            
            foreach($turns as $key_turns => $turn)
            {        
                $group = new Group();       
                $group->outbound = $going;
                $group->inbound = $turn;
                $group->getTotalPrice();
                $groups = $groups->push($group);
            }  
        }

        return $groups;
    }

    function formatOutput($combined_flights)
    {
        $combined_flights_output= collect();

        $combined_flights_output['flights'] = null;
        $combined_flights_output['groups'] =  collect();
        $combined_flights_output['totalGroups'] = 0; 
        $combined_flights_output['totalFlights'] = 0; 
        $combined_flights_output['cheapestPrice'] = 0.0; 
        $combined_flights_output['cheapestGroup'] = null;

        foreach($combined_flights as $combined_flight)
        {
            $combined_flights_output['totalGroups'] += $combined_flight->count();
            foreach($combined_flight as $combined_flight_group)
            {
                $combined_flights_output['groups']->push($combined_flight_group);

                if(($combined_flight_group->outbound->count() == 1) && ($combined_flight_group->inbound->count() == 1))
                {
                    $combined_flights_output['totalFlights'] += 1;
                }

            }
        }

        $combined_flights_output['cheapestPrice'] = $combined_flights_output['groups']->min('totalPrice');
        $combined_flights_output['cheapestGroup'] = $combined_flights_output['groups']
        
        ->where('totalPrice', $combined_flights_output['cheapestPrice'])
        [0]->uniqueId;
        
        return $combined_flights_output;
    }
    
    function groupByInboundOrOutbound($flights)
    {
        foreach($flights as $key => $flight)
        {
            $flights[$key] = $flights[$key]->groupBy('inbound');
        }

        return $flights;
    }

}

?>