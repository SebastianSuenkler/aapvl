<?php

/* Controller for Index page */

class Index extends Controller {

    // Method to render the start page of the app with login form
    public function home() {

      $hour = date('H');

      if($hour > 22 OR $hour < 5) {

        header("Location:" . URL . "/service");
      }


        $users = $this->users_model->getAllUsers();

        foreach ($users AS $user) {
            $aapvl_users[] = array("pk_users" => $user->pk_users, "name_users" => $user->name_users, "password_users" => $user->password_users, "mail_users" => $user->mail_users);
        }

        session_start();
        if (isset($_POST["login_button"])) {
            $_SESSION["name_users"] = $_POST["name_users"];
            $_SESSION["password_users"] = md5($_POST["password_users"]);
            $_SESSION["login"] = "failed";

            foreach ($aapvl_users AS $aapvl_user) {
                if (($_SESSION["name_users"] == $aapvl_user["name_users"] OR $_SESSION["name_users"] == $aapvl_user["mail_users"] ) AND ( $_SESSION["password_users"] == $aapvl_user["password_users"])) {
                    $_SESSION["login"] = "d17138cba7999220be6d69669fb50dd4";
                    $_SESSION["mail_users"] = $aapvl_user["mail_users"];
                    $pk_users = $aapvl_user["pk_users"];
                    $_SESSION["pk_users"] = $pk_users;
                    $_SESSION["language_json"] = process_json(open_external_file_to_read("/user/language/" . $_POST['language_json']));
                    $this->users_model->LogUser($pk_users);
                }
            }
        }

        if (isset($_SESSION["login"]) AND $_SESSION["login"] == "d17138cba7999220be6d69669fb50dd4") {

            header("Location:" . URL . "/dashboard");
        } else {
            $optional_views = array('application/views/index/index.php');
        }
        session_write_close();

        // Load views for the page
        $this->loadViews($optional_views, false);
    }
}
