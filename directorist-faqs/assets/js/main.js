/* eslint-disable */
jQuery(document).ready(function($) {
    /*This function handles all ajax request*/
    function atbdp_do_ajax(ElementToShowLoadingIconAfter, ActionName, arg, CallBackHandler) {
        var data;
        if (ActionName) data = "action=" + ActionName;
        if (arg) data = arg + "&action=" + ActionName;
        if (arg && !ActionName) data = arg;
        //data = data ;

        var n = data.search(listing_faqs.nonceName);
        if (n < 0) {
            data = data + "&" + listing_faqs.nonceName + "=" + listing_faqs.nonce;
        }

        jQuery.ajax({
            type: "post",
            url: listing_faqs.ajaxurl,
            data: data,
            beforeSend: function() { jQuery("<span class='atbdp_ajax_loading'></span>").insertAfter(ElementToShowLoadingIconAfter); },
            success: function(data) {
                jQuery(".atbdp_ajax_loading").remove();
                CallBackHandler(data);
            }
        });
    }
    // enable sorting if only the container has any social or skill field
    // enable sorting if only the container has any social or skill field
    var $s_wrap = $("#directorist-draggable-faq-container"); // cache it

    if ($s_wrap.length) {
        $s_wrap.sortable({
            axis: 'y',
            opacity: '0.7'
        });
    }


    // SOCIAL SECTION
    // Rearrange the IDS and Add new social field
    $("#directorist-add-faq").on('click', function() {
        var currentItems = $('.directorist-faq-box').length;
        var ID = "id=" + currentItems; // eg. 'id=3'
        var iconBindingElement = jQuery('#directorist-add-faq');
        // arrange names ID in order before adding new elements
        $('.directorist-faq-box').each(function(index, element) {
            var e = $(element);
            //console.log(index);
            e.attr('id', 'directorist-faq-' + index);
            e.find('.directorist-faq-qstn').attr('name', 'faqs[' + index + '][quez]');
            e.find('.atbdp_faqs_input').attr('name', 'faqs[' + index + '][ans]');
            e.find('.directorist-btn-faq-remove').attr('data-id', index);
        });
        // now add the new elements. we could do it here without using ajax but it would require more markup here.
        atbdp_do_ajax(iconBindingElement, 'atbdp_faqs_handler', ID, function(data) {
            $s_wrap.append(data);
            if ('normal' !== listing_faqs.ans_field) {
                tinymce.init({ selector: 'textarea' });
            }
        });
    });


    // remove the social field and then reset the ids while maintaining position
    $(document).on('click', '.directorist-btn-faq-remove', function(e) {
        let id = $(this).data("id"),
            elementToRemove = $('div#directorist-faq-' +id);
        e.preventDefault();

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

    // Open Modal for Confirm Delete
    var directoristModal = document.querySelector('.directorist-modal-js');
        $('body').on('click', '.directorist-btn-modal-js', function (e) {
            e.preventDefault();
            var data_target = $(this).attr("data-directorist_target");
            document.querySelector(".".concat(data_target)).classList.add('directorist-show');
        });

        $('body').on('click', '.directorist-modal-close-js', function (e) {
            e.preventDefault();
            $(this).closest('.directorist-modal-js').removeClass('directorist-show');
        });

        $(document).bind('click', function (e) {
        if (e.target == directoristModal) {
            directoristModal.classList.remove('directorist-show');
        }
    });

    $('.directorist-faq-accordion__content').hide();
    $('.directorist-faq-accordion__single > .directorist-faq-accordion__title > a').on("click", function(e) {
        var $this = $(this);
        $this.parent().next().slideToggle();
        $this.parent().parents(".directorist-faq-accordion__single").siblings(".directorist-faq-accordion__single").children(".directorist-faq-accordion__content").slideUp();
        $this.parent().parents(".directorist-faq-accordion__single").toggleClass("directorist-faq-active");
        $this.parent().parents(".directorist-faq-accordion__single").siblings(".directorist-faq-accordion__single").removeClass("directorist-faq-active");
        $this.toggleClass("directorist-active");
        $this.parent().parents(".directorist-faq-accordion__single").siblings(".directorist-faq-accordion__single").children(".directorist-faq-accordion__title").children("a").removeClass("directorist-active");
        e.preventDefault();
    });


});