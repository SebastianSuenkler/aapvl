<?php

/* Model with all sql statements for the app from the table users */

class ResourcesModel extends Model {

  public function GetLatestResourcebyCase($fk_cases, $calculated_score, $latest_research, $time_score) {

    $sql = "SELECT `fk_cases`, `date_resources` FROM `resources` WHERE `fk_cases` = :fk_cases ORDER BY `date_resources` DESC LIMIT 1";
    $query = $this->db->prepare($sql);
    $query->execute(array(':fk_cases' => $fk_cases));
    $date = $query->fetch();
    $date = $date->date_resources;

    if(!empty($date)) {
    $this->UpdateLatestResearchforCase($fk_cases, $date, $calculated_score, $latest_research, $time_score);
    }

  }

  private function UpdateLatestResearchforCase($fk_cases, $date, $calculated_score, $latest_research, $time_score) {

    $cur_date = date("Y-m-d");

    $d1 = new DateTime($date);
    $d2 = new DateTime($cur_date);

    $diff_cur_resources_date = $d1->diff($d2);

    if($diff_cur_resources_date->y > 3) {

      $diff_time_score = 2;

    }

    if($diff_cur_resources_date->y > 1 AND $diff_cur_resources_date->y < 3) {

      $diff_time_score = 1;

    }

    if($diff_time_score > $time_score) {

      $calculated_score = $calculated_score + $time_score;
      $calculated_score = $calculated_score + $diff_time_score;
      $time_score = $diff_time_score;

    }


    $sql = "UPDATE `cases` SET latest_research=:latest_research, time_score=:time_score, calculated_score=:calculated_score WHERE pk_cases=:pk_cases";
    $query = $this->db->prepare($sql);
    $query->execute(array(':calculated_score' => $calculated_score, ':time_score' => $time_score, ':latest_research' => $date, ':pk_cases' => $fk_cases));



  }


    public function deleteResourcesbyCase($fk_cases) {
        rrmdir('resources/' . $fk_cases);
        $sql = "DELETE FROM `resources` WHERE fk_cases=:fk_cases";
        $query = $this->db->prepare($sql);
        $query->execute(array(':fk_cases' => $fk_cases));
    }

    public function selectParentsByCase($fk_cases) {
        $current_date = date("Y-m-d", strtotime("+1 days"));
        $sql = "SELECT `pk_resources` FROM `resources` WHERE `fk_cases` = :fk_cases AND `date_resources` < :date_resources AND `parent_resources` = 0";
        $query = $this->db->prepare($sql);
        $query->execute(array(':fk_cases' => $fk_cases, ':date_resources' => $current_date));
        return $query->fetchAll();
    }

