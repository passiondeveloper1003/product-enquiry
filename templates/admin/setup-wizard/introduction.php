<?php

if (!defined('ABSPATH')) {
    exit;
}
?>

<h1>
    <?php esc_html_e('Welcome to WISDM Product Enquiry Pro!', QUOTEUP_TEXT_DOMAIN); ?>
</h1>
<p><?php esc_html_e('Thank you for trusting WisdmLabs! This quick setup wizard will help you configure the basic settings. ', QUOTEUP_TEXT_DOMAIN); ?> <strong><?php esc_html_e('It’s completely optional and shouldn’t take longer than three minutes.', QUOTEUP_TEXT_DOMAIN); ?></strong> </p>
<p><?php esc_html_e('No time right now? If you don’t want to go through the wizard, you can skip and return to the WordPress dashboard. Come back anytime if you change your mind!', QUOTEUP_TEXT_DOMAIN); ?></p>
<p class="wc-setup-actions step">
    <a href="<?php echo esc_url($wizard_handler->get_next_step_link()); ?>" class="button-primary button button-large button-next"><?php esc_html_e('Let\'s Go!', QUOTEUP_TEXT_DOMAIN); ?></a>
    <a href="<?php echo esc_url(admin_url('index.php')); ?>" class="button button-large"><?php esc_html_e('Not right now', QUOTEUP_TEXT_DOMAIN); ?></a>
</p>
