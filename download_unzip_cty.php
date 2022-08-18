<?php
/*
 * Function for geting the cty.dat from the web and unzip the file
 */
function downloadCtyDat (string $url, bool $update=false) :string
{ // lets fetch CtyDat, a BIG one
    global $extractDir;
    if ($update){$update=true;}; // update on demand not yet implemented
    /*
     * Never is an good idea to call for global variable, but let's consider this a non-mutable variable
     * Function has a lot side effects is not a pure function
     */

    /*
     * Never is an good idea to call for global variable, but let's consider this a non-mutable variable
     * Function has a lot side effects is not a pure function
     */
    $zipFile = 'BIG.CTY.zip';                                       // Rename ZIP file
    global $extractDir;                                             // Name of the directory where files are extracted
    $zipResource = fopen($zipFile, "w");
    // Get The Zip File From Server
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_FILE, $zipResource);
    $page = curl_exec($ch);
    if (!$page) {
        echo 'Error :- ' . curl_error($ch);
    }
    curl_close($ch);
    /* Open the Zip file */
    $zip = new ZipArchive;
    $extractPath = $extractDir;

    if ($zip->open($zipFile) != "true") {
        echo 'Error :- Unable to open the Zip File';
        return "";
    }

    /* Extract Zip File */
    $zip->extractTo($extractPath);
    $zip->close();
    $files = scandir($extractDir);
    $file = (array)null;
    foreach ($files as $value) {
        if ($value === 'cty.dat' || $value === 'CTY.DAT') {
            $file[] = '/' . $extractDir . '/' . $value;
        }
    }
    $i = $file[0];

    return $i; // PATH TO CTY.DAT FILE
}
?>