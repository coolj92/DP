<?php
// prevent direct access to the file
defined('ABSPATH') || die('No direct script access allowed!');
$faqsInfo = (array_key_exists('listing_faq', $args)) ? $args['listing_faq'] : array(); ?>
<!--<label for="atbdp_social"><?php /*esc_html_e('Social Information:', 'directorist-faqs'); */ ?></label>
-->
<div id="directorist-draggable-faq-container">
    <?php if ( ! empty( $faqsInfo ) ) {
        foreach ($faqsInfo as $index => $faqs) { // eg. here, $faqs = ['id'=> 'facebook', 'url'=> 'http://fb.com']
            ?>
            <div class="directorist-faq-box directorist-flex" id="directorist-faq-<?= $index; ?>">
                <!--Social ID-->
                <div class="directorist-faq-box__question">
                    <div class="directorist-form-group">
                        <!-- <label><?php /*_e('Question', 'directorist-faqs');*/ ?></label>-->
                        <input type="text" placeholder="<?php _e('Question', 'directorist-faqs'); ?>" name="faqs[<?= $index; ?>][quez]" id="atbdp_social"
                               value="<?= !empty($faqs['quez']) ? esc_attr($faqs['quez']) : ''; ?>"
                               class="directorist-form-element directorist-faq-qstn">

                    </div>
                </div>
                <!--Social URL-->
                <div class="directorist-faq-box__answer">
                    <?php
                    $faqs_ans_box = get_directorist_option('faqs_ans_box', 'normal');
                    $content = !empty($faqs['ans']) ? esc_attr($faqs['ans']) : '';
                    if ('normal' === $faqs_ans_box) { ?>
                        <textarea type="text" name="faqs[<?= $index; ?>][ans]"
                                  class="directorist-form-element directory_field atbdp_faqs_input"
                                  placeholder="<?php _e('Answer..', 'directorist-faqs'); ?>" rows="5"
                                  value=""><?= !empty($faqs['ans']) ? esc_attr($faqs['ans']) : ''; ?></textarea>
                        <?php
                    } else {
                        $settings = array(
                            'textarea_name' => "faqs[$index][ans]",//name you want for the textarea
                            'textarea_rows' => 8,
                            'tabindex' => 4,
                            'tinymce' => array(
                                'theme_advanced_buttons1' => 'bold, italic, ul, pH, temp',
                            ),
                        );
                        $id = $index;//has to be lower case
                        wp_editor($content, $id, $settings);
                    }
                    ?>
                </div>
                <div class="directorist-faq-box__action directorist-flex directorist-justify-content-center">
                    <span data-id="<?= $index; ?>" class="directorist-btn-faq-remove dashicons dashicons-trash" title="<?php _e('Remove this item', 'directorist-faqs'); ?>"></span> 
                    <span class="directorist-btn-faq-drag dashicons dashicons-move"></span>
                </div>
            </div> <!--   ends .directorist-faq-box-->
            
            <?php
        }

    } ?>
</div> <!--    ends .directorist-draggable-faq-container    -->

<button type="button" class="directorist-btn directorist-btn-xs directorist-btn-add-faq" id="directorist-add-faq"><span class="plus-sign">+</span>
    <?php esc_html_e('Add New', 'directorist-faqs'); ?>
</button>

<div class="directorist-modal directorist-modal-js directorist-modal-alert directorist-faq-remove-confirm-js directorist-fade">

    <div class="directorist-modal__dialog">

        <div class="directorist-modal__content">

            <div class="directorist-modal__body directorist-text-center directorist-modal-alert-warning">

                <div class="directorist-modal-alert-icon">

                    <?php directorist_icon( 'las la-exclamation' ); ?>
                    
                </div>

                <div class="directorist-modal-alert-text">

                    <h3 class="directorist-modal-alert-text__title directorist-text-warning"><?php _e('Are you sure?', 'directorist-faqs'); ?></h3>
                    
                    <p class="directorist-modal-alert-text__details"><?php _e('Do you really want to remove this FAQ item?', 'directorist-faqs'); ?></p>

                </div>
                
            </div>

            <div class="directorist-modal__footer directorist-text-center directorist-modal-alert-action">

                <button class="directorist-btn directorist-btn-danger directorist-modal-close directorist-modal-close-js"><?php _e('Cancel', 'directorist-faqs'); ?></button>

                <button class="directorist-btn directorist-btn-info directorist-modal-ok directorist-modal-close-js"><?php _e('Yes, Delete It!', 'directorist-faqs'); ?></button>

            </div>

        </div>

    </div>

</div>