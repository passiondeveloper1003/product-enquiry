(function($){

	$(document).ready(function(){
        // Trigger WooCommerce Tooltips. This is used to trigger tooltips added by function \wc_help_tip
        var tiptip_args = {
            'attribute': 'data-tip',
            'fadeIn': 50,
            'fadeOut': 50,
            'delay': 200,
            'defaultPosition': "bottom"
        };
        jQuery('.tips, .help_tip, .woocommerce-help-tip').tipTip(tiptip_args);

        if($('#wdm-quoteup-enquiry-fields-modal').length >= 1 && typeof quoteup_modal != "undefined" && typeof quoteup_modal.show != "undefined" && quoteup_modal.show === "yes")
        {
           $('#wdm-quoteup-enquiry-fields-modal').modal('show');
       }

       $('#btnEnquiryFieldsStgsSave').click(function(){
           var form_data = $("#enquiry-fields-form").serialize();
           var data = {
            'action':'quoteup_save_enq_stg',
            'form_data': form_data,
            'post_id':quoteup_modal.post_id
        };
        $(this).text(quoteup_modal.savingText);

        $.ajax({
            url : quoteup_modal.ajaxurl,
            data:data,
            type:'POST',
    //             beforeSend: function( ){
				// 	$(this).text(quoteup_modal.savingText);
				// },
                success:function(response){
                	// if(response == 1)
                	// {
                		// $('#wdm-quoteup-enquiry-fields-modal').text(quoteup_modal.savedText);
                		// setTimeout(function(){
                         $('#wdm-quoteup-enquiry-fields-modal').modal('hide');
                		// }, 2000);

                	// }
                },
                error: function() {
         			// $(this).text(quoteup_modal.errText);
            // 		setTimeout(function(){
              $('#wdm-quoteup-enquiry-fields-modal').modal('hide');
            		// }, 2000);
                }
            });

        return false;
    });

   });

}(jQuery));
