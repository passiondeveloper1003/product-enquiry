<?php
/**
 * Email Address Field
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/approval-rejection/email-address-field.php.
 *
 * HOWEVER, on occasion QuoteUp will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author  WisdmLabs
 * @version 6.1.0
 */
?>
<div class="enquiry-email-address">
    <p>
        <strong>
            <?php _e('Please provide your email address associated with the quote:', QUOTEUP_TEXT_DOMAIN) ?>
        </strong>
    </p>
    <form action="" method="GET">
        <?php
        $currentPermalinkStructure = get_option('permalink_structure', '');
        if (empty($currentPermalinkStructure)) {
            ?>
            <input type="hidden" name="page_id" value="<?php echo $_GET[ 'page_id' ] ?>" />
            <?php
        }
        if (quoteupIsWpmlActive() && isset($_GET['lang'])) {
            ?>
            <input type="hidden" name="lang" value="<?php echo $_GET[ 'lang' ] ?>" />
            <?php
        }
        ?>
        <input type="email" class="enquiry-email-address-textfield" name="enquiryEmail" placeholder="test_email@address.com" value=""/>
        <input type="hidden" name="quoteupHash" value="<?php echo $_GET[ 'quoteupHash' ] ?>" />
        <input type="submit" class="button enquiry-email-address-button" name="emailAddressSubmit" value="<?php _e('Submit', QUOTEUP_TEXT_DOMAIN) ?>">    
    </form>
</div>