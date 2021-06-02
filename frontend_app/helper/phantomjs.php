<?php

function rasterize($url2save, $file_screenshot, $file_html) {
    
    $phantomjs = 'phantomjs --ignore-ssl-errors=true --web-security=no --cookies-file=tmp/cookies.txt rasterize.js "' . $url2save . '" ' . $file_screenshot . ' 1920px ' . $file_html;
    

    // Schreibt den Inhalt in die Datei zurück

   
   
$fp = fopen('phantom.txt', 'a+');
fwrite($fp, "\r\n".$phantomjs."\r\n");
fclose($fp);

  
     
$output = shell_exec($phantomjs);
     
     if(empty($output)) {
         $output = -1;
     }
     
        
    return $output;
}