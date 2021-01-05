<?php
/*
 * Hamradio Prefixes for Log Analyisis
 * 9A6KX @ January 2021
 * v1.0a
 */

/*
 * Things to change or adjust following:
 */
$url='https://www.country-files.com/bigcty/download/bigcty-20201223.zip'; // URL Of BIG.CTY ZIP Archive
$extractDir = 'extracted'; // Name of the directory where files from the ZIP are extracted

/*
 * For testing purposes only
 */
ini_set('memory_limit', '1024M'); // or I can use 1G

/*
 * Function for geting the cty.dat from the web and unzip the file
 */
function downloadCtyDat ($url){
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
    }

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

        return preg_replace("/$pattern_middle/usSD", '', $string);
    }

    function parseCtyDatToArrray(string $file){
        $handle = file_get_contents($_SERVER["DOCUMENT_ROOT"] .'/log_analyzer'.$file); // getting the file to string
        $array = explode(";", $handle);                                                // getting the array from string
        $temp_array=(array) null;                                                               // I need temp array to fill in during the looping
        foreach ($array as $value) {
            list($entity, $cq_zone, $itu_zone, $continent, $latitude, $longitude, $utc_offset, $prefix, $aliases) = explode(":", $value);
            $aliases=mb_trim($aliases);
            $aliases=mb_ereg_replace('    ' , "", $aliases);                                                                   // Additional cleaning of consecutive whitespaces
            $aliases=mb_ereg_replace("\n"   , "", $aliases);                                                                   // Additional cleaning of \n
            $aliases=mb_ereg_replace("\r"   , "", $aliases);                                                                   // Additional cleaning of \r
            $aliases=explode(',', $aliases);                                                                                              // Both cleaning in this function and mbTrim function should be carefully tested and probably rewritten
            if (is_numeric($latitude)) {
                $temp_array[] = array(
                    "Entity" => mb_trim($entity),
                    "CQ_Zone" => mb_trim($cq_zone),
                    "ITU_Zone" => mb_trim($itu_zone),
                    "Continent" => mb_trim($continent),
                    "Latitunde" => mb_trim($latitude),
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
    }

    /*
     * MAIN Flow
     */

    $file=downloadCtyDat($url);                 // Download ZIP from web, extract, and get path to CTY.DAT file
    $array=parseCtyDatToArrray($file);          // Get the CTY.DAT file parsed to pretty structured array
                                                // Read the parseCtyDatToArrray function comments for info about non-standard entries

    /*
     * Display pretty formated value of structured Array, just uncomment this section
     * echo "<pre>";
     * print_r($array);
     * echo "</pre>";
    */

    /*
     * Output JSON, just uncomment this section
     * json_encode($array);
     */

    /*
     * To be done: XML structured output
     * Why would anyone need that ? :)
     */

    /*
     * What About SQLite ? :)
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
