<form method="post" action="<?php echo admin_url( 'options-general.php?page=' . $this->settingsPage ); ?>">
    <?php
    wp_nonce_field( $this->adminNonce, $this->adminNonce );

    $user_settings = get_option( $this->userSettings );
    $user_settings = (!is_array($user_settings)) ? array() : $user_settings;

    if ( $_GET['page'] == $this->settingsPage ){
        ?>
        <table class="form-table">
            <thead>
                <th>Shortcode</th>
                <th colspan="3">Action</th>
            </thead>
            <?php
            $assets_array = $this->assets_array;
            foreach ($assets_array as $shortcode_tag => $shortcode_name) {
                $shortcode_value_default = " checked='checked'";
                $shortcode_value_disable = '';
                $shortcode_value_force = '';
                if (count($user_settings) > 0){
                    $shortcode_value_default = ($user_settings[$shortcode_tag] == '') ? " checked='checked'" : "";
                    $shortcode_value_disable = ($user_settings[$shortcode_tag] == 'disable') ? " checked='checked'" : "";
                    $shortcode_value_force = ($user_settings[$shortcode_tag] == 'force') ? " checked='checked'" : "";
                }
                echo "
                    <tr>
                        <td><label for='VODServer'>$shortcode_tag</label></td>
                        <td width='50'><input type='radio' name='op_tag[$shortcode_tag]' value=''$shortcode_value_default>Default</td>
                        <td width='50'><input type='radio' name='op_tag[$shortcode_tag]' value='disable'$shortcode_value_disable>Disable</td>
                        <td width='50'><input type='radio' name='op_tag[$shortcode_tag]' value='force'$shortcode_value_force>Force</td>
                    </tr>
                ";
            }
            unset($assets_array);
            ?>
        </table>
        <p class="submit" style="clear: both;">
            <input type="submit" name="Submit"  class="button-primary" value="Update Settings" />
            <input type="hidden" name="op-tag-settings-submit" value="Y" />
        </p>

        <?php
    }
    ?>
</form>