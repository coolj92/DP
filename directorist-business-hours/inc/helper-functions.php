<?php
// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

if ( ! function_exists( 'atbdp_get_option' ) ) {

    /**
     * It retrieves an option from the database if it exists and returns false if it is not exist.
     * It is a custom function to get the data of custom setting page
     * @param string $name The name of the option we would like to get. Eg. map_api_key
     * @param string $group The name of the group where the option is saved. eg. general_settings
     * @param mixed $default    Default value for the option key if the option does not have value then default will be returned
     * @return mixed    It returns the value of the $name option if it exists in the option $group in the database, false otherwise.
     */
    function atbdp_get_option( $name, $group, $default = false ) {
        // at first get the group of options from the database.
        // then check if the data exists in the array and if it exists then return it
        // if not, then return false
        if ( empty( $name ) || empty( $group ) ) {
            if ( ! empty( $default ) ) {
                return $default;
            }

            return false;
        } // vail if either $name or option $group is empty
        $options_array = (array) get_option( $group );
        if ( array_key_exists( $name, $options_array ) ) {
            return $options_array[$name];
        } else {
            if ( ! empty( $default ) ) {
                return $default;
            }

            return false;
        }
    }
}

if ( ! function_exists( 'atbdp_sanitize_array' ) ) {
    /**
     * It sanitize a multi-dimensional array
     * @param array &$array The array of the data to sanitize
     * @return mixed
     */
    function atbdp_sanitize_array( &$array ) {

        foreach ( $array as &$value ) {

            if ( ! is_array( $value ) ) {

                // sanitize if value is not an array
                $value = sanitize_text_field( $value );

            } else {

                // go inside this function again
                atbdp_sanitize_array( $value );
            }

        }

        return $array;

    }
}

function atbdp_hoursRange( $lower = 0, $upper = 86400, $step = 3600, $format = '' ) {
    $times = [];
    foreach ( range( $lower, $upper, $step ) as $increment ) {
        $increment                  = gmdate( 'H:i', $increment );
        list( $hour, $minutes )       = explode( ':', $increment );
        $date                       = new DateTime( $hour . ':' . $minutes );
        $times[(string) $increment] = $date->format( $format );
    }
    return $times;
}

function atbdp_get_old_hours( $old ) {
    $times = atbdp_hoursRange( 0, 86400, 60 * 15, 'g:i a' );
    foreach ( $times as $key => $time ) {
        if ( $old == $key ) {
            return $key;
        } elseif ( $old == $time ) {
            return $time;
        }
    }
}

function atbdp_hours( $name, $value = '', $type = '' ) {
    $times       = atbdp_hoursRange( 0, 86400, 60 * 15, 'g:i a' );
    $type        = $type == 'open' ? __( 'Open', 'directorist-business-hours' ) : __( 'Close', 'directorist-business-hours' );
    $time_format = get_directorist_option( 'atbh_time_format', '12' );
    $html        = '';
    $html .= '<select name="' . $name . '">';
    $html .= '<option value="">' . $type . '</option>';
    foreach ( $times as $key => $time ) {
        if ( '24' == $time_format ) {
            $time = $key;
        } else {
            $time = $time;
        }
        $html .= sprintf( '<option value="%s" %s>%s</option>', $key, selected( $value, $key, false ), $time );
    }
    $html .= '</select>';
    return $html;
}

function atbdp_hoursss() {
    $times = atbdp_hoursRange( 0, 86400, 60 * 15, 'g:i a' );
    $html  = '';
    $html .= '<ul class="dbh-default-times">';
    $time_format = get_directorist_option( 'atbh_time_format', '12' );
    foreach ( $times as $key => $time ) {
        if ( '24' == $time_format ) {
            $time = $key;
        } else {
            $time = $time;
        }
        $html .= sprintf( '<li><a data-time="%s" href="">%s</a></li>', $key, $time );
    }
    $html .= '</ul>';
    return $html;
}

if( ! function_exists( 'dbh_is_open' ) ) {
    function dbh_is_open( $time_open, $time_close, $timezone ) {
        $dt           = new DateTime( 'now', new DateTimezone( $timezone ) );
        $current_time = $dt->format( 'g:i a' );
        $time_now     = DateTime::createFromFormat( 'H:i a', $current_time );

		if ($time_close >= $time_open) {

			return $time_open <= $time_now && $time_now < $time_close;

		} else {

			return !($time_close <= $time_now && $time_now < $time_open);
		}
    }
}



