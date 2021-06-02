<div id="aapvl_content" class="container-fluid">
<div id='aapvl_table' class='container-fluid' align='center'><!-- start div for site content end div tag is in footer template -->
    <h3><?php echo $_SESSION["language_json"]["Header_Admin_Panel"]; ?></h3>
    <table id='aapvl_table_config' class='display'>
        <thead>
            <tr>
                <th><?php echo $_SESSION["language_json"]["Admin_User_ID"]; ?></th>
                <th><?php echo $_SESSION["language_json"]["Admin_User_Name"]; ?></th>
                <th><?php echo $_SESSION["language_json"]["Admin_User_First_Name"]; ?></th>
                <th><?php echo $_SESSION["language_json"]["Admin_User_Last_Name"]; ?></th>
                <th><?php echo $_SESSION["language_json"]["Admin_User_Mail"]; ?></th>
                <th><?php echo $_SESSION["language_json"]["Admin_User_Symbol"]; ?></th>
                <th><?php echo $_SESSION["language_json"]["Admin_User_Options"]; ?></th>

            </tr>
        </thead>
        <tbody>
    <?php foreach ($this->users_from_db AS $user) { ?>

                <tr>
                  <td><?php echo $user->pk_users;?></td>
                  <td><?php echo $user->name_users; ?></td>
                  <td><?php echo $user->first_name_users; ?></td>
                  <td><?php echo $user->last_name_users; ?></td>
                  <td><?php echo $user->mail_users; ?></td>
                  <td><?php echo $user->symbol_users; ?></td>
                  <td>  <a href="<?php echo URL;?>admin/reset/<?php echo $user->pk_users; ?>" class="btn btn-default" role="button"><?php echo $_SESSION["language_json"]["Button_Reset_Password"]; ?></a>
                  <a href="<?php echo URL;?>admin/edit/<?php echo $user->pk_users; ?>" class="btn btn-default" role="button"><?php echo $_SESSION["language_json"]["Button_Edit_User"]; ?></a>
                  <button id="delete_user" name="delete_user" value="Delete" class="btn btn-default" onclick="var result = confirm('<?php echo $_SESSION["language_json"]["Alert_Delete_User"]; ?>');
                          if (result) {
                              window.location.href = 'delete/<?php echo $user->pk_users; ?>'
                          }
                          return false;"><?php echo $_SESSION["language_json"]["Button_Delete_User"]; ?></button>
                </td>
                </tr>
              <?php }
              ?>
        </tbody>
    </table>
</div>

<div id="aapvl_buttons">
    <a href="<?php echo URL;?>admin/add" class="btn btn-default" role="button"><?php echo $_SESSION["language_json"]["Button_Add_User"]; ?></a>
</div>

</div>
