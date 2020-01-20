<?php

/*
Plugin Name: [PC] Form Contact
Plugin URI: www.papier-code.fr
Description: Formulaire de contact
Version: 1.0.0
Author: Papier Codé
*/


add_action('after_setup_theme', function() { // en attente du plugin [PC] Tools
   
    define('CONTACT_POST_SLUG', 'contact');

    include 'include/form-contact_post.php';
    include 'include/form-contact_admin.php';

    global $settings_form_contact;
    $settings_form_contact = get_option('form-contact-settings-option');
    
    include 'include/form-contact_display-fields.php';
    include 'include/form-contact_settings.php';


    add_filter( 'pc_filter_page_content_from', 'pc_add_form_contact_to_page', 10, 1 );

        function pc_add_form_contact_to_page( $page_content_from ) {

            $page_content_from['contactform'] = array(
                'Formulaire de contact',
                dirname( __FILE__ ).'/include/form-contact_template.php'
            );

            return $page_content_from;
            
        }

});

