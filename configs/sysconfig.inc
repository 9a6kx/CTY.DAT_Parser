<?php
ini_set('memory_limit', '1024M');
$extractDir = 'files'; // Name of the directory where files from the BIG_CTY.ZIP are extracted and stored



// I used a lot str_starts_with function of PHP 8, this is workaround for PHP7
function startsWith(string $haystack, string $needle) :bool{
    return $haystack[0] === $needle[0] ? strncmp($haystack, $needle, strlen($needle)) === 0 : false;
}




?>