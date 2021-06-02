<div id="aapvl_content" class="container-fluid">
    <?php
    if (empty($this->queries_cases) AND empty($this->urls_cases)) {
        echo '<a href="#aapvl_table_queries" class="jump_label"><div id="aapvl_error">' . $_SESSION["language_json"]["Missing_Queries_URLs"] . '</div></a>';
    }
    ?>
    <h3><?php echo $_SESSION["language_json"]["Show_Case"] . " " . $this->id_cases ?></h3>
    <div class="container-fluid aapvl-form-container">
        <form class="form-horizontal aapvl-form" action="../update/<?php echo $this->pk_cases; ?>" method='post'>
            <fieldset>

                <div class="form-group">
                    <label class="control-label" for="id_case04"><?php echo $_SESSION["language_json"]["Column_Case_ID"]; ?></label>

                    <input id="id_case04" name="id_case04" placeholder="" class="form-control" value="<?php echo $this->db_values->id_cases; ?>" required type="text" size="50" oninvalid="this.setCustomValidity('<?php echo $_SESSION["language_json"]["Alert_Mandatory"]; ?>')" oninput="setCustomValidity('')" disabled />

                </div>

                <div class="form-group">
                    <label class="control-label" for="input"><?php echo $_SESSION["language_json"]["Column_Date"]; ?></label>

                    <input type="text" id="date_case04" name="date_case04" class="form-control" value="<?php echo $this->db_values->date_cases; ?>"  min="<?php echo date("Y-m-d"); ?>" required oninvalid="this.setCustomValidity('<?php echo $_SESSION["language_json"]["Alert_Mandatory"]; ?>')" oninput="setCustomValidity('')" disabled />

                </div>

                <div class="form-group">
                    <label for="comment_case04"><?php echo $_SESSION["language_json"]["Column_Comment"]; ?></label>
                    <textarea class="form-control" rows="5" id="comment_case04" name="comment_case04" disabled><?php echo $this->db_values->comment_cases; ?></textarea>
                </div>


                <div class="form-group">
                    <div class="col-md-12">
                        <label class="control-label" for="fk_users"><?php echo $_SESSION["language_json"]["Column_Employee"]; ?></label>   </div>



                    <div class="col-md-12">
                        <select class="selectpicker" name="fk_users" disabled>
                            <?php
                            foreach ($this->users_from_db AS $user) {
                                if ($user->pk_users == $this->db_values->fk_users) {
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

                    <button id="delete_case04" name="delete_case04" value="Delete" class="btn btn-default" onclick="var result = confirm('<?php echo $_SESSION["language_json"]["Alert_Delete"]; ?>');
                            if (result) {
                                window.location.href = '../delete/<?php echo $this->pk_cases; ?>'
                            }
                            return false;"><?php echo $_SESSION["language_json"]["Button_Delete"]; ?></button>
                    <button id="edit_case04" name="edit_case04" value="Edit" class="btn btn-default" onclick="return false;"><?php echo $_SESSION["language_json"]["Button_Edit"]; ?></button>
                    <button id="save_case04" name="save_case04" value="Save" class="btn btn-default" disabled><?php echo $_SESSION["language_json"]["Button_Save"]; ?></button>

                </div>

<?php if($this->judgement_table) { ?>

                <div id="aapvl_buttons">
                     <a id="judgement_case" href="<?php echo URL; ?>case04/judgement/<?php echo $this->pk_cases; ?>" class="btn btn-default" role="button" target="_blank"><?php echo $_SESSION["language_json"]["Button_Assessment"]; ?></a>
                </div>

<?php } ?>                     

            </fieldset>
        </form>


    </div>

    <div id="aapvl_buttons">
        <a href="<?php echo URL; ?>case04/add" class="btn btn-default" role="button"><?php echo $_SESSION["language_json"]["Button_New"]; ?></a>
    </div>
</div>