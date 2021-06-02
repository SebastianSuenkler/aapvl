<?php

/* Model with all sql statements for the app from the table users */

class JobsModel extends Model {

    // select all users saved in database
    public function setScrapersJobs($db_values) {

        foreach ($db_values AS $db_value) {
            $sql = "INSERT INTO `jobs_scrapers` (fk_cases, fk_queries, name_scrapers, query_queries, searchstring_scrapers, xpath_results_scrapers, next_serp_scrapers, date_scrapers, max_pages) VALUES (:fk_cases, :fk_queries, :name_scrapers, :query_queries, :searchstring_scrapers, :xpath_results_scrapers, :next_serp_scrapers, :date_scrapers, :max_pages)";
            $query = $this->db->prepare($sql);
            $query->execute(array(':fk_cases' => $db_value["fk_cases"], ':fk_queries' => $db_value["fk_queries"], ':name_scrapers' => $db_value["name_scrapers"], ':query_queries' => $db_value["query_queries"], ':searchstring_scrapers' => $db_value["searchstring_scrapers"], ':xpath_results_scrapers' => $db_value["xpath_results_scrapers"], ':next_serp_scrapers' => $db_value["next_serp_scrapers"], ':date_scrapers' => $db_value["date_scrapers"], ':max_pages' => $db_value["max_pages"]));
        }
    }

    public function getScrapersJobs() {
        $current_date = date("Y-m-d", strtotime("+7 days")); // tage, um problemhafte scraper jobs trotzdem zu scrapen z. b. ausschluss google oder so puffer momentan 7 tage
        $sql = "SELECT `pk_scrapers`, `fk_cases`, `fk_queries`, `name_scrapers`, `query_queries`, `searchstring_scrapers`, `xpath_results_scrapers`, `next_serp_scrapers`, `date_scrapers`, `max_pages`, `attempts` FROM `jobs_scrapers` WHERE `status_scrapers` IS NULL AND `date_scrapers` < :date_scrapers LIMIT 2";
        $query = $this->db->prepare($sql);
        $query->execute(array(':date_scrapers' => $current_date));
        return $query->fetchAll();
    }

    public function getUnfinishedScrapersJobs() {
        $current_date = date("Y-m-d", strtotime("+7 days")); // tage, um problemhafte scraper jobs trotzdem zu scrapen z. b. ausschluss google oder so puffer momentan 7 tage
        $sql = "SELECT `pk_scrapers`, `fk_cases`, `fk_queries`, `name_scrapers`, `query_queries`, `searchstring_scrapers`, `xpath_results_scrapers`, `next_serp_scrapers`, `date_scrapers` FROM `jobs_scrapers` WHERE `status_scrapers` IS NULL AND `date_scrapers` < :date_scrapers LIMIT 2";
        $query = $this->db->prepare($sql);
        $query->execute(array(':date_scrapers' => $current_date));
        return $query->fetchAll();
    }
    
    public function resetScraperJobs() {
        $sql = "UPDATE `jobs_scrapers` SET status_scrapers=:status_scrapers WHERE status_scrapers = -1 AND attempts < 4";
        $query = $this->db->prepare($sql);
        $query->execute(array(':status_scrapers' => NULL));
    }

    public function CheckStatus($fk_cases) {

        $date = date("Y-m-d", strtotime("+7 days"));
        $sql = 'SELECT `pk_scrapers` FROM `jobs_scrapers` WHERE `fk_cases` = :fk_cases AND (`status_scrapers` IS NULL OR `status_scrapers` = 0) AND `date_scrapers` < :date_scrapers LIMIT 1';
        $query = $this->db->prepare($sql);
        $query->execute(array(':fk_cases' => $fk_cases, ':date_scrapers' => $date));
        $status_scrapers = $query->fetch();

        $sql = 'SELECT `pk_jobs_urls` FROM `jobs_urls` WHERE `fk_cases` = :fk_cases AND (`status_jobs_urls` IS NULL OR `status_jobs_urls` = 0) AND `date_jobs_urls` < :date_jobs_urls LIMIT 1';
        $query = $this->db->prepare($sql);
        $query->execute(array(':fk_cases' => $fk_cases, ':date_jobs_urls' => $date));
        $status_urls = $query->fetch();

        $sql = 'SELECT `pk_resources` FROM `resources` WHERE `fk_cases` = :fk_cases AND (`resources_progress` IS NULL OR `resources_progress` = 0) AND `date_resources` < :date_resources LIMIT 1';
        $query = $this->db->prepare($sql);
        $query->execute(array(':fk_cases' => $fk_cases, ':date_resources' => $date));
        $status_resources = $query->fetch();

        if (empty($status_scrapers) AND empty($status_urls) AND empty($status_resources)) {
            return true;
        }
    }

    public function updateScraperJobStatus($pk_scrapers, $status_scrapers, $attempts) {

        $sql = "UPDATE `jobs_scrapers` SET status_scrapers=:status_scrapers, attempts=:attempts WHERE pk_scrapers=:pk_scrapers";
        $query = $this->db->prepare($sql);
        $query->execute(array(':pk_scrapers' => $pk_scrapers, ':status_scrapers' => $status_scrapers, ':attempts' => $attempts));
    }

    public function getURLsJobs() {
        $current_date = date("Y-m-d", strtotime("+7 days")); // tage, um problemhafte scraper jobs trotzdem zu scrapen z. b. ausschluss google oder so puffer momentan 7 tage
        $sql = "SELECT `pk_jobs_urls`, `fk_cases`, `fk_urls`, `elements_urls_path`, `date_jobs_urls` FROM `jobs_urls` WHERE `status_jobs_urls` IS NULL AND `date_jobs_urls` < :date_jobs_urls ORDER BY RAND() LIMIT 5";
        $query = $this->db->prepare($sql);
        $query->execute(array(':date_jobs_urls' => $current_date));
        return $query->fetchAll();
    }

