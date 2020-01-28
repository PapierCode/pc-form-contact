<?php
/**
 * 
 * Traitement du formulaire de contact
 * 
 ** Champs obligatoires
 ** Notification par email
 ** Préparation des valeurs
 ** Enregistrement du post
 * 
 */

/*===========================================
=            Champs obligatoires            =
===========================================*/

/**
 * 
 * @param array		$form_datas		paramètres du formulaire, cf. $form_contact_datas dans settings.php
 * 
 */

function pc_form_contact_required_fields( $form_datas ) {

	foreach ( $form_datas['fields'] as $id => $field ) {
                        
		if ( isset( $field['required'] ) && $field['required'] == true ) {
	
			$form_datas['fields'][$id]['form-error'] = false;
	
			switch ($field['type']) {
	
				case 'checkbox':
					if ( !isset($_POST[$id]) ) {
						$form_datas['fields'][$id]['form-error'] = true;
						$form_datas['errors']['global-error'] = true;
					}
					break;
	
				case 'email':
					if ( trim( $_POST[$id] ) === '' || !filter_var( trim( $_POST[$id] ), FILTER_VALIDATE_EMAIL ) ) {
						$form_datas['fields'][$id]['form-error'] =  true;
						$form_datas['errors']['global-error'] = true;
					}
					break;
				
				default:
					if ( trim( $_POST[$id] ) === '' ) {
						$form_datas['fields'][$id]['form-error'] =  true;
						$form_datas['errors']['global-error'] = true;
					}
					break;
	
			}
	
		}
	
	}

	return $form_datas;

}


/*=====  FIN Champs obligatoires  =====*/

/*===============================================
=            Préparation des valeurs            =
===============================================*/

/**
 * 
 * @param array		$fields		définition des champs, cf. $form_contact_datas['fields'] dans settings.php
 * 
 */

function pc_form_contact_sanitize( $fields )  {

	foreach ($fields as $id => $field) {
                        
		if ( isset($_POST[$id] ) && $_POST[$id] != '' ) {

			switch ( $field['type'] ) {

				case 'checkbox':
					$fields[$id]['form-value'] = '1';
					break;
	
				case 'email':
					$fields[$id]['form-value'] = sanitize_email( $_POST[$id] );
					break;
	
				case 'text':
					$fields[$id]['form-value'] = sanitize_text_field( $_POST[$id] );
					break;

				case 'textarea':
					$fields[$id]['form-value'] = sanitize_textarea_field( $_POST[$id] );
					break;

			}
	
		}
	
	}

	return $fields;

}


/*=====  FIN Préparation des valeurs  =====*/

/*==============================================
=            Notification par email            =
==============================================*/

/**
 * 
 * @param array		$fields		définition des champs, cf. $form_contact_datas['fields'] dans settings.php
 * 
 */

function pc_form_contact_notification( $fields )  {


	global $settings_form_contact;


	/*----------  Paramètres par défaut  ----------*/	
	
	$email_notification = array(
		'from-name' => 'Sans nom',
		'subject'	=> $settings_form_contact['form-subject'],
		'to'		=> $settings_form_contact['form-for']
	);


	/*----------  Contenu  ----------*/
	
	$email_notification['content'] = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head><body>';
                
	foreach ($fields as $id => $field) {
		
		if ( !isset($field['email-not-in']) && isset( $field['form-value'] ) ) {
	
			switch ( $field['type'] ) {
				case 'checkbox':
					$email_notification['content'] .= '<p><strong>'.$field['label'].' :</strong> oui</p>';
					break;
	
				case 'email':
					$email_notification['content'] .= '<p><strong>'.$field['label'].' :</strong> '.$field['form-value'].'</p>';
					// email from
					$email_notification['from-addr'] = $field['form-value'];
					break;
				
				default:
					$email_notification['content'] .= '<p><strong>'.$field['label'].' :</strong> '.$field['form-value'].'</p>';
					// nom de l'expediteur
					if ( isset($field['email-from-name']) && isset( $field['form-value'] ) ) {
						$email_notification['from-name'] = $field['form-value'];
					}
					break;
			}
	
		}
	
	}
		
	$email_notification['content'] .= '</body></html>';


	/*----------  Entêtes  ----------*/
	
	$email_notification['headers'] = array(
		'Content-Type: text/html; charset=UTF-8',
		'From: '.$email_notification['from-name'].' <'.$email_notification['from-addr'].'>',
	);


	/*----------  Filtre  ----------*/
	
	apply_filters( 'pc_filter_form_contact_notification', $email_notification );


	/*----------  Envoi ?  ----------*/

	$email_sent = wp_mail(
		$email_notification['to'],
		$email_notification['subject'],
		$email_notification['content'],
		$email_notification['headers']
	);

	return $email_sent;

}


/*=====  FIN Notification par email  =====*/

/*==============================================
=            Enregistrement du post            =
==============================================*/

/**
 * 
 * @param array		$fields		définition des champs, cf. $form_contact_datas['fields'] dans settings.php
 * 
 */

function pc_form_contact_save_post( $fields ) {

	$post_contact_metas_to_save = array();
	foreach ($fields as $id => $datas) {
		if ( isset( $datas['form-value'] ) ) {
			$post_contact_metas_to_save[$id] = $datas['form-value'];
		}
		if ( $datas['type'] == 'email' ) {
			$title = $datas['form-value'];
		}
	}

	$post_contact_save = wp_insert_post(
		array(
			'post_author'	=> 1,
			'post_title'	=> $title,
			'post_status'	=> 'publish',
			'post_type'		=> CONTACT_POST_SLUG,
			'meta_input'	=> $post_contact_metas_to_save
		)
	);

}



/*=====  FIN Enregistrement du post  =====*/