<?php
/**
 * Enquiry form footer
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/enquiry-modal/enquiry-form-footer.php.
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
if (isset($form_data[ 'show_powered_by_link' ]) && $form_data[ 'show_powered_by_link' ]) {
    $enable_opt = $form_data[ 'show_powered_by_link' ];
    ?>
    <div class="wdm-modal-footer">
        <a href='https://wisdmlabs.com/' class="wdm-poweredby">Powered by &copy; WisdmLabs
        </a>
    </div><!--/modal footer-->
    <?php
}
?>