    public function getURLsJobsProgress($fk_cases) {

        $current_date = date("Y-m-d", strtotime("+7 days")); // tage, um problemhafte scraper jobs trotzdem zu scrapen z. b. ausschluss google oder so puffer momentan 7 tage
        $sql = "SELECT `pk_jobs_urls` FROM `jobs_urls` WHERE `status_jobs_urls` = 0 AND `date_jobs_urls` < :date_jobs_urls AND `fk_cases` = :fk_cases";
        $query = $this->db->prepare($sql);
        $query->execute(array(':date_jobs_urls' => $current_date, ':fk_cases' => $fk_cases));
        return $query->fetchAll();
    }

    public function setURLsJobs($db_values) {

        foreach ($db_values AS $db_value) {
           var_dump($db_value);
            $sql = "INSERT INTO `jobs_urls` (fk_cases, fk_urls, elements_urls_path, date_jobs_urls) VALUES (:fk_cases, :fk_urls, :elements_urls_path, :date_jobs_urls)";
            $query = $this->db->prepare($sql);
            $query->execute(array(':fk_cases' => $db_value["fk_cases"], ':fk_urls' => $db_value["fk_urls"], ':elements_urls_path' => $db_value["elements_urls_path"], ':date_jobs_urls' => $db_value["date_urls"]));
        }
    }

    public function updateUrlsJobStatus($pk_jobs_urls, $status_jobs_urls) {
        $sql = "UPDATE `jobs_urls` SET status_jobs_urls=:status_jobs_urls WHERE pk_jobs_urls=:pk_jobs_urls";
        $query = $this->db->prepare($sql);
        $query->execute(array(':pk_jobs_urls' => $pk_jobs_urls, ':status_jobs_urls' => $status_jobs_urls));
    }

    public function updateUrlsJobStatusByUrls($pk_urls, $status_jobs_urls) {
        $sql = "UPDATE `jobs_urls` SET status_jobs_urls=:status_jobs_urls WHERE fk_urls=:pk_urls";
        $query = $this->db->prepare($sql);
        $query->execute(array(':pk_urls' => $pk_urls, ':status_jobs_urls' => $status_jobs_urls));
    }

    public function deleteURLsJobsbyURL($fk_urls) {
        $sql = "DELETE FROM `jobs_urls` WHERE fk_urls=:fk_urls";
        $query = $this->db->prepare($sql);
        $query->execute(array(':fk_urls' => $fk_urls));
    }

    public function deleteScrapersJobsbyQuery($fk_queries) {
        $sql = "DELETE FROM `jobs_scrapers` WHERE fk_queries=:fk_queries";
        $query = $this->db->prepare($sql);
        $query->execute(array(':fk_queries' => $fk_queries));
    }

    public function deleteURLsJobsbyCases($fk_cases) {
        $sql = "DELETE FROM `jobs_urls` WHERE fk_cases=:fk_cases";
        $query = $this->db->prepare($sql);
        $query->execute(array(':fk_cases' => $fk_cases));
    }

    public function deleteScrapersJobsbyCases($fk_cases) {
        $sql = "DELETE FROM `jobs_scrapers` WHERE fk_cases=:fk_cases";
        $query = $this->db->prepare($sql);
        $query->execute(array(':fk_cases' => $fk_cases));
    }

    public function getProgressResources($fk_cases) {
        $sql = "SELECT `pk_resources` FROM `resources` WHERE (`resources_progress` IS NULL OR `resources_progress` = 0) AND `fk_cases` = :fk_cases";
        $query = $this->db->prepare($sql);
        $query->execute(array(':fk_cases' => $fk_cases));
        return $query->fetchAll();
    }

    public function getScrapingJobsProgress($fk_cases) {
        $sql = "SELECT `pk_scrapers` FROM `jobs_scrapers` WHERE `status_scrapers` = 0 AND `fk_cases` = :fk_cases";
        $query = $this->db->prepare($sql);
        $query->execute(array(':fk_cases' => $fk_cases));
        return $query->fetchAll();
    }

    public function getAnalysisProgress($fk_cases) {
        $sql = "SELECT `pk_jobs` FROM `jobs_to_assign` WHERE `status_jobs` = 0 AND `fk_cases` = :fk_cases";
        $query = $this->db->prepare($sql);
        $query->execute(array(':fk_cases' => $fk_cases));
        return $query->fetchAll();
    }

    public function getResources($fk_cases) {
        $sql = "SELECT `pk_resources` FROM `resources` WHERE `resources_progress` = 1 AND `fk_cases` = :fk_cases";
        $query = $this->db->prepare($sql);
        $query->execute(array(':fk_cases' => $fk_cases));
        return $query->fetchAll();
    }

    public function getResults($fk_cases) {
        $sql = "SELECT `pk_results` FROM `results` WHERE `fk_cases` = :fk_cases";
        $query = $this->db->prepare($sql);
        $query->execute(array(':fk_cases' => $fk_cases));
        return $query->fetchAll();
    }

    public function deleteResults($fk_cases) {
        $sql = "DELETE FROM `results` WHERE fk_cases=:fk_cases";
        $query = $this->db->prepare($sql);
        $query->execute(array(':fk_cases' => $fk_cases));
    }

    public function deleteJobs($fk_cases) {
        $sql = "DELETE FROM `jobs_to_assign` WHERE fk_cases=:fk_cases";
        $query = $this->db->prepare($sql);
        $query->execute(array(':fk_cases' => $fk_cases));
    }

}
