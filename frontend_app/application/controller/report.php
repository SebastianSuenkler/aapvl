<?php

/* Controller for Report Generator */

class Report extends Controller {

  public function convert_controller() {

    $cases = $this->getAllCases();

    foreach($cases AS $case) {

      $fk_cases = $case->pk_cases;
      $type_cases = $case->type_cases;

      $this->convert($fk_cases, $type_cases);

    }

  }

	public function convert($fk_cases, $type_cases) {

		$results = $this->results = $this->reports_model->ReadResults($fk_cases);

    $language = "Deutsch";

		$config_json = process_json(open_external_file_to_read("/user/datatables/".$language."type_cases_".$type_cases.".json"));

    $columns = $config_json["Columns"];

    $table_name = "results_".$fk_cases;

    $responsibility = $this->cases_model->GetResponsibility($fk_cases);
    $responsibility = $responsibility->symbol_users;

    $this->reports_model->CreateCasesTable($table_name, $columns);

    $this->reports_model->ConvertResults($fk_cases, $type_cases, $results, $table_name, $responsibility);



	}


  public function export($table_id) {

    $table_name = "results_".$table_id;
    $file_name = "results/".$table_name.".csv";
    $this->download_link = URL.$file_name;

    $optional_views = array('application/views/export/index.php');

    $this->loadViews($optional_views, false);





  }


}
