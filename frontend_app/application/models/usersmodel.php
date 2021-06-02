<?php

/* Model with all sql statements for the app from the table users */

class UsersModel extends Model {

    // select all users saved in database
    public function getAllUsers() {
        $sql = "SELECT `pk_users`, `name_users`, `password_users`, `mail_users`, `symbol_users`, `first_name_users`, `last_name_users` FROM `users`";
        $query = $this->db->prepare($sql);
        $query->execute();
        return $query->fetchAll();
    }

    public function getUser($pk_users) {
        $sql = "SELECT `pk_users`, `name_users`, `password_users`, `mail_users`, `symbol_users`, `first_name_users`, `last_name_users` FROM `users`  WHERE `pk_users` = :pk_users";
        $query = $this->db->prepare($sql);
        $query->execute(array(':pk_users' => $pk_users));
        return $query->fetch();
    }

    // logUser interaction
    public function logUser($pk_users) {

        $ip_log_users = $_SERVER['REMOTE_ADDR'];
        $timestamp_log_users = date('Y-m-d H:i:s');

        $sql = "INSERT INTO `log_users` (`pk_users_log_users`, `ip_log_users`, `timestamp_log_users`) VALUES (:pk_users_log_users, :ip_log_users, :timestamp_log_users)";
        $query = $this->db->prepare($sql);
        $query->execute(array(':pk_users_log_users' => $pk_users, ':ip_log_users' => $ip_log_users, ':timestamp_log_users' => $timestamp_log_users));
    }

  public function saveUser($form_values) {

    $name_users = $form_values["name_users"];
    $password_users =  md5($form_values["password_users"]);
    $mail_users = $form_values["mail_users"];
    $symbol_users = $form_values["symbol_users"];
    $first_name_users = $form_values["first_name_users"];
    $last_name_users = $form_values["last_name_users"];


    $sql = "INSERT INTO `users` (`name_users`, `password_users`, `mail_users`, `symbol_users`, `first_name_users`, `last_name_users`) VALUES (:name_users, :password_users, :mail_users, :symbol_users, :first_name_users, :last_name_users)";
    $query = $this->db->prepare($sql);
    $query->execute(array(':name_users' => $name_users, ':password_users' => $password_users, ':mail_users' => $mail_users, ':symbol_users' => $symbol_users, ':first_name_users' => $first_name_users, ':last_name_users' => $last_name_users));

  }

  public function savePass($pk_users, $pass) {


    $password_users =  md5($pass);

    $sql = "UPDATE `users` SET password_users=:password_users WHERE pk_users=:pk_users";
    $query = $this->db->prepare($sql);
    $query->execute(array(':password_users' =>$password_users, ':pk_users' => $pk_users));

  }

  public function updateUser($form_values, $pk_users) {

    $name_users = $form_values["name_users"];
    $mail_users = $form_values["mail_users"];
    $symbol_users = $form_values["symbol_users"];
    $first_name_users = $form_values["first_name_users"];
    $last_name_users = $form_values["last_name_users"];

    $sql = "UPDATE `users` SET name_users=:name_users, mail_users=:mail_users, symbol_users=:symbol_users, first_name_users=:first_name_users, last_name_users=:last_name_users  WHERE pk_users=:pk_users";
    $query = $this->db->prepare($sql);
    $query->execute(array(':pk_users' => $pk_users, ':name_users' => $name_users, ':mail_users' => $mail_users, ':symbol_users' => $symbol_users, ':first_name_users' => $first_name_users, ':last_name_users' => $last_name_users));

  }

  public function delUser($pk_users) {

    $sql = "DELETE FROM `users` WHERE pk_users=:pk_users";
    $query = $this->db->prepare($sql);
    $query->execute(array(':pk_users' => $pk_users));

  }

}
