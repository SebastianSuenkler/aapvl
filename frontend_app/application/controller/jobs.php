<?php

/* Controller Jobs */

class Jobs extends Controller {

    public $queries_to_scrape = null;
    public $scrapers_jobs = null;
    public $urls_to_save = null;
    public $urls_jobs = null;

    function __construct() {
        // open database connection by the configuration in /config/config.php
        parent::__construct();


        $this->scrapers_jobs = $this->jobs_model->getScrapersJobs();
        $this->urls_jobs = $this->jobs_model->getURLsJobs();
    }

    public function generate_jobs_scrapers($fk_cases) {

        // Wieder Fallbezogen machen

        $db_values = array();
        $scrapers = process_json(open_external_file_to_read("/user/scraper/scrapers_list.json"));
        $queries = $this->queries_model->getQueriesforScraping($fk_cases);

        foreach ($queries AS $query) {

            $pk_queries = $query->pk_queries;
            $fk_cases = $query->fk_cases;
            $query_queries = $query->query_queries;
            $date_queries = $query->date_queries;
            $interval_days_queries = $query->interval_days_queries;
            $interval_completion_queries = $query->interval_completion_queries;
            $intervals = calc_intervals($date_queries, $interval_days_queries, $interval_completion_queries);

            foreach ($scrapers["Scrapers"] AS $scraper) {

                $scraper_config = process_json(open_external_file_to_read("/user/scraper/" . $scraper . ".json"));
                $name_scrapers = $scraper_config["search engine"];
                $searchstring_scrapers = $scraper_config["searchstring"];
                $searchstring_scrapers = str_replace("[[term]]", generate_query_from_string($query_queries), $searchstring_scrapers); // umwandeln suchanfrage mit + zeichen
                $xpath_results_scrapers = $scraper_config["xpath_results"];
                $next_serp_scrapers = $scraper_config["next_serp"];
                $max_pages = $scraper_config["max-pages"];


                $part_before = $next_serp_scrapers["part_before"];
                $xpath = $next_serp_scrapers["xpath"];
                $part_after = $next_serp_scrapers["part_after"];

                $next_serp_scrapers = '{' . '"part_before"' . ':"' . $part_before . '",' . '"xpath"' . ':"' . $xpath . '",' . '"part_after"' . ':"' . $part_after . '"}';

                foreach ($intervals AS $interval) {
                    $date_scrapers = $interval;
                    $scraping_values = array('fk_cases' => $fk_cases, 'fk_queries' => $pk_queries, 'name_scrapers' => $name_scrapers, 'query_queries' => $query_queries, 'searchstring_scrapers' => $searchstring_scrapers, 'xpath_results_scrapers' => $xpath_results_scrapers, 'next_serp_scrapers' => $next_serp_scrapers, 'date_scrapers' => $date_scrapers, 'max_pages' => $max_pages);
                    $db_values[] = $scraping_values;
                }
            }
        }

        $this->jobs_model->setScrapersJobs($db_values);
    }

    public function generate_jobs_urls($pk_urls) {

        // Wieder Fallbezogen machen

        $db_values = array();
        $urls = $this->urls_model->getURLs($pk_urls);

        foreach ($urls AS $url) {
            $fk_cases = $url->fk_cases;
            $elements_urls_path = $url->elements_urls_path;
            $date_urls = $url->date_urls;
            $interval_days_urls = $url->interval_days_urls;
            $interval_completion_urls = $url->interval_completion_urls;
            $intervals = calc_intervals($date_urls, $interval_days_urls, $interval_completion_urls);

            foreach ($intervals AS $interval) {
                $date_urls = $interval;
                $urls_values = array('fk_cases' => $fk_cases, 'fk_urls' => $pk_urls, 'elements_urls_path' => $elements_urls_path, 'date_urls' => $date_urls);
                $db_values[] = $urls_values;
            }
        }

        $this->jobs_model->setURLsJobs($db_values);
    }

    public function urls() {


        foreach ($this->urls_jobs AS $url_job) {

            $pk_jobs_urls = $url_job->pk_jobs_urls;
            $fk_cases = $url_job->fk_cases;
            $elements_urls_path = $url_job->elements_urls_path;
            $date_jobs_urls = $url_job->date_jobs_urls;
            $this->jobs_model->updateUrlsJobStatus($pk_jobs_urls, 0);
            $this->process_urls($fk_cases, $date_jobs_urls, $elements_urls_path);
            $this->jobs_model->updateUrlsJobStatus($pk_jobs_urls, 1);
        }
    }

