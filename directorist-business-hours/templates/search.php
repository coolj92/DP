<?php
// prevent direct access to the file
defined('ABSPATH') || die('No direct script access allowed!');
?>
<div class="form-group open_now">
<label><?php echo esc_attr( $field_data['label'] ); ?></label>
    <div class="check-btn">
        <div class="btn-checkbox">
            <label>
                <input type="checkbox" name="open_now" value="open_now" <?php if (!empty($_GET['open_now']) && 'open_now' == $_GET['open_now']) {
                                                                            echo "checked='checked'";
                                                                        } ?>>
                <span><?php directorist_icon( 'far fa-clock' ); ?><?php echo esc_attr( $field_data['label'] ); ?> </span>
            </label>
        </div>
    </div>
</div>