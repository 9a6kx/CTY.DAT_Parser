<?php
function WAE_information(array $big_array, array $array) :array {


   foreach ($big_array as $key=>$value) {
       foreach ($value as $description=>$content) {
           if ($description === "Primary_DXCC_Prefix" && (mb_substr($content, 0, 1) === "*") ) {
               foreach ($array as $key2 => &$value2) {
                  if($value2['main_index'] === $key){
                  $value2['WAE'] = 1;
                  }
               }
           }
       }
   }
    $wae_update = $array;

    return $wae_update;
}
?>