function atbdp_time_calculation( $business_hours = [], $time, $day, $timezone = '' ) {
	
    $_start_times = $time['start'] ? $time['start'] : '';
    $_close_time  = $time['close'] ? $time['close'] : '';
    $dt           = new DateTime( 'now', new DateTimezone( $timezone ) );
    $current_time = $dt->format( 'g:i a' );
    $time_now     = DateTime::createFromFormat( 'H:i a', $current_time );

    if ( $_start_times ) {
        $business_times = [];
        foreach ( $_start_times as $index => $_start_time ) {
            $business_times[] = [
                'start' => $_start_time,
                'close' => $_close_time[$index],
            ];
        }
        foreach ( $business_times as $business_time ) {


            $start_time = date( 'h:i a', strtotime( esc_attr( $business_time['start'] ) ) );
            $close_time = date( 'h:i a', strtotime( esc_attr( $business_time['close'] ) ) );
            $time_start = DateTime::createFromFormat( 'H:i a', $start_time );
            $time_end   = DateTime::createFromFormat( 'H:i a', $close_time );


            $is_open = dbh_is_open( $start_time, $close_time, $timezone );

            if( $is_open ) {
                $_day = date( 'D' );
            }else{
                $_day = 'cls';
            }
           
            /*
             * time start as am (12.01 am to 11.58 am)
             * lets calculate time
             * is start time is smaller than current time and grater than close time
             */
            if (  ( $time_now >= $time_start ) && ( $time_now < $time_end ) ) {
                $_day = date( 'D' );
            }

            if ( empty( $time['enable'] ) ) {
                $_day = '';
            }
        }
    }

    //e_var_dump($time['remain_close']);
    $open_all_day = ( ! empty( $time['remain_close'] ) && 'open' === $time['remain_close'] ) ? 1 : '';
    $remain_close = ( ! empty( $time['remain_close'] ) && (  ( 'on' === $time['remain_close'] ) || ( '1' === $business_hours[$day]['remain_close'] ) ) ) ? 1 : '';
    if ( 1 == $open_all_day ) {
        $_day = date( 'D' );
    }
    if ( $remain_close ) {
        $_day = '';
    }
    return $_day;

}


if ( ! function_exists( 'atbdp_time_calculationn' ) ) {

    function atbdp_time_calculationn( $business_hours = [], $time, $day, $timezone = '' ) {

        $_day         = '';
        $interval     = DateTime::createFromFormat( 'H:i a', '11:59 am' );
        $dt           = new DateTime( 'now', new DateTimezone( $timezone ) );
        $current_time = $dt->format( 'g:i a' );
        $time_now     = DateTime::createFromFormat( 'H:i a', $current_time );

        $_start_time  = ! empty( $business_hours[$day]['start'] ) ? $business_hours[$day]['start'] : '';
        $_start_times = is_array( $_start_time ) ? $_start_time : [ $_start_time ];
        $_close_time  = ! empty( $business_hours[$day]['close'] ) ? $business_hours[$day]['close'] : '';
        $_close_time  = is_array( $_close_time ) ? $_close_time : [ $_close_time ];

        if ( $_start_times ) {
            $business_times = [];
            foreach ( $_start_times as $index => $_start_time ) {
                $business_times[] = [
                    'start' => $_start_time,
                    'close' => $_close_time[$index],
                ];
            }
            foreach ( $business_times as $business_time ) {


                $start_time = date( 'h:i a', strtotime( esc_attr( $business_time['start'] ) ) );
                $close_time = date( 'h:i a', strtotime( esc_attr( $business_time['close'] ) ) );
                $time_start = DateTime::createFromFormat( 'H:i a', $start_time );
                $time_end   = DateTime::createFromFormat( 'H:i a', $close_time );


                $is_open = dbh_is_open( $start_time, $close_time, $timezone );

                if( $is_open ) {
                    $_day = date( 'D' );
                }else{
                    $_day = 'cls';
                }
               
                /*
                 * time start as am (12.01 am to 11.58 am)
                 * lets calculate time
                 * is start time is smaller than current time and grater than close time
                 */
                if (  ( $time_now >= $time_start ) && ( $time_now < $time_end ) ) {
                    $_day = date( 'D' );
                }

                if ( empty( $business_hours[$day]['enable'] ) ) {
                    $_day = '';
                }
            }
        }

        //e_var_dump($business_hours[$day]['remain_close']);
        $open_all_day = ( ! empty( $business_hours[$day]['remain_close'] ) && 'open' === $business_hours[$day]['remain_close'] ) ? 1 : '';
        $remain_close = ( ! empty( $business_hours[$day]['remain_close'] ) && (  ( 'on' === $business_hours[$day]['remain_close'] ) || ( '1' === $business_hours[$day]['remain_close'] ) ) ) ? 1 : '';
        if ( 1 == $open_all_day ) {
            $_day = date( 'D' );
        }
        if ( $remain_close ) {
            $_day = '';
        }
        return $_day;
    }
}

