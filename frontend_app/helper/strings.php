<?php

function generate_query_from_string($query) {
    $query = str_replace(" ", "+", $query);
    return $query;
}

  function cleanWhitespace($string) {
    return trim( preg_replace('/\s+/', ' ', $string) );
}

function sanitze_string_for_db($string) {
    $string = strip_tags($string);
    $string = str_replace(array("'"), '', $string);
    $string = str_replace(array('"'), '', $string);
    $string = str_replace(array('\n'), '', $string);
    return $string;
}

function find_partial_string($string, $search) {
    $pos = stripos($string, $search);
    if ($pos === false) {
        return false;
    } else {
        return true;
    }
}

function find_partial_url($part_of_url, $url) {

  preg_match('/^'.$part_of_url.'/', $url, $matches);

  				if(!empty($matches)){

  				return true;
  				}

}

function get_csv_file($csv_file) {
    $row = 1;
    if (file_exists($csv_file)) {
        $handle = fopen($csv_file, "r");
    } else {
        exit();
    }
    while (($data = fgetcsv($handle, "\n")) !== FALSE) {
        $num = count($data);

        $row++;
        for ($c = 0; $c < $num; $c++) {
            $csv[] = $data[$c];
        }
    }
    fclose($handle);
    return $csv;
}

function get_csv_file_delimiter($csv_file, $delimiter) {
    $row = 1;
    if (file_exists($csv_file)) {
        $handle = fopen($csv_file, "r");
    } else {
        exit();
    }
    while (($data = fgetcsv($handle, $delimiter)) !== FALSE) {
        $num = count($data);

        $row++;
        for ($c = 0; $c < $num; $c++) {
            $csv[] = $data[$c];
        }
    }
    fclose($handle);
    return $csv;
}

function get_csv_string($csv) {

    #  $Data = str_getcsv($csv);
    # foreach($Data as &$Row) $Row = str_getcsv($Row, "\n");

    $Row = explode("\n", $csv);
    return ($Row);
}

function get_csv_string_char($csv, $char) {

    #  $Data = str_getcsv($csv);
    # foreach($Data as &$Row) $Row = str_getcsv($Row, "\n");

    $Row = explode($char, $csv);
    return ($Row);
}

function split_string_by_delimiter($string, $delimiter) {
    $explode = explode($delimiter, $string);
    return ($explode);
}

function split_and_filter_string_by_delimiter($string, $delimiter) {

    $string = split_string_by_delimiter($string, $delimiter);
    $array = array_unique($string, SORT_REGULAR);
    return $array;
}

function replace_utf8($string) {
  $string = strtr($string, array(
  	'\u00A0'    => ' ',
  	'\u0026'    => '&',
  	'\u003C'    => '<',
  	'\u003E'    => '>',
  	'\u00E4'    => 'ä',
  	'\u00C4'    => 'Ä',
  	'\u00F6'    => 'ö',
  	'\u00D6'    => 'Ö',
  	'\u00FC'    => 'ü',
  	'\u00DC'    => 'Ü',
  	'\u00DF'    => 'ß',
  	'\u20AC'    => '€',
  	'\u0024'    => '$',
  	'\u00A3'    => '£',

  	'\u00a0'    => ' ',
  	'\u003c'    => '<',
  	'\u003e'    => '>',
  	'\u00e4'    => 'ä',
  	'\u00c4'    => 'Ä',
  	'\u00f6'    => 'ö',
  	'\u00d6'    => 'Ö',
  	'\u00fc'    => 'ü',
  	'\u00dc'    => 'Ü',
  	'\u00df'    => 'ß',
  	'\u20ac'    => '€',
  	'\u00a3'    => '£',
  ));

  return $string;

}

?>
