<?php

function aliasPrefixes($array) :array {
    $alias_prefixes = (array) null;

    foreach ($array as $key=>$entity){
        foreach ($entity as $description=>$value) {
            if (is_array($value) && $description==="Alias_DXCC_Prefixes") {
                foreach ($value as $index => $prefix) {
                    if (mb_substr($prefix, 0, 1) !== "=") {
                        $alias_prefixes[]=array(
                            "main_index" => $key,
                            "prefix" => $prefix,
                            "WAE" => 0
                        );


                    }
                }
            }
        }
    }

    return $alias_prefixes;
}
?>