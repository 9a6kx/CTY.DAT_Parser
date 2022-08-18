<?php
/**
 * @param string $file
 * @return array
 */
function parseCtyDatToArrray(string $file) :array{
    // $handle = file_get_contents($_SERVER["DOCUMENT_ROOT"] .'/log_analyzer'.$file); // getting the file to string
    $handle = file_get_contents(".".$file); // getting the file to string
    /*
     * try this on webserver if upper produces an error !
     * don't forget to CHMOD everything to 755 (only php and html file)

    */
    $array = explode(";", $handle);                                                // getting the array from string
    $temp_array=(array) null;                                                               // I need temp array to fill in during the looping
    foreach ($array as $value) {

        list($entity, $cq_zone, $itu_zone, $continent, $latitude, $longitude, $utc_offset, $prefix, $aliases) = array_pad(explode(':', $value, 9), 9, null);
        $aliases=mb_trim($aliases);
        $aliases=mb_ereg_replace('    ' , "", $aliases);                                                                   // Additional cleaning of consecutive whitespaces
        $aliases=mb_ereg_replace("\n"   , "", $aliases);                                                                   // Additional cleaning of \n
        $aliases=mb_ereg_replace("\r"   , "", $aliases);                                                                   // Additional cleaning of \r
        $aliases=explode(',', $aliases);                                                                                   // Both cleaning in this function and mbTrim function should be carefully tested and probably rewritten
        if (is_numeric($latitude)) {
            $temp_array[] = array(
                "Entity" => mb_trim($entity),
                "CQ_Zone" => mb_trim($cq_zone),
                "ITU_Zone" => mb_trim($itu_zone),
                "Continent" => mb_trim($continent),
                "Latitude" => mb_trim($latitude),
                "Longitude" => mb_trim($longitude),
                "UTC_offset" => mb_trim($utc_offset),
                "Primary_DXCC_Prefix" => mb_trim($prefix),
                "Alias_DXCC_Prefixes" =>mb_trim($aliases)
            );
        }
    }
    $donbas_prefixes=array(0 => "D0", 1 => "D1");
    $donbas=array(
        "Entity" => "Donetsk People's Republic",
        "CQ_Zone" => "16",
        "ITU_Zone" => "29",
        "Continent" => "EU",
        "Latitude" => "50.00",
        "Longitude" => "-30.00",
        "UTC_offset" => "-2.0",
        "Primary_DXCC_Prefix" => "D0",
        "Alias_DXCC_Prefixes" => $donbas_prefixes
    );

    array_push($temp_array, $donbas);

    $array = $temp_array;

    /*
     * So I got pretty structured array
     * With some specific details:
     *
     * 	Primary DXCC Prefix:
     *  “*” preceding this prefix indicates that the country is on the DARC WAEDC list, and counts in CQ-sponsored contests, but not ARRL-sponsored contests).
     *  i.e. Sicily [IT9] and African Italy (Pantaleria Island) [IG9] are still counted as Italy in ARRL contests
     *  i.e. Similar case with Asiatic Turkey [TA] and European Turkey [TA1] - both Turkey [TA] for ARRL contests
     *
     *
     * The following special characters can be applied after an alias prefix:
     * (#)	Override CQ Zone
     * [#]	Override ITU Zone
     * <#/#>	Override latitude/longitude
     * {aa}	Override Continent
     * ~#~	Override local time offset from GMT
     *
     * If an alias prefix is preceded by ‘=’, this indicates that the prefix is to be treated as a full callsign, i.e. must be an exact match.
     *
     */
    return $array;
}
?>