    public function scraping() {

        foreach ($this->scrapers_jobs AS $scraper_job) {
            $pk_scrapers = $scraper_job->pk_scrapers;
            $fk_cases = $scraper_job->fk_cases;
            $search_engine = $scraper_job->name_scrapers;
            $date_scrapers = $scraper_job->date_scrapers;
            $searchstring = $scraper_job->searchstring_scrapers;
            $xpath_results = $scraper_job->xpath_results_scrapers;
            $next_serp = $scraper_job->next_serp_scrapers;
            $attempts = $scraper_job->attempts;
            $max_pages = $scraper_job->max_pages;

            if (empty($attempts)) {
                $attempts = 0;
            }

            if ($attempts == 3) {
                $this->jobs_model->updateScraperJobStatus($pk_scrapers, 1, $attempts);
            } else {

                $this->jobs_model->updateScraperJobStatus($pk_scrapers, 0, ($attempts + 1));
                $this->scrape_results($pk_scrapers, $fk_cases, $search_engine, $date_scrapers, $searchstring, $xpath_results, $next_serp, 1, $max_pages, ($attempts + 1));
            }
        }
    }

    public function reset_scraper_jobs() {
        $this->jobs_model->resetScraperJobs();
    }

    private function scrape_results($pk_scrapers, $fk_cases, $search_engine, $date_scrapers, $searchstring, $xpath_results, $next_serp, $page_number, $max_pages, $attempts) {


        $json_file = "resources/" . $fk_cases . "/" . $pk_scrapers . "_" . $date_scrapers . "_" . $search_engine . "_" . $page_number . "_" . $attempts . ".json";


        if (!file_exists($json_file) AND $page_number <= $max_pages) {
            $next = json_decode($next_serp);

            $part_before = $next->part_before;
            $xpath_next_serp = $next->xpath;
            $part_after = $next->part_after;


            $scraper_status = 1;

            $curl = curl($searchstring);

            $serp = $curl["Source"];


            if ($serp == -1) {
                $scraper_status = -1;
            }

            $search_results = scrape($serp, $xpath_results);

            if (!empty($search_results)) {
                write_to_file($search_results, $json_file);
            }

            $searchstring = "";



            $doc = new DOMDocument();
            @$doc->loadHTML($serp);
            $next_serp_xpath = new DOMXPath($doc);

            $hrefs = $next_serp_xpath->evaluate($xpath_next_serp);

            for ($y = 0; $y < $hrefs->length; $y++) {

                $href = $hrefs->item($y);
                $searchstring = $part_before . $href->getAttribute('href') . $part_after;
                
            }


            if ($scraper_status != -1) {

                if (!empty($search_results)) {
                    $this->process_urls($fk_cases, $date_scrapers, $json_file);
                }

                if (!empty($searchstring)) {
                    ++$page_number;
                    $this->scrape_results($pk_scrapers, $fk_cases, $search_engine, $date_scrapers, $searchstring, $xpath_results, $next_serp, $page_number, $max_pages, $attempts);
                } else {
                    $this->jobs_model->updateScraperJobStatus($pk_scrapers, 1, $attempts);
                   
                }
            } else {
                $this->jobs_model->updateScraperJobStatus($pk_scrapers, -1, $attempts);
            }
        } else {
                $this->jobs_model->updateScraperJobStatus($pk_scrapers, 1, $attempts);
            }
    }

    private function process_urls($fk_cases, $date, $json_file) {

        $cases = $this->cases_model->getCasebyPK($fk_cases);
        $type_cases = $cases->type_cases;
        $sub_type_cases = $cases->sub_type_cases;

        $config_json = process_json(open_external_file_to_read("/user/cases/" . "Cases0" . $type_cases . ".json"));
        $screenshots = $config_json["Config"]["Screenshots"];

        $analysis_module = $config_json["Modules"];


        $modules_string = "";

        foreach ($analysis_module AS $module) {
            $modules_string = $modules_string . $module . ",";
        }

        $modules_string = rtrim($modules_string, ",");

        $contact_modules_string = $modules_string;

        $analysis_modules_string = str_replace("1,", "", $modules_string);

        $modules = array("Analysis" => $analysis_modules_string, "Contact" => $contact_modules_string);


        $content = process_external_json_from_file($json_file);

        foreach ($content AS $array) {
            foreach ($array AS $element) {
                $urls[] = $element["URL"];
            }
        }

        $urls = array_unique($urls, SORT_REGULAR);

        $this->resources_model->saveURLstoDB($fk_cases, $date, $urls, $screenshots, $modules);
    }

