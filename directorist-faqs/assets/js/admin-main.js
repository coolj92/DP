jQuery(document).ready(function ($) {

    /*This function handles all ajax request*/
    function atbdp_do_ajax( ElementToShowLoadingIconAfter, ActionName, arg, CallBackHandler) {
        var data;
        if(ActionName) data = "action=" + ActionName;
        if(arg)    data = arg + "&action=" + ActionName;
        if(arg && !ActionName) data = arg;
        //data = data ;

        var n = data.search(listing_faqs_js_obj.nonceName);
        if(n<0){
            data = data + "&" + listing_faqs_js_obj.nonceName + "=" + listing_faqs_js_obj.nonce;
        }

        jQuery.ajax({
            type: "post",
            url: listing_faqs_js_obj.ajaxurl,
            data: data,
            beforeSend: function() { jQuery("<span class='atbdp_ajax_loading'></span>").insertAfter(ElementToShowLoadingIconAfter); },
            success: function( data ) {
                jQuery(".atbdp_ajax_loading").remove();
                CallBackHandler(data);
            }
        });
    }
// enable sorting if only the container has any social or skill field
    // enable sorting if only the container has any social or skill field
    var $s_wrap = $("#directorist-draggable-faq-container");// cache it
        $( window ).load(function() {
            if ($s_wrap.length) {
                $("#directorist-draggable-faq-container").sortable(
                    {
                        axis: 'y',
                        opacity: '0.7'
                    }
                );
            }
        });
        
        $('select[name="directory_type"]').on('change', function(){
            setTimeout( function () {
               // var $s_wrap = $("#directorist-draggable-faq-container");// cache it
                if ($("#directorist-draggable-faq-container").length) {
                    $("#directorist-draggable-faq-container").sortable(
                        {
                            axis: 'y',
                            opacity: '0.7'
                        }
                    );
                }
    
            }, 1000 );
    });
        
    

    // SOCIAL SECTION
    // Rearrange the IDS and Add new social field
    $('body').on('click', '#directorist-add-faq', function(){
        var $s_wrap = $("#directorist-draggable-faq-container");// cache it
        var currentItems = $('.directorist-faq-box').length;
        var ID = "id="+currentItems; // eg. 'id=3'
        var iconBindingElement = jQuery('#directorist-add-faq');
        // arrange names ID in order before adding new elements
        $('.directorist-faq-box').each(function( index , element) {
            var e = $(element);
            //console.log(index);
            e.attr('id','directorist-faq-'+index);
            e.find('.directorist-faq-qstn').attr('name', 'faqs['+index+'][quez]');
            e.find('.atbdp_faqs_input').attr('name', 'faqs['+index+'][ans]');
            e.find('.directorist-btn-faq-remove').attr('data-id',index);
        });
        // now add the new elements. we could do it here without using ajax but it would require more markup here.
        atbdp_do_ajax( iconBindingElement, 'atbdp_faqs_handler', ID, function(data){
            //console.log(data);
            $s_wrap.append(data);
            // tinymce.init({selector:'textarea'});
            if('normal' !== listing_faqs_js_obj.ans_field){
                tinymce.init({selector:'textarea'});
            }
        });
    });


    // remove the social field and then reset the ids while maintaining position
    $(document).on('click', '.directorist-btn-faq-remove', function(e){
        let id = $(this).data("id"),
            elementToRemove = $('div#directorist-faq-'+id);
        event.preventDefault();

        // Faq Remove Confirm Modal
        $('.directorist-faq-remove-confirm-js').on('click', " .directorist-modal-ok", function(){
            elementToRemove.remove();
            $('.directorist-faq-box').each(function( index , element) {
                let e = $(element);
                e.attr('id','directorist-faq-'+index);
                e.find('.directorist-faq-qstn').attr('name', 'faqs['+index+'][quez]');
                e.find('.atbdp_faqs_input').attr('name', 'faqs['+index+'][ans]');
                e.find('.directorist-btn-faq-remove').attr('data-id',index);
            });
        });
    });

    // activate license and set up updated
    $('#faqs_activated input[name="faqs_activated"]').on('change', function (event) {
        event.preventDefault();
        var form_data = new FormData();
        var faqs_license = $('#faqs_license input[name="faqs_license"]').val();
        form_data.append('action', 'atbdp_faqs_license_activation');
        form_data.append('faqs_license', faqs_license);
        $.ajax({
            method: 'POST',
            processData: false,
            contentType: false,
            url: listing_faqs_js_obj.ajaxurl,
            data: form_data,
            success: function (response) {
                if (response.status === true) {
                    $('#success_msg').remove();
                    $('#faqs_activated').after('<p id="success_msg">' + response.msg + '</p>');
                    location.reload();
                } else {
                    $('#error_msg').remove();
                    $('#faqs_activated').after('<p id="error_msg">' + response.msg + '</p>');
                }
            },
            error: function (error) {
                // console.log(error);
            }
        });
    });
    // deactivate license
    $('#faqs_deactivated input[name="faqs_deactivated"]').on('change', function (event) {
        event.preventDefault();
        var form_data = new FormData();
        var faqs_license = $('#faqs_license input[name="faqs_license"]').val();
        form_data.append('action', 'atbdp_faqs_license_deactivation');
        form_data.append('faqs_license', faqs_license);
        $.ajax({
            method: 'POST',
            processData: false,
            contentType: false,
            url: listing_faqs_js_obj.ajaxurl,
            data: form_data,
            success: function (response) {
                if (response.status === true) {
                    $('#success_msg').remove();
                    $('#faqs_deactivated').after('<p id="success_msg">' + response.msg + '</p>');
                    location.reload();
                } else {
                    $('#error_msg').remove();
                    $('#faqs_deactivated').after('<p id="error_msg">' + response.msg + '</p>');
                }
            },
            error: function (error) {
                // console.log(error);
            }
        });
    });


});