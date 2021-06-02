<?php
if (isset($_SESSION["login"]) AND $_SESSION["login"] == "failed") {
    ?>

    <div id="failed">
        <h1>
            Login failed!
        </h1>
    </div>

    <?php
}
?>



<div id="footer">&copy; <?php echo date("Y");?> <a href="http://www.ls.haw-hamburg.de/~foodControl/index.php" target="_blank"><?php echo (isset($_SESSION["language_json"]["Label_Project"])) ? $_SESSION["language_json"]["Label_Project"] : 'AAPVL Research Project'; ?></a> <?php echo (isset($_SESSION["language_json"]["Funded"])) ? $_SESSION["language_json"]["Funded"] : 'funded by'; ?> <a href="http://www.ble.de" target="_blank">Bundesanstalt für Ernährung und Landwirtschaft</a></div>
<div id="border"></div>
</div>
<script type="text/javascript" language="javascript" src="<?php echo URL; ?>inc/js/aapvl.js"></script>
<!-- <script src="<?php echo URL; ?>inc/js/aapvl_datatables.js"></script> -->
<script src="<?php echo (isset($_SESSION["language_json"]["Language"])) ? URL.'user/language/js/'.$_SESSION["language_json"]["Language"].'DataTables.js' : ''; ?>"></script>
<script src="<?php echo (isset($_SESSION["language_json"]["Language"])) ? URL.'user/language/js/'.$_SESSION["language_json"]["Language"].'DatePicker.js' : ''; ?>"></script>
</body>
</html>
