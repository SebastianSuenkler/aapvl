<div id="aapvl_content" class="container-fluid">
    <h3><?php echo $_SESSION["language_json"]["Edit_Case_URLs"]?><a href="../../../<?php echo $this->case_type; ?>/show/<?php echo $this->fk_cases; ?>"> <?php echo $this->id_cases; ?></a></h3>
    <div class="container-fluid aapvl-form-container">
        <form class="form-horizontal aapvl-form" action="../../update/<?php echo $this->case_type; ?>/<?php echo $this->fk_cases; ?>/<?php echo $this->pk_urls; ?>" method='post'>
            <fieldset>

                <div class="form-group">
                    <label class="control-label" for="id_urls"><?php echo $_SESSION["language_json"]["ID_URLs"]; ?></label>  
                    <input id="id_urls" name="id_urls" placeholder="" class="form-control" type="text" value='<?php echo $this->urls_values->id_urls; ?>' required oninvalid="this.setCustomValidity('<?php echo $_SESSION["language_json"]["Alert_Mandatory"]; ?>')" oninput="setCustomValidity('')" disabled />

                </div>


                <div class="form-group">
                    <label for="elements_urls"><?php echo $_SESSION["language_json"]["Textarea_URLs"]; ?></label>
                    <textarea class="form-control" rows="15" id="elements_urls" name="elements_urls" required oninvalid="this.setCustomValidity('<?php echo $_SESSION["language_json"]["Alert_Mandatory"]; ?>')" oninput="setCustomValidity('')" placeholder='' disabled><?php echo $this->elements_urls; ?></textarea>
                </div>

                <div class="form-group">
                    <label class="control-label" for="date_urls"><?php echo $_SESSION["language_json"]["Column_Start_URLs"]; ?></label>  
                    <input type="text" id="date_urls" name="date_urls" class="form-control" required value="<?php echo $this->urls_values->date_urls; ?>" min="<?php echo date("Y-m-d"); ?>" oninvalid="this.setCustomValidity('<?php echo $_SESSION["language_json"]["Alert_Mandatory"]; ?>')" oninput="setCustomValidity('')" disabled />
                    <div id="date_urls_warning" class="up-arrow"><span class="info-icon"><img src="<?php echo URL; ?>inc/img/info_icon.png" /></span><?php echo $_SESSION["language_json"]["Alert_Date"]; ?></div>
                </div>

                <div class="form-group">
                    <label class="control-label" for="interval_days_urls"><?php echo $_SESSION["language_json"]["Column_URLs_Days"]; ?></label>  
                    <input id="interval_days_urls" name="interval_days_urls" placeholder="" class="form-control" value="<?php echo $this->urls_values->interval_days_urls; ?>" type="number" size="50" min="0" oninvalid="this.setCustomValidity('<?php echo $_SESSION["language_json"]["Alert_Mandatory"]; ?>')" oninput="setCustomValidity('')" disabled />

                </div>


                <div class="form-group">
                    <label class="control-label" for="interval_completion_urls"><?php echo $_SESSION["language_json"]["Column_URLs_Days_End"]; ?></label>  
                    <input type="text" id="interval_completion_urls" name="interval_completion_urls" value="<?php echo $this->urls_values->interval_completion_urls; ?>"   min="<?php echo date("Y-m-d"); ?>" class="form-control" disabled />
                    <div id="interval_completion_urls_warning" class="up-arrow"><span class="info-icon"><img src="<?php echo URL; ?>inc/img/info_icon.png" /></span><?php echo $_SESSION["language_json"]["Altert_Final_Date"]; ?></div>
                </div>

                <!-- Button to save form dd"ata -->
                <div id="aapvl_buttons">

                    <button id="delete_urls" name="delete_urls" value="Delete" class="btn btn-default" onclick="var result = confirm('<?php echo $_SESSION["language_json"]["Alert_Delete"]; ?>');
                            if (result) {
                                window.location.href = '../../delete/<?php echo $this->fk_cases; ?>/<?php echo $this->pk_urls; ?>/<?php echo $this->case_type; ?>'
                            }
                            return false;"><?php echo $_SESSION["language_json"]["Button_Delete"]; ?></button>
                    <button id="edit_urls" name="edit_urls" value="Edit" class="btn btn-default" onclick="return false;"><?php echo $_SESSION["language_json"]["Button_Edit"]; ?></button>
                    <button id="save_urls" name="save_urls" value="Save" class="btn btn-default" disabled><?php echo $_SESSION["language_json"]["Button_Save"]; ?></button>

                </div>


            </fieldset>
        </form>

    </div>    
</div>




