<?php
/**
 * Modal dialog
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/enquiry-modal/modal-dialog.php.
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

/**
 * Before enquiry modal dialog HTML.
 *
 * @param  array  $args  Array containing data required to fill in the
 *                       enquiry modal.
 */
do_action('quoteup_before_wdm_modal', $args);
?>
<div class="wdm-modal wdm-fade" id="wdm-quoteup-modal-<?php echo $prod_id ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display:none">
    <div class="wdm-modal-dialog">
        <?php
        /**
         * Before enquiry modal content.
         *
         * @param  array  $args  Array containing data required to fill in the
         *                       enquiry modal.
         */
        do_action('quoteup_before_wdm_modal_content', $args);
        ?>
        <div class="wdm-modal-content" <?php echo $color; ?> >
            <?php
            /**
             * quoteup_wdm_modal_content hook.
             *
             * @hooked enquiryModalFormHeader - 10 (outputs Modal header)
             * @hooked enquiryModalFormBody - 20 (outputs modal body)
             * @hooked enquiryModalFormFooter - 30 (outputs modal footer)
             *
             * @param  array  $args  Array containing data required to fill in the
             *                       enquiry modal.
             */
            do_action('quoteup_wdm_modal_content', $args);
            ?>
        </div> <!--/modal-content-->
        <?php
        /**
         * After enquiry modal content.
         *
         * @param  array  $args  Array containing data required to fill in the
         *                       enquiry modal.
         */
        do_action('quoteup_after_wdm_modal_content', $args);
        ?>
    </div> <!--/modal-dialog-->
</div> <!--/modal-->
<!--/New modal-->
<?php
/**
 * After enquiry modal dialog HTML.
 *
 * @param  array  $args  Array containing data required to fill in the
 *                       enquiry modal.
 */

do_action('quoteup_after_wdm_modal', $args);

