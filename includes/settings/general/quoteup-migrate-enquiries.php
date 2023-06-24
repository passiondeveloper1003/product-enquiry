<?php

namespace Includes\Settings;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/*
 * This function is used to display Migrate Enquiries Section
 */
function migrateEnquiriesSection()
{
    $migrated = get_option('wdm_enquiries_migrated');
    global $wpdb;
    $enquiry_tbl = $wpdb->prefix.'enquiry_details';
    if ($wpdb->get_var("SHOW TABLES LIKE '$enquiry_tbl'") == $enquiry_tbl && $migrated != 1) {
        ?>
        <fieldset>
            <?php echo '<legend>'.__('Migrate Enquiries', QUOTEUP_TEXT_DOMAIN).'</legend>';
        ?>
            <div class="fd">
                <input type="hidden" id="migratenonce" value="<?php echo wp_create_nonce('migratenonce');
        ?>">
                <input type='button' value='<?php echo 'Migrate Enquiries' ?>' name='btnMigrate' id='btnMigrate' class='button-primary'>
                <em class="wdm-migrate-txt"> <?php _e('This update includes database architectural changes. We strongly recommend to migrate the previous enquiries. Previous enquiries will be displayed in enquiry details table only after migration. No need to migrate enquiries if already migrated', QUOTEUP_TEXT_DOMAIN);
        ?>
                </em>
                <div class="wdm-migrate-loader-wrap"><span class="wdm-migrate-loader-txt"><?php _e('Please wait', QUOTEUP_TEXT_DOMAIN);
        ?>.. </span><img class="wdm-migrate-loader" src="<?php echo QUOTEUP_PLUGIN_URL.'/images/3bar-loader.gif';
        ?>" alt="ajax loader" /></div>
            </div>
        </fieldset>
        <?php
    }
}

migrateEnquiriesSection();
