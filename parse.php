<?php
// declare(strict_types = 1);

/*
 * Hamradio Prefixes for Log Analyisis
 * 9A6KX @ January 2021
 * v1.0 b
 * Version name: IU5HES Michele
 */

/*
 * Things to change or adjust following:
 */
$url='https://www.country-files.com/bigcty/download/2022/bigcty-20220804.zip'; // URL Of BIG.CTY ZIP Archive
$extractDir = 'extracted'; // Name of the directory where files from the ZIP are extracted

/*
 * For testing purposes only
 */
ini_set('memory_limit', '1024M'); // or I can use 1G

/*
 * Don't know why :)
 */

/*
 *
 */

/*
 * Function for geting the cty.dat from the web and unzip the file
 */
    function downloadCtyDat ($url) :string{
    /*
     * Never is an good idea to call for global variable, but let's consider this a non-mutable variable
     * Function has a lot side effects is not a pure function
     */
    $zipFile = 'BIG.CTY.zip';                                       // Rename ZIP file
    global $extractDir;                                             // Name of the directory where files are extracted
    $zipResource = fopen($zipFile, "w");
    // Get The Zip File From Server
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_FILE, $zipResource);
    $page = curl_exec($ch);
    if (!$page) {
        echo 'Error :- ' . curl_error($ch);
    }
    curl_close($ch);
    /* Open the Zip file */
    $zip = new ZipArchive;
    $extractPath = $extractDir;

    if ($zip->open($zipFile) != "true") {
        echo 'Error :- Unable to open the Zip File';
        return 0;
    }

    /* Extract Zip File */
    $zip->extractTo($extractPath);
    $zip->close();
    $files=scandir($extractDir);
    $file=(array) null;
    foreach ($files as $value) {
        if($value==='cty.dat' || $value==='CTY.DAT' ) {
            $file[]='/'.$extractDir.'/'.$value;
        }
    }
    $i=$file[0];

    return $i; // PATH TO CTY.DAT FILE
    } // lets fetch CtyDat, a BIG one
    function mb_trim($string, $charlist='\\\\s', $ltrim=true, $rtrim=true) {
        $both_ends = $ltrim && $rtrim;

        $char_class_inner = preg_replace(
            array( '/[\^\-\]\\\]/S', '/\\\{4}/S' ),
            array( '\\\\\\0', '\\' ),
            $charlist
        );

        $work_horse = '[' . $char_class_inner . ']+';
        $ltrim && $left_pattern = '^' . $work_horse;
        $rtrim && $right_pattern = $work_horse . '$';

        if($both_ends)
        {
            $pattern_middle = $left_pattern . '|' . $right_pattern;
        }
        elseif($ltrim)
        {
            $pattern_middle = $left_pattern;
        }
        else
        {
            $pattern_middle = $right_pattern;
        }

        $cleaned = preg_replace("/$pattern_middle/usSD", '', $string);
        return $cleaned;
    } // custom made safe multibyte trimming
    function parseCtyDatToArrray(string $file) :array{
        $handle = file_get_contents($_SERVER["DOCUMENT_ROOT"] .'/log_analyzer'.$file); // getting the file to string
        /*
         * try this on webserver if upper produces an error !
         * don't forget to CHMOD everything to 755 (only php and html file)
        $handle = file_get_contents(".".$file); // getting the file to string
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
    } // Lets make an array from file, big array
    function tryToFindExact1st(string $callsign, array $array)
    { // match input callsign as exact match
        /* Here we will match input callsign uppon prefixes preceded by '=' meaning that the prefix is to be treated as a full callsign, i.e. must be an exact match (this is coded as EXACT)
        * or match call prefixes preceded by '=' and ending with modifiers that override CQ or ITU zones, or both
        */
        $exact_callsign = "=" . $callsign; // prepending '=' sign to the call to do patching
        $output_structure = (array)null; // this is our output array containing match and all callsign/entity data if one found, or else we're returning it as null !
        $lenght_of_exact = mb_strwidth($exact_callsign);


        foreach ($array as $key => $value) {
            foreach ($value as $description => $content) {
                if (is_array($content) && sizeof($content) > 1) { // if aliases array have more than one entry - for ex. 1A is defined as primary prefix, but also as single entry in aliases dxcc prefixes, so its on no concern, skip those kind of entities
                    foreach ($content as $index => $exact_call) {
                        if ($exact_callsign === $exact_call) { // exact matching of input call to alias dxcc prefixes preceded by = sign !
                            $output_structure["Callsign"] = $callsign;
                            $output_structure["Entity"] = $value["Entity"];
                            $output_structure["Primary_DXCC_Prefix"] = $value["Primary_DXCC_Prefix"];
                            $output_structure["CQ_Zone"] = $value["CQ_Zone"];
                            $output_structure["ITU_Zone"] = $value["ITU_Zone"];
                            $output_structure["Continent"] = $value["Continent"];
                            $output_structure["Latitude"] = $value["Latitude"];
                            $output_structure["Longitude"] = $value["Longitude"];
                            $output_structure["UTC_offset"] = $value["UTC_offset"];
                            return $output_structure; // out of the function with desired results
                        }
                        else if (str_starts_with($exact_call, $exact_callsign)) {
                            $a = mb_strwidth($exact_call);
                            $b = $a - $lenght_of_exact;
                            $c = mb_substr($exact_call, -$b);
                            $matches = (array)null;
                            if ($b === 3 || $b === 4) {
                                preg_match("/\[[^\]]*\]/", $c, $matches); // modifiers [#] or [##] - finding them with regex and returning updated values of ITU zone, ex:
                                if ($matches != null) { // UA9OW/BY2HIT example of exact where ITU only modified
                                    $output_structure["Callsign"] = $callsign;
                                    $output_structure["Entity"] = $value["Entity"];
                                    $output_structure["Primary_DXCC_Prefix"] = $value["Primary_DXCC_Prefix"];
                                    $output_structure["CQ_Zone"] = $value["CQ_Zone"];
                                    $output_structure["ITU_Zone"] = mb_substr($matches[0], 1, -1);
                                    $output_structure["Continent"] = $value["Continent"];
                                    $output_structure["Latitude"] = $value["Latitude"];
                                    $output_structure["Longitude"] = $value["Longitude"];
                                    $output_structure["UTC_offset"] = $value["UTC_offset"];
                                    return $output_structure; // out of the function with desired results
                                }

                                preg_match("/\(([^\)]*)\)/", $c, $matches); // modifiers (#) or (##) (rare) - finding them with regex and returning updated values of CQ zone
                                if ($matches != null) { // 8S8ODEN example of only CQ zone modified
                                    $output_structure["Callsign"] = $callsign;
                                    $output_structure["Entity"] = $value["Entity"];
                                    $output_structure["Primary_DXCC_Prefix"] = $value["Primary_DXCC_Prefix"];
                                    $output_structure["CQ_Zone"] = mb_substr($matches[0], 1, -1);
                                    $output_structure["ITU_Zone"] = $value["ITU_Zone"];
                                    $output_structure["Continent"] = $value["Continent"];
                                    $output_structure["Latitude"] = $value["Latitude"];
                                    $output_structure["Longitude"] = $value["Longitude"];
                                    $output_structure["UTC_offset"] = $value["UTC_offset"];
                                    return $output_structure; // out of the function with desired results
                                }
                            }
                            if ($b === 6 || $b === 7 || $b === 8) { // examples both CQ and ITU zone modified BY1WXD/0 , KH6JGA ... searching for both [] and () modifers values and return updated
                                preg_match("/\[[^\]]*\]/", $c, $matches);
                                preg_match("/\(([^\)]*)\)/", $c, $matches2);
                                $output_structure["Callsign"] = $callsign;
                                $output_structure["Entity"] = $value["Entity"];
                                $output_structure["Primary_DXCC_Prefix"] = $value["Primary_DXCC_Prefix"];
                                $output_structure["CQ_Zone"] = mb_substr($matches2[0], 1, -1);
                                $output_structure["ITU_Zone"] = mb_substr($matches[0], 1, -1);
                                $output_structure["Continent"] = $value["Continent"];
                                $output_structure["Latitude"] = $value["Latitude"];
                                $output_structure["Longitude"] = $value["Longitude"];
                                $output_structure["UTC_offset"] = $value["UTC_offset"];
                                return $output_structure;
                            } else {
                                $output_structure = (array)null; // matching non enlisted full call in aliases for ex. 9a6kx returns null, so its a validation for calling function that'll do surgery upon the input call, yikes !
                                return $output_structure;
                            }
                        }
                    }
                }
            }
        }
    }

 // we match Exact call of Exact/CQ_or_ITU_override
   function anatomyOfACallsign(string $callsign, array $array) : array
    {
        /* calssign additions */

        $callsign_additions_legal = array("P", "M", "A", "AM", "MM",); // normal additions
        $callsign_additions_illegal = array("LGT", "LH", "LS", "LHT", "QRP", "PM", "LT", "J", "JOTA", "YL", "MILL", "FF"); // 9A6KX/MILL OMG :)
        $additions = array_merge($callsign_additions_illegal, $callsign_additions_legal); // combining all additions in single array

        /*
         * "Normal" / Legal additions are /P, /M ... I consider /QRP to be useless callsign addition, so I listed it in "illegal"
         * as others ten variants of /Lighthouse abrevations, YL - ever considered calling 9A6KX/OM ? :)
         * is it /YL at the end Lithuania or /OM at the end Slovakia, or is it just a gender notice?
         * who knows, I decided to list 'YL' as gender, op guest in YL will probably be YL/KK1L or similar
         * am I 9A6KX/LH in Norway or I'm just silly giving something unusal as call addition operating from lighthouse
         */


        if(mb_substr_count($callsign, "/")===2) {
            list($a, $b, $c) = explode("/", $callsign);
        }
        elseif (mb_substr_count($callsign, "/")===1){
            list($a, $b) = explode("/", $callsign);
            $c = null;
        }
        elseif (mb_substr_count($callsign, "/")===0){
            $a = $callsign;
            $b = null;
            $c = null;
        }


        /* We divided callsign to three variables determined by slashes
        * Imagine HB0/9A8MM/P
        */

        /* I will take only alias prefixes from big_cty array (main prefix is listed under aliases also) , clean it of overrides but preserve info of entity index of big array
        * and return new array with overrides of cq and itu zone if any
        * so we can match callsign on smaller array to make this go faster ?
        * array of alias dxcc prefixes has > 7000 entries :)
        * we will always check uppon that unless format I/9A6KX where 'I' matches EXACT Primary Prefix
        */

        $alias_prefixes = (array)null; // empty array we will fill in

        /* Lucky for us there is a quite common thing - if you go in SP, you should identify as SP/9A6KX not 3Z/9A6KX
        * althought both are valid Poland prefixes
        * so we will build up a 'base' of primary prefixes only (another a lot smaller array of prefixes)
        */

        $primary_prefixes = (array)null;

        /*
         * Now we're creating alias dxcc prefixes list that will hold key to call full entry from big_cty array
         * overrides for CQ and ITU zones if any
         * and a Alias Prefix (Where Primary is also listed)
         */
foreach ($array as $key=>$value) {
    foreach ($value as $description => $content) {
        if (is_array($content)) {
            foreach ($content as $index => $prefix) { // $key holds index of big array with dxcc entity
                // we will check prefix for = sign at the end and ignore exacts

                if (mb_substr($prefix, 0, 1) !== "=") {
                    $cleaned_prefix = $prefix;
                    $overrides = null;
                    $cq_override = mb_strstr($prefix, "(", true); // check CQ override exists and clean prefix from start to the begining of (##)
                    $itu_override = mb_strstr($prefix, "[", true); // check ITU override exists and clean prefix from start to the begining of [##]

                    if ($cq_override) { // if we have CQ override its only CQ zone override or maybe both CQ and ITU
                        $cleaned_prefix = $cq_override;
                        $overrides = mb_strstr($prefix, "(", false); // rest from the bigining of '(' sign

                    } else if ($itu_override) { // if we dont have CQ but have ITU override
                        $cleaned_prefix = $itu_override;
                        $overrides = mb_strstr($prefix, "[", false); // rest from the bigining of '[' sign
                    }

                    $alias_prefixes[] = array(
                        "main_index" => $key,
                        "prefix" => $cleaned_prefix,
                        "overrides" => $overrides);
                }

            }
        }
    }
}

        /*
         * Lets fill also array of Primary DXCC Prefixes
         */
        foreach ($array as $key => $value) {
            foreach ($value as $description => $content) {
                if ($description === "Primary_DXCC_Prefix") {
                    $primary_prefixes[] = array(
                        "main_index" => $key,
                        "prefix" => $content);
                }
            }
        }

        /* If last part ($c) doesn't exist - callsign is composed of only two parts
        * lets look the last one, if its from addition list, ignore it and point only $a for prefix matching for ex. YU1LM/QRP
        * if not we will match the second part as prefix for ex. I1AAA/IS0 - because what the hell else could it be ??
        */

        $addition_present = null;
        $primary_match = (array)null; // This will hold our return Values if they are found
        if ($c === null) { // check second part on the list of both legal and illegal additions
            foreach ( $additions as $key => $value) {
                if ($b === $value) { // callsign has addition from our list
                    $addition_present = array(1); // return confirmation
                }
            }
        }

        if ($addition_present !== null && $addition_present[0] === 1) { // we don't observe last part as it is know addition for ex. /P

            $first_letter = mb_substr($a, 0, 1);

            $match = (array)null;
            $cq_override = (array)null;
            $itu_override = (array)null;

            foreach ($alias_prefixes as $key => $value) {
                foreach ($value as $cleaned => $clean_prefix) {

                    if ($cleaned === "prefix") {

                        $lenght_of_alias = mb_strlen($clean_prefix);

                        if ($first_letter !== mb_substr($clean_prefix, 0, 1)) {
                            continue; // if first letter of our prefix and lookup 'cell' is not same, just skip the entry
                            // faster than exact evaluation of equality
                        }


                        if (mb_substr($a, 0, $lenght_of_alias) === mb_substr($clean_prefix, 0, $lenght_of_alias)) {
                            foreach ($array as $main_key => $main_value_array) {
                                if ($main_key === $value['main_index']) {

                                    preg_match("/(?<=\[).+?(?=\])/", $value['overrides'], $matches);
                                    $itu_override = $matches;
                                    preg_match("/(?<=\().+?(?=\))/", $value['overrides'], $matches);
                                    $cq_override = $matches;
                                    $match = array(
                                        "Callsign" => $callsign,
                                        "Entity" => $main_value_array["Entity"],
                                        "Primary_DXCC_Prefix" => $main_value_array["Primary_DXCC_Prefix"],
                                        "CQ_Zone" => $main_value_array["CQ_Zone"],
                                        "ITU_Zone" => $main_value_array["ITU_Zone"],
                                        "Continet" => $main_value_array["Continent"],
                                        "Latitude" => $main_value_array["Latitude"],
                                        "Logitude" => $main_value_array["Longitude"],
                                        "UTC_offset" => $main_value_array["UTC_offset"]
                                    );
                                }
                            }
                        }

                    }
                }
            }
        } else {
            /* if addition was not known for ex. /QRP then we will consider rest after slash as a prefix
            * unless it matches exactly the aliass prefixes !!!
            * unless is such a silly word in programing :)
            */

            $match = (array)null;

            foreach ($primary_prefixes as $key => $value) { // does it matches primary prefix as in SP/DK5AV ?
                foreach ($value as $item_name => $content) {
                    if ($item_name === "prefix" && $a === $content) {
                        foreach ($array as $main_key => $main_value_array) {
                            if ($main_key === $value['main_index']) {
                                $primary_match = array( // If yes we are seting primary_match
                                    "Callsign" => $callsign,
                                    "Entity" => $main_value_array["Entity"],
                                    "Primary_DXCC_Prefix" => $main_value_array["Primary_DXCC_Prefix"],
                                    "CQ_Zone" => $main_value_array["CQ_Zone"],
                                    "ITU_Zone" => $main_value_array["ITU_Zone"],
                                    "Continet" => $main_value_array["Continent"],
                                    "Latitude" => $main_value_array["Latitude"],
                                    "Logitude" => $main_value_array["Longitude"],
                                    "UTC_offset" => $main_value_array["UTC_offset"]
                                );
                            }
                        }
                    }
                }
            }

            /*
            * We want to check also silly lid style of II1AAA/IS0
            * so we're talking of $b portion of eploxed callsign
            */

            $first_letter = mb_substr($b, 0, 1);
            $cq_override = (array)null;   // I have a habbit of constantly setting variables to null :)
            $itu_override = (array)null;  // even when not needed :)

            if (!$primary_match) {
                foreach ($alias_prefixes as $key => $value) {
                    foreach ($value as $cleaned => $clean_prefix) {

                        if ($cleaned === "prefix") {

                            $lenght_of_alias = mb_strlen($clean_prefix);

                            if ($first_letter !== mb_substr($clean_prefix, 0, 1)) {
                                continue;
                            }

                            if (mb_substr($b, 0, $lenght_of_alias) === mb_substr($clean_prefix, 0, $lenght_of_alias)) {
                                foreach ($array as $main_key => $main_value_array) {
                                    if ($main_key === $value['main_index']) {

                                        preg_match("/(?<=\[).+?(?=\])/", $value['overrides'], $matches);
                                        $itu_override = $matches;
                                        preg_match("/(?<=\().+?(?=\))/", $value['overrides'], $matches);
                                        $cq_override = $matches;

                                        $match = array(
                                            "Callsign" => $callsign,
                                            "Entity" => $main_value_array["Entity"],
                                            "Primary_DXCC_Prefix" => $main_value_array["Primary_DXCC_Prefix"],
                                            "CQ_Zone" => $main_value_array["CQ_Zone"],
                                            "ITU_Zone" => $main_value_array["ITU_Zone"],
                                            "Continet" => $main_value_array["Continent"],
                                            "Latitude" => $main_value_array["Latitude"],
                                            "Logitude" => $main_value_array["Longitude"],
                                            "UTC_offset" => $main_value_array["UTC_offset"]
                                        );
                                    }
                                }
                            }

                        }
                    }
                }
            } else $match = $primary_match;
        }

        if (!$b && !$c) { // only one portion of callsign ex 9A1P
            // callsign is $a
            $first_letter = mb_substr($a, 0, 1);
            $match = (array)null;
            foreach ($alias_prefixes as $key => $value) {
                foreach ($value as $cleaned => $clean_prefix) {

                    if ($cleaned === "prefix") {

                        $lenght_of_alias = mb_strlen($clean_prefix);

                        if ($first_letter !== mb_substr($clean_prefix, 0, 1)) {
                            continue;
                        }

                        if (mb_substr($a, 0, $lenght_of_alias) === mb_substr($clean_prefix, 0, $lenght_of_alias)) {
                            foreach ($array as $main_key => $main_value_array) {
                                if ($main_key === $value['main_index']) {
                                    $match = array(
                                        "Callsign" => $callsign,
                                        "Entity" => $main_value_array["Entity"],
                                        "Primary_DXCC_Prefix" => $main_value_array["Primary_DXCC_Prefix"],
                                        "CQ_Zone" => $main_value_array["CQ_Zone"],
                                        "ITU_Zone" => $main_value_array["ITU_Zone"],
                                        "Continet" => $main_value_array["Continent"],
                                        "Latitude" => $main_value_array["Latitude"],
                                        "Logitude" => $main_value_array["Longitude"],
                                        "UTC_offset" => $main_value_array["UTC_offset"]
                                    );
                                }
                            }
                        }

                    }
                }
            }
        }

        if ($itu_override) {
            $match['ITU_Zone'] = $itu_override[0];
        }
        if ($cq_override) {
            $match['CQ_Zone'] = $cq_override[0];
        }
    return $match;
    } // we match uppon prefixes


