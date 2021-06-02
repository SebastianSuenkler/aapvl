<?php

require 'strings.php';
require 'curl.php';

if ($_POST["action"] == "Normalize_URLs") {
    $elements_urls = explode("\n", $_POST["elements"]);
    $elements_urls_normalized = array();
    foreach ($elements_urls AS $element) {
        if (!empty($element)) {
            $elements_urls_normalized[] = url_normalizer($element);
        }
    }

    $elements_urls_imploded = implode("\n", $elements_urls_normalized);
    echo $elements_urls_imploded;
}