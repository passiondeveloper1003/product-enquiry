<?php

if (!defined('ABSPATH')) {
    exit;
}

?>
<div class="wisdm-setup-done">
    <?php echo $setup_wizard->get_checked_image_html(); ?>
    <h1><?php esc_html_e('Your enquiry system setup is done!', QUOTEUP_TEXT_DOMAIN); ?></h1>
</div>
<div class="wisdm-setup-done-content">
    <p class="wc-setup-actions step">
        <?php
        if ($enable_quote) :
            ?>
                <a class="button button-primary" href="<?php echo esc_url($create_new_quote_url); ?>"><?php esc_html_e('Create a new quote', QUOTEUP_TEXT_DOMAIN); ?></a>
                <?php
        endif;
        ?>
        <a class="button" href="<?php echo esc_url($settings_url); ?>"><?php esc_html_e('More Settings', QUOTEUP_TEXT_DOMAIN); ?></a>
    </p>
</div>
