error_reporting(0);

// DB table to use
$table = $_GET["table"];

// Table's primary key
$primaryKey = 'pk_results';

$columns = array(
array( 'db' => '`r`.`pk_results`', 'dt' => 0, 'field' => 'pk_results' ),
array( 'db' => '`r`.`Datum`', 'dt' => 1, 'field' => 'Datum' ),
array( 'db' => '`r`.`Domain`', 'dt' => 2, 'field' => 'Domain'),
array( 'db' => '`r`.`Unterseiten`', 'dt' => 3, 'field' => 'Unterseiten'),
array( 'db' => '`r`.`Nummer`', 'dt' => 4, 'field' => 'Nummer'),
array( 'db' => '`r`.`BioC`', 'dt' => 5, 'field' => 'BioC'),
array( 'db' => '`r`.`Bewerbung`', 'dt' => 6, 'field' => 'Bewerbung'),
array( 'db' => '`r`.`Anbietername`', 'dt' => 7, 'field' => 'Anbietername'),
array( 'db' => '`r`.`Straße`', 'dt' => 8, 'field' => 'Straße'),
array( 'db' => '`r`.`PLZ`', 'dt' => 9, 'field' => 'PLZ'),
array( 'db' => '`r`.`Ort`', 'dt' => 10, 'field' => 'Ort'),
array( 'db' => '`r`.`Bundesland`', 'dt' => 11, 'field' => 'Bundesland'),
array( 'db' => '`r`.`Kreis`', 'dt' => 12, 'field' => 'Kreis'),
array( 'db' => '`r`.`Bearbeiter`', 'dt' => 13, 'field' => 'Bearbeiter'),
array( 'db' => '`r`.`Shop`', 'dt' => 14, 'field' => 'Shop'),
array( 'db' => '`r`.`Lebensmittel-Shop`', 'dt' => 15, 'field' => 'Lebensmittel-Shop'),
array( 'db' => '`r`.`Verdacht`', 'dt' => 16, 'field' => 'Verdacht'),
array( 'db' => '`r`.`Rückmeldungen`', 'dt' => 17, 'field' => 'Rückmeldungen'),
array( 'db' => '`r`.`Kommentar`', 'dt' => 18, 'field' => 'Kommentar'),
array( 'db' => '`r`.`Bewertungsdatum`', 'dt' => 19, 'field' => 'Bewertungsdatum')
);



// SQL server connection information Server
/*
$sql_details = array(
    'user' => 'aapvl',
    'pass' => 'SFZA{x}~',
    'db'   => 'aapvl',
    'host' => 'localhost'
);

*/
// SQL server connection information local
$sql_details = array(
    'user' => 'root',
    'pass' => '',
    'db'   => 'aapvl',
    'host' => 'localhost'
);


require( 'ssp.php' );

$joinQuery = "FROM ".$table." r";

echo json_encode(
	SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere )
);