if ( ! function_exists( 'atbdp_hours_badge' ) ) {
    function atbdp_hours_badge( $business_hours, $time, $day, $timezone ) {
        $open_close  = '';
        $open_       = get_directorist_option( 'open_badge_text', __( 'Open', 'directorist-business-hours' ) );
        $close_      = get_directorist_option( 'close_badge_text', 'Closed', 'directorist-business-hours' );
        $time        = atbdp_time_calculation( $business_hours, $time, $day, $timezone );
        $open_class  = directorist_legacy_mode() ? 'atbd_badge atbdp_info_list atbd_badge_open' : 'directorist-badge directorist-info-item directorist-badge-open';
        $close_class = directorist_legacy_mode() ? 'atbd_badge atbdp_info_list atbd_badge_close' : 'directorist-badge directorist-info-item directorist-badge-close';
        $_day        = date( 'D' );
        if ( 'cls' === $time ) {
            $open_close = '<span class="' . $close_class . '">' . $close_ . '</span>';
        } elseif ( $_day === $time ) {
            $open_close = '<span class="' . $open_class . '">' . $open_ . '</span>';
        }
        return $open_close;
    }
}

if ( ! function_exists( 'business_open_close_status' ) ) {
    function business_open_close_status( $business_hours, $time, $day, $timezone ) {
        $open_close = false;
        $time       = atbdp_time_calculation( $business_hours, $time, $day, $timezone );
        $_day       = date( 'D' );
        if ( 'cls' === $time ) {
            $open_close = false;
        } elseif ( $_day === $time ) {
            $open_close = true;
        }
        var_dump($open_close);
        return $open_close;
    }
}

function directorist_business_hours_get_template( $template_file, $args = [] ) {
    if ( is_array( $args ) ) {
        extract( $args );
    }

    $theme_template  = '/directorist-business-hours/' . $template_file . '.php';
    $plugin_template = BDBH_TEMPLATES_DIR . $template_file . '.php';

    if ( file_exists( get_stylesheet_directory() . $theme_template ) ) {
        $file = get_stylesheet_directory() . $theme_template;
    } elseif ( file_exists( get_template_directory() . $theme_template ) ) {
        $file = get_template_directory() . $theme_template;
    } else {
        $file = $plugin_template;
    }
    if ( file_exists( $file ) ) {
        include $file;
    }
}

if ( ! function_exists( 'directorist_open_close_badge_class' ) ) {
    function directorist_open_close_badge_class() {
        return directorist_legacy_mode() ? 'atbd_badge atbd_badge_close' : 'directorist-badge directorist-badge-success directorist-badge-open';
    }
}


/**
 * @param   array     $business_time Multislot business time of a single day
 * @param   string    $day Business day
 * @return  boolean   Business open or closed
 * @since   2.3.0
 */

if ( ! function_exists( 'directorist_business_open_by_hours' ) ) {
    function directorist_business_open_by_hours( $time, $day ) {
        $is_open = false;

        if( strtolower(date("l")) != $day ) {
            return false;
        }

        $current         = date('Y-m-d H:i:s');
        $starting_times  = ! empty( $time['start'] ) ? $time['start'] : [];
        $clasing_times   = ! empty( $time['close'] ) ? $time['close'] : [];

        foreach ( $starting_times as $index => $starting_time ) {
            
            if ( ! $starting_time ) {
                continue;
            }

            $start  = date( 'Y-m-d H:i:s', strtotime( $starting_time ) );
            $close  = date( 'Y-m-d H:i:s', strtotime( $clasing_times[$index] ) );
            
            if( directorist_calculated_status( $current, $start, $close ) ) {
                $is_open = true;
            }

        }

        return $is_open;
    }
}


/**
 * @param   object    $current Time time of defined timezone
 * @param   object    $start Time time of defined timezone
 * @param   object    $close Time time of defined timezone
 * @return  boolean   Business open or closed
 * @since   2.3.0
 */

