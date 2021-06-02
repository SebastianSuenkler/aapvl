<div id="aapvl_content" class="container-fluid">
    <h3><?php echo $_SESSION["language_json"]["Add_Case"] ?></h3>
    <div class="container-fluid aapvl-form-container">
        <form class="form-horizontal aapvl-form" action="save" method='post'>
            <fieldset>

                <div class="form-group">
                    <label class="control-label" for="id_case02"><?php echo $_SESSION["language_json"]["Column_Case_ID"]; ?></label>

                    <input id="id_case02" name="id_case02" placeholder="" class="form-control" value="" required type="text" size="50" oninvalid="this.setCustomValidity('<?php echo $_SESSION["language_json"]["Alert_Mandatory"]; ?>')" oninput="setCustomValidity('')" />

                </div>

                <div class="form-group">
                    <div class="col-md-12">
                        <label class="control-label" for="sub"><?php echo $_SESSION["language_json"]["Column_Case_Type"]; ?></label>
                    </div>

                    <div class="col-md-12">
                        <select class="selectpicker" name="select_sub_type">
                            <?php
                            $subs_json = process_json(open_external_file_to_read("/user/cases/" . $_SESSION["language_json"]["Language"] . "cases02_config.json"));
                            $subs_array = $subs_json["Config"];
                            foreach ($subs_array AS $subs) {
                                $id = $subs["ID"];
                                $name = $subs["Name"];
                                echo "<option value='$id'>" . $name . "</option>";
                            }
                            ?>
                        </select>
                    </div></div>

                <!--
                <div class="form-group">
                    <label class="control-label" for="input"><?php echo $_SESSION["language_json"]["Column_Date"]; ?></label>

                       <input type="text" id="date_case02" name="date_case02" class="form-control" value="<?php echo date("Y-m-d"); ?>"  min="<?php echo date("Y-m-d"); ?>" required oninvalid="this.setCustomValidity('<?php echo $_SESSION["language_json"]["Alert_Mandatory"]; ?>')" oninput="setCustomValidity('')" />
                </div>
                -->

                <div class="form-group">
                    <div class="col-md-12">
                        <label class="control-label" for="risk_decision"><?php echo $_SESSION["language_json"]["Risk Decision"]; ?></label>   </div>
                    <div class="col-md-12">
                      <select class="selectpicker" name="risk_decision">
                          <?php
                          $subs_json = process_json(open_external_file_to_read("/user/cases/" . $_SESSION["language_json"]["Language"] . "cases02_config.json"));
                          $subs_array = $subs_json["Risk Decision"];
                          foreach ($subs_array AS $subs) {
                              $id = $subs["ID"];
                              $name = $subs["Name"];
                              echo "<option value='$id'>" . $name . "</option>";
                          }
                          ?>
                      </select>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-12">
                        <label class="control-label" for="classification"><?php echo $_SESSION["language_json"]["Classification"]; ?></label></div>
                    <div class="col-md-12">
                      <select class="selectpicker" name="classification">
                          <?php
                          $subs_json = process_json(open_external_file_to_read("/user/cases/" . $_SESSION["language_json"]["Language"] . "cases02_config.json"));
                          $subs_array = $subs_json["Classification"];
                          foreach ($subs_array AS $subs) {
                              $id = $subs["ID"];
                              $name = $subs["Name"];
                              echo "<option value='$id'>" . $name . "</option>";
                          }
                          ?>
                      </select>
                     </div>
                </div>



                <div class="form-group">
                    <label for="comment_case02"><?php echo $_SESSION["language_json"]["Column_Comment"]; ?></label>
                    <textarea class="form-control" rows="5" id="comment_case02" name="comment_case02"></textarea>
                </div>


                <div class="form-group">
                    <label class="control-label" for="risk_points"><?php echo $_SESSION["language_json"]["Risk_Points"]; ?></label>

                    <input id="risk_points" name="risk_points" placeholder="" class="form-control" value="0" type="text" size="50" oninvalid="this.setCustomValidity('<?php echo $_SESSION["language_json"]["Alert_Mandatory"]; ?>')" oninput="setCustomValidity('')" />

                </div>


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

                    <button id="save_case02" name="save_case02" value="Save" class="btn btn-default"><?php echo $_SESSION["language_json"]["Button_Save"]; ?></button>

                </div>

            </fieldset>
        </form>

    </div>
</div>
