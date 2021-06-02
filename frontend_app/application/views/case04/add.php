<div id="aapvl_content" class="container-fluid">
    <h3><?php echo $_SESSION["language_json"]["Add_Case"] ?></h3>
    <div class="container-fluid aapvl-form-container">
        <form class="form-horizontal aapvl-form" action="save" method='post'>
            <fieldset>

                <div class="form-group">
                    <label class="control-label" for="id_case04"><?php echo $_SESSION["language_json"]["Column_Case_ID"]; ?></label>  

                    <input id="id_case04" name="id_case04" placeholder="" class="form-control" value="" required type="text" size="50" oninvalid="this.setCustomValidity('<?php echo $_SESSION["language_json"]["Alert_Mandatory"]; ?>')" oninput="setCustomValidity('')" />

                </div>



                <!--
                <div class="form-group">
                    <label class="control-label" for="input"><?php echo $_SESSION["language_json"]["Column_Date"]; ?></label>  

                       <input type="text" id="date_case04" name="date_case04" class="form-control" value="<?php echo date("Y-m-d"); ?>"  min="<?php echo date("Y-m-d"); ?>" required oninvalid="this.setCustomValidity('<?php echo $_SESSION["language_json"]["Alert_Mandatory"]; ?>')" oninput="setCustomValidity('')" />
                </div>
                -->

                <div class="form-group">
                    <label for="comment_case04"><?php echo $_SESSION["language_json"]["Column_Comment"]; ?></label>
                    <textarea class="form-control" rows="5" id="comment_case04" name="comment_case04"></textarea>
                </div>

                <!--
                <div class="form-group">          
                    <label class="control-label" for="input"><?php echo $_SESSION["language_json"]["Column_Options"]; ?></label>  
                    <br/>
                    <label class="checkbox-inline"><input type="checkbox" name="screenshots" value="1">&nbsp;<?php echo $_SESSION["language_json"]["Column_Option_Screenshot"]; ?></label>
                </div>
                -->

                <div class="form-group">
                    <div class="col-md-12">
                        <label class="control-label" for="fk_users"><?php echo $_SESSION["language_json"]["Column_Employee"]; ?></label>   </div>
                    <div class="col-md-12">
                        <select class="selectpicker" name="fk_users">
<?php
foreach ($this->users_from_db AS $user) {
    if ($user->pk_users == $this->fk_users) {
        $selected = "selected";
    } else {
        $selected = "";
    }

    echo "<option $selected value='$user->pk_users'>" . $user->symbol_users . " (" . $user->first_name_users . " " . $user->last_name_users . ")" . "</option>";
}
?>
                        </select> </div>
                </div>



                <!-- Button to save form dd"ata -->
                <div id="aapvl_buttons">

                    <button id="save_case04" name="save_case04" value="Save" class="btn btn-default"><?php echo $_SESSION["language_json"]["Button_Save"]; ?></button>

                </div>

            </fieldset>
        </form>

    </div>    
</div>




