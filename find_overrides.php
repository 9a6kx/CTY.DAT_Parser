<?php

function findOverrides(string $a) :array {
    $i = (array) null;


    preg_match("/\([^\]]*\)/", $a, $matches); // Find all within square brackets !
    preg_match("/\[[^\]]*\]/", $a, $matches2); // Find all within square brackets !

    if ($matches && $matches2) {
    $cq_zone = mb_substr($matches[0], 1 , -1);
    $itu_zone = mb_substr($matches2[0], 1 , -1);
    }

    if ($matches && !$matches2) {
        $cq_zone = mb_substr($matches[0], 1 , -1);
        $itu_zone = 0;
    }

    if (!$matches && $matches2) {
        $cq_zone = 0;
        $itu_zone = mb_substr($matches2[0], 1 , -1);
    }

    if (!$matches && !$matches2) {
        $cq_zone = 0;
        $itu_zone = 0;
    }

    $i = array("CQ_Zone"=>$cq_zone, "ITU_Zone"=>$itu_zone);

    return $i;
}

?>