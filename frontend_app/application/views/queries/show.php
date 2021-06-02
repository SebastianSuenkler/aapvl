<div id="aapvl_content" class="container-fluid">
    <h3><?php echo $_SESSION["language_json"]["Edit_Queries_Case"]?> <a href="../../../<?php echo $this->case_type; ?>/show/<?php echo $this->fk_cases; ?>"> <?php echo $this->id_cases; ?></a></h3>
    <div class="container-fluid aapvl-form-container">
        <form class="form-horizontal aapvl-form" action="../../update/<?php echo $this->case_type; ?>/<?php echo $this->fk_cases; ?>/<?php echo $this->pk_queries; ?>" method='post'>
            <fieldset>

                <div class="form-group">
                    <label class="control-label" for="query_queries"><?php echo $_SESSION["language_json"]["Column_Query"]; ?></label>  
                    <input id="query_queries" name="query_queries" placeholder="" class="form-control" value='<?php echo $this->queries_values->query_queries; ?>' required type="text" size="50" oninvalid="this.setCustomValidity('<?php echo $_SESSION["language_json"]["Alert_Mandatory"]; ?>')" oninput="setCustomValidity('')" disabled />

                </div>

                <div class="form-group">
                    <label class="control-label" for="date_queries"><?php echo $_SESSION["language_json"]["Queries_Start"]; ?></label>  
                    <input type="text" id="date_queries" name="date_queries" class="form-control" required value="<?php echo $this->queries_values->date_queries; ?>"  min="<?php echo date("Y-m-d"); ?>" oninvalid="this.setCustomValidity('<?php echo $_SESSION["language_json"]["Alert_Mandatory"]; ?>')" oninput="setCustomValidity('')" disabled />
                    <div id="date_queries_warning" class="up-arrow"><span class="info-icon"><img src="<?php echo URL; ?>inc/img/info_icon.png" /></span><?php echo $_SESSION["language_json"]["Alert_Date"]; ?></div>
                </div>

                <div class="form-group">
                    <label class="control-label" for="interval_days_queries"><?php echo $_SESSION["language_json"]["Column_Query_Start"]; ?></label>  
                    <input id="interval_days_queries" name="interval_days_queries" placeholder="" class="form-control" value="<?php echo $this->queries_values->interval_days_queries; ?>" type="number" size="50" min="0" oninvalid="this.setCustomValidity('<?php echo $_SESSION["language_json"]["Alert_Mandatory"]; ?>')" oninput="setCustomValidity('')" disabled />
                </div>

                <div class="form-group">
                    <label class="control-label" for="interval_completion_queries"><?php echo $_SESSION["language_json"]["Queries_End"]; ?></label>  
                    <input type="text" id="interval_completion_queries" name="interval_completion_queries" class="form-control"  value="<?php echo $this->queries_values->interval_completion_queries; ?>"  min="<?php echo date("Y-m-d"); ?>" disabled />
                    <div id="interval_completion_queries_warning" class="up-arrow"><span class="info-icon"><img src="<?php echo URL; ?>inc/img/info_icon.png" /></span><?php echo $_SESSION["language_json"]["Altert_Final_Date"]; ?></div>
                </div>
                
                 <input type="hidden" name="type_cases" id="type_cases" value="<?php echo $this->type_cases; ?>" />

                <!-- Button to save form dd"ata -->
                <div id="aapvl_buttons">

                    <button id="delete_query" name="delete_query" value="Delete" class="btn btn-default" onclick="var result = confirm('<?php echo $_SESSION["language_json"]["Alert_Delete"]; ?>');
                            if (result) {
                                window.location.href = '../../delete/<?php echo $this->fk_cases; ?>/<?php echo $this->pk_queries; ?>/<?php echo $this->case_type; ?>'
                            }
                            return false;"><?php echo $_SESSION["language_json"]["Button_Delete"]; ?></button>
                    <button id="edit_query" name="edit_query" value="Edit" class="btn btn-default" onclick="return false;"><?php echo $_SESSION["language_json"]["Button_Edit"]; ?></button>
                    <button id="save_queries" name="save_queries" value="Save" class="btn btn-default" disabled><?php echo $_SESSION["language_json"]["Button_Save"]; ?></button>

                </div>


            </fieldset>
        </form>

    </div>    
</div>




