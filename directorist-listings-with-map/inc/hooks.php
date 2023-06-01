<?php
// prevent direct access to the file
defined('ABSPATH') || die('No direct script access allowed!');
class BDMV_Hooks {
    public function __construct()
    {
        //add parameter on all listings shortcode
       add_filter('atbdp_all_listings_params',array($this,'all_listings_param'));
       add_filter('atbdp_search_results_param',array($this,'search_results_param'));
       add_filter('atbdp_single_cat_param',array($this,'single_category_param'));
       add_filter('atbdp_single_location_param',array($this,'single_location_param'));
       add_filter('atbdp_single_tag_param',array($this,'single_tag_param'));
    }

    public function all_listings_param($params) {
        $listings_with_map_columns        = get_directorist_option('bdmv_listings_with_map_columns','3');
        $listing_map_filters = get_directorist_option('listing_map_filters',array('search_text', 'search_category', 'search_location', 'search_price', 'search_price_range', 'search_rating', 'search_tag', 'search_custom_fields', 'radius_search'));
        $listings_map_filters_button = get_directorist_option('listings_map_filters_button',array('reset_button','apply_button'));
        $params['listings_with_map_columns'] = !empty($listings_with_map_columns) ? $listings_with_map_columns : '3';
        $params['listings_with_map_filter_fields'] = !empty($listing_map_filters) ? $listing_map_filters : '';
        return $params;
    }

    public function search_results_param($params) {
        $listings_with_map_columns        = get_directorist_option('bdmv_listings_with_map_columns','3');
        $listing_map_filters = get_directorist_option('listing_map_filters',array('search_text', 'search_category', 'search_location', 'search_price', 'search_price_range', 'search_rating', 'search_tag', 'search_custom_fields', 'radius_search'));
        $listings_map_filters_button = get_directorist_option('listings_map_filters_button',array('reset_button','apply_button'));
        $params['listings_with_map_columns'] = !empty($listings_with_map_columns) ? $listings_with_map_columns : '3';
        $params['listings_with_map_filter_fields'] = !empty($listing_map_filters) ? $listing_map_filters : '';
        return $params;
    }

    public function single_category_param($params) {
        $listings_with_map_columns        = get_directorist_option('bdmv_listings_with_map_columns','3');
        $listing_map_filters = get_directorist_option('listing_map_filters',array('search_text', 'search_category', 'search_location', 'search_price', 'search_price_range', 'search_rating', 'search_tag', 'search_custom_fields', 'radius_search'));
        $listings_map_filters_button = get_directorist_option('listings_map_filters_button',array('reset_button','apply_button'));
        $params['listings_with_map_columns'] = !empty($listings_with_map_columns) ? $listings_with_map_columns : '3';
        $params['listings_with_map_filter_fields'] = !empty($listing_map_filters) ? $listing_map_filters : '';
        return $params;
    }

    public function single_location_param($params) {
        $listings_with_map_columns        = get_directorist_option('bdmv_listings_with_map_columns','3');
        $listing_map_filters = get_directorist_option('listing_map_filters',array('search_text', 'search_category', 'search_location', 'search_price', 'search_price_range', 'search_rating', 'search_tag', 'search_custom_fields', 'radius_search'));
        $listings_map_filters_button = get_directorist_option('listings_map_filters_button',array('reset_button','apply_button'));
        $params['listings_with_map_columns'] = !empty($listings_with_map_columns) ? $listings_with_map_columns : '3';
        $params['listings_with_map_filter_fields'] = !empty($listing_map_filters) ? $listing_map_filters : '';
        return $params;
    }

    public function single_tag_param($params) {
        $listings_with_map_columns        = get_directorist_option('bdmv_listings_with_map_columns','3');
        $listing_map_filters = get_directorist_option('listing_map_filters',array('search_text', 'search_category', 'search_location', 'search_price', 'search_price_range', 'search_rating', 'search_tag', 'search_custom_fields', 'radius_search'));
        $listings_map_filters_button = get_directorist_option('listings_map_filters_button',array('reset_button','apply_button'));
        $params['listings_with_map_columns'] = !empty($listings_with_map_columns) ? $listings_with_map_columns : '3';
        $params['listings_with_map_filter_fields'] = !empty($listing_map_filters) ? $listing_map_filters : '';
        return $params;
    }
} //end class