<?php
function matchAlias(string $checked_callsign, array $checked_callsign_splited, array $primary_prefixes, array $alias_prefixes, array $big_array, bool $last_part_check) :array {
    $solution = (array) null;

    if ($last_part_check===true && $checked_callsign_splited['int']===2) {
        if ($checked_callsign_splited['a']==="IS0") {
            array_walk($checked_callsign_splited, function (&$checked_callsign_splited){
                $checked_callsign_splited['a']="IS";
            });
        }
        $what_to_match = $checked_callsign_splited['a'];

    }
    if ($last_part_check===false && $checked_callsign_splited['int']===2) {
        $what_to_match = $checked_callsign_splited['a'];
        if ($checked_callsign_splited['a']==="IS0") {
            $what_to_match="IS";
        }
    }
    elseif ($checked_callsign_splited['int']===1) {
        $what_to_match = $checked_callsign_splited['a'];
    }
    else {$what_to_match="";}
    $first_letter = mb_substr($what_to_match, 0, 1);

    foreach ($alias_prefixes as $key => $value) {
        foreach ($value as $cleaned => $clean_prefix) {
            if ($cleaned === "prefix") {
                $lenght_of_alias = mb_strlen($clean_prefix);

                if ($first_letter !== mb_substr($clean_prefix, 0, 1)) {
                    continue;
                }

                if (mb_substr($what_to_match, 0, $lenght_of_alias) === mb_substr($clean_prefix, 0, $lenght_of_alias)) {
                    foreach ($big_array as $main_key => $main_value_array) {
                        if ($main_key === $value['main_index']) {

                            $overrides = findOverrides($clean_prefix);

                            if ($overrides['ITU_Zone'] !== 0) {
                                $itu_zone = $overrides['ITU_Zone'];
                            } else {
                                $itu_zone = $main_value_array["ITU_Zone"];
                            }
                            if ($overrides['CQ_Zone'] !== 0) {
                                $cq_zone = $overrides['ITU_Zone'];
                            } else {
                                $cq_zone = $main_value_array["CQ_Zone"];
                            }

                            $official = null;

                            if ($value['WAE'] === 1) {
                                $official = false;
                            }
                            if ($value['WAE'] === 0) {
                                $official = true;
                            }

                            if (mb_substr($checked_callsign,0,1)==="*") {$checked_callsign=mb_substr($checked_callsign,1 );}

                            $solution[] = array(
                                "Callsign" => $checked_callsign,
                                "Entity" => $main_value_array["Entity"],
                                "Primary_DXCC_Prefix" => $main_value_array["Primary_DXCC_Prefix"],
                                "CQ_Zone" => $cq_zone,
                                "ITU_Zone" => $itu_zone,
                                "Continent" => $main_value_array["Continent"],
                                "Latitude" => $main_value_array["Latitude"],
                                "Longitude" => $main_value_array["Longitude"],
                                "UTC_offset" => $main_value_array["UTC_offset"],
                                "Official" => $official
                            );
                        }
                    }
                }

            }
        }
    }
    if(empty($solution)) {
    foreach ($primary_prefixes as $key => $value) {
        foreach ($value as $cleaned => $clean_prefix) {
            if ($cleaned === "prefix") {
                $lenght_of_alias = mb_strlen($clean_prefix);

                if ($first_letter !== mb_substr($clean_prefix, 0, 1)) {
                    continue;
                }

                if (mb_substr($what_to_match, 0, $lenght_of_alias) === mb_substr($clean_prefix, 0, $lenght_of_alias)) {
                    foreach ($big_array as $main_key => $main_value_array) {
                        if ($main_key === $value['main_index']) {

                            $overrides = findOverrides($clean_prefix);

                            if ($overrides['ITU_Zone'] !== 0) {
                                $itu_zone = $overrides['ITU_Zone'];
                            } else {
                                $itu_zone = $main_value_array["ITU_Zone"];
                            }
                            if ($overrides['CQ_Zone'] !== 0) {
                                $cq_zone = $overrides['ITU_Zone'];
                            } else {
                                $cq_zone = $main_value_array["CQ_Zone"];
                            }

                            $official = null;

                            if ($value['WAE'] === 1) {
                                $official = false;
                            }
                            if ($value['WAE'] === 0) {
                                $official = true;
                            }

                            $solution[] = array(
                                "Callsign" => $checked_callsign,
                                "Entity" => $main_value_array["Entity"],
                                "Primary_DXCC_Prefix" => $main_value_array["Primary_DXCC_Prefix"],
                                "CQ_Zone" => $cq_zone,
                                "ITU_Zone" => $itu_zone,
                                "Continent" => $main_value_array["Continent"],
                                "Latitude" => $main_value_array["Latitude"],
                                "Longitude" => $main_value_array["Longitude"],
                                "UTC_offset" => $main_value_array["UTC_offset"],
                                "Official" => $official
                            );
                        }
                    }
                }

            }
        }
    }
    }



    // FUNCTION OUTPUT
    return $solution;
}
?>