<?php

function primaryPrefixes (array $array) :array {
    $primary_prefixes = (array)null;

    foreach ($array as $key=>$entity){
        foreach ($entity as $description=>$value) {
            if ($description === "Primary_DXCC_Prefix") {
                if (mb_strpos($value, "*",0 )===0) {
                    $value=mb_substr($value,1);
                    $primary_prefixes[] = array(
                        "main_index" => $key,
                        "prefix" => $value,
                        "WAE" => 1);
                }
                elseif (mb_strpos($value, "*",0 )!==0) {
                    $primary_prefixes[] = array(
                        "main_index" => $key,
                        "prefix" => $value,
                        "WAE" => 0);
                }
            }


        }

    }
    return $primary_prefixes;
}
?>