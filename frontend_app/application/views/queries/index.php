<div id="aapvl_content" class="container-fluid">
<div id='aapvl_table' class='container-fluid' align='center'><!-- start div for site content end div tag is in footer template -->
    <h3><?php echo $_SESSION["language_json"]["Header_Search_Queries"]; ?></h3>
    <table id='aapvl_table_queries' class='display'>

        <thead>
            <tr>     
                <th><?php echo $_SESSION["language_json"]["Column_Query"]; ?></th>
                <th><?php echo $_SESSION["language_json"]["Queries_Start"]; ?></th>
                <th><?php echo $_SESSION["language_json"]["Column_Query_Start"]; ?></th>
                <th><?php echo $_SESSION["language_json"]["Column_Query_End"]; ?></th>
            </tr>
        </thead>
        <tbody>
            <!-- Tasks Table -->
            <?php foreach ($this->queries_cases AS $query) { ?>
                <tr> 
                    <td><a href="<?php echo URL; ?>queries/show/<?php echo $this->case_type ?>/<?php echo $query->pk_queries ?>"><?php echo $query->query_queries ?></a></td>
                    <td><?php echo $query->date_queries ?></td>
                    <td><?php echo $query->interval_days_queries ?></td>
                    <td><?php echo $query->interval_completion_queries ?></td>
                   
                </tr>
            <?php }
            ?>
        </tbody>
    </table>
</div>
    
  
<!-- Button to open Dialog: dialog options and function is in public/js/application.js -->
<!-- To-Do: Dynamic Language for button name -->
<div id="aapvl_buttons">
    <?php
    $sub = 0;
    if(!empty($this->sub_type_cases)) {
        $sub = $this->sub_type_cases;
    }
    ?>
    
    <a href="<?php echo URL;?>queries/add/<?php echo $this->case_type; ?>/<?php echo $this->pk_cases; ?>/<?php echo $sub; ?>" class="btn btn-default" role="button"><?php echo $_SESSION["language_json"]["Button_Add_Queries"]; ?></a>
</div>    

</div>
