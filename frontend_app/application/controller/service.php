<?php

class Service extends Controller {
  public function index() {
      $optional_views = array('application/views/service/index.php');
      $this->loadViews($optional_views, false);
  }
}

 ?>