    public function selectResourcesByParent($parent_resources) {

        $sql = "SELECT `pk_resources`, `parent_resources`, `url_resources`, `html_resources`, `screenshot_resources`, `crawler_progress` FROM `resources` WHERE `parent_resources` = :parent_resources OR `pk_resources` = :parent_resources";
        $query = $this->db->prepare($sql);
        $query->execute(array(':parent_resources' => $parent_resources));
        return $query->fetchAll();
    }

// select all users saved in database
    public function saveURLstoDB($fk_cases, $date, $urls, $screenshots, $modules) {


        $blacklist_urls = process_json(open_external_file_to_read("/user/lists/url_blacklist.json"));


        foreach ($urls AS $url) {

            $save_url_to_db = true;

            foreach ($blacklist_urls["URLs"] AS $blacklist_url) {
                if (find_partial_url($blacklist_url, $url)) {
                    $save_url_to_db = false;
                }
            }

            if ($save_url_to_db) {

                $url_info = get_url_info($url);
                $url = $url_info["Normalized_URL"];
                $parent = $url_info["Parent"];
                $protocol = $url_info["Protocol"];
                $ip = $url_info["IP"];



                $parent_output = array();
                $result_output = array();
                $crawler_output = array();



// check if parent already exists

                $sql = 'SELECT `pk_resources` FROM `resources` WHERE `fk_cases` = :fk_cases AND `date_resources` = :date_resources AND `url_resources` = :url_resources';
                $query = $this->db->prepare($sql);
                $query->execute(array(':fk_cases' => $fk_cases, ':date_resources' => $date, ':url_resources' => $parent));
                $result = $query->fetch();

                if (empty($result)) {
// insert parent
                    $sql = "INSERT INTO `resources` (parent_resources, url_resources, protocol_resources, date_resources, ip_resources, fk_cases) VALUES (:parent_resources, :url_resources, :protocol_resources, :date_resources, :ip_resources, :fk_cases)";
                    $query = $this->db->prepare($sql);
                    $query->execute(array(':parent_resources' => 0, ':url_resources' => $parent, ':protocol_resources' => $protocol, ':date_resources' => $date, ':ip_resources' => $ip, ':fk_cases' => $fk_cases));
                    $parent_resources = $this->db->lastInsertId();

                    $parent_output = $this->rasterizeURL($parent_resources, $protocol, $parent, $parent, $date, $screenshots, $fk_cases);

                    if ($parent_output["success"] != -1) {
                        $resources_progress = 1;

                        $this->generate_analysis_job($modules["Contact"], $parent_output["html_db"], $parent_output["screenshot_db"], $parent_resources, NULL, $parent, $fk_cases);
                    } else {
                        $resources_progress = -1;
                    }

                    $sql = "UPDATE `resources` SET html_resources=:html_resources, screenshot_resources=:screenshot_resources, resources_progress=:resources_progress, screenshot_date_resources=:screenshot_date_resources WHERE pk_resources=:pk_resources";
                    $query = $this->db->prepare($sql);
                    $query->execute(array(':html_resources' => $parent_output["html_db"], ':screenshot_resources' => $parent_output["screenshot_db"], ':pk_resources' => $parent_resources, ':resources_progress' => $resources_progress, ':screenshot_date_resources' => date("Y-m-d")));
                } else {
                    $parent_resources = $result->pk_resources;
                }


                if ($url != $parent) {
// insert search result

                    $sql = "INSERT INTO `resources` (parent_resources, url_resources, protocol_resources, date_resources, ip_resources, fk_cases) VALUES (:parent_resources, :url_resources, :protocol_resources, :date_resources, :ip_resources, :fk_cases)";
                    $query = $this->db->prepare($sql);
                    $query->execute(array(':parent_resources' => $parent_resources, ':url_resources' => $url, ':protocol_resources' => $protocol, ':date_resources' => $date, ':ip_resources' => $ip, ':fk_cases' => $fk_cases));
                    $pk_resources = $this->db->lastInsertId();

                    $result_output = $this->rasterizeURL($pk_resources, $protocol, $url, $parent, $date, $screenshots, $fk_cases);

                    if ($result_output["success"] != -1) {
                        $resources_progress = 1;

                        $this->generate_analysis_job($modules["Analysis"], $result_output["html_db"],  $result_output["screenshot_db"], $pk_resources, $parent_resources, $url, $fk_cases);
                    } else {
                        $resources_progress = -1;
                    }

                    $sql = "UPDATE `resources` SET html_resources=:html_resources, screenshot_resources=:screenshot_resources, resources_progress=:resources_progress, screenshot_date_resources=:screenshot_date_resources WHERE pk_resources=:pk_resources";
                    $query = $this->db->prepare($sql);
                    $query->execute(array(':html_resources' => $result_output["html_db"], ':screenshot_resources' => $result_output["screenshot_db"], ':pk_resources' => $pk_resources, ':resources_progress' => $resources_progress, ':screenshot_date_resources' => date("Y-m-d")));
                }


// grab impressumsseite

                $sql = 'SELECT `contact_crawler_progress` FROM `resources` WHERE `fk_cases` = :fk_cases AND  `parent_resources` = :parent_resources AND contact_crawler_progress IS NOT NULL';
                $query = $this->db->prepare($sql);
                $query->execute(array(':fk_cases' => $fk_cases, ':parent_resources' => $parent_resources));
                $contact_crawler = $query->fetch();




                if (empty($contact_crawler)) {


                    $urls = "";

                    $filtered_url = "";

                    $config_crawler = process_json(open_external_file_to_read("/user/crawler/1.json"));

                    $url_filter = $config_crawler["url_filter"];


                    if (!empty($result_output) AND $result_output["success"] != -1) {
                        $result_content = file_get_contents($result_output["file_html"]);
                        $urls = extract_urls_to_json($result_content, $parent);
                        $urls = process_json($urls);
                        $filtered_url = filter_urls($urls, $url_filter);
                    }



                    if (empty($filtered_url)) {

                        $result_content = file_get_contents($parent_output["file_html"]);
                        $urls = extract_urls_to_json($result_content, $parent);
                        $urls = process_json($urls);
                        $filtered_url = filter_urls($urls, $url_filter);

                        if (!empty($filtered_url)) {

                            if ((preg_match('/' . "http:" . '/is', $filtered_url) == 1) OR ( preg_match('/' . "https:" . '/is', $filtered_url) == 1)) {

                            } else {
                                $filtered_url = $protocol . $filtered_url;
                            }
                        }
                    }




                    if (!empty($filtered_url)) {

                        $filtered_url = implode('/', array_unique(explode('/', $filtered_url)));

                        $filtered_url_info = get_url_info($filtered_url);



                        $sql = "INSERT INTO `resources` (parent_resources, url_resources, protocol_resources, date_resources, ip_resources, fk_cases, contact_crawler_progress) VALUES (:parent_resources, :url_resources, :protocol_resources, :date_resources, :ip_resources, :fk_cases, :contact_crawler_progress)";
                        $query = $this->db->prepare($sql);
                        $query->execute(array(':parent_resources' => $parent_resources, ':url_resources' => $filtered_url_info["Normalized_URL"], ':protocol_resources' => $filtered_url_info["Protocol"], ':date_resources' => $date, ':ip_resources' => $filtered_url_info["IP"], ':fk_cases' => $fk_cases, 'contact_crawler_progress' => 1));
                        $pk_resources = $this->db->lastInsertId();


                        $crawled_output = $this->rasterizeURL($pk_resources, $filtered_url_info["Protocol"], $filtered_url_info["Normalized_URL"], $parent, $date, $screenshots, $fk_cases);

                        if ($crawled_output["success"] != -1) {
                            $resources_progress = 1;

                            $this->generate_analysis_job($modules["Contact"], $crawled_output["html_db"], $crawled_output["screenshot_db"], $pk_resources, $parent_resources, $filtered_url_info["Normalized_URL"], $fk_cases);
                        } else {
                            $resources_progress = -1;
                        }

                        $sql = "UPDATE `resources` SET html_resources=:html_resources, screenshot_resources=:screenshot_resources, resources_progress=:resources_progress, screenshot_date_resources=:screenshot_date_resources WHERE pk_resources=:pk_resources";
                        $query = $this->db->prepare($sql);
                        $query->execute(array(':html_resources' => $crawled_output["html_db"], ':screenshot_resources' => $crawled_output["screenshot_db"], ':pk_resources' => $pk_resources, ':resources_progress' => $resources_progress, ':screenshot_date_resources' => date("Y-m-d")));
                    }
                }
            }
        }
    }

