<?php
/**
 * Enquiry mail multi products table row
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/enquiry-mail/multi-product-enquiry/products-table-row.php.
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
global $quoteup;
?>
<tr>
    <?php do_action('quoteup_before_product_name', $data_obtained_from_form, $source); ?>
    <td class='product-name'>
        <a href='<?php echo $url; ?>'><?php echo $title; ?></a>
        <?php
        if (!empty($variationString)) {
            ?>
            <div style='margin-left:10px'>
                <?php echo $variationString; ?>
            </div>
            <?php
        }
        ?>
    </td>
    <?php do_action('quoteup_before_sku', $data_obtained_from_form, $source); ?>
    <td class='sku'>
        <?php echo $sku; ?>
    </td>
    <?php do_action('quoteup_before_qty', $data_obtained_from_form, $source); ?>
    <td class='qty'>
        <?php echo $element['quantity']; ?>
    </td>
    <?php
    // Check if 'Price' column disabled.
    if (!quoteupIsPriceColumnDisabled($form_data)) :
        do_action('quoteup_before_price', $data_obtained_from_form, $source);
        ?>
        <td class='price'>
        <?php
        $price = $quoteup->quoteupEnquiryCart->getTotalPrice($enable_price, $element);
        if ($source == 'admin') {
            $price = wc_price(floatval($element['price'])*$element['quantity']);
        }
        echo $price;
        // if ($enable_price == 'yes'  || $source == 'admin') {
        // } else {
            // echo '-';
        // }
        ?>
        </td>
        <?php
    endif;

    // Check if 'Remarks' column disabled.
    if (!quoteupIsRemarksColumnDisabled($form_data)) :
        do_action('quoteup_before_remarks', $data_obtained_from_form, $source);
        ?>
        <td class='remarks'>
            <?php echo $element[ 'remark' ]; ?> 
        </td>
        <?php
    endif;
    ?>
</tr>
