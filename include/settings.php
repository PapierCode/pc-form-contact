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
	'title'				=> 'Formulaire de contact',
	'label-required'	=> '&nbsp;<abbr title="Champ obligatoire" class="form-required">*</abbr>',
	'label-recaptcha'	=> 'Protection contre les spams',
	'submit-txt'		=> 'Envoyer',
	'submit-title'		=> 'Envoyer un e-mail',
	'msg-field-error'	=> 'Le formulaire contient des erreurs.',
	'msg-mail-sent'		=> 'Le message est envoyé.',
	'msg-mail-fail'		=> 'Une erreur est survenue, merci de valider à nouveau le formulaire.',
);

$form_contact_texts = apply_filters( 'pc_filter_form_contact_texts', $form_contact_texts );

 
/*=====  FIN Formulaire  =====*/

/*=============================================
=            Définition des champs            =
=============================================*/

if ( class_exists('PC_Add_metabox') ) {

	global $settings_form_contact;
	
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
				'form-label'		=> 'J\'ai lu et j\'accepte la <a href="'.$settings_form_contact['cgu-page'].'" title="Politique de confidentialité">Politique de confidentialité</a>', // pour le formulaire public
				'form-desc'			=> 'Les données saisies dans ce formulaire nous sont réservées et ne seront pas cédées ou revendues à des tiers.', // pour le formulaire public,
				'email-not-in'		=> true // pour la notification mail

			)
		)
	);

	$post_contact_fields = apply_filters( 'pc_filter_post_contact_fields', $post_contact_fields );
	
	$post_contact_fields_declaration = new PC_Add_Metabox( CONTACT_POST_SLUG, 'Champs', 'form-contact-fields', $post_contact_fields, 'normal', 'low' );
	
	
} // FIN if class_exist()

	
/*=====  FIN Définition des champs  =====*/

/*================================================
=            Paramètres du formulaire            =
================================================*/

global $form_contact_datas;
$form_contact_datas = array(
	'css' => 'form form--contact',
	'errors' => array(
		'global-error' 		=> false, // erreur globale
		'spam-error'		=> false, // erreur recaptcha
		'mail-sent' 		=> false, // validation envoi email
		'mail-sent-error'	=> false, // erreur envoi email
	),
	'fields' => array()
);

// création des  champs d'après ceux du post
foreach ( $post_contact_fields['fields'] as $field ) {
	$form_contact_datas['fields'][$post_contact_fields['prefix'].'-'.$field['id']] = $field;
}

// filtre
$form_contact_datas = apply_filters( 'pc_filter_form_contact_settings', $form_contact_datas );


/*=====  FIN Paramètres du formulaire  =====*/