if ( ! function_exists( 'directorist_calculated_status' ) ) {
    function directorist_calculated_status( $current, $start, $close ) {
        if( ( $start < $current && $close > $current) ){
            return true;
        }else{
            return false;
        }
    }
}

function directorist_calculated_statuss( $current, $start, $close ) {

    $f = DateTime::createFromFormat('!H:i', $start);
    $t = DateTime::createFromFormat('!H:i', $close);
    $i = DateTime::createFromFormat('!H:i', $current);
    if ($f > $t) $t->modify('+1 day');
    return ($f <= $i && $i <= $t) || ($f <= $i->modify('+1 day') && $i <= $t);
}

/**
 * It displays business hours in an unordered list
 * @param int $listing_id default get_the_ID()
 * @return void
 * @since   2.3.0
 */

if ( ! function_exists( 'directorist_business_open_close_status' ) ) {

    function directorist_business_open_close_status( $listing_id = '' ) {

        if( ! $listing_id && is_singular( ATBDP_POST_TYPE ) ) {
            $listing_id = get_the_ID();
        }
        
        $general_none   = get_directorist_option( 'timezone', 'America/New_York' );
        $listing_zone   = get_post_meta( $listing_id, '_timezone', true );
        $timezone       = $listing_zone ? $listing_zone : $general_none;

        $bdbh           = get_post_meta( $listing_id, '_bdbh', true );
        $for24_7        = get_post_meta( $listing_id, '_enable247hour', true );
        $disable_hours  = get_post_meta( $listing_id, '_disable_bz_hour_listing', true );
        $business_hours = ! empty( $bdbh ) ? atbdp_sanitize_array( $bdbh ) : array(); // arrays of days and times if exist
        
        date_default_timezone_set( $timezone );
        $today = strtolower(date("l"));
        
        if( $for24_7 ) {
            return true;
        }

        if( ! $business_hours ) {
            return false;
        }

        if( $disable_hours ) {
            return false;
        }

        $status = false;

        foreach( $business_hours as $day => $time ) {

            if ( $day != $today ) {
                continue;
            }

            if ( empty( $time['enable'] ) ) {
                $status = false;
                continue;
            }

            if ( ! empty( $time['remain_close'] ) && ( 'open' === $time['remain_close'] ) ) {
                $status = true;
                continue;
            }

            $is_open = directorist_business_open_by_hours( $time, $day );
            $status  = true;

            if ( empty( $is_open ) ) {
                $status = false;
            }

        }

        return $status;
    }
}

/**
 * It return the day string
 * @return string
 * @since  2.7.5
 */
if ( ! function_exists( 'directorist_day' ) ) {
    function directorist_day( $day ) {
        
        if( empty( $day ) ) {
            return '';
        }

        $days = [
            'saturday'  => __( 'Saturday', 'directorist-business-hours' ),
            'sunday'    => __( 'Sunday', 'directorist-business-hours' ),
            'monday'    => __( 'Monday', 'directorist-business-hours' ),
            'tuesday'   => __( 'Tuesday', 'directorist-business-hours' ),
            'wednesday' => __( 'Wednesday', 'directorist-business-hours' ),
            'thursday'  => __( 'Thursday', 'directorist-business-hours' ),
            'friday'    => __( 'Friday', 'directorist-business-hours' ),
        ];
        return array_key_exists( $day, $days ) ? $days[$day] : '';
    }
}


/**
 * It displays business hours in an unordered list
 * @return void
 * @since  2.3.0
 */

