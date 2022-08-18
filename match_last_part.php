<?php
/**
 * @param array $portions
 * @return bool
 */
function lastPartKnown(array $portions) :bool
{
    global $legal;
    global $illegal;

    $abrevations = array_merge($legal, $illegal);

    $index = $portions['int'];

    $last_part = "";

    switch ($index) {
        case 3:
            $last_part = $portions['c'];
            if(is_numeric($last_part)) {return true;}
            break;
        case 2:
            $last_part = $portions['b'];
            if(is_numeric($last_part)) {return true;}
            break;
        case 1:
            $last_part = $portions['a'];
            if(is_numeric($last_part)) {return true;}
            break;
    }
    $a=(array) null;
    foreach ($abrevations as $key => $value) {
        if ($value === $last_part) {
        $a[]=array(true);
        }
    }
    if(!empty($a)) {return true;} else {return false;}
}
?>