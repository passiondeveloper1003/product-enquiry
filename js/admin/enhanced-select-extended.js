/**
 * This is a replica of WooCommerce's wc-enhanced-select.js. Here we are also sending language so that we get only language specfic products
 */
 /*global wc_enhanced_select_params */
 jQuery( function( $ ) {
    function getEnhancedSelectFormatString() {
        var formatString = {
            formatMatches: function( matches ) {
                if ( 1 === matches ) {
                    return wc_enhanced_select_params.i18n_matches_1;
                }

                return wc_enhanced_select_params.i18n_matches_n.replace( '%qty%', matches );
            },
            formatNoMatches: function() {
                return wc_enhanced_select_params.i18n_no_matches;
            },
            formatAjaxError: function() {
                return wc_enhanced_select_params.i18n_ajax_error;
            },
            formatInputTooShort: function( input, min ) {
                var number = min - input.length;

                if ( 1 === number ) {
                    return wc_enhanced_select_params.i18n_input_too_short_1;
                }

                return wc_enhanced_select_params.i18n_input_too_short_n.replace( '%qty%', number );
            },
            formatInputTooLong: function( input, max ) {
                var number = input.length - max;

                if ( 1 === number ) {
                    return wc_enhanced_select_params.i18n_input_too_long_1;
                }

                return wc_enhanced_select_params.i18n_input_too_long_n.replace( '%qty%', number );
            },
            formatSelectionTooBig: function( limit ) {
                if ( 1 === limit ) {
                    return wc_enhanced_select_params.i18n_selection_too_long_1;
                }

                return wc_enhanced_select_params.i18n_selection_too_long_n.replace( '%qty%', limit );
            },
            formatLoadMore: function() {
                return wc_enhanced_select_params.i18n_load_more;
            },
            formatSearching: function() {
                return wc_enhanced_select_params.i18n_searching;
            }
        };

        return formatString;
    }

    $( document.body )

    .on( 'wc-enhanced-select-init', function() {

            // Ajax product search box
            $( ':input.wc-product-search' ).filter( ':not(.enhanced)' ).each( function() {
                var select2_args = {
                    allowClear:  $( this ).data( 'allow_clear' ) ? true : false,
                    placeholder: $( this ).data( 'placeholder' ),
                    minimumInputLength: $( this ).data( 'minimum_input_length' ) ? $( this ).data( 'minimum_input_length' ) : '3',
                    escapeMarkup: function( m ) {
                        return m;
                    },
                    ajax: {
                        url:         wc_enhanced_select_params.ajax_url,
                        dataType:    'json',
                        quietMillis: 250,
                        data: function( term ) {
                            return {
                                term:     term,
                                action:   $( this ).data( 'action' ) || 'woocommerce_wpml_json_search_products_and_variations',
                                language: ("quoteup-create-quote" == getParameterByName("page") || "quoteup-details-edit" == getParameterByName("page")) ? $( '.wc-language-selector' ).val() : wc_enhanced_select_params.enquiryLanguage,
                                security: wc_enhanced_select_params.search_products_nonce,
                                exclude:  $( this ).data( 'exclude' ),
                                limit:    $( this ).data( 'limit' )
                            };
                        },
                        results: function( data ) {
                            var terms = [];
                            if ( data ) {
                                $.each( data, function( id, data ) {
                                    if(data.product_type == 'variation'){
                                        terms.push( { 
                                            id: id, 
                                            text: data.formatted_name, 
                                            product_type: data.product_type ,
                                            product_id: data.product_id,
                                            variation_id: data.variation_id,
                                            variation_attributes: data.variation_attributes,
                                            price: data.price,
                                            title: data.product_title,
                                            variation_string: data.variation_string,
                                            url : data.url,
                                            product_image : data.product_image,
                                            sku : data.sku
                                        } );
                                    }else {
                                        terms.push( { 
                                            id: id, 
                                            text: data.formatted_name, 
                                            product_type: data.product_type ,
                                            product_id: data.product_id,
                                            price: data.price,
                                            title: data.product_title,
                                            url : data.url,
                                            product_image : data.product_image,
                                            sku : data.sku
                                        } );
                                    }
                                });
                            }
                            return {
                                results: terms
                            };
                        },
                        cache: true
                    }
                };

                if ( $( this ).data( 'multiple' ) === true ) {
                    select2_args.multiple = true;
                    select2_args.initSelection = function( element, callback ) {
                        var data     = $.parseJSON( element.attr( 'data-selected' ) );
                        var selected = [];

                        $( element.val().split( ',' ) ).each( function( i, val ) {
                            selected.push({
                                id: val,
                                text: data[ val ]
                            });
                        });
                        return callback( selected );
                    };
                    select2_args.formatSelection = function( data ) {
                        return '<div class="selected-option" data-id="' + data.id + '">' + data.text + '</div>';
                    };
                } else {
                    select2_args.multiple = false;
                    select2_args.initSelection = function( element, callback ) {
                        var data = {
                            id: element.val(),
                            text: element.attr( 'data-selected' )
                        };
                        return callback( data );
                    };
                }

                select2_args = $.extend( select2_args, getEnhancedSelectFormatString() );

                $( this ).select2( select2_args ).addClass( 'enhanced' );
                //$( this ).
            });

})

        // WooCommerce Backbone Modal
        .on( 'wc_backbone_modal_before_remove', function() {
            $( ':input.wc-product-search' ).select2( 'close' );
        })

        // Get Ajax data
        .on( 'wc_get_variation_data', function() {
            $( ':input.wc-product-search' ).change(function(){
                var variationData = $(this).select2('data');
            });
        })
        .trigger( 'wc-enhanced-select-init' )
        .trigger( 'wc_get_variation_data' );

        function getParameterByName(name, url) {
            if (!url) {
              url = window.location.href;
          }
          name = name.replace(/[\[\]]/g, "\\$&");
          var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
          results = regex.exec(url);
          if (!results) return null;
          if (!results[2]) return '';
          return decodeURIComponent(results[2].replace(/\+/g, " "));
      }

  });
