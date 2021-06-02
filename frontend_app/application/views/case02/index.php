<div id="aapvl_content" class="container-fluid">
<div id='aapvl_table' class='container-fluid' align='center'><!-- start div for site content end div tag is in footer template -->
    <h3><?php echo $_SESSION["language_json"]["Header_Mission_Case"]; ?></h3>
    <table id='aapvl_table_case02' class='display'>

        <thead>
            <tr>
                <th><?php echo $_SESSION["language_json"]["Column_Case_ID"]; ?></th>
                <th><?php echo $_SESSION["language_json"]["Column_Date"]; ?></th>
                <th><?php echo $_SESSION["language_json"]["Column_Employee"]; ?></th>
                <th><?php echo $_SESSION["language_json"]["Column_Comment"]; ?></th>
                <th><?php echo "Letze Recherche"; ?></th>
                <th><?php echo "Risikobewertung"; ?></th>
            </tr>
        </thead>
        <tbody>
            <!-- Tasks Table -->
            <?php foreach ($this->cases_from_db AS $case) { ?>
                <tr>
                    <td><a href="<?php echo URL; ?>case02/show/<?php echo $case->pk_cases ?>"><?php echo $case->id_cases ?></a></td>
                    <td><?php echo $case->date_cases ?></td>
                    <td><?php echo $case->symbol_users ?></td>
                    <td><?php echo $case->comment_cases ?></td>
                    <td><?php echo $case->latest_research ?></td>
                    <td><?php echo $case->calculated_score ?></td>
                </tr>
            <?php }
            ?>
        </tbody>
    </table>
</div>
<!-- Button to open Dialog: dialog options and function is in public/js/application.js -->
<!-- To-Do: Dynamic Language for button name -->
<div id="aapvl_buttons">
    <a href="<?php echo URL;?>case02/add" class="btn btn-default" role="button"><?php echo $_SESSION["language_json"]["Button_Add"]; ?></a>
</div>

</div>
