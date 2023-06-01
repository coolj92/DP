(function($) {

    var hours_container = $('.directorist-open-hours');

    // Load hours and badge for single listing
    if( atbdp_business_hours.cache_plugin_compatibility && hours_container.length ) {
        $(window).load(function() {
            var listing_id = $( hours_container ).data('listing_id');
            print_hours( listing_id );
        });
    }


    // DOM Mutation observer
    function initObserver() {
        const targetNode = document.querySelector('.directorist-archive-contents');
        const observer = new MutationObserver( print_archive_badge );
        observer.observe( targetNode, { childList: true } );
    }
    window.addEventListener('DOMContentLoaded', ()=>{
        if(document.querySelector('.directorist-archive-contents') !== null){
            initObserver();
        }
    } );
    window.addEventListener('DOMContentLoaded', print_archive_badge );

    function print_archive_badge(){
        var badge_containers = $('.directorist_open_status_badge');

        if( ! ( atbdp_business_hours.cache_plugin_compatibility && badge_containers.length ) ) {
            return;
        }

        let listing_ids = [];
        if( badge_containers.length > 0 ) {
            badge_containers.each(function (index, container) {
                var listing_id = $( container ).data('listing_id');
                listing_ids.push( listing_id );
            });
        }
        print_hours_badges( listing_ids );

    }

    function print_hours_badges( listing_ids ) {
        var form_data = new FormData();
        form_data.append('action', 'atbdp_business_hours_badge');
        form_data.append('listing_ids', listing_ids );
        $.ajax({
            method: 'POST',
            processData: false,
            contentType: false,
            url: atbdp_business_hours.ajaxurl,
            data: form_data,
            success: function(response) {
                $.map( response, function( val, i ) {
                    $(`#directorist_open_status_badge-${ val.listing_id }`).append( val.badge );
                  });
            },
            error: function(error) {
                console.log(error);
            }
        });
    }

    function print_hours( listing_id ) {
        var form_data = new FormData();

        form_data.append('action', 'atbdp_business_hours_time');
        form_data.append('listing_id', listing_id );

        $.ajax({
            method: 'POST',
            processData: false,
            contentType: false,
            url: atbdp_business_hours.ajaxurl,
            data: form_data,
            success: function(response) {

                if( response['hours'] ) {
                    $('.directorist-open-hours').append( response['hours'] );
                }

                if( response['badge'] ) {
                    $( '.directorist_open_status_badge' ).append( response['badge'] );
                }
            },
            error: function(error) {
                console.log(error);
            }
        });
    }

    $(window).load(function() {
        $('input[name="disable_bz_hour_listing"]').each((index, element) => {
            if ($(element)[0].checked) {
                $(element).closest('.directorist-bh-extras').siblings('.directorist-bh-selection').removeClass('directorist-bh-show');
            }
        });

        setTimeout(() => {
            $('input[name="directorist_bh_option"]').each((index, element) => {
                if ($(element)[0].checked) {
                    $(element).closest('.directorist-bh-extras').siblings('.directorist-bh-selection').addClass('directorist-bh-show');
                    $(element).closest('.directorist-bh-extras').siblings('.directorist-bh-selection').find('.directorist-bh-dayzone__single').addClass('directorist-enable-hour-time-selection');
                    $('.directorist-switch-input').each(function() {
                        if ($(this)[0].checked) {
                            $(this).closest('.directorist-bh-dayzone__single').addClass('directorist-enable-hour-time-selection')
                        } else {
                            $(this).closest('.directorist-bh-dayzone__single').removeClass('directorist-enable-hour-time-selection')
                        }
                    });

                    $('.directorist-bh-dayzone__single--hour-selection').each(function() {
                        if ($(this).find('input[type="checkbox"]')[0].checked) {
                            $(this).closest('.directorist-bh-dayzone__single').addClass('directorist-full-time')
                        } else {
                            $(this).closest('.directorist-bh-dayzone__single').removeClass('directorist-full-time')
                        }
                    });
                }
            });
        }, 2000);

        $('input[name="enable247hour"]').each((index, element) => {
            if ($(element)[0].checked) {
                $(element).closest('.directorist-bh-extras').siblings('.directorist-bh-selection').addClass('directorist-bh-show');
                $(element).closest('.directorist-bh-extras').siblings('.directorist-bh-selection').find('.directorist-bh-dayzone__single').addClass('directorist-enable-hour-time-selection');
                $('.directorist-switch-input').each(function() {
                    if ($(this)[0].checked) {
                        $(this).closest('.directorist-bh-dayzone__single').addClass('directorist-enable-hour-time-selection')
                    } else {
                        $(this).closest('.directorist-bh-dayzone__single').removeClass('directorist-enable-hour-time-selection')
                    }
                });

                $('.directorist-bh-dayzone__single--hour-selection').each(function() {
                    if ($(this).find('input[type="checkbox"]')[0].checked) {
                        $(this).closest('.directorist-bh-dayzone__single').addClass('directorist-full-time')
                    } else {
                        $(this).closest('.directorist-bh-dayzone__single').removeClass('directorist-full-time')
                    }
                });
            }
        });

    });

    // Day Switch
    $('body').on('change', '.directorist-switch-input', function() {
        if ($(this)[0].checked) {
            $(this).closest('.directorist-bh-dayzone__single').addClass('directorist-enable-hour-time-selection')
        } else {
            $(this).closest('.directorist-bh-dayzone__single').removeClass('directorist-enable-hour-time-selection')
        }
    });

    // Business Hour Selection
    $('body').on('change', 'input[type="checkbox"].directorist-247-alternative', function() {
        $('input[name="enable247hour"]')[0].checked = false;
        switch (this.value) {
            case 'hide':
                $(this).closest('.directorist-bh-extras').siblings('.directorist-bh-selection').removeClass('directorist-bh-show');
                $('input[name="directorist_bh_option"]')[0].checked = false;
                break;
            case 'open':
                if ($(this)[0].checked) {
                    $(this).closest('.directorist-bh-extras').siblings('.directorist-bh-selection').addClass('directorist-bh-show');
                    $('input[name="disable_bz_hour_listing"]')[0].checked = false;
                    $('.directorist-bh-dayzone__single--hour-selection').each(function() {
                        $(this).find('input[type="checkbox"]')[0].checked = false;
                        $(this).parent('.directorist-bh-dayzone__single').addClass('directorist-enable-hour-time-selection');
                        $(this).parent('.directorist-bh-dayzone__single').removeClass('directorist-full-time');
                    });
                    $('.directorist-bh-dayzone__single--swtich').each(function() {
                        $(this).find('input[type="checkbox"]')[0].checked = true;
                    });
                } else {
                    $(this).closest('.directorist-bh-extras').siblings('.directorist-bh-selection').removeClass('directorist-bh-show');
                }

                break;
            default:
                $(this).closest('.directorist-bh-extras').siblings('.directorist-bh-selection').removeClass('directorist-bh-show');
        }
    });

    // Active 24-7 Business Hour
    $('body').on('change', 'input[name="enable247hour"]', function() {
        $('input[name="directorist_bh_option"]')[0].checked = false;
        $('input[name="disable_bz_hour_listing"]')[0].checked = false;

        if ($(this)[0].checked) {
            //console.log($(this).closest('.directorist-bh-extras').siblings('.directorist-bh-selection'))
            $(this).closest('.directorist-bh-extras').siblings('.directorist-bh-selection').removeClass('directorist-enable-247-bh directorist-bh-show');
            $('.directorist-bh-dayzone__single--hour-selection').each(function() {
                $(this).find('input[type="checkbox"]')[0].checked = false;
                $(this).parent('.directorist-bh-dayzone__single').addClass('directorist-enable-hour-time-selection');
                $(this).parent('.directorist-bh-dayzone__single').removeClass('directorist-full-time');
            });
            $('.directorist-bh-dayzone__single--swtich').each(function() {
                $(this).find('input[type="checkbox"]')[0].checked = true;
            });
        } else {
            $(this).closest('.directorist-bh-extras').siblings('.directorist-bh-selection').removeClass('directorist-enable-247-bh directorist-bh-show');
            $('.directorist-bh-dayzone__single--hour-selection').each(function() {
                $(this).find('input[type="checkbox"]')[0].checked = false;
                if ($(this).find('input[type="checkbox"]')[0].checked == false) {
                    $(this).parent('.directorist-bh-dayzone__single').removeClass('directorist-full-time')
                }
            });
        }
    });

    setTimeout(() => {
        if ($('input[name="enable247hour"]').is(':checked') === true) {
            $('.directorist-bh-selection').removeClass('directorist-enable-247-bh directorist-bh-show');
        }
    }, 3000);

    $('body').on('change', '.directorist-bh-dayzone__single--hour-selection .directorist-checkbox input', function() {
        if ($(this).prop("checked")) {
            $(this).closest('.directorist-bh-dayzone__single--hour-selection').siblings('.directorist-bh-dayzone__single--choice').parent('.directorist-bh-dayzone__single').addClass('directorist-full-time');
        } else {
            $(this).closest('.directorist-bh-dayzone__single--hour-selection').siblings('.directorist-bh-dayzone__single--choice').parent('.directorist-bh-dayzone__single').removeClass('directorist-full-time');
        }
    });

    $(document).ready(function() {
        // timezone dropdown
        if ($('#dbh-select-timezone').length) {
            $('#dbh-select-timezone').select2({
                placeholder: "Select Timezone",
                //allowClear: true,
            });
        }
    });

    //select hours copy
    const selectsWrapper = $('.directorist-bh-dayzone__single--choice-wrapper');
    $('body').on('click', '.directorist-select-add', function(e) {
        e.preventDefault();
        $('.directorist-select select').select2({
            allowClear: false,
        });
        var selectClone = $(this).closest('.directorist-bh-dayzone__single--choice').find('.directorist-bh-dayzone__single--choice-wrapper').clone();

        const selectStart = $(selectClone[selectClone.length - 1]).find('.directorist-select--start select');
        const selectClose = $(selectClone[selectClone.length - 1]).find('.directorist-select--close select');

        selectStart.removeAttr("data-select2-id");
        selectClose.removeAttr("data-select2-id");

        selectStart.attr('name', selectStart.attr('name').split('[start]')[0] + '[start]' + `[${eval(selectStart.attr('name').split('[start]')[1])[0] + 1}]`);

        selectClose.attr('name', selectClose.attr('name').split('[close]')[0] + '[close]' + `[${eval(selectClose.attr('name').split('[close]')[1])[0] + 1}]`);

        $(this).closest('.directorist-bh-dayzone__single--choice').append(selectClone[selectClone.length - 1]);
    });

    $('body').on('click', '.directorist-select-remove', function(e) {
        e.preventDefault();
        if ($(this).closest('.directorist-bh-dayzone__single--choice').find('.directorist-bh-dayzone__single--choice-wrapper').length > 1) {
            $(this).closest('.directorist-flex.directorist-bh-dayzone__single--choice-wrapper').remove();
        }
    });

})(jQuery);

