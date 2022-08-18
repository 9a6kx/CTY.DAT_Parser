<?php

function exactMatches($array) :array {

    $exact_matches_list = (array) null;

    foreach ($array as $key=>$entity){
        foreach ($entity as $description=>$value) {
            if (is_array($value) && $description==="Alias_DXCC_Prefixes") {
                foreach ($value as $index => $prefix) {
                    if (mb_substr($prefix, 0, 1) === "=") {

                        $exact_matches_list[]=array(
                            "main_index" => $key,
                            "prefix" => $prefix,
                            "WAE" => 0
                        );


                    }
                }
            }
        }
    }
    return $exact_matches_list;
}
?>