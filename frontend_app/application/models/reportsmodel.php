<?php


/* Model with all sql statements for the app for all kind of cases */

class ReportsModel extends Model {

    // select all users saved in database
    public function getResultsByCase($fk_cases, $type_cases) {
        $sql = "SELECT `pk_results`, `fk_resources`, r.`parent_resources`, `manual_results`, r.`path_resources`, r.`url_resources`, `contact_crawler_progress` FROM `results` AS `r`, `resources` AS `re` WHERE `pk_resources` = `fk_resources` AND r.fk_cases = :fk_cases AND r.`parent_resources` IS NULL";
        $query = $this->db->prepare($sql);
        $query->execute(array(':fk_cases' => $fk_cases));
        $parents = $query->fetchAll();

        $sql = "SELECT `pk_results`, `fk_resources`, r.`parent_resources`, `manual_results`, r.`path_resources`, r.`url_resources`, `contact_crawler_progress` FROM `results` AS `r`, `resources` AS `re` WHERE `pk_resources` = `fk_resources` AND r.fk_cases = :fk_cases  AND r.`parent_resources` IS NOT NULL AND `contact_crawler_progress` IS NULL";
        $query = $this->db->prepare($sql);
        $query->execute(array(':fk_cases' => $fk_cases));
        $sub_pages = $query->fetchAll();

        $sql = "SELECT `pk_results`, `fk_resources`, r.`parent_resources`, `manual_results`, r.`path_resources`, r.`url_resources`, `contact_crawler_progress` FROM `results` AS `r`, `resources` AS `re` WHERE `pk_resources` = `fk_resources` AND r.fk_cases = :fk_cases  AND r.`parent_resources` IS NOT NULL AND `contact_crawler_progress` = 1";
        $query = $this->db->prepare($sql);
        $query->execute(array(':fk_cases' => $fk_cases));
        $contact_pages = $query->fetchAll();


        $results = $this->prepareRoughResults($parents, $sub_pages, $contact_pages, $type_cases);
        return $results;
    }

