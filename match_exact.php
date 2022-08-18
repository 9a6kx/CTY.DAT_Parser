<?php
function matchExact(string $callsign, array $exact_array, array $big_array) :array
{

    $solution = (array)null;

    $callsign = "=".$callsign;

    foreach ($exact_array as $this_key => $item) {
        foreach ($item as $key => $value) {
            if (($key === 'prefix' && startsWith($value, $callsign)) && true) {
                // input is: NH7RO
                // enmtries in big_cty ? :)
                // =NH7RO/M
                // =NH7RO(4)[7]
                // =NH7RO/5(4)[7]




                $overrides = findOverrides($value);
                $callsign = mb_substr($callsign, 1, mb_strwidth($callsign));
                $primary_id = array('Callsign'=> $callsign, 'main_index'=> $item['main_index'], 'WAE_override'=> $item['WAE'], 'CQ_Zone'=> $overrides['CQ_Zone'], 'ITU_Zone'=> $overrides['ITU_Zone']);
                $callsign = $primary_id['Callsign'];

                $entity=$big_array[$primary_id['main_index']]['Entity'];

                if ($primary_id['CQ_Zone']===0){
                    $cq_zone=$big_array[$primary_id['main_index']]['CQ_Zone'];
                }
                else{
                    $cq_zone=$primary_id['CQ_Zone'];
                }

                if ($primary_id['ITU_Zone']===0){
                    $itu_zone=$big_array[$primary_id['main_index']]['ITU_Zone'];
                }
                else{
                    $itu_zone=$primary_id['ITU_Zone'];
                }
                $continet=$big_array[$primary_id['main_index']]['Continent'];

                $latitude=$big_array[$primary_id['main_index']]['Latitude'];
                $longitude=$big_array[$primary_id['main_index']]['Longitude'];
                $utc_offset=$big_array[$primary_id['main_index']]['UTC_offset'];
                $primary_dxcc_prefix=$big_array[$primary_id['main_index']]['Primary_DXCC_Prefix'];

                if($primary_id['WAE_override']===1) {$official=false;}
                if($primary_id['WAE_override']===0) {$official=true;}

                $solution[]=array(
                    'Callsign'=>$callsign,
                    'Primary_DXCC_Prefix'=>$primary_dxcc_prefix,
                    'Entity'=>$entity,
                    'CQ_Zone'=>$cq_zone,
                    'ITU_Zone'=>$itu_zone,
                    'Continent'=>$continet,
                    'Latitude'=>$latitude,
                    'Longitude'=>$longitude,
                    'UTC_offset'=>$utc_offset,
                    'Official'=>$official);
            }

        }
    }

    /*
     Array
    (
    [0] => =9M2/PG5M      // CALL
    [1] => 53             // big array key
    [2] => 0              // WAE override
    )
     */

    return $solution;
}
?>
