<?php
// prevent direct access to the file
defined('ABSPATH') || die('No direct script access allowed!');
class BDMV_Ajax {
    public function __construct()
    {
        add_action('wp_ajax_ajax_search_listing', array($this, 'ajax_search_listing'));
        add_action('wp_ajax_nopriv_ajax_search_listing', array($this, 'ajax_search_listing'));
    }

    public function ajax_search_listing() {
        ob_start();

        $listings = new Listings_With_Map_Model();
        $listings->setup_ajax_data();

        echo ob_get_clean();
        die;
    }

} //end class