    private function prepareRoughResults($parents, $sub_pages, $contact_pages, $type_cases) {


        $main_columns = array("fk_resources", "url", "path");

        if ($type_cases == 1) {
            // todo: change to language settings
            $result_columns = array("3", "2", "Kreis", "Land", "Ort", "PLZ", "Bundesland", "Unternehmen", "Strasse", "shop_one", "shop_sites", "shop_avg", "lm_sites", "lm_avg", "lm_one", "total_sites");
        }

        $columns = array_merge($main_columns, $result_columns);

        $csv_header = "";

        foreach ($columns AS $column) {
            $csv_header = $csv_header . $column . "\t";
        }

        $csv_header = $csv_header;

        $parents_counter = count($parents);
        $sub_pages_counter = count($sub_pages);
        $contact_pages_counter = count($contact_pages);

        $handle = fopen("2_run.csv", "a+");

        fwrite($handle, $csv_header);



        for ($x = 0; $x < $parents_counter; $x++) {


            $fk_resources = $parents[$x]->fk_resources;
            $url = $parents[$x]->url_resources;
            $path = $parents[$x]->path_resources;

            $json_results = json_decode($parents[$x]->manual_results);
            $add_results = $json_results->add;
            $modules_results = $json_results->modules;
            $contact_results = array();

            foreach ($modules_results AS $key => $value) {
                $contact_results[$key] = $value;
            }



            $csv_results = "\n\n" . $fk_resources . "\t" . $url . "\t" . $path . "\t";

            foreach ($result_columns AS $result_column) {

                if (!empty($contact_results[1][0][0]->$result_column)) {
                    $csv_results = $csv_results . $contact_results[1][0][0]->$result_column . "\t";
                } else if (!empty($modules_results->$result_column)) {
                    $csv_results = $csv_results . $modules_results->$result_column . "\t";
                } else if (!empty($add_results->$result_column)) {
                    $csv_results = $csv_results . $add_results->$result_column . "\t";
                } else {
                    $csv_results = $csv_results . "\t";
                }
            }


            fwrite($handle, $csv_results);




            for ($a = 0; $a < $sub_pages_counter; $a++) {
                if ($sub_pages[$a]->parent_resources == $parents[$x]->fk_resources) {


                    $fk_resources = $sub_pages[$a]->fk_resources;
                    $url = $sub_pages[$a]->url_resources;
                    $path = $sub_pages[$a]->path_resources;

                    $json_results = json_decode($sub_pages[$a]->manual_results);
                    $modules_results = $json_results->modules;
                    $contact_results = array();

                    foreach ($modules_results AS $key => $value) {
                        $contact_results[$key] = $value;
                    }


                    $csv_results = "\n" . $fk_resources . "\t" . $url . "\t" . $path . "\t";

                    foreach ($result_columns AS $result_column) {

                        if (!empty($contact_results[1][0][0]->$result_column)) {
                            $csv_results = $csv_results . $contact_results[1][0][0]->$result_column . "\t";
                        } else if (!empty($modules_results->$result_column)) {
                            $csv_results = $csv_results . $modules_results->$result_column . "\t";
                        } else {
                            $csv_results = $csv_results . "\t";
                        }
                    }


                    fwrite($handle, $csv_results);
                }
            }

            for ($b = 0; $b < $contact_pages_counter; $b++) {

                if ($contact_pages[$b]->parent_resources == $parents[$x]->fk_resources) {
                    $fk_resources = $contact_pages[$b]->fk_resources;
                    $url = $contact_pages[$b]->url_resources;
                    $path = $contact_pages[$b]->path_resources;

                    $json_results = json_decode($contact_pages[$b]->manual_results);
                    $modules_results = $json_results->modules;
                    $contact_results = array();

                    foreach ($modules_results AS $key => $value) {
                        $contact_results[$key] = $value;
                    }


                    $csv_results = "\n" . $fk_resources . "\t" . $url . "\t" . $path . "\t";

                    foreach ($result_columns AS $result_column) {

                        if (!empty($contact_results[1][0][0]->$result_column)) {
                            $csv_results = $csv_results . $contact_results[1][0][0]->$result_column . "\t";
                        } else if (!empty($modules_results->$result_column)) {
                            $csv_results = $csv_results . $modules_results->$result_column . "\t";
                        } else {
                            $csv_results = $csv_results . "\t";
                        }
                    }



                    fwrite($handle, $csv_results);
                }
            }
        }

        fclose($handle);
    }

    private function prepareRoughResults2($results) {


        // set all parents first

        foreach ($results AS $result) {
            if ($result->parent_resources == NULL) {
                $parents[] = $result->fk_resources;
                $report_results[$result->fk_resources][] = array("fk_resources" => $result->fk_resources, "url" => $result->url_resources, "path" => $result->path_resources, "results" => process_json($result->manual_results));
            }
        }




        // set all sub pages

        foreach ($parents AS $parent) {




            foreach ($results AS $result) {
                if ($result->parent_resources == $parent AND $result->contact_crawler_progress != 1) {
                    $report_results[$parent][] = array("fk_resources" => $result->fk_resources, "url" => $result->url_resources, "path" => $result->path_resources, "results" => process_json($result->manual_results));
                }
            }
        }





    }

