<?php
$table = $_GET["table"];

$pk_results = $_REQUEST['pk_results'];
$newValue 	= utf8_decode($_REQUEST['newValue']);
$colName 	= utf8_decode($_REQUEST['colName']);
$cur_date = date("Y-m-d");

require '../application/config/config.php';

try {
    $conn = new PDO(DB_TYPE . ':host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    #echo "Connected successfully";

    $sql = "UPDATE `$table` SET `$colName`='$newValue', `Bewertungsdatum` = '$cur_date' WHERE `pk_results`=$pk_results";
    $query = $conn->prepare($sql);
    $query->execute();

    }
catch(PDOException $e)
    {
    #echo "Connection failed: " . $e->getMessage();
    }









/*
$fp = fopen('data.txt', 'w+');
fwrite($fp, $success );
fwrite($fp, $pk_results );
fwrite($fp, $newValue );
fwrite($fp, $colName);
fclose($fp);
*/

?>
