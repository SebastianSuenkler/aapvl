<?php

function curl($url) {

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.79 Safari/537.36 Edge/14.14393');
    curl_setopt($curl, CURLOPT_ENCODING, '');
    curl_setopt($curl, CURLOPT_AUTOREFERER, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_BINARYTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_REFERER, '');
    curl_setopt($curl, CURLOPT_COOKIEFILE, "cookie.txt");
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: text/html; charset=UTF-8'));

    $html = curl_exec($curl);
    $info = curl_getinfo($curl); // Holt sich Informationen des Transfers

    $url_info = array();

    $http_statuscode = $info['http_code'];

    curl_close($curl);


    $isHTML = strpos($info['content_type'], 'html');

    if (($html == false OR $isHTML == false) OR strlen($html) < 50 OR $http_statuscode < 200 OR $http_statuscode > 400) {
        $html = -1;
    }


    if ($info['ssl_verify_result'] > 0) {
        $url_info["Protocol"] = "https://";
    } else {
        $url_info["Protocol"] = "http://";
    }

 if(find_partial_string($html, "")) {
         $json = utf8_decode($html);
     }
    
    return array("Source" => $html, "Protocol" => $url_info["Protocol"]);
}

function change_relative_urls_to_absolute($html, $parent) {
    $regexp = "<base\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)";
    preg_match_all("/$regexp/siU", $html, $matches);
    $base_href = $matches[2][0];

    if (empty($base_href)) {
        $base_href = $parent;
    }

    $base_href .= "/";

    $html = preg_replace("#(<\s*a\s+[^>]*href\s*=\s*[\"'])(?!http)([^\"'>]+)([\"'>]+)#", '$1' . $base_href . '$2$3', $html);

    return $html;
}
