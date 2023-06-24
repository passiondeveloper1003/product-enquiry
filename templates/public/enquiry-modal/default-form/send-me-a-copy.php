<?php
/**
 * send me a copy
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/enquiry-modal/default-form/send-me-a-copy.php.
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
<div class='ck form_input'>
    <label class='contact-cc-wrap' <?php echo $dialogue_text_color; ?> >
        <input type='checkbox' id='contact-cc'  name='cc' value='yes'  />
        <span class='contact-cc-txt'><?php _e('Send me a copy', QUOTEUP_TEXT_DOMAIN) ?>
        </span>
    </label>
</div>