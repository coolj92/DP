<?php
namespace SWBDP_DIRLINK\Setup;

class Settings {

	public function register() {

		add_filter( 'atbdp_extension_fields', array( $this, 'atbdp_extension_fields' ) );
		add_filter( 'atbdp_listing_type_settings_field_list', array( $this, 'atbdp_listing_type_settings_field_list' ) );
		

	}

	public function __construct() {
		// Add authorize.net gateway to the active gateway & default gateways selections.
		
	}

	public function atbdp_extension_fields(  $fields ) {
        $fields[] = ['enable_linking_type'];
        return $fields;
    }

	public function atbdp_listing_type_settings_field_list( $linking_fields ) {
        $linking_fields['enable_linking_type'] = [
            'label'             => __('Directory Linking Type', 'directorist-directory-linking'),
            'type'              => 'toggle',
            'value'             => true,
            'description'       => __('Allow users add and display Link Post for a listing.', 'directorist-directory-linking'),
        ];
		
	
       
        
        return $linking_fields;
    }

	

}
