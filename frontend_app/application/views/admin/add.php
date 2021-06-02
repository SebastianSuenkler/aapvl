<div id="aapvl_content" class="container-fluid">
    <h3><?php echo $_SESSION["language_json"]["Add_User"] ?></h3>
    <div class="container-fluid aapvl-form-container">
        <form class="form-horizontal aapvl-form" action="save" method='post'>
            <fieldset>

                <div class="form-group">
                    <label class="control-label" for="name_users"><?php echo $_SESSION["language_json"]["Admin_User_Name"]; ?></label>

                    <input id="name_users" name="name_users" placeholder="" class="form-control" value="" required type="text" size="50" oninvalid="this.setCustomValidity('<?php echo $_SESSION["language_json"]["Alert_Mandatory"]; ?>')" oninput="setCustomValidity('')" />

                </div>

                <div class="form-group">
                    <label class="control-label" for="password_users"><?php echo $_SESSION["language_json"]["Admin_User_Pass"]; ?></label>

                    <input id="password_users" name="password_users" placeholder="" class="form-control" value="" required type="text" size="50" oninvalid="this.setCustomValidity('<?php echo $_SESSION["language_json"]["Alert_Mandatory"]; ?>')" oninput="setCustomValidity('')" />

                </div>

                <div class="form-group">
                    <label class="control-label" for="first_name_users"><?php echo $_SESSION["language_json"]["Admin_User_First_Name"]; ?></label>

                    <input id="first_name_users" name="first_name_users" placeholder="" class="form-control" value="" required type="text" size="50" oninvalid="this.setCustomValidity('<?php echo $_SESSION["language_json"]["Alert_Mandatory"]; ?>')" oninput="setCustomValidity('')" />

                </div>

                <div class="form-group">
                    <label class="control-label" for="last_name_users"><?php echo $_SESSION["language_json"]["Admin_User_Last_Name"]; ?></label>

                    <input id="last_name_users" name="last_name_users" placeholder="" class="form-control" value="" required type="text" size="50" oninvalid="this.setCustomValidity('<?php echo $_SESSION["language_json"]["Alert_Mandatory"]; ?>')" oninput="setCustomValidity('')" />

                </div>

                <div class="form-group">
                    <label class="control-label" for="mail_users"><?php echo $_SESSION["language_json"]["Admin_User_Mail"]; ?></label>

                    <input id="mail_users" name="mail_users" placeholder="" class="form-control" value="" required type="text" size="50" oninvalid="this.setCustomValidity('<?php echo $_SESSION["language_json"]["Alert_Mandatory"]; ?>')" oninput="setCustomValidity('')" />

                </div>

                <div class="form-group">
                    <label class="control-label" for="symbol_users"><?php echo $_SESSION["language_json"]["Admin_User_Symbol"]; ?></label>

                    <input id="symbol_users" name="symbol_users" placeholder="" class="form-control" value="" required type="text" size="50" oninvalid="this.setCustomValidity('<?php echo $_SESSION["language_json"]["Alert_Mandatory"]; ?>')" oninput="setCustomValidity('')" />

                </div>


                <div id="aapvl_buttons">

                    <button id="save_user" name="save_user" value="Save" class="btn btn-default"><?php echo $_SESSION["language_json"]["Button_Save"]; ?></button>

                </div>

            </fieldset>
        </form>

    </div>
</div>