if ( ! function_exists( 'show_business_hours' ) ) {

    function show_business_hours( $listing_id = '' ) {


        if( ! $listing_id ) {
            $listing_id     = get_the_ID();
        }
        $general_none   = get_directorist_option( 'timezone', 'America/New_York' );
        $time_format    = get_directorist_option( 'atbh_time_format', '12' );
        $listing_zone   = get_post_meta( $listing_id, '_timezone', true );
        $timezone       = $listing_zone ? $listing_zone : $general_none;
        $bdbh           = get_post_meta( $listing_id, '_bdbh', true );
        $disabled       = get_post_meta( $listing_id, '_disable_bz_hour_listing', true );
        $bdbh_version   = get_post_meta( $listing_id, '_bdbh_version', true );
        $business_hours = !empty( $bdbh ) ? atbdp_sanitize_array( $bdbh ) : array(); // arrays of days and times if exist
        date_default_timezone_set( $timezone );
        $today          = strtolower(date("l"));
        $current        = date('Y-m-d H:i:s');


        if( ! $business_hours ) {
            return;
        }

        if( $disabled ) {
            return;
        }
        ?> <ul> <?php
        foreach( $business_hours as $day => $time ) {

            if ( ! $time ) {
                continue;
            }

            $starting_times  = ! empty( $time['start'] ) ? $time['start'] : '';
            $clasing_times   = ! empty( $time['close'] ) ? $time['close'] : '';

            $is_open = directorist_business_open_close_status( $listing_id );
            if ( ! $starting_times ) {
                continue;
            }

            $the_day    = $day == $today ? ' directorist-open-hours__today' : '';
            $open__now  = $is_open ? 'directorist-open-hours__open' : 'directorist-open-hours__closed';

            ?>
            <li class="<?php echo esc_attr( $open__now ); echo esc_attr( $the_day ); ?>">

            <span class="directorist-business-day"><?php echo esc_html( directorist_day( $day ) ); ?></span>

            <?php if( ( empty( $bdbh_version ) ) && ( ! empty( $time['remain_close'] ) && (  ( 'on' === $time['remain_close'] ) || ( '1' === $time['remain_close'] ) ) ) ) { ?>

            <span><?php _e( 'Closed', 'directorist-business-hours' )?></span>

            <?php }elseif( ! empty( $bdbh_version ) && empty( $time['enable'] ) ) { ?>

            <span><?php _e( 'Closed', 'directorist-business-hours' )?></span>

            <?php }else{ ?>

            <div class="directorist-open-hours__time">

            <?php if ( ! empty( $time['remain_close'] ) && ( 'open' === $time['remain_close'] ) ) { ?>

            <span class="time"><?php echo __( 'Open 24h', 'directorist-business-hours' ); ?></span>

            <?php }else{
            
            
            // time-slots
            foreach ( $starting_times as $index => $starting_time ) {
                

                if ( ! $starting_time ) {
                    continue;
                }

                $start  = date( 'h:i a', strtotime( $starting_time ) );
                $close  = date( 'h:i a', strtotime( $clasing_times[$index] ) );

                if ( '24' == $time_format ) {
                    $start = DateTime::createFromFormat( 'H:i a', $start )->format( 'H:i' );
                    $close = DateTime::createFromFormat( 'H:i a', $close )->format( 'H:i' );
                }

                $start_  = date( 'Y-m-d H:i:s', strtotime( $starting_time ) );
                $close_  = date( 'Y-m-d H:i:s', strtotime( $clasing_times[$index] ) );

                //print time for multi slot
                $open_slot = '';

                if( directorist_calculated_status( $current, $start_, $close_ ) ) {
                    $open_slot = 'directorist-open-hours__open_slot';
                }
                ?>
                <div class='directorist-time-single <?php echo esc_attr( $open_slot ); ?>'>
                    <span class="time directorist-start-time"><?php echo $start; ?></span> - <span class="time directorist-close-time"><?php echo $close; ?></span>
                </div>
                <?php
            } // end foreach of timeslot
            } // if not 24*7
            } // if not close show hours
            ?> </li> <?php
        } // end foreach of days
        ?> </ul> <?php

    }
}

/**
 * It displays business badges
 * @return void
 * @since  2.3.0
 */

if ( ! function_exists( 'directorist_show_open_close_badge' ) ) {

    function directorist_show_open_close_badge( $listing_id = '' ) {

        $open = get_directorist_option('open_badge_text', 'Open' );
        $close = get_directorist_option('close_badge_text', 'Closed' );

        $status = directorist_business_open_close_status( $listing_id );

        if( $status ) { ?>
            <span class="directorist-badge directorist-info-item directorist-badge-open"><?php echo esc_attr( $open ); ?></span>
        <?php
        }else{ ?>
            <span class="directorist-badge directorist-info-item directorist-badge-close"><?php echo esc_attr( $close ); ?></span>
        <?php }


    }
}

/**
 * 
 * @return bool If cache_plugin_compatibility option is enabled or disabled
 * @since  2.3.0
 */

if ( ! function_exists( 'directorist_hours_cache_plugin_compatibility' ) ) {

    function directorist_hours_cache_plugin_compatibility() {

        return get_directorist_option('cache_plugin_compatibility', false );

    }
}

