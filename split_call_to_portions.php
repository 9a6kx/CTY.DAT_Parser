<?php
function splitCall(string $callsign) :array{
    if(mb_substr_count($callsign, "/")===2) {
        list($a, $b, $c) = explode("/", $callsign);
        $int=3;
    }
    elseif (mb_substr_count($callsign, "/")===1){
        list($a, $b) = explode("/", $callsign);
        $c = false;
        $int=2;
    }
    elseif (mb_substr_count($callsign, "/")===0){
        $a = $callsign;
        $b = false;
        $c = false;
        $int=1;
    }

    $callsign_composition=array("int"=>$int,"a"=>$a,"b"=>$b,"c"=>$c);

    return $callsign_composition;
}

?>