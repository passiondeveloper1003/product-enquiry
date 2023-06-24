<?php
/**
 * Quote Rejection Message.
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/quote-rejected-mail/quote-rejection-message.php.
 *
 * HOWEVER, on occasion QuoteUp will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author  WisdmLabs
 * @version 6.5.0
 */

?>
<div>
    <p><?php echo wp_kses_post($args['rejected_message']); ?></p>
</div>