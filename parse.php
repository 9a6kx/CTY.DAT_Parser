<?php
declare(strict_types = 1);
include_once './configs/sysconfig.inc'; // where system variables are set
include_once './configs/hamradio.inc';  // where HAM specific variables are set

include_once 'download_unzip_cty.php';  // download BIG_CTY.zip file from URL defined in hamradio.inc --> downloadCtyDat() function;
include_once 'to_big_array.php'; // dump ugly structure of CTY.DAT to big preformated array --> mb_trim() function;
include_once 'find_overrides.php'; // function to find CQ and ITU zone Overrides in Alias dxcc prefix --> findOverrides() function;
include_once 'primary_prefix.php'; // function that creates Primary Prefix array --> primaryPrefixes() function;
include_once 'alias_prefix_list.php'; // function that holds Aliases Prefixes --> aliasPrefixes() function;
include_once 'wae_information.php'; // function that update exact matches array and alias prefixes with info about WAE override --> WAE_information() function;
include_once 'exact_matches.php'; // function that holds Exact entries in big.cty --> exactMatches() function;
include_once 'match_exact.php'; // function that matches call upon Exact entries in array of exact_matches --> matchExact() function - returns structured array;
include_once 'split_call_to_portions.php'; // splits callsign in up to tree parts if slashes found --> splitCall() function;
include_once './resources/mb_trim.php'; // multibyte trim function to trim whitespaces safetly--> mb_trim() function;
include_once 'match_last_part.php'; // matching 2nd or 3rd part of callsign with slashes with /P /QRP or similar --> lastPartKnown() function - returns true or false;
include_once 'match_primary_prefix_slash.php'; // matching 2nd or 1st part of callsign with slashes with Primary DXCC prefix --> matchPrimary() function - returns structured array;
include_once 'match_callsign.php'; // matching full call uppon prefixes--> matchAlias() function - returns structured array;
include_once 'manually_override.php'; // override exeptions & stupidity !

$callsign = $_POST['call'] ?? 'IH9/9A6KX/P';

$checked_callsign=$callsign; //INPUT CALL


$checked_callsign=mb_trim($checked_callsign);

$checked_callsign=mb_strtoupper($checked_callsign); // TO UPPERCASE

$big_array = parseCtyDatToArrray(downloadCtyDat($url)); // GET BIG_CTY.ZIP TO BIG ARRAY

$primary_prefixes = primaryPrefixes($big_array); // BUILD MAIN PREFIX LIST
/*
 * [main_index] => 166
 * [prefix] => *IG9
 * [WAE] => 1 // or value 0
 */
$alias_prefixes = WAE_information($big_array, aliasPrefixes($big_array)); // BUILD 2ND-ARY PREFIX LIST
/*
 * [main_index] => 69
 * [prefix] => BV
 * [WAE] => -1 // only this value
 */
$exact_matches = WAE_information($big_array, exactMatches($big_array)); // MAKE EXACT MATCHES LIST AND RETURN WITH INFO ABOUT OFFICIAL DXCC ENTITY OR EXTENDED WAE LIST

/*
 * [main_index] => 25
 * [prefix] => =4Z5FL/LH
 * [WAE] => -1 // only this value
 */

$lets_try_exact_match = matchExact($checked_callsign, $exact_matches, $big_array); // TRY TO MAKE EXACT MATCH
 /*
  * Returnes structured array if found
  * with flag Offical that is bool false or true !!
  * if unnoficial is on WAE extended list with IT9, IH9, TA1, GM/s ...
  *
  * returns (array) null if exact not found
  *
  * note: CQ or ITU, or both zones overrides are final on output of this function
  */

$last_part_check=false;
$match_primary_prefix=(array) null;
$super_partial_match=(array) null;

if (empty($lets_try_exact_match)) {
    $checked_callsign_splited = splitCall($checked_callsign); // SPLIT UPPON SLASHES



    $last_part_check = lastPartKnown($checked_callsign_splited); // check last part for known slash addons, returns true or false

    $match_primary_prefix = matchPrimary($checked_callsign, $checked_callsign_splited, $alias_prefixes, $big_array);
    $a=$match_primary_prefix;

    /*
*  If primary prefix is matched in HB0/9A8MM or in LID in action scenarion 9A6KX/TK outputs structured array
*  if not matched it outputs null array
*/

    if (empty($match_primary_prefix)) {
        $super_partial_match = matchAlias($checked_callsign, $checked_callsign_splited, $primary_prefixes, $alias_prefixes, $big_array);
        $a = $super_partial_match;
        var_dump($super_partial_match);
        echo "<pre>";
    }
}
else {
    $a = $lets_try_exact_match;
}

