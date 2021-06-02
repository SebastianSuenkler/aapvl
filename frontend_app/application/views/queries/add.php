<div id="aapvl_content" class="container-fluid">
    <h3><?php echo $_SESSION["language_json"]["Queries_Case"]?> <a href="../../../../<?php echo $this->case_type; ?>/show/<?php echo $this->fk_cases; ?>"> <?php echo $this->id_cases; ?></a></h3>
    <div class="container-fluid aapvl-form-container">
        <form class="form-horizontal aapvl-form" action="../../../save/<?php echo $this->case_type; ?>/<?php echo $this->fk_cases; ?>" method='post'>
            <fieldset>

                <div class="form-group">
                    <label for="queries_query"><?php echo $_SESSION["language_json"]["Textarea_Queries"]; ?></label>
                    <textarea class="form-control" rows="15" id="queries_query" name="queries_query" required oninvalid="this.setCustomValidity('<?php echo $_SESSION["language_json"]["Alert_Mandatory"]; ?>')" oninput="setCustomValidity('')" placeholder=''></textarea>
                </div>

                <?php
                $cases_queries_json = process_json(open_external_file_to_read("/user/cases/" . $_SESSION["language_json"]["Language"] .$this->case_type."_queries.json"));
                
                $cases_queries_array = $cases_queries_json["Queries"];

                foreach ($cases_queries_array AS $cases_queries) {
					
					if ($cases_queries["ID"] == $this->sub_type_cases OR $cases_queries["ID"] == 0) {
                        if (!empty($cases_queries["Queries"])) {
                            $queries = implode("\n", $cases_queries["Queries"]);
                        }

                        if (!empty($cases_queries["Extension"])) {

                            $extension = implode("\n", $cases_queries["Extension"]);
                        }
                    }
                }
                ?>

                <?php if (!empty($queries)) { ?>

                    <button id="generate_queries" name="generate_queries" value="<?php echo $queries; ?>" class="btn btn-default"><?php echo $_SESSION["language_json"]["Button_Generate_Queries"]; ?></button>

                <?php } ?>

                <?php if (!empty($extension)) { ?>

                    <button id="extend_queries" name="extend_queries" value="<?php echo $extension; ?>" class="btn btn-default"><?php echo $_SESSION["language_json"]["Button_Extend_Queries"]; ?></button>

                <?php } ?>


                <div class="form-group">
                    <label class="control-label" for="date_queries"><?php echo $_SESSION["language_json"]["Queries_Start"]; ?></label>  
                    <input type="text" id="date_queries" name="date_queries" class="form-control" required value="<?php echo date("Y-m-d"); ?>" min="<?php echo date("Y-m-d"); ?>" oninvalid="this.setCustomValidity('<?php echo $_SESSION["language_json"]["Alert_Mandatory"]; ?>')" oninput="setCustomValidity('')" />
                    <div id="date_queries_warning" class="up-arrow"><span class="info-icon"><img src="<?php echo URL; ?>inc/img/info_icon.png" /></span><?php echo $_SESSION["language_json"]["Alert_Date"]; ?></div>
                </div>

                <div class="form-group">
                    <label class="control-label" for="interval_days_queries"><?php echo $_SESSION["language_json"]["Column_Query_Start"]; ?></label>  
                    <input id="interval_days_queries" name="interval_days_queries" placeholder="" class="form-control" value="0" type="number" size="50" min="0" oninvalid="this.setCustomValidity('<?php echo $_SESSION["language_json"]["Alert_Mandatory"]; ?>')" oninput="setCustomValidity('')" />   
                </div>
                   
                <div class="form-group">
                    <label class="control-label" for="interval_completion_queries"><?php echo $_SESSION["language_json"]["Queries_End"]; ?></label>  
                    <input type="text" id="interval_completion_queries" name="interval_completion_queries"  min="<?php echo date("Y-m-d"); ?>" class="form-control" />
                    <div id="interval_completion_queries_warning" class="up-arrow"><span class="info-icon"><img src="<?php echo URL; ?>inc/img/info_icon.png" /></span><?php echo $_SESSION["language_json"]["Altert_Final_Date"]; ?></div>
                </div>
                    
                <input type="hidden" name="type_cases" id="type_cases" value="<?php echo $this->type_cases; ?>" />


                <!-- Button to save form dd"ata -->
                <div id="aapvl_buttons">
                    <button id="save_queries" name="save_queries" value="Save" class="btn btn-default"><?php echo $_SESSION["language_json"]["Button_Save"]; ?></button>

                </div>

            </fieldset>
        </form>

    </div>    
</div>




