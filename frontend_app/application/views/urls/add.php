<div id="aapvl_content" class="container-fluid">
    <h3><?php echo $_SESSION["language_json"]["Case_URLs"]?><a href="../../../../<?php echo $this->case_type; ?>/show/<?php echo $this->fk_cases; ?>"> <?php echo $this->id_cases; ?></a></h3>
    <div class="container-fluid aapvl-form-container">
        <form class="form-horizontal aapvl-form" action="../../../save/<?php echo $this->case_type; ?>/<?php echo $this->fk_cases; ?>" method='post'>
            <fieldset>

                <div class="form-group">
                    <label class="control-label" for="id_urls"><?php echo $_SESSION["language_json"]["ID_URLs"]; ?></label>  
                    <input id="id_urls" name="id_urls" placeholder="" class="form-control" type="text" required oninvalid="this.setCustomValidity('<?php echo $_SESSION["language_json"]["Alert_Mandatory"]; ?>')" oninput="setCustomValidity('')" />

                </div>


                <div class="form-group">
                    <label for="elements_urls"><?php echo $_SESSION["language_json"]["Textarea_URLs"]; ?></label>
                    <textarea class="form-control" rows="15" id="elements_urls" name="elements_urls" required oninvalid="this.setCustomValidity('<?php echo $_SESSION["language_json"]["Alert_Mandatory"]; ?>')" oninput="setCustomValidity('')" placeholder=''></textarea>
                </div>

                <div class="form-group">
                    <label class="control-label" for="date_urls"><?php echo $_SESSION["language_json"]["Column_Start_URLs"]; ?></label>  
                    <input type="text" id="date_urls" name="date_urls" class="form-control" required value="<?php echo date("Y-m-d"); ?>" min="<?php echo date("Y-m-d"); ?>" oninvalid="this.setCustomValidity('<?php echo $_SESSION["language_json"]["Alert_Mandatory"]; ?>')" oninput="setCustomValidity('')" />
                    <div id="date_urls_warning" class="up-arrow"><span class="info-icon"><img src="<?php echo URL; ?>inc/img/info_icon.png" /></span><?php echo $_SESSION["language_json"]["Alert_Date"]; ?></div>
                </div>

                <div class="form-group">
                    <label class="control-label" for="interval_days_urls"><?php echo $_SESSION["language_json"]["Column_URLs_Days"]; ?></label>  
                    <input id="interval_days_urls" name="interval_days_urls" placeholder="" class="form-control" value="0" type="number" size="50" min="0" oninvalid="this.setCustomValidity('<?php echo $_SESSION["language_json"]["Alert_Mandatory"]; ?>')" oninput="setCustomValidity('')" />

                </div>


                <div class="form-group">
                    <label class="control-label" for="interval_completion_urls"><?php echo $_SESSION["language_json"]["Column_URLs_Days_End"]; ?></label>  
                    <input type="text" id="interval_completion_urls" name="interval_completion_urls"  min="<?php echo date("Y-m-d"); ?>" class="form-control" />
                    <div id="interval_completion_urls_warning" class="up-arrow"><span class="info-icon"><img src="<?php echo URL; ?>inc/img/info_icon.png" /></span><?php echo $_SESSION["language_json"]["Altert_Final_Date"]; ?></div>
                </div>
                
                <input type="hidden" name="type_cases" id="type_cases" value="<?php echo $this->type_cases; ?>" />

                <!-- Button to save form dd"ata -->
                <div id="aapvl_buttons">

                    <button id="save_urls" name="save_urls" value="Save" class="btn btn-default"><?php echo $_SESSION["language_json"]["Button_Save"]; ?></button>

                </div>

            </fieldset>
        </form>

    </div>    
</div>