$manually_override=lastCheck($a, $big_array);

$cleanup=cleanUpFinal($manually_override);
array_multisort($cleanup, SORT_DESC);

// NOTES about MATCHING:

// SV/a, KH8/s only exact matches in CTY file // will need a fix sometime in future
// VP6/d, VP8/g, VP8/h, VP8/o, VP8/s only exact matches in CTY file // will need a fix sometime in future

// JD/m, JD/o only exact matches in CTY file
// 3Y/b, 3Y/p only exact matches in CTY file
// E5/n only exact matches in CTY file - all other E5 that are not exact are matched as South Cook E5
// FK/c only exact matches in CTY file - all other FK New Caledonia
// 3D2/c, 3D2/r, only exact matches in CTY file - all other 3D2 Fiji


// All Matching id done by guessing :)

function cleanUpFinal(array $solution) :array{
    if (array_key_exists(0,$solution)) {
        $new_return=(array) null;
        if (sizeof($solution)===1) {
            foreach ($solution as $key=>$value){
                $new_return=array(
                  "Callsign"=>$solution[0]["Callsign"],
                  "Primary_DXCC_Prefix"=>$solution[0]["Primary_DXCC_Prefix"],
                  "Entity"=>$solution[0]["Entity"],
                  "CQ_Zone"=>$solution[0]["CQ_Zone"],
                  "ITU_Zone"=>$solution[0]["ITU_Zone"],
                  "Continent"=>$solution[0]["Continent"],
                  "Latitude"=>$solution[0]["Latitude"],
                  "Longitude"=>$solution[0]["Longitude"],
                  "UTC_offset"=>$solution[0]["UTC_offset"],
                  "Official"=>$solution[0]["Official"]
                );
                return $new_return;
            }


        }
    }
    return $solution;
}

$dxcc_entry=(array) null;
$wae_extended=(array) null;

if (key_exists(0, $cleanup)) {
    if($cleanup[0]["Official"]===true) {
        $dxcc_entry=array(
            "Callsign"=>$cleanup[0]["Callsign"],
            "Primary_DXCC_Prefix"=>$cleanup[0]["Primary_DXCC_Prefix"],
            "Entity"=>$cleanup[0]["Entity"],
            "CQ_Zone"=>$cleanup[0]["CQ_Zone"],
            "ITU_Zone"=>$cleanup[0]["ITU_Zone"],
            "Continent"=>$cleanup[0]["Continent"],
            "Latitude"=>$cleanup[0]["Latitude"],
            "Longitude"=>$cleanup[0]["Longitude"],
            "UTC_offset"=>$cleanup[0]["UTC_offset"]
        );
        $wae_extended=array(
            "Callsign"=>$cleanup[1]["Callsign"],
            "Primary_DXCC_Prefix"=>$cleanup[1]["Primary_DXCC_Prefix"],
            "Entity"=>$cleanup[1]["Entity"],
            "CQ_Zone"=>$cleanup[1]["CQ_Zone"],
            "ITU_Zone"=>$cleanup[1]["ITU_Zone"],
            "Continent"=>$cleanup[1]["Continent"],
            "Latitude"=>$cleanup[1]["Latitude"],
            "Longitude"=>$cleanup[1]["Longitude"],
            "UTC_offset"=>$cleanup[1]["UTC_offset"]
        );
    }
    elseif (($cleanup[0]["Official"]===false)) {
        $wae_extended=array(
            "Callsign"=>$cleanup[0]["Callsign"],
            "Primary_DXCC_Prefix"=>$cleanup[0]["Primary_DXCC_Prefix"],
            "Entity"=>$cleanup[0]["Entity"],
            "CQ_Zone"=>$cleanup[0]["CQ_Zone"],
            "ITU_Zone"=>$cleanup[0]["ITU_Zone"],
            "Continent"=>$cleanup[0]["Continent"],
            "Latitude"=>$cleanup[0]["Latitude"],
            "Longitude"=>$cleanup[0]["Longitude"],
            "UTC_offset"=>$cleanup[0]["UTC_offset"]
        );
        $dxcc_entry=array(
            "Callsign"=>$cleanup[1]["Callsign"],
            "Primary_DXCC_Prefix"=>$cleanup[1]["Primary_DXCC_Prefix"],
            "Entity"=>$cleanup[1]["Entity"],
            "CQ_Zone"=>$cleanup[1]["CQ_Zone"],
            "ITU_Zone"=>$cleanup[1]["ITU_Zone"],
            "Continent"=>$cleanup[1]["Continent"],
            "Latitude"=>$cleanup[1]["Latitude"],
            "Longitude"=>$cleanup[1]["Longitude"],
            "UTC_offset"=>$cleanup[1]["UTC_offset"]
        );
    }
} else {
    if(!key_exists(0, $cleanup)) {
        $dxcc_entry = array(
            "Callsign" => $cleanup["Callsign"],
            "Primary_DXCC_Prefix" => $cleanup["Primary_DXCC_Prefix"],
            "Entity" => $cleanup["Entity"],
            "CQ_Zone" => $cleanup["CQ_Zone"],
            "ITU_Zone" => $cleanup["ITU_Zone"],
            "Continent" => $cleanup["Continent"],
            "Latitude" => $cleanup["Latitude"],
            "Longitude" => $cleanup["Longitude"],
            "UTC_offset" => $cleanup["UTC_offset"]
        );
        $wae_extended = (array)null;
    }
}
?>

