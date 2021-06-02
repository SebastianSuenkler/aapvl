<?php $id_cases = $this->pk_cases;

$js_template_url = URL."inc/js/aapvl_datatables_case04_template.js";

$php_template_url = URL."server/server_processing_case04_template.php";

$php_path = "server/server_processing_".$this->pk_cases.".php";

$php_server_file = "server_processing_".$this->pk_cases.".php";

$script = URL."inc/js/aapvl_datatables_".$this->pk_cases.".js";

$script_path = "inc/js/aapvl_datatables_".$this->pk_cases.".js";

$result_table = "results_".$id_cases;

$custom_columns = $this->custom_columns;

$custom_column_index = $this->last_column_index-1;



for($i = 0; $i < count($custom_columns); $i++) {

$custom_index[] = $i + $custom_column_index;

}



$search_columns_template = '"targets": [1,2,3,4,5,6,7,8,9,12,13,14,15,16],';

$exp_search_columns = explode('],', $search_columns_template);

$search_columns_part_1 = $exp_search_columns[0];

foreach($custom_index AS $value) {

$search_columns_part_1 = $search_columns_part_1.",".$value;

}

$search_columns = $search_columns_part_1.'],';

$edit_columns_template = '"targets": [4,5,6,7,8,9,12,13,14,15,16],';

$exp_edit_columns = explode('],', $edit_columns_template);

$edit_columns_part_1 = $exp_edit_columns[0];

foreach($custom_index AS $value) {

$edit_columns_part_1 = $edit_columns_part_1.",".$value;

}

$edit_columns = $edit_columns_part_1.'],';


$php_template = file_get_contents($php_template_url);

$exp_php_template = explode("\n", $php_template);


for($i = 0; $i < count($exp_php_template); $i++) {

  if (strpos($exp_php_template[$i], "'dt' => 18") !== false) {
    $split_line_php = $i+1;
  }

}

for($i = 0; $i < count($custom_index); $i++) {

   $append_php_def[] =  ",array( 'db' => '`r`.`".$custom_columns[$i]."`', 'dt' => ".$custom_index[$i].", 'field' => '".$custom_columns[$i]."')";
}

$split_array_php_write = array_chunk($exp_php_template, $split_line_php);

$merge_php = array_merge($split_array_php_write[0], $append_php_def, $split_array_php_write[1], $split_array_php_write[2]);

if(!empty($custom_index)) {

$write_php = implode("\n", $merge_php);

$write_php = "<?php\n".$write_php;

}

else {

  $write_php = "<?php\n".$php_template;

}

$fp = fopen($php_path, 'w+');
fwrite($fp, $write_php);
fclose($fp);

$js_template = file_get_contents($js_template_url);

$write = str_replace("server_processing.php", $php_server_file, $js_template);

$write = str_replace("result_table", $result_table, $write);

if(!empty($custom_index)) {

$write = str_replace($search_columns_template, $search_columns, $write);

$write = str_replace($edit_columns_template, $edit_columns, $write);



$exp_write = explode("\n", $write);

for($i = 0; $i < count($exp_write); $i++) {

  if (strpos($exp_write[$i], "$('td:eq(15)") !== false) {
    $split_line = $i+1;
  }

}

for($i = 0; $i < count($custom_index); $i++) {

   $append_column_def[] = "$('td:eq(".$custom_index[$i].")', row).css('min-width', '80px').attr('name', '".$custom_columns[$i]."').attr('id', data[0]).css('vertical-align', 'top');";
}

$split_array_write = array_chunk($exp_write, $split_line);

$merge = array_merge($split_array_write[0], $append_column_def, $split_array_write[1]);

$write = implode("\n", $merge);

}

$fp = fopen($script_path, 'w+');
fwrite($fp, $write);
fclose($fp);




?>

<div id="aapvl_content" class="container-fluid">
<div id='aapvl_table' class='container-fluid' align='center'><!-- start div for site content end div tag is in footer template -->
    <h3>Nachbewertung</h3>



  <form class="form-horizontal aapvl-form" action="../add_custom_column_for_judgement/<?php echo $id_cases; ?>" method='post' style="width: 40%; float: left;">
      <fieldset>



              <label class="control-label" for="add_custom_column">Add new Column</label>

              <input id="add_custom_column" name="add_custom_column" placeholder="" value="" type="text" size="1" style="width: 30%;"/>


              <button id="save_custom_column" name="save_custom_column" value="Save" class="btn btn-default"><?php echo $_SESSION["language_json"]["Button_Save"]; ?></button>



</fieldset>
</form>

<form class="form-horizontal aapvl-form" action="../remove_custom_column_for_judgement/<?php echo $id_cases; ?>" method='post' style="width: 30%; float: left;">
    <fieldset>

                  <label class="control-label" for="del_custom_column">Remove custom Column</label>


                  <select name="del_custom_column">

                    <?php
                    foreach($custom_columns AS $value) {
                      echo '<option value="'.$value.'">'.$value.'</option>';

                    }

                    ?>

                  </select>


                  <button id="submit_custom_column" name="submit_custom_column" value="Save" class="btn btn-default"><?php echo $_SESSION["language_json"]["Button_Delete"]; ?></button>


                </fieldset>
                </form>

  <div style="float: left; margin-bottom: 20px;">

  <p>Bundesland: <input type="text" class="bundesland" id="bundesland"></p>

   <span id="shop"></span>
  <span id="lm-shop"></span>

</div>


<div style="width: 100%; margin-bottom: 20px; float: left;">

<form class="form-horizontal aapvl-form" onsubmit="return redirect_export(<?php echo $id_cases; ?>)">
    <fieldset>

  <button id="export_results" name="export_results" value="Save" class="btn btn-default">Ergebnisse Exportieren</button>

</fieldset>
</form>

</div>

    <div id='aapvl_table' class='container-fluid' align='center'><!-- start div for site content end div tag is in footer template -->

            <table id='aapvl_table_judgement_cases01' class='display' width="3000">

        <!-- Research Table Columns for header (static) -->

        <!-- To-Do: Dynamic Language for Colums -->
        <!-- To-Do: turn table into grid -->
        <thead>
            <tr id='aapvl_table_header'>

                <th>Id</th>
                <th>Datum</th>
                <th>Domain</th>
                <th>Unterseiten</th>
                <th>Nummer</th>
                <th>BioC</th>
                <th>Anbieter</th>
                <th>Straße</th>
                <th>PLZ</th>
                <th>Ort</th>
                <th>Bundesland</th>
                <th>Kreis</th>
                <th>Bearbeiter</th>
                <th>Shop</th>
                <th>LM-Shop</th>
                <th>Verdacht</th>
                <th>Rückmeldungen</th>
                <th>Kommentar</th>
                <th>Bewertungsdatum</th>
<?php

foreach($custom_columns AS $value) {

  echo "<th>".$value."</th>";
}

?>

            </tr>
        </thead>

        </table>
    </div>

</div>

<script src="<?php echo $script; ?>"></script>
