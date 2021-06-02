<?php

if ($_SESSION["login"] != "d17138cba7999220be6d69669fb50dd4") {
    header("Location:" . URL);
    exit();
}
?>
