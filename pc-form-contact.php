<?php
/*
Plugin Name: [PC] Form Contact
Plugin URI: www.papier-code.fr
Description: Formulaire de contact
Version: 3.8.0
Author: Papier Codé
*/


/*===========================================================
=            Ajout des options dans les réglages            =
===========================================================*/

add_filter( 'pc_filter_settings_pc_fields', 'pc_contact_edit_settings_pc_fields' );

function pc_contact_edit_settings_pc_fields( $settings_pc_fields ) {

	$settings_pc_fields[] = array(
		'title'     => 'Formulaire de contact',
		'id'        => 'contactform',
		'prefix'    => 'contactform',
		'fields'    => array(
			array(
				'type'      => 'radio',
				'label_for' => 'captcha',
				'label'     => 'Type de captcha',
				'options'	=> array(
					'Calcul'	=> 'math',
					'hCaptcha' 	=> 'hcaptcha'
				),
				'default'	=> 'math'
			),
			array(
				'type'      => 'text',
				'label_for' => 'math-pass',
				'label'     => 'Calcul : mot de passe',
				'css'		=> 'width:100%'
			),
			array(
				'type'      => 'text',
				'label_for' => 'math-iv',
				'label'     => 'Calcul : vecteur',
				'attr'		=> 'maxlength="16"',
				'desc'		=> '16 caractères.',
				'css'		=> 'width:100%'
			)
		)
	);

	return $settings_pc_fields;

}


/*=====  FIN Ajout des options dans les réglages  =====*/

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

/*==============================================
=            Création du formulaire            =
==============================================*/

add_action( 'wp', 'pc_contact_form_init', 100 );

	function pc_contact_form_init() {

		if ( is_page() ) {

			global $pc_post;
			$metas = $pc_post->metas;

			if ( isset( $metas['content-from'] ) && 'contactform' == $metas['content-from'] ) {

				include 'form/class-pc-contact-form.php';

				global $post_contact_fields, $pc_contact_form;

				if ( class_exists( 'PC_Contact_Form' ) ) {
					$pc_contact_form = new PC_Contact_Form( $post_contact_fields, $pc_post );
				}

			}

		}

	}


/*=====  FIN Création du formulaire  =====*/

/*========================================
=            CRON suppression            =
========================================*/

if ( !wp_next_scheduled( 'pc_form_contact_cron' ) ) { 	wp_schedule_event( time(), 'daily', 'pc_form_contact_cron' ); }

add_action( 'pc_form_contact_cron', 'pc_form_contact_cron_delete_messages' );

	function pc_form_contact_cron_delete_messages() {

		$posts_to_delete = get_posts( array(
			'post_type' => FORM_CONTACT_POST_SLUG,
			'posts_per_page' => -1,
			'date_query' => array(
				array(
					'column' => 'post_date_gmt',
					'before' => '1 year ago',
				)
			)
		));

		$nb_posts_deleted = 0;

		if ( !empty( $posts_to_delete ) ) {

			foreach ( $posts_to_delete as $post ) {
				wp_delete_post( $post->ID, true );
				$nb_posts_deleted++;
			}		

			mail( 'papiercode@gmail.com', 'PC Form Contact', $nb_posts_deleted.' suppression(s) pour '.get_bloginfo( 'name' ) );

		}

	}


/*=====  FIN CRON suppression  =====*/