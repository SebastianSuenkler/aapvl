<?php

/* Model with all sql statements for the app for all kind of cases */

class CasesModel extends Model {

    public function getAllCases() {
      $sql = "SELECT `pk_cases`, `id_cases`, `sub_type_cases`, `type_cases` FROM `cases`";
      $query = $this->db->prepare($sql);
      $query->execute();
      return $query->fetchAll();
    }



    // select all users saved in database
    public function getCasesByType($type_cases) {
      if($type_cases == 1)
      {
        $sql = "SELECT `pk_cases`, `id_cases`, `sub_type_cases`, `comment_cases`, `date_cases`, `config_json_cases`, `fk_users`, `symbol_users` FROM `cases`, `users`  WHERE `type_cases` = :type_cases AND `fk_users` = `pk_users`";
        $query = $this->db->prepare($sql);
        $query->execute(array(':type_cases' => $type_cases));
      }

      if($type_cases == 4)
      {
        $sql = "SELECT `pk_cases`, `id_cases`, `sub_type_cases`, `comment_cases`, `date_cases`, `config_json_cases`, `fk_users`, `symbol_users` FROM `cases`, `users`  WHERE `type_cases` = :type_cases AND `fk_users` = `pk_users`";
        $query = $this->db->prepare($sql);
        $query->execute(array(':type_cases' => $type_cases));
      }

      if($type_cases == 2)
      {
        $sql = "SELECT `pk_cases`, `id_cases`, `sub_type_cases`, `comment_cases`, `date_cases`, `config_json_cases`, `fk_users`, `symbol_users`, `latest_research`, `time_score`, `calculated_score`  FROM `cases`, `users`  WHERE `type_cases` = :type_cases AND `fk_users` = `pk_users`";
        $query = $this->db->prepare($sql);
        $query->execute(array(':type_cases' => $type_cases));
      }

        return $query->fetchAll();
    }

    public function saveCase($db_values) {

        // Speichere die Formulardaten und die IP in der Datenbank
        $sql = "INSERT INTO `cases` (id_cases, type_cases, sub_type_cases, fk_users, comment_cases, date_cases, ip_cases, config_json_cases, risk_decision, classification, time_score, latest_research, calculated_score) VALUES (:id_cases, :type_cases, :sub_type_cases, :fk_users, :comment_cases, :date_cases, :ip_cases, :config_json_cases, :risk_decision, :classification, :latest_research, :time_score, :calculated_score)";
        $query = $this->db->prepare($sql);
        $query->execute(array(':id_cases' => $db_values["id_cases"], ':type_cases' => $db_values["type_cases"], ':sub_type_cases' => $db_values["sub_type_cases"], ':fk_users' => $db_values["fk_users"], ':comment_cases' => $db_values["comment_cases"], ':date_cases' => $db_values["date_cases"], ':ip_cases' => $db_values["ip_cases"], ':config_json_cases' => $db_values["config_json_cases"], ':risk_decision' => $db_values["risk_decision"], ':classification' => $db_values["classification"], ':latest_research' => $db_values["date_cases"], 'time_score' => 0, 'calculated_score' => ($db_values["risk_decision"] + $db_values["classification"] + $db_values["risk_points"])));
        $last_id = $this->db->lastInsertId();

        return $last_id;
    }

    public function getCasebyPK($pk_cases) {
        $sql = "SELECT `pk_cases`, `id_cases`, `type_cases`, `sub_type_cases`, `comment_cases`, `date_cases`, `config_json_cases`, `fk_users`, `symbol_users` FROM `cases`, `users`  WHERE `pk_cases` = :pk_cases AND `fk_users` = `pk_users`";
        $query = $this->db->prepare($sql);
        $query->execute(array(':pk_cases' => $pk_cases));
        return $query->fetch();
    }

    public function updateCase($db_values, $pk_cases) {

        $sql = "UPDATE `cases` SET id_cases=:id_cases, type_cases=:type_cases, sub_type_cases=:sub_type_cases, comment_cases=:comment_cases, date_cases=:date_cases, config_json_cases=:config_json_cases, fk_users=:fk_users WHERE pk_cases=:pk_cases";
        $query = $this->db->prepare($sql);
        $query->execute(array(':id_cases' => $db_values["id_cases"], ':type_cases' => $db_values["type_cases"], ':sub_type_cases' => $db_values["sub_type_cases"], ':comment_cases' => $db_values["comment_cases"], ':date_cases' => $db_values["date_cases"], ':config_json_cases' => $db_values["config_json_cases"], ':fk_users' => $db_values["fk_users"], ':pk_cases' => $pk_cases));
    }

    public function deleteCase($pk_cases) {

        $sql = "DELETE FROM `cases` WHERE pk_cases=:pk_cases";
        $query = $this->db->prepare($sql);
        $query->execute(array(':pk_cases' => $pk_cases));

        $results_table = "results_" . $pk_cases;

        $sql = "DROP TABLE `" . $results_table . "`;";
        $query = $this->db->prepare($sql);
        $query->execute();
    }

    public function GetResponsibility($fk_cases) {

        $sql = "SELECT `symbol_users`, `fk_users` FROM `cases`, `users`  WHERE `pk_cases` = :fk_cases AND `fk_users` = `pk_users`";
        $query = $this->db->prepare($sql);
        $query->execute(array(':fk_cases' => $fk_cases));
        return $query->fetch();

}

}
