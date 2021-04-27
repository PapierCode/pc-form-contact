<?php

/*
Plugin Name: [PC] Form Contact
Plugin URI: www.papier-code.fr
Description: Formulaire de contact
Version: 2.0.2
Author: Papier Codé
*/


/*================================
=            Includes            =
================================*/

add_action( 'plugins_loaded', 'pc_plugin_form_contact_init' );

	function pc_plugin_form_contact_init() {

		// fonctions
		include 'include/fn-display-fields.php';
		include 'include/fn-validation.php';

		// post
		define('FORM_CONTACT_POST_SLUG', 'contact');
		include 'include/register.php';
		// post : champs
		include 'include/fields.php';

		// formulaire : textes
		global $form_contact_texts;
		$form_contact_texts = apply_filters( 'pc_filter_form_contact_texts', array(
			'label-required'	=> ' <span class="form-label-required">(obligatoire)</span>',
			'label-recaptcha'	=> 'Protection contre les spams',
			'submit-txt'		=> 'Envoyer',
			'submit-title'		=> 'Envoyer un e-mail',
			'msg-field-error'	=> 'Le formulaire contient des erreurs.',
			'msg-mail-sent'		=> 'Le message est envoyé.',
			'msg-mail-fail'		=> 'Une erreur est survenue, merci de valider à nouveau le formulaire.'
		) );

		// formulaire : configuration globale  
		global $form_contact_args;
		$form_contact_args = array(
			'css' => 'form form--contact',
			'recaptacha' => false,
			'errors' => array(
				'global-error' 		=> false, // erreur globale
				'spam-error'		=> false, // erreur recaptcha
				'mail-sent' 		=> false, // validation envoi email
				'mail-sent-error'	=> false, // erreur envoi email
			),
			'fields' => array()
		);

		// formulaire : champs
		foreach ( $post_contact_fields['fields'] as $field ) {
			$form_contact_args['fields'][$post_contact_fields['prefix'].'-'.$field['id']] = $field;
		}
		// formulaire : filtre configuration
		$form_contact_args = apply_filters( 'pc_filter_form_contact_datas', $form_contact_args );

		// paramètres administrables
		include 'include/settings.php';
		global $settings_form_contact;
		$settings_form_contact = get_option('form-contact-settings-option');

		// modification administration
		include 'include/admin.php';

	}


/*=====  FIN Includes  =====*/

/*========================================================
=            Ajout de l'option dans les pages            =
========================================================*/
	
add_filter( 'pc_filter_settings_project', 'pc_form_contact_edit_content_from', 10, 1 );

	function pc_form_contact_edit_content_from( $settings_project ) {

		$settings_project['page-content-from']['contactform'] = array(
			'Formulaire de contact',
			dirname( __FILE__ ).'/include/template-form.php'
		);

		return $settings_project;
		
	}


/*=====  FIN Ajout de l'option dans les pages  =====*/

/*================================================
=            Traitement du formulaire            =
================================================*/
	
// avant le chargement de la template    
add_action( 'template_redirect', 'pc_form_contact_validation', 999 );

	function pc_form_contact_validation() {
		
		$post_id = get_the_ID();

		/*----------  Conditions d'affichage  ----------*/            

		if ( !is_page() || get_post_meta( $post_id, 'content-from', true ) != 'contactform' ) { return; }

		
		/*===================================================
		=            Configuration du formulaire            =
		===================================================*/
		
		// $settings_pc : configuration projet, cf. plugin [PC] Custom WP
		// $form_contact_args : paramètres formulaire, cf. settings.php
		global $settings_pc, $settings_project, $form_contact_args;
		
		// création du captcha
		if ( $settings_pc['google-recaptcha-site'] != '' && $settings_pc['google-recaptcha-secret'] != '' ) {
			$form_contact_args['recaptacha'] = new PC_recaptcha( $settings_pc['google-recaptcha-site'], $settings_pc['google-recaptcha-secret'] );
		}
		

		/*=====  FIN Configuration du formulaire  =====*/

		/*=====================================================
		=            Si le formulaire a été validé            =
		=====================================================*/

		if ( !empty( $_POST ) ) {

			/*----------  Vérification des champs obligatoires  ----------*/                    
			
			// si le navigateur n'a pas gèré l'attribut required
			$form_contact_args = pc_form_contact_required_fields( $form_contact_args );
			// si erreur captcha
			if ( $form_contact_args['recaptacha'] && $form_contact_args['recaptacha']->isValid( $_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR'] ) === false ) {
				$form_contact_args['errors']['spam-error'] = true;
				$form_contact_args['errors']['global-error'] = true;
			}
			

			/*----------  Si pas d'erreur  ----------*/

			if ( !$form_contact_args['errors']['global-error'] ) {

			
				/*----------  Préparation des valeurs  ----------*/
				
				$form_contact_args['fields'] = pc_form_contact_sanitize( $form_contact_args['fields'] );
			
				
				/*----------  envoi de l'email, enregistrement du post et validation  ----------*/
				
				if ( pc_form_contact_notification( $form_contact_args['fields'], $post_id ) ) { 
				
					// enregistrement du post
					pc_form_contact_save_post( $form_contact_args['fields'], $post_id );
					// validation
					$form_contact_args['errors']['mail-sent'] = true;
					// affichage dans la meta title
					add_filter( 'pc_filter_seo_metas', 'pc_form_seo_metas_mail_sent' );
						function pc_form_seo_metas_mail_sent( $seo_metas ) {
							global $form_contact_texts;
							$seo_metas['title'] = $form_contact_texts['msg-mail-sent'];
							return $seo_metas;
						}
					
				} else {

					// problème lors de l'envoi de l'email
					$form_contact_args['errors']['mail-sent-error'] = true;
					// affichage dans la meta title
					add_filter( 'pc_filter_seo_metas', 'pc_form_seo_metas_mail_error' );
						function pc_form_seo_metas_mail_error( $seo_metas ) {
							global $form_contact_texts;
							$seo_metas['title'] = $form_contact_texts['msg-mail-fail'];
							return $seo_metas;
						};

				}
				

			} // FIN if !form_contact_global_error                    

		} /*=====  FIN Si le formulaire a été validé  =====*/


	} // FIN pc_form_contact_validation()

/*=====  FIN Traitement du formulaire  =====*/