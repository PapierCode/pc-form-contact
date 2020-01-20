<?php

/**
*
** Traitement du formulaire de contact
*
**/

/*=================================
=            Variables            =
=================================*/

// erreur globale
$form_contact_global_error 		= false;
// erreur recaptcha
$form_contact_spam_error		= false;

// validation envoi
$form_contact_mail_sent_error	= false;
$form_contact_mail_sent 		= false;


/*=====  FIN Variables  ======*/


// si le formulaire vient d'être validé
if ( !empty( $_POST ) ) {

/*============================================================
=            Validation des champs obligatoires            =
============================================================*/

foreach ($form_contact_fields as $id => $datas) {
	
	if ( isset( $datas['required'] ) && $datas['required'] == true ) {

		$form_contact_fields[$id]['form-error'] = false;

		switch ($datas['type']) {

			case 'checkbox':
				if ( !isset($_POST[$id]) ) {
					$form_contact_fields[$id]['form-error'] = true;
					$form_contact_global_error = true;
				}
				break;

			case 'email':
				if ( trim( $_POST[$id] ) === '' || !filter_var( trim( $_POST[$id] ), FILTER_VALIDATE_EMAIL ) ) {
					$form_contact_fields[$id]['form-error'] =  true;
					$form_contact_global_error = true;
				}
				break;
			
			default:
				if ( trim( $_POST[$id] ) === '' ) {
					$form_contact_fields[$id]['form-error'] =  true;
					$form_contact_global_error = true;
				}
				break;

		}

	}

}


/*----------  reCaptcha  ----------*/

// if ( $captcha->isValid( $_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR'] ) === false ) {

// 	$form_contact_spam_error 	= true;
// 	$form_contact_global_error	= true;

// }


/*=====  FIN Vérification des champs obligatoires  ======*/


if ( !$form_contact_global_error ) {

/*===============================================
=            Préparation des valeurs            =
===============================================*/

foreach ($form_contact_fields as $id => $datas) {

	if ( isset($_POST[$id] ) && $_POST[$id] != '' ) {

		switch ( $datas['type'] ) {
			case 'checkbox':
				$form_contact_fields[$id]['form-value'] = '1';
				break;

			case 'email':
				$form_contact_fields[$id]['form-value'] = sanitize_email( $_POST[$id] );
				break;
			
			default:
				$form_contact_fields[$id]['form-value'] = sanitize_text_field( $_POST[$id] );
		}

	}

}


/*=====  FIN Préparation des valeurs  =====*/

/*===================================================
=            Construction & envoi E-mail            =
===================================================*/


/*----------  Contenu  ----------*/

$form_contact_message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head><body>';

foreach ($form_contact_fields as $id => $datas) {
	
	if ( !isset($datas['email-not-in']) && isset( $datas['form-value'] ) ) {

		switch ( $datas['type'] ) {
			case 'checkbox':
				$form_contact_message .= '<p><strong>'.$datas['label'].' :</strong> oui</p>';
				break;

			case 'email':
				$form_contact_message .= '<p><strong>'.$datas['label'].' :</strong> '.$datas['form-value'].'</p>';
				// paramètre email
				$form_contact_from_mail = sanitize_email( $_POST[$id] );
				break;
			
			default:
				$form_contact_message .= '<p><strong>'.$datas['label'].' :</strong> '.$datas['form-value'].'</p>';
				// paramètre email
				if ( isset($datas['email-from-txt']) ) { $form_contact_from_name = $datas['form-value']; }
				break;
		}

	}

}

$form_contact_message .= '</body></html>';


/*----------  Paramètres  ----------*/

// sujet du mail
$form_contact_subject = $settings_project['form-subject'];
// destinataire
$form_contact_to = $settings_project['form-for'];
// si nom de l'expéditeur manquant
if ( !isset( $form_contact_from_name ) || $form_contact_from_name == '' ) { $form_contact_from_name = 'sans nom'; }
// entête
$form_contact_mail_headers = array(
   'Content-Type: text/html; charset=UTF-8',
   'From: '.$form_contact_from_name.' <'.$form_contact_from_mail.'>',
);


/*----------  envoi/validation  ----------*/

include 'form-contact_save.php';
// if ( wp_mail( $form_contact_to, $form_contact_subject, $form_contact_message, $form_contact_mail_headers ) ) { 
// 	$form_contact_mail_sent = true;
// } else {
// 	$form_contact_mail_sent_error = true;
// }


/*=====  FIN Construction & envoi E-mail  ======*/


} // FIN if !form_contact_global_error

} // FIN if !empty($_POST)