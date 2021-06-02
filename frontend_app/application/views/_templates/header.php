<?php
session_start();
session_write_close();
?>

<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>AAPVL Tool</title>
        <meta name="description" content="">
        <meta http-equiv="pragma" content="no-cache" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="author" content="Sebastian Sünkler, Dorle Osterode, Niklas Finck, Alexandra Krewinkel">
        <meta http-equiv="content-type" content="text/html; charset=utf-8">
        <!-- icon -->
        <link rel="icon" href="<?php echo URL; ?>inc/img/favicon.ico" type='image/x-icon' />

        <link rel="stylesheet" type="text/css" href="<?php echo URL; ?>inc/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="<?php echo URL; ?>inc/css/jquery-ui.min.css" rel="stylesheet">

        <link rel="stylesheet" type="text/css" href="<?php echo URL; ?>inc/css/dataTables.jqueryui.css">
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/keytable/2.4.0/css/keyTable.dataTables.min.css">
        <link rel="stylesheet" type="text/css" href="<?php echo URL; ?>inc/css/font-awesome.min.css" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="<?php echo URL; ?>inc/css/main.css" rel="stylesheet">



        <script type="text/javascript" language="javascript" src="https://code.jquery.com/jquery-3.3.1.js"></script>
        <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.10.18/js/jquery.dataTables.min.js"></script>

        <script type="text/javascript" language="javascript" src="<?php echo URL; ?>inc/js/bootstrap.min.js"></script>
        <script type="text/javascript" language="javascript" src="<?php echo URL; ?>inc/js/jquery-ui.min.js"></script>
        <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/keytable/2.4.0/js/dataTables.keyTable.min.js"></script>
        <script type="text/javascript" language="javascript" src="<?php echo URL; ?>inc/js/dataTables.jqueryui.js"></script>
        <script type="text/javascript" language="javascript" src="<?php echo URL; ?>inc/js/js.cookie.js"></script>





    </head>
    <body>

        <script>var php_language = '<?php echo json_encode($_SESSION["language_json"]); ?>';</script>
        <!-- header -->
        <div id="aapvl_main">
            <!-- AAPVL-Logo -->

            <div class='container-fluid'>
                <div id='aapvl_logo' class='row'>
                    <a href='<?php echo URL; ?>'><img id='aapvl_logo' src="<?php echo URL; ?>inc/img/aapvl_logo.jpg" /></a>
                </div>
            </div>
            <noscript><p class="noscript">Please activate Javascript to use the AAPVL Tool / Bitte aktivieren Sie Javascript in ihrem Webbrowser, um das AAPL Tool zu nutzen</p><style type="text/css">
                #aapvl_content {display:none;}
            </style></noscript>

            <!-- Content -->


            <div align='center'>

                <?php if (isset($_SESSION["login"]) AND $_SESSION["login"] == "d17138cba7999220be6d69669fb50dd4") { ?>
                    <div>
                        <ul class="topnav" id="AAPVLTopNav" >
                            <li><a href="<?php echo URL; ?>dashboard/"><?php echo $_SESSION["language_json"]["Label_Dashboard"]; ?></a></li>
                            <li><a href="<?php echo URL; ?>case01/"><?php echo $_SESSION["language_json"]["Label_Enterprise_Case"]; ?></a></li>
                            <li><a href="<?php echo URL; ?>case02/"><?php echo $_SESSION["language_json"]["Label_Mission_Case"]; ?></a></li>
                            <li><a href="<?php echo URL; ?>case04/"><?php echo $_SESSION["language_json"]["Label_Bio_Case"]; ?></a></li>
                            <li><a href="<?php echo URL; ?>admin/"><?php echo $_SESSION["language_json"]["Label_Admin_Panel"]; ?></a></li>
                            <li><a href="<?php echo URL; ?>logout/"><?php echo $_SESSION["language_json"]["Label_Logout"]; ?></a></li>
                        </ul>
                    </div>
                    <div id="progress"></div>
                <?php };
                ?>

                <?php
                if (isset($_GET["status"])) {
                    echo '<div id="aapvl_status">';
                    echo '<div id="status">';
                    switch ($_GET["status"]) {
                        case "c_s":
                            echo "<i class='fa fa-check-square-o fa-1x' aria-hidden='true'></i> " . $_SESSION["language_json"]["Save_Case"];
                            break;
                        case "c_u":
                            echo "<i class='fa fa-check-square-o fa-1x' aria-hidden='true'></i> " . $_SESSION["language_json"]["Update_Case"];
                            break;
                        case "c_d":
                            echo "<i class='fa fa-check-square-o fa-1x' aria-hidden='true'></i> " . $_SESSION["language_json"]["Delete_Case"];
                            break;
                        case "q_s":
                            echo "<i class='fa fa-check-square-o fa-1x' aria-hidden='true'></i> " . $_SESSION["language_json"]["Save_Queries"];
                            break;
                        case "q_u":
                            echo "<i class='fa fa-check-square-o fa-1x' aria-hidden='true'></i> " . $_SESSION["language_json"]["Update_Query"];
                            break;
                        case "q_d":
                            echo "<i class='fa fa-check-square-o fa-1x' aria-hidden='true'></i> " . $_SESSION["language_json"]["Delete_Query"];
                            break;
                        case "u_s":
                            echo "<i class='fa fa-check-square-o fa-1x' aria-hidden='true'></i> " . $_SESSION["language_json"]["Save_URLs"];
                        case "u_u":
                            echo "<i class='fa fa-check-square-o fa-1x' aria-hidden='true'></i> " . $_SESSION["language_json"]["Update_URL"];
                            break;
                        case "u_d":
                            echo "<i class='fa fa-check-square-o fa-1x' aria-hidden='true'></i> " . $_SESSION["language_json"]["Delete_URL"];
                            break;
                    }
                    echo '</div></div>';
                }
                ?>
