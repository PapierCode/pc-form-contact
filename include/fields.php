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
	$post_contact_fields = array(
		'prefix'        => 'contact',
		'fields'        => array(
			array(
				'type'      		=> 'text',
				'id'        		=> 'last-name',
				'label'     		=> 'Nom',
				'attr'				=> 'readonly',
            	'css'       		=> 'width:100%',
				'email-from-name'	=> true // pour la notification mail
			),
			array(
				'type'      		=> 'text',
				'id'        		=> 'name',
				'label'     		=> 'Prénom',
				'attr'				=> 'readonly',
				'css'       		=> 'width:100%',
			),
			array(
				'type'      		=> 'text',
				'id'        		=> 'phone',
				'label'     		=> 'Téléphone',
				'attr'				=> 'readonly',
				'css'       		=> 'width:100%',
			),
			array(
				'type'      		=> 'email',
				'id'        		=> 'mail',
				'label'     		=> 'E-mail',
				'attr'				=> 'readonly',
            	'css'       		=> 'width:100%',
				'required' 	    	=> true
			),
			array(
				'type'      		=> 'textarea',
				'id'        		=> 'message',
				'label'     		=> 'Message',
				'attr'				=> 'readonly',
            	'css'       		=> 'width:100%',
				'form-attr'			=> 'rows="5"', // pour le formulaire public
				'required' 	    	=> true
			),
			array(
				'type'      		=> 'checkbox',
				'id'        		=> 'cgu',
				'label'     		=> 'CGU acceptées',
				'attr'				=> 'disabled',
				'required' 	    	=> true,
				'form-label'		=> 'J\'ai lu et j\'accepte les <a href="{{cgu}}">conditions générales d\'utilisation</a>', // pour le formulaire public
				'form-desc'			=> 'Les données saisies dans ce formulaire nous sont réservées et ne seront pas cédées ou revendues à des tiers.', // pour le formulaire public,
				'email-not-in'		=> true // pour la notification mail

			)
		)
	);

	// filtre
	$post_contact_fields = apply_filters( 'pc_filter_post_contact_fields', $post_contact_fields );

	// déclaration
	$register_post_contact = new PC_Add_Metabox( FORM_CONTACT_POST_SLUG, 'Champs', 'message-fields', $post_contact_fields, 'normal', 'low' );
	
	
} // FIN if class_exist()

	
/*=====  FIN Définition des champs  =====*/
