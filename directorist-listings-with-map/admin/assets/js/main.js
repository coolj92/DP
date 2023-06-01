(function ($) {
    $(document).ready(function () {
        var lmvf = $("#listing_map_visible_fields");
        lmvf.hide();
        $('#bdmv_listings_with_map_columns select[name="bdmv_listings_with_map_columns"]').on("change", function () {
            if($(this).val() === "2"){
                lmvf.show();
            }else{
                lmvf.hide();
            }
        });
        if($('#bdmv_listings_with_map_columns select[name="bdmv_listings_with_map_columns"]').val() === "2"){
            lmvf.show();
        }

        // activate license and set up updated
        $('#listings_map_activated input[name="listings_map_activated"]').on('change', function (event) {
            event.preventDefault();
            var form_data = new FormData();
            var listings_map_license = $('#listings_map_license input[name="listings_map_license"]').val();
            form_data.append('action', 'atbdp_listings_map_license_activation');
            form_data.append('listings_map_license', listings_map_license);
            $.ajax({
                method: 'POST',
                processData: false,
                contentType: false,
                url: listings_map_js_obj.ajaxurl,
                data: form_data,
                success: function (response) {
                    if (response.status === true) {
                        $('#success_msg').remove();
                        $('#listings_map_activated').after('<p id="success_msg">' + response.msg + '</p>');
                        location.reload();
                    } else {
                        $('#error_msg').remove();
                        $('#listings_map_activated').after('<p id="error_msg">' + response.msg + '</p>');
                    }
                },
                error: function (error) {
                    // console.log(error);
                }
            });
        });
        // deactivate license
        $('#listings_map_deactivated input[name="listings_map_deactivated"]').on('change', function (event) {
            event.preventDefault();
            var form_data = new FormData();
            var listings_map_license = $('#listings_map_license input[name="listings_map_license"]').val();
            form_data.append('action', 'atbdp_listings_map_license_deactivation');
            form_data.append('listings_map_license', listings_map_license);
            $.ajax({
                method: 'POST',
                processData: false,
                contentType: false,
                url: listings_map_js_obj.ajaxurl,
                data: form_data,
                success: function (response) {
                    if (response.status === true) {
                        $('#success_msg').remove();
                        $('#listings_map_deactivated').after('<p id="success_msg">' + response.msg + '</p>');
                        location.reload();
                    } else {
                        $('#error_msg').remove();
                        $('#listings_map_deactivated').after('<p id="error_msg">' + response.msg + '</p>');
                    }
                },
                error: function (error) {
                    // console.log(error);
                }
            });
        });

    });
})(jQuery);

