<div class="wrap clearfix">
    <h2><?php _e('Settings', 'fundamentet-api'); ?></h2>
    <h3><?php _e('Label', 'fundamentet-api'); ?></h3>
    <form action="" method="post" enctype="multipart/form-data">
        <fieldset>
            <label><?php _e('Url'); ?></label>
            <input type="text" name="url"
                id="value" value="<?php echo $settings['url']; ?>" />
        </fieldset>

        <fieldset>
            <label><?php _e('Key'); ?></label>
            <input type="text" name="key"
                id="value" value="<?php echo $settings['key']; ?>" />
        </fieldset>

        <fieldset>
            <label><?php _e('Secret'); ?></label>
            <input type="text" name="secret"
                id="value" value="<?php echo $settings['secret']; ?>" />
        </fieldset>


        <fieldset>
            <label><?php _e('Use proxy'); ?></label>
            <input type="checkbox" id="use_proxy" name="use_proxy" value="1"
                <?php echo $settings['use_proxy'] ? 'checked': '' ?> />
        </fieldset>

        <fieldset>
            <label><?php _e('Proxy url'); ?></label>
            <input type="text" name="proxy_url"
                id="proxy_url" value="<?php echo $settings['proxy_url']; ?>" />
        </fieldset>

        <fieldset>
            <label><?php _e('Proxy port'); ?></label>
            <input type="text" name="proxy_port"
                id="proxy_port" value="<?php echo $settings['proxy_port']; ?>" />
        </fieldset>

        <?php wp_nonce_field('fundamentet-api-settings', 'nonce'); ?>
        <?php submit_button(); ?>
    </form>
</div>
