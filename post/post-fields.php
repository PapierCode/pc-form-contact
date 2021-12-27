<?php

/**
 * 
 * [PC] Form Contact : champs du post 
 * 
 */

/*=============================================
=            Définition des champs            =
=============================================*/

if ( class_exists('PC_Add_metabox') ) {
	
	// champs
	global $post_contact_fields;
	$post_contact_fields = array(
		'prefix'        => 'contact',
		'fields'        => array(
			array(
				'type'      				=> 'text',
				'id'        				=> 'last-name',
				'label'     				=> 'Nom',
				'label-en'     				=> 'Last Name',
				'attr'						=> 'readonly',
            	'css'       				=> 'width:100%',
				'form-attr'					=> 'autocomplete="family-name"', // pour le formulaire public
				'notification-from-name'	=> true // pour la notification mail
			),
			array(
				'type'      				=> 'text',
				'id'        				=> 'name',
				'label'     				=> 'Prénom',
				'label-en'     				=> 'First Name',
				'attr'						=> 'readonly',
				'css'       				=> 'width:100%',
				'form-attr'					=> 'autocomplete="given-name"', // pour le formulaire public
			),
			array(
				'type'      				=> 'text',
				'id'        				=> 'phone',
				'label'     				=> 'Téléphone',
				'label-en'     				=> 'Phone',
				'attr'						=> 'readonly',
				'css'       				=> 'width:100%',
				'form-attr'					=> 'autocomplete="tel"'
			),
			array(
				'type'      				=> 'email',
				'id'        				=> 'mail',
				'label'     				=> 'E-mail',
				'label-en'     				=> 'E-mail',
				'attr'						=> 'readonly',
            	'css'       				=> 'width:100%',
				'required' 	    			=> true,
				'notification-from-email'	=> true, // pour la notification mail
				'form-attr'					=> 'autocomplete="email"'
			),
			array(
				'type'      				=> 'textarea',
				'id'        				=> 'message',
				'label'     				=> 'Message',
				'label-en'     				=> 'Message',
				'attr'						=> 'readonly',
            	'css'       				=> 'width:100%',
				'form-attr'					=> 'rows="5"', // pour le formulaire public
				'required' 	    			=> true
			),
			array(
				'type'      				=> 'checkbox',
				'id'        				=> 'cgu',
				'label'     				=> 'CGU acceptées',
				'attr'						=> 'disabled',
				'required' 	    			=> true,
				'form-rgpd'					=> true,
				'form-label'				=> 'J\'ai lu et j\'accepte les <a href="{{cgu}}">conditions générales d\'utilisation</a>', // pour le formulaire public
				'form-label-en'				=> 'I have read and accept the <a href="{{cgu}}">general conditions of use</a>', // pour le formulaire public
				'form-desc'					=> 'Les données saisies dans ce formulaire nous sont réservées et ne seront pas cédées ou revendues à des tiers.', // pour le formulaire public,
				'form-desc-en'				=> 'The data entered in this form is reserved for us and will not be transferred or sold to third parties.', // pour le formulaire public,
				'notification-not-in'		=> true // pour la notification mail

			)
		)
	);

	// filtre
	$post_contact_fields = apply_filters( 'pc_filter_post_contact_fields', $post_contact_fields );

	// déclaration
	$register_post_contact = new PC_Add_Metabox( FORM_CONTACT_POST_SLUG, 'Valeurs saisies', 'message-fields', $post_contact_fields, 'normal', 'low' );
	
	
} // FIN if class_exist()

	
/*=====  FIN Définition des champs  =====*/
