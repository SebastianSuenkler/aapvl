<div id="aapvl_content" class="container-fluid">
    <h3><?php echo $_SESSION["language_json"]["Admin_Heder_Pass"] ?></h3>
    <div class="container-fluid aapvl-form-container">
        <form class="form-horizontal aapvl-form" action="../pass/<?php echo $this->user->pk_users; ?>" method='post'>
            <fieldset>

                <div class="form-group">
                    <label class="control-label" for="name_users"><?php echo $_SESSION["language_json"]["Admin_User_Name"]; ?></label>

                    <input id="name_users" name="name_users" placeholder="" class="form-control" value="<?php echo $this->user->name_users; ?>" required type="text" size="50" oninvalid="this.setCustomValidity('<?php echo $_SESSION["language_json"]["Alert_Mandatory"]; ?>')" oninput="setCustomValidity('')" disabled />

                </div>

                <div class="form-group">
                    <label class="control-label" for="password_users"><?php echo $_SESSION["language_json"]["Admin_User_Pass"]; ?></label>

                    <input id="password_users" name="password_users" placeholder="" class="form-control" value="" required type="text" size="50" oninvalid="this.setCustomValidity('<?php echo $_SESSION["language_json"]["Alert_Mandatory"]; ?>')" oninput="setCustomValidity('')" />

                </div>




                <div id="aapvl_buttons">

                    <button id="save_user_pass" name="save_user_pass" value="Save" class="btn btn-default"><?php echo $_SESSION["language_json"]["Button_Save"]; ?></button>

                </div>

            </fieldset>
        </form>

    </div>
</div>
