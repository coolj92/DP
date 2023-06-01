jQuery( document ).ready( function() {
    // auto update
    var $ = jQuery;
    $('#gallery_activated input[name="gallery_activated"]').on('change', function (event) {
        event.preventDefault();
        var form_data = new FormData();
        var gallery_license = $('#gallery_license input[name="gallery_license"]').val();
        form_data.append('action', 'atbdp_gallery_license_activation');
        form_data.append('gallery_license', gallery_license);
        $.ajax({
            method: 'POST',
            processData: false,
            contentType: false,
            url: gallery_js_obj.ajaxurl,
            data: form_data,
            success: function (response) {
                if (response.status === true){
                    $('#success_msg').remove();
                    $('#gallery_activated').after('<p id="success_msg">' + response.msg + '</p>');
                    location.reload();
                }else{
                    $('#error_msg').remove();
                    $('#gallery_activated').after('<p id="error_msg">' + response.msg + '</p>');
                }
            },
            error: function (error) {
                //console.log(error);
            }
        });
    });
    // license deactivation
    $('#gallery_deactivated input[name="gallery_deactivated"]').on('change', function (event) {
        event.preventDefault();
        var form_data = new FormData();
        var gallery_license = $('#gallery_license input[name="gallery_license"]').val();
        form_data.append('action', 'atbdp_gallery_license_deactivation');
        form_data.append('gallery_license', gallery_license);
        $.ajax({
            method: 'POST',
            processData: false,
            contentType: false,
            url: gallery_js_obj.ajaxurl,
            data: form_data,
            success: function (response) {
                if (response.status === true){
                    $('#success_msg').remove();
                    $('#gallery_deactivated').after('<p id="success_msg">' + response.msg + '</p>');
                    location.reload();
                }else{
                    $('#error_msg').remove();
                    $('#gallery_deactivated').after('<p id="error_msg">' + response.msg + '</p>');
                }
            },
            error: function (error) {
                //console.log(error);
            }
        });
    });

});