    public function rasterizeURLs($fk_cases) {

        $cases = $this->cases_model->getCasebyPK($fk_cases);
        $type_cases = $cases->type_cases;
        $sub_type_cases = $cases->sub_type_cases;

        $config_json = process_json(open_external_file_to_read("/user/cases/" . "Cases0" . $type_cases . ".json"));
        $screenshots = $config_json["Config"]["Screenshots"];

        $analyisis_module = $config_json["Modules"];

        $modules_string = "";

        foreach ($analysis_module AS $module) {
            $modules_string = $modules_string . $module . ",";
        }

        $modules_string = rtrim($modules_string, ",");

        $contact_modules_string = $modules_string;

        $analysis_modules_string = str_replace("1,", "", $modules_string);

        $modules = array("Analyis" => $analysis_modules_string, "Contact" => $contact_modules_string);


        $this->resources_model->rasterizeURLs($fk_cases, $screenshots, $modules);
    }

    public function test() {
        /*
          $urls[] = "werbeartikel-discount.com";
          $date = "2017-06-19";
          $fk_cases = 1;
          $this->resources_model->saveURLstoDB($fk_cases, $date, $urls);
         * */

        $urls = process_json(open_external_file_to_read("urls.json"));
        foreach ($urls AS $url) {
            foreach ($url AS $c) {
                echo $c["URL"];
                echo "\n";
            }
        }
    }

    public function check_progress($fk_cases) {

        $progress_resources = $this->jobs_model->getProgressResources($fk_cases);
        $progress_scrapers = $this->jobs_model->getScrapingJobsProgress($fk_cases);
        $progress_analysis = $this->jobs_model->getAnalysisProgress($fk_cases);
        $resources = $this->jobs_model->getResources($fk_cases);
        $results = $this->jobs_model->getResults($fk_cases);



        if (!empty($progress_resources)) {

            echo '<script>$("#progress").show();</script>';
            echo '
            <div id="progress_urls" style="margin-left: 1% !important; width:98% !important; height: 2em !important; margin-bottom: 10px !important; margin-top: 10px !important;">
            <div style=" float: left;
                margin-left: 40%;   
                font-weight: bold;
                font-size: 120%;
                text-shadow: 1px 1px 0 #fff;">' . $_SESSION["language_json"]["Progress_URLs"] . '</div>
           </div>     
           <script>
           $( "#progress_urls" ).progressbar({
            value: false
           });

         
           </script>';
        }

        if (!empty($progress_scrapers)) {

            echo '<script>$("#progress").show();</script>';
            echo '
            <div id="progress_scrapers" style="margin-left: 1% !important; width:98% !important; height: 2em !important; margin-bottom: 10px !important; margin-top: 10px !important;">
            <div style=" float: left;
                margin-left: 40%;   
                font-weight: bold;
                font-size: 120%;
                text-shadow: 1px 1px 0 #fff;">' . $_SESSION["language_json"]["Progess_Queries"] . '</div>
           </div>     
           <script>
           $( "#progress_scrapers" ).progressbar({
            value: false
           });

         
           </script>';
        }

        if (!empty($progress_analysis)) {

            echo '<script>$("#progress").show();</script>';
            echo '
            <div id="progress_analysis" style="margin-left: 1% !important; width:98% !important; height: 2em !important; margin-bottom: 10px !important; margin-top: 10px !important;">
            <div style=" float: left;
                margin-left: 40%;   
                font-weight: bold;
                font-size: 120%;
                text-shadow: 1px 1px 0 #fff;">' . $_SESSION["language_json"]["Progress_Analysis"] . '</div>
           </div>     
           <script>
           $( "#progress_analysis" ).progressbar({
            value: false
           });

         
           </script>';
        }

        if (empty($progress_resources) AND empty($progress_scrapers) AND empty($progress_analysis) AND ! empty($resources) AND ! empty($results)) {
            echo "<script> $('#judgement_case').show();</script>";
        } else {
            echo "<script> $('#judgement_case').hide();</script>";
        }
    }

}
