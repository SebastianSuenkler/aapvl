<?php

/* Helper to process JSON Data */

// Method to read JSON and save it as Array
function process_json($json_object) {
    return (json_decode($json_object, true));
}

function process_external_json_from_file($file) {

    $file_content = file_get_contents($file);
    $file_content = process_json($file_content);
    return $file_content;
}
