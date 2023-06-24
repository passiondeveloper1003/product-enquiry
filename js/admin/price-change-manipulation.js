/**
 * Calculate and return the change (difference) between the old price and new price in percentage.
 */
function quoteupReturnOldNewPriceChangeInPerc(oldPrice, newPrice)
{
    let percentageChange = 0;

    if (oldPrice != newPrice) {
        percentageChange = 100 - (newPrice/oldPrice) * 100;
    }

    return percentageChange.toFixed(2);
}

/**
 * Return the approtiate class based on the change (difference) in the the old price and new price.
 */
function quoteupReturnClassForCostChange(percentageChange)
{
    let classAttr = 'no-cost-change';
    if (percentageChange > 0) {
        classAttr = 'positive-cost-change';
    } else if (percentageChange < 0) {
        classAttr = 'negative-cost-change';
    }

    return classAttr;
}

/**
 * Update entire quotation total price.
 */
function quoteupAlterGrandTotalPriceChange(grandTotalOgPrice, grandTotalNewPrice)
{
    let percentageChange = quoteupReturnOldNewPriceChangeInPerc(grandTotalOgPrice, grandTotalNewPrice);
    let classAttr        = quoteupReturnClassForCostChange(percentageChange);

    // Og Price
    jQuery('#amount_total .quote-final-og-total s').html(quoteupFormatPrice(grandTotalOgPrice));
    jQuery('#amount_total .quote-final-og-total').removeClass("positive-cost-change negative-cost-change no-cost-change").addClass(classAttr);

    // New Price
    jQuery('#database-amount').data('old-value', grandTotalOgPrice);
    jQuery('#database-amount').val(grandTotalNewPrice);
    jQuery('#amount_total .quote-final-new-total').html(quoteupFormatPrice(grandTotalNewPrice));

    // Percentage change
    jQuery('#amount_total .percentage-diff').html("(" + percentageChange + "%)");
    jQuery('#amount_total .percentage-diff').removeClass("positive-cost-change negative-cost-change no-cost-change").addClass(classAttr);
}

/**
 * Update per product total price.
 */
function quoteupAlterProductTotalPriceChange(totalOgPrice, totalNewprice, rowNumber)
{
    let percentageChange = quoteupReturnOldNewPriceChangeInPerc(totalOgPrice, totalNewprice);
    let classAttr        = quoteupReturnClassForCostChange(percentageChange);

    // Og Price
    jQuery('#content-cost-' + rowNumber + ' .item-content-og-cost s').html(quoteupFormatPrice(totalOgPrice));
    jQuery('#content-cost-' + rowNumber + ' .item-content-og-cost').removeClass("positive-cost-change negative-cost-change no-cost-change").addClass(classAttr);

    // New Price
    jQuery('#content-amount-'+rowNumber).data('old-value', totalOgPrice);
    jQuery('#content-amount-'+rowNumber).val(totalNewprice);
    jQuery('#content-cost-' + rowNumber + ' .item-content-new-cost').html(quoteupFormatPrice(totalNewprice));

    // Percentage change
    jQuery('#content-cost-' + rowNumber + ' .percentage-diff').html("(" + percentageChange + "%)");
    jQuery('#content-cost-' + rowNumber + ' .percentage-diff').removeClass("positive-cost-change negative-cost-change no-cost-change").addClass(classAttr);
}
