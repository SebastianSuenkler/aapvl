<div id="aapvl_content" class="container-fluid">
    <div class="container-fluid aapvl-form-container">
        <form class="form-horizontal aapvl-form" action="#" method='post'>
            <fieldset>

                <div class="form-group">
                    <label class="control-label" for="name">Name / Email</label>  

                    <input id="name_users" name="name_users" placeholder="" class="form-control" value="<?php if (isset($_SESSION["name_users"])) echo $_SESSION["name_users"]; ?>" required type="text" size="50" oninvalid="this.setCustomValidity('This field is required.')" oninput="setCustomValidity('')" />

                </div>
                <div class="form-group">
                    <label class="control-label" for="password">Password</label>  

                    <input id="password_users" name="password_users" placeholder="" class="form-control" required type="password" size="50" oninvalid="this.setCustomValidity('This field is required.')" oninput="setCustomValidity('')" />

                </div>


                <div class="form-group">
                    <div class="col-md-12">
                    <label class="control-label" for="language_json">Language</label>  
</div>
                    
                       <div class="col-md-12">
                    <select class="selectpicker" name="language_json">
                        <?php
                        $languages_json = process_json(open_external_file_to_read("/user/language/languages_list.json"));
                        foreach ($languages_json AS $languages_array) {
                            foreach ($languages_array AS $language) {
                                echo "<option value='$language.json'>" . $language . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div></div>


                <!-- Button to save form dd"ata -->
                <div class="form-group">
                    <label class="control-label" for="login_button"></label>

                    <button id="login_button" name="login_button" value="Login" class="btn btn-default">Login</button>

                </div>

            </fieldset>
        </form>

    </div>    
</div>




