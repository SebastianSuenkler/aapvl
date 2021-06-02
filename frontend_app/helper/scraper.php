<?php

/* Helper to scrape Search Engines JSON Data */

function scrape($serp, $xpath) {

    $doc = new DOMDocument();
    @$doc->loadHTML($serp);

    $capture_xpath = new DOMXPath($doc);
    $hrefs = $capture_xpath->evaluate($xpath);

    for ($y = 0; $y < $hrefs->length; $y++) {

        $href = $hrefs->item($y);
        $url_href = $href->getAttribute('href');

        $url_href = str_replace('\\', "/", $url_href);

        $url_href = str_replace('"', "", $url_href);

        $url_text = sanitze_string_for_db($href->nodeValue);

        $url_text = str_replace(array("\n"), '', $url_text);

        $url_text = trim($url_text);

        if ((preg_match('/' . "http:" . '/is', $url_href) == 1) OR ( preg_match('/' . "https:" . '/is', $url_href) == 1)) {
            $results[] = array("URL" => $url_href, "Text" => $url_text);
        }
    }
    if (!empty($results)) {
        $results = array_unique($results, SORT_REGULAR);
        $json_items = "";
        $json = '{"URLs":[';

        foreach ($results AS $result) {
            $json_items .= '{"URL": "' . $result["URL"] . '", "Text": "' . $result["Text"] . '"},';
        }

        $json_items = substr($json_items, 0, -1);

        $json .= $json_items . ']}';
        
     if(find_partial_string($json, "")) {
         $json = utf8_decode($json);
     }
     

        return $json;
    }
}

function extract_urls_to_json($html, $parent) {

    $regexp = "<base\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)";
    preg_match_all("/$regexp/siU", $html, $matches);
    $base_href = $matches[2][0];

    if (empty($base_href)) {
        $base_href = $parent;
    }

    $base_href .= "/";

    $doc = new DOMDocument();


    @$doc->loadHTML($html);

    $capture_xpath = new DOMXPath($doc);
    $hrefs = $capture_xpath->evaluate("//a");

    for ($y = 0; $y < $hrefs->length; $y++) {

        $href = $hrefs->item($y);
        $result = $href->getAttribute('href');
        
      

        $result = preg_replace('/([^:])(\/{2,})/', '$1/', $result);
        $result = preg_replace('/[ \t]+/', ' ', preg_replace('/\s*$^\s*/m', "\n", $result));

        $result = preg_replace("/\r|\n/", "", $result);
        
        $result = str_replace('"', '', $result);

        $result = trim($result);
        
       
        $text = sanitze_string_for_db($href->nodeValue);
               
        $text = preg_replace('/[ \t]+/', ' ', preg_replace('/\s*$^\s*/m', "\n", $text));

        $text = preg_replace("/\r|\n/", "", $text);

        $text = trim($text);
        
         
       

        if ((preg_match('/' . "http:" . '/is', $result) == 1) OR ( preg_match('/' . "https:" . '/is', $result) == 1)  OR ( preg_match('/' . "www." . '/is', $result) == 1)) {
            
        } else {
            $result = $base_href . $result;
            $result = preg_replace('/([^:])(\/{2,})/', '$1/', $result);
        }
        
      $result = ltrim($result, '//');

            $results[] = array("URL" => $result, "Text" => $text);
            
           

        
    }
    if (!empty($results)) {
        $results = array_unique($results, SORT_REGULAR);
        $json_items = "";
        $json = '{"urls":[';

        foreach ($results AS $result) {

            $json_item = '{"URL": "' . $result["URL"] . '", "Text": "' . $result["Text"] . '"},';
            $json_item = preg_replace('/[ \t]+/', ' ', preg_replace('/\s*$^\s*/m', "\n", $json_item));
            $json_item = trim($json_item);


            $json_items .= $json_item;
        }

        $json_items = substr($json_items, 0, -1);

        $json .= $json_items . ']}';

        return $json;
    }
}

function filter_urls($urls, $url_filter) {

    $filtered_results = array();

    foreach ($url_filter AS $filter) {
        $keyword = $filter["keyword"];
        $weight = $filter["weight"];



        foreach ($urls AS $url) {



            foreach ($url AS $elements) {
                $url_text = $elements["Text"];
                $url_href = $elements["URL"];



                if (find_partial_string($keyword, $url_text) OR find_partial_string($keyword, $url_href)) {
                    $filtered_results[] = array("URL" => $url_href, "Weight" => $weight);
                }
            }
        }
    }

    $filtered_results = array_unique($filtered_results, SORT_REGULAR);

    return $filtered_results[0]["URL"];
}

function extract_meta($html) {

    $html = mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8");
    $html = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html);
    $html = preg_replace('#<style(.*?)>(.*?)</style>#is', '', $html);



    $doc = new DOMDocument();


    @$doc->loadHTML($html);
    $capture_xpath = new DOMXPath($doc);


    $title = $capture_xpath->query("//title");
    $description = $capture_xpath->evaluate("//meta[@name='description']/@content");
    $keywords = $capture_xpath->evaluate("//meta[@name='keywords']/@content");

    foreach ($title as $t) {
        $meta_title = $t->nodeValue . PHP_EOL;
    }

    foreach ($description as $d) {
        $meta_description = $d->value . PHP_EOL;
    }

    foreach ($keywords as $k) {
        $meta_keywords = $k->value . PHP_EOL;
    }

    $meta = array("Title" => $meta_title, "Description" => $meta_description, "Keywords" => $meta_keywords);

    return $meta;
}

?>
