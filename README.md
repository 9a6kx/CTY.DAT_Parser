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


Live demo:
https://icm2022.9a1p.com/call_checker/

Please test & report

Thank You and 73
