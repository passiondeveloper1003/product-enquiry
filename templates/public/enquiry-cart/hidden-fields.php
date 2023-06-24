<?php
/**
 * Hidden fields
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/enquiry-cart/hidden-fields.php.
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
<input type='hidden' name='mpe_ajax_nonce' id='mpe_ajax_nonce' value='<?php echo $ajax_nonce; ?>'>
<input type='hidden' name='wdmLocale' id='wdmLocale' value='<?php echo $currentLocale; ?>'>
<input type='hidden' name='submit_value' id='submit_value'>

<input type='hidden' name='site_url' id='site_url' value='<?php echo $url; ?>'>
<input type='hidden' name='tried' id='tried' value='yes' />