    private function prepareResults($results) {

        // read all parents first

        foreach ($results AS $result) {
            if ($result->parent_resources == NULL) {
                $report_results[$result->fk_resources] = array("fk_resources" => $result->fk_resources, "url" => $result->url_resources, "path" => $result->path_resources, "results" => $result->manual_results);
            }
        }

        foreach ($report_results AS $report_parent) {
            $parent = $report_parent["fk_resources"];

            foreach ($results AS $result) {
                if ($result->parent_resources == $parent) {
                    $report_results[$parent][] = array("url" => $result->url_resources);
                }
            }
        }

        #var_dump($report_results);
    }

public function CreateCasesTable($table_name, $columns) {

// Build SQL Statement



$sql = "create table if not exists `$table_name` (`pk_results` int(11) unsigned NOT NULL auto_increment, `$columns[0]` varchar(255), `$columns[1]` varchar(255), ";


for($i = 2; $i < count($columns); $i++) {

  $sql.=" `$columns[$i]` text NULL default '',";

}





$sql.=" primary key (`pk_results`), UNIQUE KEY `unique_key` (`$columns[0]`, `$columns[1]`)) ENGINE=InnoDB  DEFAULT CHARSET=utf8";

$query = $this->db->prepare($sql);
$query->execute();

}

public function ReadResults($fk_cases) {
	$sql = "SELECT `pk_results`, `analysis_results`, `manual_results`, `date_resources`, `validation_results`, r.`path_resources`, r.`screenshot_resources`, `fk_resources`, r.`parent_resources`, r.`url_resources` FROM `results` AS r, `resources` AS res WHERE r.`fk_cases` = $fk_cases AND `fk_resources` = `pk_resources` ORDER BY `url_resources`";

        $query = $this->db->prepare($sql);
        $query->execute();
        $results = $query->fetchAll();

		return $results;
	}

