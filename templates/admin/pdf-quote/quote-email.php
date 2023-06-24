<?php
$heading = __('QUOTATION', QUOTEUP_TEXT_DOMAIN);
do_action('woocommerce_email_header', $heading, null);
?>
        <div id="message">
            <p><?php echo $args['quote_message']; ?></p><br><br>
        </div>
        <div id="head">
            <h2 class="quote-heading">
                <?php _e('Quote Request', QUOTEUP_TEXT_DOMAIN); ?> #<?php echo "$enquiry_id"; ?>
            </h2>
        </div> <!-- #head ends here -->
        <div id="Enquiry">
            <?php
                quoteupGetAdminTemplatePart('pdf-quote/quote-table', '', $args);
                quoteupGetAdminTemplatePart('pdf-quote/tax-shipping-note', '', $args);
            ?>
        </div> <!-- #Enquiry ends here -->
