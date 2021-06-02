<?php

/* Helper to open files to process them in the app */

// Method to open any external text file
function open_external_file_to_read($file) {

    $file_content = file_get_contents(URL . $file);
    return $file_content;
}

function write_to_file($content, $file) {
    $fp = fopen($file, 'w+');
    fwrite($fp, $content);
    fclose($fp);
}

function list_files($dir, $file_extension){
    
    if ($handle = opendir($dir)) {

    while (false !== ($entry = readdir($handle))) {

        if ($entry != "." && $entry != ".." && find_partial_string($entry, ".".$file_extension)) {

            $files[] = $entry;
        }
    }

    closedir($handle);
    
    return $files;
}
    
}

function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir . "/" . $object) == "dir")
                    rrmdir($dir . "/" . $object);
                else
                    unlink($dir . "/" . $object);
            }
        }
        reset($objects);
        rmdir($dir);
    }
}
