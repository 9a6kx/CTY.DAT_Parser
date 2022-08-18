<?php
/**
 * @param string $callsign
 * @param array $portions
 * @param array $primary_list
 * @param array $big_array
 * @param bool $last_part_known
 * @return array
 */
function matchPrimary(string $callsign, array $portions, array $primary_list, array $big_array, bool $last_part_known) :array
{
    $solution = (array)null;

    $what_to_match="";

    foreach ($primary_list as $key=>&$value) {
        if (startsWith($value['prefix'], "*")) {
            $value['prefix']=mb_substr($value['prefix'],1, mb_strwidth($value['prefix']));
        }
    }

    if ($last_part_known && $portions['int']===3) { // if $a/$b/$c and /$c is known as legal or illegal modifier for ex 9A/HB0MM/P
        $what_to_match = $portions['a'];
    }
    if (!$last_part_known && $portions['c'] !== null) { // if $a/$b lets check if there is LID in action for. ex. 9A6KX/E7
        $what_to_match = $portions['b'];
    }
        foreach ($primary_list as $item => $prefix_entry) {
            if ($prefix_entry['prefix'] === $what_to_match) {
                $entity = $big_array[$prefix_entry['main_index']]['Entity'];
                $cq_zone = $big_array[$prefix_entry['main_index']]['CQ_Zone'];
                $itu_zone = $big_array[$prefix_entry['main_index']]['ITU_Zone'];
                $continet = $big_array[$prefix_entry['main_index']]['Continent'];
                $latitude = $big_array[$prefix_entry['main_index']]['Latitude'];
                $longitude = $big_array[$prefix_entry['main_index']]['Longitude'];
                $utc_offset = $big_array[$prefix_entry['main_index']]['UTC_offset'];
                $primary_dxcc_prefix = $big_array[$prefix_entry['main_index']]['Primary_DXCC_Prefix'];

                $official = null;
                if ($prefix_entry['WAE'] === 1) {
                    $official = false;
                }
                if ($prefix_entry['WAE'] === 0) {
                    $official = true;
                }

                $solution[] = array(
                    'Callsign' => $callsign,
                    'Primary_DXCC_Prefix' => $primary_dxcc_prefix,
                    'Entity' => $entity,
                    'CQ_Zone' => $cq_zone,
                    'ITU_Zone' => $itu_zone,
                    'Continent' => $continet,
                    'Latitude' => $latitude,
                    'Longitude' => $longitude,
                    'UTC_offset' => $utc_offset,
                    'Official' => $official);

            } elseif (!$last_part_known && $portions['int'] === 2) { // if $a/$b and /$b is not known as legal or illegal modifer for ex. 9A/II2P
                $what_to_match = $portions['a'];
                if ($prefix_entry['prefix'] === $what_to_match) {
                    $callsign = $portions['a'] . "/" . $portions['b'];
                    $entity = $big_array[$prefix_entry['main_index']]['Entity'];
                    $cq_zone = $big_array[$prefix_entry['main_index']]['CQ_Zone'];
                    $itu_zone = $big_array[$prefix_entry['main_index']]['ITU_Zone'];
                    $continet = $big_array[$prefix_entry['main_index']]['Continent'];
                    $latitude = $big_array[$prefix_entry['main_index']]['Latitude'];
                    $longitude = $big_array[$prefix_entry['main_index']]['Longitude'];
                    $utc_offset = $big_array[$prefix_entry['main_index']]['UTC_offset'];
                    $primary_dxcc_prefix = $big_array[$prefix_entry['main_index']]['Primary_DXCC_Prefix'];

                    $official = null;
                    if ($prefix_entry['WAE'] === 1) {
                        $official = false;
                    }
                    if ($prefix_entry['WAE'] === 0) {
                        $official = true;
                    }

                    $solution[] = array(
                        'Callsign' => $callsign,
                        'Primary_DXCC_Prefix' => $primary_dxcc_prefix,
                        'Entity' => $entity,
                        'CQ_Zone' => $cq_zone,
                        'ITU_Zone' => $itu_zone,
                        'Continent' => $continet,
                        'Latitude' => $latitude,
                        'Longitude' => $longitude,
                        'UTC_offset' => $utc_offset,
                        'Official' => $official);

                }
            }

        }
    return $solution;

}
?>