    public function rasterizeURLs($fk_cases, $screenshots, $modules) {

// dont save locals before scraping and url lists are done
        /*
          $date = date("Y-m-d", strtotime("+1 days"));
          $sql = 'SELECT `pk_scrapers` FROM `jobs_scrapers` WHERE `fk_cases` = :fk_cases AND `status_scrapers` != 0 AND `date_scrapers` < :date_scrapers LIMIT 1';
          $query = $this->db->prepare($sql);
          $query->execute(array(':fk_cases' => $fk_cases, ':date_scrapers' => $date));
          $status_scrapers = $query->fetch();

          $sql = 'SELECT `pk_jobs_urls` FROM `jobs_urls` WHERE `fk_cases` = :fk_cases AND `status_jobs_urls` != 0 AND `date_jobs_urls` < :date_jobs_urls LIMIT 1';
          $query = $this->db->prepare($sql);
          $query->execute(array(':fk_cases' => $fk_cases, ':date_jobs_urls' => $date));
          $status_urls = $query->fetch();
         */



        $date = date("Y-m-d");

        $sql = 'SELECT `pk_resources`, `parent_resources`, `protocol_resources`, `url_resources`, `html_resources`, `screenshot_resources`, `date_resources` FROM `resources` WHERE `fk_cases` = :fk_cases AND `resources_progress` IS NULL';
        $query = $this->db->prepare($sql);
        $query->execute(array(':fk_cases' => $fk_cases));
        $resources = $query->fetchAll();



        if (!empty($resources)) {

// rasterize all other urls
            foreach ($resources AS $resource) {

                $url = $resource->url_resources;
                $url_info = get_url_info($url);
                $parent = $url_info["Parent"];
                $protocol = $url_info["Protocol"];
                $ip = $url_info["IP"];
                $date = $resource->date_resources;
                $current_date = date("Y-m-d");


                $result_output = array();


                $pk_resources = $resource->pk_resources;


                $sql = "UPDATE `resources` SET resources_progress=:resources_progress WHERE pk_resources=:pk_resources";
                $query = $this->db->prepare($sql);
                $query->execute(array(':pk_resources' => $pk_resources, ':resources_progress' => 0));

                $result_output = $this->rasterizeURL($pk_resources, $protocol, $url, $parent, $date, $screenshots, $fk_cases);

                if ($result_output["success"] != -1) {
                    $resources_progress = 1;
                    $this->generate_analysis_job($modules["Analysis"], $result_output["html_db"], $result_output["screenshot_db"], $resource->pk_resources, $resource->parent_resources, $url, $fk_cases);
                } else {
                    $resources_progress = -1;
                }

                $sql = "UPDATE `resources` SET html_resources=:html_resources, screenshot_resources=:screenshot_resources, resources_progress=:resources_progress, screenshot_date_resources=:screenshot_date_resources WHERE pk_resources=:pk_resources";
                $query = $this->db->prepare($sql);
                $query->execute(array(':html_resources' => $result_output["html_db"], ':screenshot_resources' => $result_output["screenshot_db"], ':pk_resources' => $pk_resources, ':resources_progress' => $resources_progress, ':screenshot_date_resources' => date("Y-m-d")));
            }
        }
    }

