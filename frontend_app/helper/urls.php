<?php

function url_normalizer($url) {
    if (find_partial_string($url, "http://")) {
        $url = str_replace('http://', '', $url);
    }

    if (find_partial_string($url, "https://")) {
        $url = str_replace('https://', '', $url);
    }

    if (find_partial_string($url, "www.")) {
        $url = str_replace('www.', '', $url);
    }


    $url = preg_replace('/([^:])(\/{2,})/', '$1/', $url);
    $url = preg_replace('/[ \t]+/', ' ', preg_replace('/\s*$^\s*/m', "\n", $url));
    $url = ltrim($url);
    $url = rtrim($url);
    $url = rtrim($url, '/');

    return $url;
}

function normalize_urls_json($urls) {

    $urls = explode("\n", $urls);

    $urls_json = '{"URLs": [';

    foreach ($urls AS $url) {
        if (!empty($url)) {

            $urls_json .= '{"URL": "' . url_normalizer($url) . '"},';
        }
    }

    $urls_json = rtrim($urls_json, ',');
    $urls_json .= $json_items . ']}';


    return $urls_json;
}

function get_url_info($url) {


    $url_content = curl($url);
    $protocol = $url_content["Protocol"];
    $normalized_url = url_normalizer($url);
    $parent = parse_url($protocol.$normalized_url, PHP_URL_HOST);
    $ip = gethostbyname($parent);
    $parent = str_replace("www.", "", $parent);
    $parent = url_normalizer($parent);



    $url_info = array("IP" => $ip, "Parent" => $parent, "Normalized_URL" => $normalized_url, "Protocol" => $protocol);
    return $url_info;
}
