<?php
/*
Plugin Name: [PC] Form Contact
Plugin URI: www.papier-code.fr
Description: Formulaire de contact
Version: 3.2.0
Author: Papier Codé
*/


/*=====================================================
=            Post Messages & form settings            =
=====================================================*/

add_action( 'after_setup_theme', 'pc_contact_form_setup' );

	function pc_contact_form_setup() {

		// post
		define('FORM_CONTACT_POST_SLUG', 'contact');
		include 'post/post.php';
		include 'post/post-fields.php';
		include 'post/post-admin.php';

		// paramètres
		include 'form/form-admin.php';
		global $settings_form_contact;
		$settings_form_contact = get_option('form-contact-settings-option');

	}


/*=====  FIN Post Messages & form settings  =====*/

/*========================================================
=            Ajout de l'option dans les pages            =
========================================================*/
	
add_filter( 'pc_filter_settings_project', 'pc_form_contact_edit_content_from', 10, 1 );

function pc_form_contact_edit_content_from( $settings_project ) {

	$settings_project['page-content-from']['contactform'] = array(
		'Formulaire de contact',
		dirname( __FILE__ ).'/form/form-template.php'
	);

	return $settings_project;
	
}


/*=====  FIN Ajout de l'option dans les pages  =====*/

/*==============================================
=            Création du formulaire            =
==============================================*/

add_action( 'wp', 'pc_contact_form_init', 100 );

	function pc_contact_form_init() {

		include 'form/class-pc-contact-form.php';

		global $pc_post, $post_contact_fields, $pc_contact_form;

		if ( class_exists( 'PC_Contact_Form' ) ) {
			$pc_contact_form = new PC_Contact_Form( $post_contact_fields, $pc_post );
		}

	}


/*=====  FIN Création du formulaire  =====*/