# CTY.DAT_Parser
Parsing CTY.DAT files wih PHP ...
Downloading ZIP directly from web...
Matching callsign on that and checking results.

Version entity is: Kosovo
https://www.country-files.com/big-cty-11-august-2022/



HTML file is a sample input.
PHP is for version 8 and above.
Version 7 will report erros uppon str_starts_with() function.
This one: (str_starts_with($exact_call, $exact_callsign))
Replace with: (mb_strpos($exact_call, $exact_callsign)=== 0)

Note - I made woraround for PHP 7 now and replaced all PHP 8 built-in function str_starts_with($haystack, $needle) in files with own made startsWith($haystack, $needle) function.
Recomendation is to use str_starts_with if you have PHP 8 installed.

If you run once at least this script that is giving results back, it means you just downloaded BIG_CTY.zip, unpacked (created folder and similar)

Change in parse.php file this line to rely on downloaded & extracted file and skip repeatingly downloading BIG_CTY.zip to your servers HDD.

// AS PUBLISHED HERE ON GITHUB
$big_array = parseCtyDatToArrray(downloadCtyDat($url)); // GET BIG_CTY.ZIP TO BIG ARRAY


// IF SCRIPT IS RETURNIG RESULTS, YOU MAY CHANGE THE CODE PORTION TO THIS
// IF YOU DIDN'T TOUCH THIS sysconfig.inc file in config folder
// $extractDir = 'files'; // Name of the directory where files from the BIG_CTY.ZIP are extracted and stored

$big_array = parseCtyDatToArray("/files/cty.dat");

This is especially useful for matching loops of big loogbooks/sql dbs against this script.

Live demo:
https://icm2022.9a1p.com/call_checker/

Please test & report

Thank You and 73
