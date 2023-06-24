<?php
$promotionImageDirUrl = QUOTEUP_PLUGIN_URL . '/images/promotion/';
?>

<div class="main-content">
    <div class="feature-section-wrapper">
        <!-- Compatibility with MailChimp section -->
        <div class="heading-subheading">
            <h1 class="pep-wn-head">
                <?php
                esc_html_e('Compatibility with MailChimp', QUOTEUP_TEXT_DOMAIN);
                ?>
            </h1>
        </div>
        <div class="content">
            <div class="pep-wn-img-section">
                <a href="<?php echo esc_url($promotionImageDirUrl . 'mailchimp.png'); ?>" target="_blank" rel="noopener noreferrer">
                    <img  src="<?php echo esc_url($promotionImageDirUrl . 'mailchimp.png'); ?>" class="feature-image">
                </a>
            </div>
        </div>
        <!-- End of Compatibility with MailChimp section -->

        <!-- Price Difference section -->
        <div class="heading-subheading">
            <h1 class="pep-wn-head">
                <?php
                esc_html_e('Highlight your New Quoted Price', QUOTEUP_TEXT_DOMAIN);
                ?>
            </h1>
        </div>
        <div class="content">
            <div class="pep-wn-img-section">
                <a href="<?php echo esc_url($promotionImageDirUrl . 'price-difference.png'); ?>" target="_blank" rel="noopener noreferrer">
                    <img  src="<?php echo esc_url($promotionImageDirUrl . 'price-difference.png'); ?>" class="feature-image">
                </a>
            </div>
        </div>
        <!-- End of Price Difference section -->
    </div>

    <hr class="feature-seperator">
    <div class="pep-wn-section-other"> 
        <div class="content">
            <h3><?php esc_html_e('Features', QUOTEUP_TEXT_DOMAIN); ?></h3>
            <ul>
                <li>
                    <?php
                    esc_html_e('MailChimp plugin Compatibility.', QUOTEUP_TEXT_DOMAIN);
                    ?>
                    <div class="help-doc-link">
                        <a href="<?php echo esc_url('https://wisdmlabs.com/docs/article/wisdm-product-enquiry-pro/pep-user-guide/how-to-enable-mailchimp-subscription-checkbox-on-enquiry-form/'); ?>" target="_blank"><i><?php echo esc_html(__('See Documentation', QUOTEUP_TEXT_DOMAIN)); ?></i></a>
                    </div>
                </li>
                <li>
                    <?php
                    esc_html_e('Introduced Setup Wizard for new installation.', QUOTEUP_TEXT_DOMAIN);
                    ?>
                </li>
                <li>
                    <?php
                    esc_html_e('Ability to show/ hide the enquiry button for logged-in users or guest users or all users.', QUOTEUP_TEXT_DOMAIN);
                    ?>
                </li>
                <li>
                    <?php
                    esc_html_e('Send an email to the admin when the user rejects the quote.', QUOTEUP_TEXT_DOMAIN);
                    ?>
                </li>
                <li>
                    <?php
                    esc_html_e('Show difference between old and new prices on quote edit page, my account page and in quote email.', QUOTEUP_TEXT_DOMAIN);
                    ?>
                </li>
                <li>
                    <?php
                    esc_html_e('Global setting to hide prices for all products.', QUOTEUP_TEXT_DOMAIN);
                    ?>
                </li>
                <li>
                    <?php
                    esc_html_e('Global setting to hide Add to cart button for all products.', QUOTEUP_TEXT_DOMAIN);
                    ?>
                </li>
                <li>
                    <?php
                    esc_html_e('Setting for \'Return to Shop\' button shown on the Enquiry Cart page.', QUOTEUP_TEXT_DOMAIN);
                    ?>
                </li>
                <li>
                    <?php
                    esc_html_e('Ability to add/ remove text in Quote PDF footer or Quote Email footer.', QUOTEUP_TEXT_DOMAIN);
                    ?>
                </li>
            </ul>
            <h3><?php esc_html_e('Bug Fixes', QUOTEUP_TEXT_DOMAIN); ?></h3>
            <ul>
                <li>
                    <?php
                    esc_html_e('PEP Mini Cart UI issue in Safari.', QUOTEUP_TEXT_DOMAIN);
                    ?>
                </li>
                <li>
                    <?php
                    esc_html_e('The total column is not showing the price on the enquiry list page.', QUOTEUP_TEXT_DOMAIN);
                    ?>
                </li>
                <li>
                    <?php
                    esc_html_e('\'Please wait\' text is not translatable on custom form.', QUOTEUP_TEXT_DOMAIN);
                    ?>
                </li>
            </ul>

            <h3><?php esc_html_e('Tweaks', QUOTEUP_TEXT_DOMAIN); ?></h3>
            <ul>
                <li>
                    <?php
                    esc_html_e('Changed the UI of the settings, \'Disable Price Column\' and \'Disable Expected Price or Remarks Column\'.', QUOTEUP_TEXT_DOMAIN);
                    ?>
                </li>
                <li>
                    <?php
                    esc_html_e('Improved the UI for the Customer Data section on the Enquiry/ Quote Edit page.', QUOTEUP_TEXT_DOMAIN);
                    ?>
                </li>
                <li>
                    <?php
                    esc_html_e('Show labels to all the custom form fields.', QUOTEUP_TEXT_DOMAIN);
                    ?>
                    <div class="help-doc-link">
                        <a href="<?php echo esc_url('https://wisdmlabs.com/docs/article/wisdm-product-enquiry-pro/pep-tips-and-tricks/how-to-add-labels-in-the-custom-form/'); ?>" target="_blank"><i><?php echo esc_html(__('See Documentation', QUOTEUP_TEXT_DOMAIN)); ?></i></a>
                    </div>
                </li>
            </ul>
        </div>
    </div>
    <div class="pep-cta">
        <a href="https://wisdmlabs.com/contact-us/#support" class="button" target="_blank"><?php esc_html_e('Support', QUOTEUP_TEXT_DOMAIN); ?></a>
        <a href="https://wisdmlabs.com/docs/product/wisdm-product-enquiry-pro/" class="button" target="_blank"><?php esc_html_e('Docs', QUOTEUP_TEXT_DOMAIN); ?></a>
        <a href="https://wisdmlabs.com/docs/article/wisdm-product-enquiry-pro/pep-changelog/changelog-pep/" class="button" target="_blank"><?php esc_html_e('Changelog', QUOTEUP_TEXT_DOMAIN); ?></a>
    </div>
</div>