/*
* MAIN Flow
*/
    $callsign = $_POST['call'] ?? 'HB0/9A8MM/P';


    $file=downloadCtyDat($url);                                 // Download ZIP from web, extract, and get path to CTY.DAT file
    $array=parseCtyDatToArrray(downloadCtyDat($url));           // Get the CTY.DAT file parsed to pretty structured array
                                                                // Read the parseCtyDatToArrray function comments for info about non-standard entries


    $callsign = strtoupper($callsign);
    $returned = tryToFindExact1st($callsign,$array) ?? anatomyOfACallsign($callsign, $array); // return array from matching if null (exact matching failed) else lets do math

    /*
     * Display pretty formated value of structured Array, just uncomment this section
     * echo "<pre>";
     * print_r($returned);
     * echo "</pre>";
    */

    /*
     * Output JSON, just uncomment this section
     * echo json_encode($returned);
     */




     /*
     * This is intended as a part of bigger Hamradio log analyzer with GUI
     * Still playing around in spare time
     * 73s to all
     * Mirko 9A6KX
     */
     
     /*
     Copyright notice from www.country-files.com
     Jim Reisert AD1C
     Copyright © 1994-

     Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the “Software”), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

     The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

     THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
     */


?>

<!-- Generation of HTML elements and listing values-->
<!DOCTYPE html>
<html>
<head>
    <title>Callsign DXCC check by 9a6kx</title>
    <link rel=”stylesheet” href=”https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css”rel=”nofollow” integrity=”sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm” crossorigin=”anonymous”>
</head>
<body>

<h2>Check Callsign by 9A6KX</h2>

<span><b>Callsign: </b> <?= $returned["Callsign"] ?></span><br />
<span><b>DXCC Entity: </b> <?= $returned["Entity"] ?></span><br />
<span><b>Primary DXCC Prefix: </b> <?= $returned["Primary_DXCC_Prefix"]?></span><br />
<span><b>CQ Zone: </b> <?= $returned["CQ_Zone"] ?></span><br />
<span><b>ITU Zone: </b> <?= $returned["ITU_Zone"] ?></span><br />
<span><b>Continent: </b> <?= $returned["Continet"] ?></span><br />
<span><b>Latitude: </b> <?= $returned["Latitude"] ?></span><br />
<span><b>Longitude: </b> <?= $returned["Logitude"] ?></span><br />
<span><b>UTC Offset: </b> <?= $returned["UTC_offset"] ?></span><br />

<p><i>Please report back all that you find incorrect on my qrz.com e-mail address</i></p>
</body>
</html>