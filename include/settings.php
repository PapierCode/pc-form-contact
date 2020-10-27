<?php

/**
 * 
 * Formulaire de contact : configuration 
 * 
 */

/*==================================
=            Formulaire            =
==================================*/

/*----------  Textes  ----------*/

global $form_contact_texts;
$form_contact_texts = array(
	'label-required'	=> ' <span class="form-label-required">(obligatoire)</span>',
	'label-recaptcha'	=> 'Protection contre les spams',
	'submit-txt'		=> 'Envoyer',
	'submit-title'		=> 'Envoyer un e-mail',
	'msg-field-error'	=> 'Le formulaire contient des erreurs.',
	'msg-mail-sent'		=> 'Le message est envoyé.',
	'msg-mail-fail'		=> 'Une erreur est survenue, merci de valider à nouveau le formulaire.'
);

$form_contact_texts = apply_filters( 'pc_filter_form_contact_texts', $form_contact_texts );

 
/*=====  FIN Formulaire  =====*/

/*=============================================
=            Définition des champs            =
=============================================*/

if ( class_exists('PC_Add_metabox') ) {
	
	// champs
	$form_contact_post_fields = array(
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
	$form_contact_post_fields = apply_filters( 'pc_filter_post_contact_fields', $form_contact_post_fields );

	// déclaration
	$form_contact_post_fields_declaration = new PC_Add_Metabox( FORM_CONTACT_POST_SLUG, 'Champs', 'message-fields', $form_contact_post_fields, 'normal', 'low' );
	
	
} // FIN if class_exist()

	
/*=====  FIN Définition des champs  =====*/

/*================================================
=            Paramètres du formulaire            =
================================================*/

global $form_contact_datas;
$form_contact_datas = array(
	'css' => 'form form--contact layout-sub',
	'recaptacha' => false,
	'errors' => array(
		'global-error' 		=> false, // erreur globale
		'spam-error'		=> false, // erreur recaptcha
		'mail-sent' 		=> false, // validation envoi email
		'mail-sent-error'	=> false, // erreur envoi email
	),
	'fields' => array()
);

// propriétés des champs d'après ceux du post
foreach ( $form_contact_post_fields['fields'] as $field ) {
	$form_contact_datas['fields'][$form_contact_post_fields['prefix'].'-'.$field['id']] = $field;
}
// filtre
$form_contact_datas = apply_filters( 'pc_filter_form_contact_datas', $form_contact_datas );


/*=====  FIN Paramètres du formulaire  =====*/