<!-- Generation of HTML elements and listing values-->
<!DOCTYPE html>
<html>
<head>
    <title>Callsign DXCC check by 9a6kx</title>
    <link rel=”stylesheet” href=”https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css”rel=”nofollow” integrity=”sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm” crossorigin=”anonymous”>
</head>
<body style="padding-left: 20px;">

<h2>Check Callsign by 9A6KX</h2>
<br />
<h3>Offical DXCC Entity:</h3>
<span><b>Callsign: </b> <?= $dxcc_entry["Callsign"] ?></span><br />
<span><b>DXCC Entity: </b> <?= $dxcc_entry["Entity"] ?></span><br />
<span><b>Primary DXCC Prefix: </b> <?= $dxcc_entry["Primary_DXCC_Prefix"]?></span><br />
<span><b>CQ Zone: </b> <?= $dxcc_entry["CQ_Zone"] ?></span><br />
<span><b>ITU Zone: </b> <?= $dxcc_entry["ITU_Zone"] ?></span><br />
<span><b>Continent: </b> <?= $dxcc_entry["Continent"] ?></span><br />
<span><b>Latitude: </b> <?= $dxcc_entry["Latitude"] ?></span><br />
<span><b>Longitude: </b> <?= $dxcc_entry["Longitude"] ?></span><br />
<span><b>UTC Offset: </b> <?= $dxcc_entry["UTC_offset"] ?></span><br />

<p><i>Please report back all that you find incorrect on my qrz.com e-mail address</i></p>
</body>
</html>

<?php
if (!empty($wae_extended)){
    echo "
    <h3>WAE List Entry (not official DX Entity):</h3><br />
    <span><b>Callsign: </b> {$wae_extended['Callsign']}</span><br />
    <span><b>Callsign: </b> {$wae_extended['Entity']}</span><br />
    <span><b>Callsign: </b> {$wae_extended['Primary_DXCC_Prefix']}</span><br />
    <span><b>Callsign: </b> {$wae_extended['CQ_Zone']}</span><br />
    <span><b>Callsign: </b> {$wae_extended['ITU_Zone']}</span><br />
    <span><b>Callsign: </b> {$wae_extended['Continent']}</span><br />
    <span><b>Callsign: </b> {$wae_extended['Latitude']}</span><br />
    <span><b>Callsign: </b> {$wae_extended['Longitude']}</span><br />
    <span><b>Callsign: </b> {$wae_extended['UTC_offset']}</span><br />
    ";
}
echo "<h3>Explanatory:</h3><p>";
echo "Callsign matching is primary done based on guessing<br />
      // SV/a, KH8/s only exact matches in CTY file // will need a fix sometime in future<br />
      // VP6/d, VP8/g, VP8/h, VP8/o, VP8/s only exact matches in CTY file // will need a fix sometime in future<br />
      // JD/m, JD/o only exact matches in CTY file<br />
      // 3Y/b, 3Y/p only exact matches in CTY file<br />
      // E5/n only exact matches in CTY file - all other E5 that are not exact are matched as South Cook E5<br />
      // FK/c only exact matches in CTY file - all other FK New Caledonia<br />
      // 3D2/c, 3D2/r, only exact matches in CTY file - all other 3D2 Fiji<br />
      // If callsign consists of three parts for ex. HB0/9A8MM/QRP and third part is rubbish then we ignore it<br />
      // If someone is LID and is using 9A6KX/E7 instead E7/9A6KX or similar, I just don't want to observe people ingoring the rules and laws";
?>