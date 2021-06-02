<?php

function rasterize($url2save, $file_screenshot, $file_html) {


    if (!empty($file_screenshot)) {

        $phantomjs = 'phantomjs --ignore-ssl-errors=true --web-security=no --cookies-file=tmp/cookies.txt rasterize.js "' . $url2save . '" ' . $file_screenshot . ' 1920px ' . $file_html;


        // Schreibt den Inhalt in die Datei zurück


        $fp = fopen('phantom.txt', 'a+');
        fwrite($fp, "\r\n" . $phantomjs . "\r\n");
        fclose($fp);

        $output = shell_exec($phantomjs);
    } else {
       
        $curl = curl($url2save);
      
        $output = $curl["Source"];

        if ($output != -1) {

            $fp = fopen($file_html, 'w+');
            fwrite($fp, $output);
            fclose($fp);
        }
    }

    if (empty($output)) {
        $output = -1;
    }



    return $output;
}
