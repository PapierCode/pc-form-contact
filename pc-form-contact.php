<?php
/*
Plugin Name: [PC] Form Contact
Plugin URI: www.papier-code.fr
Description: Formulaire de contact
Version: 4.0.0
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

add_filter( 'pc_filter_settings_project_fields', 'pc_contact_settings' );

function pc_contact_settings( $settings ) {

	$settings[] = array(
		'title'     => 'Notification de nouveau message',
		'id'        => 'contact-form',
		'prefix'    => 'form',
		'fields'    => array(
			array(
				'type'      => 'text',
				'label_for' => 'for',
				'label'     => 'Destinataire(s)',
				'desc'      => '1 ou plusieurs e-mails séparés par des virgules, sans espaces.',
				'css'       => 'width:100%;',
				'attr'      => 'placeholder="contact@mon-site.fr,devis@gmail.com"',
				'required'  => true
			),
			array(
				'type'      => 'text',
				'label_for' => 'subject',
				'label'     => 'Sujet',
				'css'       => 'width:100%;',
				'attr'      => 'placeholder="Formulaire de contact"',
				'required'  => true
			)
		)
	);
	return $settings;
}


/*=====  FIN Ajout des options dans les réglages  =====*/

/*========================================================
=            Ajout de l'option dans les pages            =
========================================================*/
	
add_filter( 'pc_filter_settings_project', 'pc_form_contact_edit_content_from', 10, 1 );

function pc_form_contact_edit_content_from( $settings_project ) {

	$settings_project['page-content-from']['contactform'] = array(
		'Formulaire de contact',
		dirname( __FILE__ ).'/form-template.php'
	);

	return $settings_project;
	
}


/*=====  FIN Ajout de l'option dans les pages  =====*/

/*==============================================
=            Création du formulaire            =
==============================================*/

add_action( 'wp', 'pc_contact_form_init', 100 );

	function pc_contact_form_init() {

		if ( is_page() ) {

			global $pc_post;
			$metas = $pc_post->metas;

			if ( isset( $metas['content-from'] ) && 'contactform' == $metas['content-from'] ) {

				include 'class-pc-contact-form.php';

				global $post_contact_fields, $pc_contact_form;

				if ( class_exists( 'PC_Contact_Form' ) ) {
					$pc_contact_form = new PC_Contact_Form( $post_contact_fields, $pc_post );
				}

			}

		}

	}


/*=====  FIN Création du formulaire  =====*/