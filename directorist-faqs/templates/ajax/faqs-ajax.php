<?php
// prevent direct access to the file
defined('ABSPATH') || die('No direct script access allowed!');
$id = (array_key_exists('id', $args)) ? $args['id'] : 0;
?>


<div class="directorist-faq-box directorist-flex" id="directorist-faq-<?= $id; ?>">
    <div class="directorist-faq-box__question">
        <div class="directorist-form-group">
           <!-- <label><?php /*_e('Question', 'directorist-faqs')*/?></label>-->
            <input type="text" placeholder="<?php _e('Question', 'directorist-faqs'); ?>" name="faqs[<?php echo $id ; ?>][quez]" id="atbdp_social" value="" class="directorist-form-element directorist-faq-qstn">

        </div>
    </div>
    <div class="directorist-faq-box__answer">
        <div class="directorist-form-group">
            <?php
            $faqs_ans_box = get_directorist_option('faqs_ans_box', 'normal');
            $placeholder=__('Answer..', 'directorist-faqs');
            if ('normal' === $faqs_ans_box){
                ?>
                <textarea type="text" rows="5" placeholder="<?php _e('Answer..', 'directorist-faqs'); ?>" name="faqs[<?= $id; ?>][ans]" class="directorist-form-element directory_field atbdp_faqs_input" value=""></textarea>
            <?php
            }else{
                $settings = array(
                    'textarea_name'=>"faqs[$id][ans]",//name you want for the textarea
                    'textarea_rows' => 8,
                    'tabindex' => 4,
                    'tinymce' => array(
                        'theme_advanced_buttons1' => 'bold, italic, ul, pH, temp',
                    ),
                );
                wp_editor($placeholder,$id,$settings);
            }

            ?>
        </div>
    </div>
    <div class="directorist-faq-box__action directorist-flex directorist-justify-content-center">
        <span data-id="<?= $id; ?>" class="directorist-btn-faq-remove directorist-btn-modal-js dashicons dashicons-trash" title="<?php _e('Remove this item', 'directorist-faqs'); ?>" data-directorist_target="directorist-faq-remove-confirm-js"></span>
        <span class="directorist-btn-faq-drag dashicons dashicons-move"></span>
    </div>
</div>