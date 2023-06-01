jQuery(function($){
// Set all variables to be used in scope
var frame,
    selection,
    multiple_image= true,
    metaBox = $('body').find('#atbdp_gallery'), // meta box id here
    addImgLink = metaBox.find('#directorist-listing-gallery-btn'),
    delImgLink = metaBox.find( '#directorist-gallery-remove'),
    imgContainer = metaBox.find( '.directorist-listing-gallery-container'),
    active_mi_ext = atbdp_admin_data.active_mi_ext;

/*if the multiple image extension is active then set the multiple image parameter to true*/
if(1 === active_mi_ext){ multiple_image = true }

// ADD IMAGE LINK
$('body').on('click', '#directorist-listing-gallery-btn', function(event) {
    event.preventDefault();

    // If the media frame already exists, reopen it.
    if ( frame ) {
        frame.open();
        return;
    }

    // Create a new media frame
    frame = wp.media({
        title: atbdp_admin_data.i18n_text.upload_image,
        button: {
            text: atbdp_admin_data.i18n_text.choose_image
        },
        library : { type : 'image'}, // only allow image upload only
        multiple: multiple_image  // Set to true to allow multiple files to be selected. it will be set based on the availability of Multiple Image extension
    });


    // When an image is selected in the media frame...
    frame.on( 'select', function() {
        /*get the image collection array if the MI extension is active*/
        /*One little hints: a constant can not be defined inside the if block*/
        if (multiple_image){
            selection = frame.state().get( 'selection' ).toJSON();
        }else {
            selection = frame.state().get( 'selection' ).first().toJSON();
        }
        var data = ''; // create a placeholder to save all our image from the selection of media uploader

        // if no image exist then remove the place holder image before appending new image
        if ($('.directorist-listing-gallery-single').length === 0) {
            $('#directorist-bdg-gallery-upload .directorist-listing-gallery-container').html('');
        }


        //handle multiple image uploading.......
        if ( multiple_image ){
            $(selection).each(function () {
                // here el === this
                // append the selected element if it is an image
                if ('image' === this.type) {
                    // we have got an image attachment so lets proceed.
                    // target the input field and then assign the current id of the attachment to an array.
                    data += '<div class="directorist-listing-gallery-single">';
                    data += '<input class="directorist-listing-gallery-single__attatchment" name="gallery_img[]" type="hidden" value="'+this.id+'">';
                    data += '<img style="width: 100%; height: 100%;" src="'+this.url+'" alt="Listing Image" /> <span class="directorist-listing-gallery-single__remove fa fa-times" title="Remove it"></span></div>';
                }

            });
        }else{
            // Handle single image uploading

            // add the id to the input field of the image uploader and then save the ids in the database as a post meta
            // so check if the attachment is really an image and reject other types
            if ('image' === selection.type){
                // we have got an image attachment so lets proceed.
                // target the input field and then assign the current id of the attachment to an array.
                data += '<div class="directorist-listing-gallery-single">';
                data += '<input class="directorist-listing-gallery-single__attatchment" name="gallery_img[]" type="hidden" value="'+selection.id+'">';
                data += '<img style="width: 100%; height: 100%;" src="' + selection.url + '" alt="Listing Image" /> <span class="directorist-listing-gallery-single__remove  fa fa-times" title="Remove it"></span></div>';
            }
        }

        // If MI extension is active then append images to the listing, else only add one image replacing previous upload
        if(multiple_image){
            $('#directorist-bdg-gallery-upload .directorist-listing-gallery-container').append(data);
        }else {
            $('#directorist-bdg-gallery-upload .directorist-listing-gallery-container').html(data);
        }

        // Un-hide the remove image link
        delImgLink.removeClass( 'hidden' );
    });
    // Finally, open the modal on click
    frame.open();
});


// DELETE ALL IMAGES LINK
delImgLink.on( 'click', function( event ){
    event.preventDefault();
    // Clear out the preview image and set no image as placeholder
    imgContainer.html( '<img src="' + atbdp_admin_data.AdminAssetPath + 'images/no-image.png" alt="Listing Image" />' );
    // Hide the delete image link
    delImgLink.addClass( 'hidden' );


});

/*REMOVE SINGLE IMAGE*/
$(document).on('click', '.directorist-listing-gallery-single__remove', function (e) {
    e.preventDefault();
    $(this).parent().remove();
    // if no image exist then add placeholder and hide remove image button
    if ($('.directorist-listing-gallery-single').length === 0) {

        imgContainer.html( '<img src="'+atbdp_admin_data.AdminAssetPath+'images/no-image.png" alt="Listing Image" /><p>No images</p> ' +
            '<small>(allowed formats jpeg. png. gif)</small>' );
        delImgLink.addClass( 'hidden' );

    }
});
});