<?php
function lastCheck(array $callsign, array $big_array) :array
{
    $solution = $callsign;

    $holder=(array)null;
    $destruct=false;

    foreach ($callsign as $key => $value) {
        if ($value["Entity"] === "Svalbard") {
            $prefix_lenght = mb_strlen($value['Primary_DXCC_Prefix']);
            $suffix = mb_substr($value["Callsign"], $prefix_lenght);
                if (mb_strpos($suffix, "B") !== false ) {
                    $holder=array(
                        'Callsign'=>$value['Callsign'],
                        'Primary_DXCC_Prefix'=>"JW/b",
                        'Entity'=>"Bear Island",
                        'CQ_Zone'=>"40",
                        'ITU_Zone'=>"18",
                        'Continent'=>"EU",
                        'Latitude'=>"74.43",
                        'Longitude'=>"-19.08",
                        'UTC_offset'=>"-1",
                        'Official'=>false);
                }
        }

       else if ($value["Entity"] === "Shetland Islands") {
            $holder=array(
                'Callsign'=>$value['Callsign'],
                'Primary_DXCC_Prefix'=>"GM",
                'Entity'=>"Scotland",
                'CQ_Zone'=>"14",
                'ITU_Zone'=>"27",
                'Continent'=>"EU",
                'Latitude'=>"60.50",
                'Longitude'=>"1.5",
                'UTC_offset'=>"0.0",
                'Official'=>true);
        }

       else if(startsWith($value["Callsign"], "FT")) {
         $suffix=mb_substr($value["Callsign"], 2);
         if (mb_strpos($suffix, "G") !== false) {
             $holder=array(
                 'Callsign'=>$value['Callsign'],
                 'Primary_DXCC_Prefix'=>"FT/g",
                 'Entity'=>"Glorioso Islands",
                 'CQ_Zone'=>"39",
                 'ITU_Zone'=>"53",
                 'Continent'=>"AF",
                 'Latitude'=>"-11.55",
                 'Longitude'=>"-4.0",
                 'UTC_offset'=>"-4.0",
                 'Official'=>true);
         }
           if (mb_strpos($suffix, "J") !== false || mb_strpos($suffix, "E") !== false) {
               $holder=array(
                   'Callsign'=>$value['Callsign'],
                   'Primary_DXCC_Prefix'=>"FT/j",
                   'Entity'=>"Juan de Nova, Europa",
                   'CQ_Zone'=>"39",
                   'ITU_Zone'=>"53",
                   'Continent'=>"AF",
                   'Latitude'=>"-17.05",
                   'Longitude'=>"-42.72",
                   'UTC_offset'=>"-3.0",
                   'Official'=>true);
           }
           if (mb_strpos($suffix, "T") !== false) {
               $holder=array(
                   'Callsign'=>$value['Callsign'],
                   'Primary_DXCC_Prefix'=>"FT/t",
                   'Entity'=>"Tromelin Island",
                   'CQ_Zone'=>"39",
                   'ITU_Zone'=>"53",
                   'Continent'=>"AF",
                   'Latitude'=>"-15.58",
                   'Longitude'=>"-54.50",
                   'UTC_offset'=>"-4.0",
                   'Official'=>true);
           }
           if (mb_strpos($suffix, "W") !== false) {
               $holder=array(
                   'Callsign'=>$value['Callsign'],
                   'Primary_DXCC_Prefix'=>"FT/w",
                   'Entity'=>"Crozet Island",
                   'CQ_Zone'=>"39",
                   'ITU_Zone'=>"68",
                   'Continent'=>"AF",
                   'Latitude'=>"-46.42",
                   'Longitude'=>"-51.75",
                   'UTC_offset'=>"-5.0",
                   'Official'=>true);
           }
           if (mb_strpos($suffix, "X") !== false) {
               $holder=array(
                   'Callsign'=>$value['Callsign'],
                   'Primary_DXCC_Prefix'=>"FT/x",
                   'Entity'=>"Kerguelen Islands",
                   'CQ_Zone'=>"39",
                   'ITU_Zone'=>"68",
                   'Continent'=>"AF",
                   'Latitude'=>"-49.00",
                   'Longitude'=>"-69.27",
                   'UTC_offset'=>"-5.0",
                   'Official'=>true);
           }
           if (mb_strpos($suffix, "Z") !== false) {
               $holder=array(
                   'Callsign'=>$value['Callsign'],
                   'Primary_DXCC_Prefix'=>"FT/z",
                   'Entity'=>"Amsterdan & St. Paul Is.",
                   'CQ_Zone'=>"39",
                   'ITU_Zone'=>"68",
                   'Continent'=>"AF",
                   'Latitude'=>"-37.85",
                   'Longitude'=>"-77.53",
                   'UTC_offset'=>"-5.0",
                   'Official'=>true);
           }
         }
       else if(startsWith($value["Callsign"], "FO")) {
           $suffix=mb_substr($value["Callsign"], 2);
           if (mb_strpos(mb_substr($suffix, -2), "/A",0) !== false) {
               $holder=array(
                   'Callsign'=>$value['Callsign'],
                   'Primary_DXCC_Prefix'=>"FO/a",
                   'Entity'=>"Austral Islands",
                   'CQ_Zone'=>"32",
                   'ITU_Zone'=>"63",
                   'Continent'=>"OC",
                   'Latitude'=>"-23.37",
                   'Longitude'=>"149.48",
                   'UTC_offset'=>"10.0",
                   'Official'=>true);
               $destruct=true;
           }
           if (mb_strpos(mb_substr($suffix, -2), "/C",0) !== false) {
               $holder=array(
                   'Callsign'=>$value['Callsign'],
                   'Primary_DXCC_Prefix'=>"FO/c",
                   'Entity'=>"Clipperton Island",
                   'CQ_Zone'=>"07",
                   'ITU_Zone'=>"10",
                   'Continent'=>"NA",
                   'Latitude'=>"10.28",
                   'Longitude'=>"109.22",
                   'UTC_offset'=>"8.0",
                   'Official'=>true);
               $destruct=true;
           }
           if (mb_strpos(mb_substr($suffix, -2), "/M",0) !== false) {
               $holder=array(
                   'Callsign'=>$value['Callsign'],
                   'Primary_DXCC_Prefix'=>"FO/m",
                   'Entity'=>"Marquesas Islands",
                   'CQ_Zone'=>"31",
                   'ITU_Zone'=>"63",
                   'Continent'=>"OC",
                   'Latitude'=>"-8.92",
                   'Longitude'=>"140.07",
                   'UTC_offset'=>"9.5",
                   'Official'=>true);
               $destruct=true;
           }
       }
       elseif (startsWith($value["Callsign"], "CE0X")) { // San Felix & Ambrosio
           $holder=array(
               'Callsign'=>$value['Callsign'],
               'Primary_DXCC_Prefix'=>"CE0X",
               'Entity'=>"San Felix & San Ambrosio",
               'CQ_Zone'=>"12",
               'ITU_Zone'=>"14",
               'Continent'=>"SA",
               'Latitude'=>"-26.28",
               'Longitude'=>"80.07",
               'UTC_offset'=>"4.0",
               'Official'=>true);
           $destruct=true;
       }
       elseif (startsWith($value["Callsign"], "CE0Y")) { // Easter Island
           $holder=array(
               'Callsign'=>$value['Callsign'],
               'Primary_DXCC_Prefix'=>"CE0Y",
               'Entity'=>"Easter Island",
               'CQ_Zone'=>"12",
               'ITU_Zone'=>"63",
               'Continent'=>"SA",
               'Latitude'=>"-27.10",
               'Longitude'=>"109.37",
               'UTC_offset'=>"6.0",
               'Official'=>true);
           $destruct=true;
       }
       elseif (startsWith($value["Callsign"], "CE0Z")) { // Juan Fernandez
           $holder=array(
               'Callsign'=>$value['Callsign'],
               'Primary_DXCC_Prefix'=>"CE0Z",
               'Entity'=>"Juan Fernandez Islands",
               'CQ_Zone'=>"12",
               'ITU_Zone'=>"14",
               'Continent'=>"SA",
               'Latitude'=>"-33.60",
               'Longitude'=>"78.85",
               'UTC_offset'=>"4.0",
               'Official'=>true);
           $destruct=true;
       }
   }
// TA1 and other TA now are all Turkey official but EU/AS as continet, in unofficial values
// European Turkey and Asiatic Turkey
    if (array_key_exists(0, $solution)){
    if ($solution[0]["Entity"]==="Asiatic Turkey" && array_key_exists(1,$solution)) {
        array_walk($solution, function (&$solution){
            if($solution["Entity"]=="Asiatic Turkey"){
            $solution["Entity"]="Turkey";
            $solution["Continent"]="EU";
            $solution["Latitude"]="41.02";
            $solution["Longitude"]="-28.97";
            }
        });
        }
    }

    if (array_key_exists(0, $solution)) {
        if ($solution[0]["Entity"] === "Asiatic Turkey" && !array_key_exists(1, $solution)) {
            array_walk($solution, function (&$solution) {
                if ($solution["Entity"] == "Asiatic Turkey") {
                    $solution["Entity"] = "Turkey";
                }
            });
            $holder = array(
                'Callsign' => $solution[0]["Callsign"],
                'Primary_DXCC_Prefix' => "TA",
                'Entity' => "Asiatic Turkey",
                'CQ_Zone' => "20",
                'ITU_Zone' => "39",
                'Continent' => "AS",
                'Latitude' => "39.18",
                'Longitude' => "-35.65",
                'UTC_offset' => "-2.0",
                'Official' => false);
        }
    }

// appending overrides

    if(!empty($holder)){
        if ($destruct===false) {
            array_push($solution, $holder);
        }
        if ($destruct===true){
            array_splice($solution, 0);
            array_push($solution, $holder);
        }
    }

// fix for cases like F/FR , EA/EA6/EA8/EA9 , 3Y/3Y0, BV/BV9P

    $count=count($solution);

            if ($count===2 && ($solution[0]['Official'] && !$solution[1]['Official']) && !startsWith($solution[0]["Callsign"],"TA1" )) {
                return $solution;
            }
            if ($count===2 && (!$solution[0]['Official'] && $solution[1]['Official']) && !startsWith($solution[0]["Callsign"],"TA1" )) {
                return $solution;
            }

// fix for EA // EA6/EA8/EA9 & F // FR, FK ... & I / IS0 , 3C/3C0, BV/BV9P
            if ($count >= 2 ) {
                if ($solution[0]["Entity"]==="Spain") {
                array_multisort($solution, SORT_ASC);
                return array_shift($solution);
                }
                if ($solution[0]["Entity"]==="France") {
                    array_multisort($solution, SORT_DESC);
                    return array_shift($solution);
                }

                if ($solution[0]["Entity"]==="Italy" && $solution[1]["Entity"]==="Sardinia") {
                    array_multisort($solution, SORT_DESC);
                    return array_shift($solution);
                }
                if ($solution[0]["Entity"]==="Taiwan" && $solution[1]["Entity"]!=="Taiwan") {
                    array_multisort($solution, SORT_ASC);
                    return array_shift($solution);
                }
                if ($solution[0]["Entity"]==="Equatorial Guinea" && $solution[1]["Equatorial Guinea"]!=="Taiwan") {
                    array_multisort($solution, SORT_ASC);
                    return array_shift($solution);
                }
                if (sizeof($solution)>1 && $solution[0]["Entity"]==="United States") {
                    array_multisort($solution, SORT_ASC);
                    return array_shift($solution);
                }
           }

// fix for IG9/IK3UNA to diplay Official DXCC
    // tu mi ide KH8TTT u provjeru kao H8TTT !!! :)
    if (sizeof($solution)===1 && !$solution[0]["Official"]) {
        $check_official=mb_substr($solution[0]["Callsign"],mb_strlen($solution[0]["Primary_DXCC_Prefix"]));
        global $primary_prefixes;
        global $alias_prefixes;
        global $big_array;
        $helper=$solution[0]["Callsign"];
        $holder = matchAlias($check_official, array("int"=>1,"a"=>$check_official,"b"=>false,"c"=>null), $primary_prefixes, $alias_prefixes, $big_array, true);
        array_walk($holder, function(&$holder) use ($helper){
            $holder["Callsign"]=$helper;
        });
        array_push($solution, $holder[0]);
        return $solution;
    }



    return $solution;
}
?>
