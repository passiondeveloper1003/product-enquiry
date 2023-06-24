jQuery(window).on("load", function() {
    /**
     * Function to check if rating is set and is greater than 0
     * @param  object currentElement object reference of current element
     */
     function clearStatus(currentElement)
     {
        if(currentElement.attr('aria-valuenow') <= 0  || currentElement.attr('aria-valuenow') == '') {
            currentElement.prev('.rateit-reset').hide();
        }else {
            currentElement.prev('.rateit-reset').show();
        }
    }

    /**
     * This function decides to show or hide clear on page load
     */
     jQuery('.rateit-range').each(function(){
        clearStatus(jQuery(this));
    })

    //Hide or show clear for ratings
    jQuery('.rateit-range').hover(function() {
        jQuery( this ).prev('.rateit-reset').hide();
    }, function() {
        clearStatus(jQuery(this));
    })

    /**
     * Hide 'clear' button on click.
     *
     * Trigger 'change' event on Rating hidden field, so that conditional logic
     * of other fields which (other fields) are dependent on Rating field will
     * work. (For example, 'Message' field will be displayed if Rating is more
     * than 4.)
     */
     jQuery('.rateit-reset').click(function(){
        let $resetButton        = jQuery(this),
            $ratingHiddenField  = $resetButton.closest('div[id^=Rating_].form-group').find('input:hidden[id^=Rating_]');
        $resetButton.hide();
        $ratingHiddenField.val('');
        $ratingHiddenField.change();
    })
 });