	public function ConvertResults($fk_cases, $type_cases, $results, $table_name, $responsibility) {

	$sorted_results = array();

		foreach($results AS $result) {


		if(empty($result->parent_resources)) {
			$parent = $result->fk_resources;
			$parent_url = $result->url_resources;
			$sorted_results[$parent_url]["Parent"] = $parent_url;
      $sorted_results[$parent_url]["Date"] =  $result->date_resources;
			$sorted_results[$parent_url]["Parent_Path"] = $result->path_resources;
			$sorted_results[$parent_url]["Parent_Screenshot"] = $result->screenshot_resources;

			$analysis_result = process_json($result->analysis_results);


			if($analysis_result["add"]["shop_avg"] > 80) {

				$sorted_results[$parent_url]["Shop"] = 1;

			}

			else if($analysis_result["add"]["shop_avg"] > 50 AND $analysis_result["add"]["shop_avg"] < 80) {

				$sorted_results[$parent_url]["Shop"] = -1;

			}

			else {

				$sorted_results[$parent_url]["Shop"] = 0;
			}

			if($analysis_result["add"]["lm_avg"] > 80) {

				$sorted_results[$parent_url]["Food_Shop"] = 1;

			}

			else if($analysis_result["add"]["lm_avg"] > 50 AND $analysis_result["add"]["lm_avg"] < 80) {

				$sorted_results[$parent_url]["Food_Shop"] = -1;

			}

			else {

				$sorted_results[$parent_url]["Food_Shop"] = 0;
			}



			$parent_adress = array("Unternehmen" => $analysis_result["modules"][1][0][0]["Unternehmen"], "Strasse" => $analysis_result["modules"][1][0][0]["Strasse"], "PLZ" => $analysis_result["modules"][1][0][0]["PLZ"], "Ort" => $analysis_result["modules"][1][0][0]["Ort"], "Bundesland" => $analysis_result["modules"][1][0][0]["Bundesland"], "Kreis" => $analysis_result["modules"][1][0][0]["Kreis"]);

      if($type_cases == 4) {

        $legal_numbers_parent = array();

        if(empty($legal_numbers_parent)) {
        $legal_numbers_parent = $analysis_result["add"]["oeko"]["legal"];
        }

      }



		}

		else if($result->parent_resources == $parent) {

			$analysis_results = process_json($result->analysis_results);


			if (count($analysis_results["modules"][1]) > 0) {



				for($i = 0; $i < count($analysis_results["modules"][1]); $i++) {


				$possible_adresses[$parent_url][$i] = array("Unternehmen" => $analysis_results["modules"][1][$i][0]["Unternehmen"], "Strasse" => $analysis_results["modules"][1][$i][0]["Strasse"], "PLZ" => $analysis_results["modules"][1][$i][0]["PLZ"], "Ort" => $analysis_results["modules"][1][$i][0]["Ort"], "Bundesland" => $analysis_results["modules"][1][$i][0]["Bundesland"], "Kreis" => $analysis_results["modules"][1][$i][0]["Kreis"]);


				}



			}


			$sorted_results[$parent_url]["URLs"][] = $result->url_resources;
			$sorted_results[$parent_url]["URLs_Path"][] = $result->path_resources;
			$sorted_results[$parent_url]["URLs_Screenshot"][] = $result->screenshot_resources;
      $sorted_results[$parent_url]["Adresses"] = $possible_adresses[$parent_url];
      $sorted_results[$parent_url]["BioC"] = $analysis_result["add"]["bioc"][0]["periods"][0][1];
      $sorted_results[$parent_url]["Ads"] = $analysis_result["add"]["oeko"]["ads"];
      $sorted_results[$parent_url]["Fake"] = $analysis_result["add"]["oeko"]["fake"];


                        if(!empty($parent_adress["PLZ"])) {
                            $sorted_results[$parent_url]["Adresses"][] = $parent_adress;
                        }


          if($type_cases == 4) {
                            $legal_numbers = array();

                            $legal_numbers =  $analysis_results["modules"]["10"]["numbers"];



                            if(empty($legal_numbers)) {
                            $legal_numbers = $analysis_results["modules"]["6"]["legal"];
                            }




                            if(empty($legal_numbers)){
                              $legal_numbers = $legal_numbers_parent;
                        }




                        $sorted_results[$parent_url]["Legal"] = $legal_numbers;



      }



      }


		}


// Create Values for Web pages

// GET current number of columns

$sql = "SELECT count(*) AS count_columns FROM information_schema.columns WHERE table_schema = 'aapvl' AND table_name = '$table_name'";

$query = $this->db->prepare($sql);
$query->execute();
$count_columns = $query->fetchAll();

$count_columns = $count_columns[0]->count_columns;

foreach($sorted_results AS $result) {

$date = "";

$date = $result["Date"];

$parent = "";
$parent_path = "";
$parent_screenshot = "";


$parent = $result["Parent"];
$parent_path = $result["Parent_Path"];
$parent_screenshot = $result["Parent_Screenshot"];

$parent_combined = "";



if(!empty($parent_screenshot)) {

$parent_url = $parent_screenshot;

}

else if(!empty($parent_path)) {

$parent_url = $parent_path;

}

$parent_combined = '<a href="http://'.$parent.'" target="_blank">'.$parent.'</a><br/><a href="'.$parent_url.'" target="_blank">'.$parent.' (Kopie)</a>';

$urls = "";

for($i = 0; $i < count($result["URLs"]); $i++) {

$result_url = "";

if(!empty($result["URLs_Screenshot"][$i])) {

$result_url = $result["URLs_Screenshot"][$i];

}

else if(!empty($result["URLs_Path"][$i])) {

$result_url = $result["URLs_Path"][$i];

}

$urls.= '<a href="http://'.$result["URLs"][$i].'" target="_blank">'.$result["URLs"][$i].'</a><br/><a href="'.$result_url.'" target="_blank">'.$result["URLs"][$i].' (Kopie)</a><br/><br/>';


}

$shop = "";
$food = "";

$shop = $result["Shop"];;
$food = $result["Food_Shop"];

$company = "";
$street = "";
$zipcode = "";
$city = "";
$district = "";
$state = "";


$company = replace_utf8($result["Adresses"][0]["Unternehmen"]);
$street = replace_utf8($result["Adresses"][0]["Strasse"]);
$zipcode = replace_utf8($result["Adresses"][0]["PLZ"]);
$city = replace_utf8($result["Adresses"][0]["Ort"]);
$district = replace_utf8($result["Adresses"][0]["Kreis"]);
$state = replace_utf8($result["Adresses"][0]["Bundesland"]);



$result_array = array();

$result_array = array("NULL", "$date", "$parent", "$parent_combined", "$urls", "$shop" ,"$food", "$company", "$street", "$zipcode", "$city", "$district", "$state", "$responsibility");

if($type_cases == 4) {

  $ads = $result["Ads"];
  $adwords = "";

  foreach($ads AS $value) {

    $adwords = $adwords.replace_utf8($value).",";
  }


  $legal = $result["Legal"];
  $legal_numbers = "";

  foreach($legal AS $value) {
    $legal_numbers = $legal_numbers.replace_utf8($value).",";
  }

  $fake = $result["Fake"];
  $fake_numbers = "";

  foreach($fake AS $value) {
    $fake_numbers = $fake_numbers.replace_utf8($value).",";
  }

  $legal_numbers = substr($legal_numbers, 0, -1);
  $fake_numbers = substr($fake_numbers, 0, -1);
  $adwords = substr($adwords, 0, -1);

  $bioc = $result["BioC"];

  $bioc_valid = "";

if(!empty($bioc)) {

  $bioc_date = $bioc["year"]."-".$bioc["month"]."-".$bioc["day"];
  $cur_date = date("d-m-Y");

  if ($cur_date > $bioc_date) {
          $bioc_valid = "Expired Eco-Label";
      }
  else {
      $bioc_valid = "Valid Eco-Label";
  }

}

  $result_array = array("NULL", "$date", "$parent", "$parent_combined", "$urls", "$legal_numbers", "$bioc_valid", "$adwords", "$shop" ,"$food", "$company", "$street", "$zipcode", "$city", "$district", "$state", "$responsibility", "$fake_numbers");

}


if($count_columns != count($result_array)) {
for($i = count($result_array); $i < $count_columns; $i++)  {
  $result_array[$i] = "''";
}
}



$prepare_insert = "";

foreach($result_array AS $value) {

$value = str_replace("'", "", $value);

$prepare_insert.="'".$value."',";
}

$prepare_insert = substr($prepare_insert, 0, -1);

$sql = "INSERT INTO `$table_name` VALUES ($prepare_insert)";

$query = $this->db->prepare($sql);
$query->execute();


}

	}

