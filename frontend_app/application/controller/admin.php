<?php

class Admin extends Controller {

  public $users_from_db = null;
  public $user = null;

    public function index() {

        $this->users_from_db = $this->users_model->getAllUsers();
        $optional_views = array('application/views/admin/index.php');
        $this->loadViews($optional_views);
    }

    public function add() {

        $this->users_from_db = $this->users_model->getAllUsers();
        $optional_views = array('application/views/admin/add.php');
        $this->loadViews($optional_views);
    }


    public function save() {

        $form_values = $_POST;

        $this->users_model->saveUser($form_values);

        header('Location: ' . URL . 'admin/');

    }

    public function edit($pk_users) {

        $this->user = $this->users_model->getUser($pk_users);
        $optional_views = array('application/views/admin/edit.php');
        $this->loadViews($optional_views);

    }


    public function update($pk_users) {

      $form_values = $_POST;

      $this->users_model->updateUser($form_values, $pk_users);

      header('Location: ' . URL . 'admin/');

    }


    public function reset($pk_users) {

      $this->user = $this->users_model->getUser($pk_users);
      $optional_views = array('application/views/admin/reset.php');
      $this->loadViews($optional_views);

    }

    public function pass($pk_users) {

        $pass = $_POST["password_users"];
        $this->users_model->savePass($pk_users, $pass);
        header('Location: ' . URL . 'admin/');

    }

    public function delete($pk_users) {

        $this->users_model->delUser($pk_users);
        header('Location: ' . URL . 'admin/');

    }

}

 ?>
