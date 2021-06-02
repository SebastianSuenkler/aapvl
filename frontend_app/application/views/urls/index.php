<div id="aapvl_content" class="container-fluid">
    <div id='aapvl_table' class='container-fluid' align='center'><!-- start div for site content end div tag is in footer template -->
        <h3><?php echo $_SESSION["language_json"]["Header_URL"]; ?></h3>
        <table id='aapvl_table_urls' class='display'>

            <thead>
                <tr>     
                    <th><?php echo $_SESSION["language_json"]["Column_URLs"]; ?></th>
                    <th><?php echo $_SESSION["language_json"]["Column_Start_URLs"]; ?></th>
                    <th><?php echo $_SESSION["language_json"]["Column_URLs_Days"]; ?></th>
                    <th><?php echo $_SESSION["language_json"]["Column_URLs_Days_End"]; ?></th>
                </tr>
            </thead>
            <tbody>
                <!-- Tasks Table -->
                <?php foreach ($this->urls_cases AS $url) { ?>
                    <tr> 
                        <td><a href="<?php echo URL; ?>urls/show/<?php echo $this->case_type ?>/<?php echo $url->pk_urls ?>"><?php echo $url->id_urls ?></a></td>
                        <td><?php echo $url->date_urls ?></td>
                        <td><?php echo $url->interval_days_urls ?></td>
                        <td><?php echo $url->interval_completion_urls ?></td>
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
        
        
        <a href="<?php echo URL; ?>urls/add/<?php echo $this->case_type; ?>/<?php echo $this->pk_cases; ?>/<?php echo $sub; ?>" class="btn btn-default" role="button"><?php echo $_SESSION["language_json"]["Button_Add_URLs"]; ?></a>
    </div>    

</div>
