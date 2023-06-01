<?php
// prevent direct access to the file
defined('ABSPATH') || die('No direct script access allowed!');
$faqsInfo = !empty( $field_data ) ? $field_data['value'] : array();
if( !empty( $faqsInfo ) ) {
?>
<div class="directorist-faq-accordion">
    <?php
        foreach ($faqsInfo as $index => $faqInfo) {
            $quz = !empty($faqInfo['quez']) ? $faqInfo['quez'] : '';
            $ans = !empty($faqInfo['ans'])? $faqInfo['ans'] : '';
        
        ?>
        <div class="directorist-faq-accordion__single">
            <h3 class="directorist-faq-accordion__title"><?php directorist_icon( 'las la-plus' ); ?><a href="#"><?php echo esc_attr( $quz ) ?></a></h3>
            <div class="directorist-faq-accordion__content"><?php echo do_shortcode(wpautop($ans)); ?></div>
        </div>
            <?php
        }
    ?>
</div>
<?php
}