    private function rasterizeURL($pk_resources, $protocol, $url, $parent, $date, $screenshots, $fk_cases) {

        if ($pk_resources != 0) {
            $result = array();
            $file_html_db = RESOURCE_URL . $fk_cases . "/html/" . $parent . "_" . $date . "_" . $pk_resources . ".txt";
            $file_screenshot_db = RESOURCE_URL . $fk_cases . "/screenshots/" . $parent . "_" . $date . "_" . $pk_resources . ".jpg";

            $file_html = str_replace(URL, "", $file_html_db);

            if ($screenshots == 1) {
                $file_screenshot = str_replace(URL, "", $file_screenshot_db);

            } else {
                $file_screenshot = "";
                $file_screenshot_db = "";
            }

            $output = "";

            if (file_exists($file_html)) {

                $output = 1;
            } else {

                $output = rasterize($protocol . $url, $file_screenshot, $file_html);
            }

            if ($output == -1) {
                $output = rasterize($protocol . "www." . $url, $file_screenshot, $file_html);
            }

            if ($output == -1) {
                $output = rasterize($protocol . $url . "/", $file_screenshot, $file_html);
            }

            if ($output == -1) {
                $output = rasterize($protocol . "www." . $url . "/", $file_screenshot, $file_html);
            }

            if ($output != -1) {
                $result = array("success" => 1, "html_db" => $file_html_db, "screenshot_db" => $file_screenshot_db, "file_html" => $file_html);
            } else {
                $result = array("success" => -1, "html_db" => -1, "screenshot_db" => -1);
            }

            return $result;
        }
    }

    private function generate_analysis_job($modules, $path_resources, $screenshot_resources, $fk_resources, $parent_resources, $url_resources, $fk_cases) {
        if ($fk_resources != 0) {
            $sql = "INSERT INTO `jobs_to_assign` (status_jobs, modules_jobs, path_resources, screenshot_resources, fk_resources, parent_resources, url_resources, fk_cases) VALUES (:status_jobs, :modules_jobs, :path_resources, :screenshot_resources, :fk_resources, :parent_resources, :url_resources, :fk_cases)";
            $query = $this->db->prepare($sql);
            $query->execute(array(':status_jobs' => 0, ':modules_jobs' => $modules, ':path_resources' => $path_resources, ':screenshot_resources' => $screenshot_resources, ':fk_resources' => $fk_resources, ':parent_resources' => $parent_resources, ':url_resources' => $url_resources, ':fk_cases' => $fk_cases));
        }
    }

    public function deleteAssignedJobsbyCase($fk_cases) {
        $sql = "DELETE FROM `jobs_to_assign` WHERE fk_cases=:fk_cases";
        $query = $this->db->prepare($sql);
        $query->execute(array(':fk_cases' => $fk_cases));
    }

    public function getResultsforJudgement($table) {
        $sql = 'SELECT * FROM `' . $table . '`';
        $query = $this->db->prepare($sql);
        $query->execute();
        $results = $query->fetchAll();
        return $results;
    }

    public function getColumnsFromTable($table) {
        $sql = 'SHOW FULL COLUMNS FROM `' . $table . '`';
        $query = $this->db->prepare($sql);
        $query->execute();
        $results = $query->fetchAll();
        return $results;
    }

}
