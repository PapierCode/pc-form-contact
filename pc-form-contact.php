<?php
/*
Plugin Name: [PC] Form Contact
Plugin URI: www.papier-code.fr
Description: Formulaire de contact
Version: 3.1.0
Author: Papier Codé
*/


/*=================================================
=            Création du post Messages            =
=================================================*/

add_action( 'after_setup_theme', function() {

		// post
		define('FORM_CONTACT_POST_SLUG', 'contact');
		include 'include/post.php';
		include 'include/post-fields.php';
		include 'include/post-admin.php';

		// paramètres
		include 'include/settings.php';
		global $settings_form_contact;
		$settings_form_contact = get_option('form-contact-settings-option');

});


/*=====  FIN Création du post Messages  =====*/

/*========================================================
=            Ajout de l'option dans les pages            =
========================================================*/
	
add_filter( 'pc_filter_settings_project', 'pc_form_contact_edit_content_from', 10, 1 );

function pc_form_contact_edit_content_from( $settings_project ) {

	$settings_project['page-content-from']['contactform'] = array(
		'Formulaire de contact',
		dirname( __FILE__ ).'/include/template.php'
	);

	return $settings_project;
	
}


/*=====  FIN Ajout de l'option dans les pages  =====*/

/*==============================================
=            Création du formulaire            =
==============================================*/

add_action( 'wp', function() {

		include 'include/class-pc-contact-form.php';

		global $pc_post, $post_contact_fields, $pc_contact_form;

		if ( class_exists( 'PC_Contact_Form' ) ) {
			$pc_contact_form = new PC_Contact_Form( $post_contact_fields, $pc_post );
		}

}, 100 );


/*=====  FIN Création du formulaire  =====*/