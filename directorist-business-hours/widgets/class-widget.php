<?php
/**
 * Adds BD_Business_Hour_Widget widget.
 */
class BD_Business_Hour_Widget extends WP_Widget {

    /**
     * Register widget with WordPress.
     */
    public function __construct() {
        $widget_options = array(
            'classname' => 'atbd_widget',
            'description' => __('You can show business hour on the sidebar of every single listing ( listing details page ) by this widget ', 'directorist-business-hours'),
        );
        parent::__construct(
            'bdbh_widget', // Base ID, must be unique
            __( 'Directorist - Business Hour', 'directorist-business-hours' ), // Name
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

        if (!get_directorist_option('enable_business_hour', 1)) return; // vail if the business hour is not enabled
        global $post;
        $disable_bz_hour_listing          = get_post_meta($post->ID, '_disable_bz_hour_listing', true);
        $plan_hours = true;
       /*  if (is_fee_manager_active()){
            $plan_hours = is_plan_allowed_business_hours(get_post_meta($post->ID,'_fm_plans', true));
        } */
        $allowBusinessHour = apply_filters('atbdp_allow_business_hour', true);
        if ($plan_hours && empty($disable_bz_hour_listing) && $allowBusinessHour){
            global $post;
            /*@todo; check enable_business_hour settings toggling*/
            $listing_id = $post->ID;
            $text247                = get_directorist_option('text247',  __('Open 24/7', 'directorist-business-hours')); // text for 24/7 type listing
            $bdbh                   = get_post_meta($listing_id, '_bdbh', true);
            $enable247hour          = get_post_meta($listing_id, '_enable247hour', true);
            $business_hours         = !empty($bdbh) ? atbdp_sanitize_array($bdbh) : array(); // arrays of days and times if exist

            // Show the widget if we have data to display
            if (  (!is_empty_v($business_hours) || !empty($enable247hour)) ) {
                ;
                $title = !empty($instance['title']) ? esc_html($instance['title']) : esc_html__('Business Hour', 'directorist-business-hours');
                echo $args['before_widget'];
                echo '<div class="atbd_widget_title">';
                echo $args['before_title'] . esc_html(apply_filters('widget_title', $title));   
                ?>
                <div class="atbd_upper_badge directorist_open_status_badge" data-listing_id="<?php echo esc_attr( get_the_ID() ); ?>">
                    <?php if( ! directorist_hours_cache_plugin_compatibility() ) {
                    directorist_show_open_close_badge( get_the_ID() );
                } ?>
                </div>
                <?php
                echo $args['after_title'];
                echo '</div>';
                echo '<div class="directorist-open-hours">';
                // if 24 hours 7 days open then show it only, otherwise, show the days and its opening time.
                if( ! directorist_hours_cache_plugin_compatibility() ){
                    if (!empty($enable247hour)) {
                        echo '<p>'. esc_html($text247) . '</p>';
                    } else {
                        show_business_hours(); // show the business hour in an unordered list.
                    }
                }
                echo '</div>';
                echo $args['after_widget'];
            }
        }

    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     * @return void
     */
    public function form( $instance ) {
        $title = ! empty( $instance['title'] ) ? esc_html($instance['title']) : esc_html__( 'Business Hour', 'directorist-business-hours' );
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'directorist-business-hours' ); ?></label>
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

} // class BD_Business_Hour_Widget