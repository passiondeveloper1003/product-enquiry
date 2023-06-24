jQuery(document).ready(function () {
    var $wdmCartCount = jQuery('#wdm-cart-count');
    var displayBubble = $wdmCartCount.data('display-bubble');

	//To make the enquiry cart bubble draggable.
    $wdmCartCount.draggable({ containment: "body", scroll: false});

    if(document.cookie.indexOf("quoteup_wp_session=") >= 0 && 1 == Number(displayBubble)) {
        $wdmCartCount.css("display", "block");
    }
})
