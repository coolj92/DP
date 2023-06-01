<?php
// prevent direct access to the file
defined('ABSPATH') || die('No direct script access allowed!');
class FAQs_Widget extends WP_Widget {

    /**
     * Register widget with WordPress.
     */
    public function __construct() {
        $widget_options = array(
            'classname' => 'atbd_widget',
            'description' => __('Let user to shoe FAQs on the sidebar of every single listing ( listing details page ) by this widget ', 'directorist-faqs'),
        );
        parent::__construct(
            'bdfaqs_widget', // Base ID, must be unique
            __( 'Directorist - FAQs', 'directorist-faqs' ), // Name
            $widget_options // Args
        );
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget( $args, $instance ) {
        if( !is_singular( ATBDP_POST_TYPE ) ) {
            return;
        }

        $plan_faqs = true;
        global $post;
        $listing_id = $post->ID;
        if (class_exists('ATBDP_Fee_Manager')){
            $plan_faqs = is_plan_allowed_listing_faqs(get_post_meta($listing_id, '_fm_plans', true));
        }
        if ($plan_faqs){

            // Show the widget if we have data to display
           // if (  (!is_empty_v($business_hours) || !empty($enable247hour)) ) {
                ;
                $title = !empty($instance['title']) ? esc_html($instance['title']) : esc_html__('Listing FAQs', 'directorist-faqs');
                echo $args['before_widget'];
                echo '<div class="atbd_widget_title">';
                echo $args['before_title'] . esc_html(apply_filters('widget_title', $title));
                echo '</div>';
                echo $args['after_title'];
                echo '<div class="atbdp atbdp_faq_widget">';
                // if 24 hours 7 days open then show it only, otherwise, show the days and its opening time.
                echo '<div class="directorist-faq-accordion">';
                $listing_info = get_post_meta($listing_id, '_faqs', true);
                $faqs = !empty($listing_info)?$listing_info:array();
                foreach ($faqs as $index => $faqInfo) {
                    $quez = !empty($faqInfo['quez'])?esc_attr($faqInfo['quez']):'';
                    $ans = !empty($faqInfo['ans'])?$faqInfo['ans']:'';
                    echo '<div class="directorist-faq-accordion__single">';
                    echo '<h3 class="directorist-faq-accordion__title"><a href="#">'.$quez.'</a></h3>';
                    echo '<div class="directorist-faq-accordion__content">' . wpautop( $ans ) . '</div>';
                    echo '</div>';
                }
                echo '</div>';
                echo '</div>';
                echo $args['after_widget'];
            }
        }

    //}

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     * @return void
     */
    public function form( $instance ) {
        $title = ! empty( $instance['title'] ) ? esc_html($instance['title']) : esc_html__( 'Listing FAQs', 'directorist-faqs' );
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'directorist-faqs' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <?php
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

        return $instance;
    }

} // class FAQs_Widget