  public function GetTableColumns($table_name) {

  $sql = "DESCRIBE ".$table_name;
  $query = $this->db->prepare($sql);
  $query->execute();
  $table_fields = $query->fetchAll(PDO::FETCH_COLUMN);


  return $table_fields;

}

public function AddNewColumn($table_name, $column){

 $sql = "ALTER TABLE `".$table_name."` ADD COLUMN `".$column."` TEXT";

  $query = $this->db->prepare($sql);
  $query->execute();

}

public function RemoveColumn($table_name, $column){

 $sql = "ALTER TABLE `".$table_name."` DROP COLUMN `".$column."`";

  $query = $this->db->prepare($sql);
  $query->execute();

}

public function ExportResults($table_id) {

  $table_name = "results_".$table_id;


  $file_name = "results/".$table_name.".csv";

  $sql = "SELECT * FROM `".$table_name."`";
  $query = $this->db->prepare($sql);
  $query->execute();
  $results = $query->fetchAll();

  for($i = (count($results) - 1); $i <= count($results); $i++) {

    foreach($results[$i] AS $keys => $value) {
      $columns[] = $keys;
    }

}

foreach($columns AS $column) {

  $csv_columns = $csv_columns.$column."\t";
}

$csv_columns = substr($csv_columns, 0, -1);

$csv_columns = $csv_columns."\n";

$fp = fopen($file_name, 'w+');
fwrite($fp, $csv_columns);


  for($i = 0; $i < count($results); $i++) {

    // Strip HTML Tags for Domains

$csv_result = "";

foreach($results[$i] AS $value) {

  $value = strip_tags($value, "<br>");
  $value = str_replace("<br/>", ";", $value);

$csv_result = $csv_result.$value."\t";

}

$csv_result = substr($csv_result, 0, -1);

$csv_result = $csv_result."\n";

fwrite($fp, $csv_result);



  }

fclose($fp);

return $table_id;

}

public function getResultTableByCase($pk_cases) {

$table_name = "results_".$pk_cases;

$sql = "SELECT * FROM `$table_name` LIMIT 10;";
$query = $this->db->prepare($sql);
$query->execute();
$result = $query->fetch();



if(!empty($result)) {

return true;

}

}

}
