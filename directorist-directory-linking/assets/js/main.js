/*
    Name: Directorist directory linking
    Author: WPWax
*/

(function($){
    //Initialize slick slider for directory linking cards
    $(document).ready(function(){
        $('.directorist-linking-content__slider').each(function(id, elm){
            $(elm).slick({
                slidesToScroll: 1,
                rows: 2,
                slidesPerRow: 2,
                arrows: false,
            });
        })
        // Custom carousel nav
        $('.directorist-linking-content__slider-nav--prev').on('click', function(e){
            e.preventDefault();
            $(this).parents('.directorist-linking-content__action').siblings('.directorist-linking-content__slider').slick('slickPrev');
        } );
        $('.directorist-linking-content__slider-nav--next').on('click', function(e){
            e.preventDefault();
            $(this).parents('.directorist-linking-content__action').siblings('.directorist-linking-content__slider').slick('slickNext');
        } );
    })

})(jQuery)