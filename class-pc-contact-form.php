<?php
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

class PC_Contact_Form {

	public $pc_post;				// [object] post en cours
	private $captcha;				// [object] captcha

	private $lang;					// [string] langue du formulaire
	private $texts;					// [array] textes, exceptés les champs
	private $css;					// [array] classes css, exceptés les champs
	private $notification;			// [array] paramètres notification

	public $fields = array();		// [array] liste & paramètres des champs

	public $errors;					// [array][bool] types d'errreurs
	public $done;					// [bool] terminé


	/*=================================
	=            Construct            =
	=================================*/
	
	public function __construct ( $post_fields, $pc_post ) {

		/*----------  Post en cours  ----------*/
		
		$this->pc_post = $pc_post;

		/*----------  Validation finale  ----------*/
		
		$this->done = false;

		/*----------  Types d'erreur  ----------*/
		
		$this->errors = array(
			'field' 		=> false, // erreur champ
			'captcha'		=> false, // erreur captcha
			'notification'	=> false, // erreur envoi email
			'post'			=> false, // erreur création post
		);

		/*----------  Textes  ----------*/

		$this->lang = apply_filters( 'pc_filter_form_contact_language', pc_get_html_attr_lang() );
		$this->prepare_texts();

		/*----------  CSS containers  ----------*/
		
		$this->css = apply_filters( 'pc_filter_form_contact_css_classes', array(
			'form-container' => array( 'form', 'form--contact' ),
			'button-submit'	=> array( 'form-submit', 'button' )
		) );

		/*----------  Création captcha  ----------*/
		
		global $settings_pc;

		if ( 'hcaptcha' == $settings_pc['contactform-captcha'] && '' != $settings_pc['hcaptcha-site'] && '' != $settings_pc['hcaptcha-secret'] ) {

			$this->captcha = new PC_Hcaptcha( $settings_pc['hcaptcha-site'], $settings_pc['hcaptcha-secret'] );

		} else {

			$this->captcha = new PC_MathCaptcha( $settings_pc['contactform-math-pass'], $settings_pc['contactform-math-iv'], $this->lang );

		}

		/*----------  Configuration des champs  ----------*/
		
		$this->prepare_fields( $post_fields );

		/*----------  Notification  ----------*/
		
		global $settings_project;
		$to_email = ( isset( $settings_project['form-for'] ) && '' != $settings_project['form-for'] ) ? $settings_project['form-for'] : 'contact@papier-code.fr';
		$to_subject = ( isset( $settings_project['form-subject'] ) && '' != $settings_project['form-subject'] )? $settings_project['form-subject'] : 'Contact depuis '.get_bloginfo( 'name' );

		$this->notification = array(
			'to-email' 		=> $to_email,	// email du destinataire
			'to-subject' 	=> $to_subject,	// sujet de l'email
			'from-name'		=> 'Sans nom',	// nom de l'expéditeur
			'from-email'	=> '',			// email de l'expéditeur
			'content'		=> ''			// contenu de l'email
		);

		/*----------  Validation du formulaire  ----------*/
		
		if ( isset($_POST['none-pc-contact-form']) && wp_verify_nonce( $_POST['none-pc-contact-form'], basename( __FILE__ ) ) ) {
			$this->validation_form();
		}

	}
	
	/*=====  FIN Construct  =====*/

	/*==============================================
	=            Préparation des textes            =
	==============================================*/
	
	private function prepare_texts() {

		switch ( $this->lang ) {

			case 'fr':		
				$texts = array(
					'form-aria-label'			=> 'Formulaire de contact',
					'label-required'			=> '&nbsp;<span class="form-label-required">(obligatoire)</span>',
					'submit-txt'				=> 'Envoyer un message',
					'submit-title'				=> 'Valider le formulaire',
					'msg-error-field'			=> 'Le formulaire contient des erreurs',
					'msg-error-field-default'	=> 'Le champ <strong>{{label}}</strong> est obligatoire',
					'msg-error-field-rgpd'		=> 'Vous devez <strong>accepter les Conditions Générales d\'Utilisation</strong>',
					'msg-error-field-mail'		=> 'Le format du champ <strong>{{label}}</strong> n\'est pas valide',
					'msg-error-actions'			=> 'Une erreur est survenue, merci de valider à nouveau l\'<strong>antispam</strong> et le <strong>formulaire</strong>',
					'msg-done'					=> 'Le message est envoyé'
				);
				break;
			
			case 'en':
				$texts = array(
					'form-aria-label'			=> 'Contact form',
					'label-required'			=> '&nbsp;<span class="form-label-required">(required)</span>',
					'submit-txt'				=> 'Send a message',
					'submit-title'				=> 'Validate the form',
					'msg-error-field'			=> 'The form contains errors',
					'msg-error-field-default'	=> 'The field <strong>{{label}}</strong> is required.',
					'msg-error-field-rgpd'		=> 'You must <strong>accept the General Conditions of Use</strong>.',
					'msg-error-field-mail'		=> 'The format of the <strong>{{label}}</strong> field is not valid. ',
					'msg-error-actions'			=> 'An error occurred, please validate the <strong>antispam</strong> and the <strong>form</strong> again.',
					'msg-done'					=> 'The message is sent'
				);
				break;
				
		}

		$this->texts = apply_filters( 'pc_filter_form_contact_texts', $texts, $this->lang );

	}
	
	
	/*=====  FIN Préparation des textes  =====*/

	/*==============================================
	=            Préparation des champs            =
	==============================================*/
	
	private function prepare_fields( $post_fields ) {
	
		foreach ( $post_fields['fields'] as $field ) {

			// suivant si conditionné à une variable
			if ( isset( $field['form-in-if-query-var'] ) && '' == get_query_var( $field['form-query-var'] ) ) { continue; }
			// attributs id/name
			$name = $post_fields['prefix'].'-'.$field['id'];

			// paramètres de base
			$params = array(
				'type' 	=> $field['type'],
				'css' 	=> array(
					'form-item',
					'form-item--'.$field['type'],
					'form-item--'.$name
				),
				'attr' => array(),
				'error' => false
			);

			// labels
			switch ( $this->lang ) {
				case 'fr':
					$params['label'] = ( isset( $field['form-label'] ) ) ? $field['form-label'] : $field['label'];
					break;
				case 'en':
					$params['label'] = ( isset( $field['form-label-en'] ) ) ? $field['form-label-en'] : $field['label-en'];
					break;
			}

			// css customs
			if ( isset( $field['form-css'] ) ) {
				$params['css'] = array_merge( $params['css'], $field['form-css'] );
			}

			// obligatoire			
			if ( isset( $field['required'] ) ) {
				$params['attr'][] = 'required';
				$params['required'] = true;
			}

			// attributs customs
			if ( isset( $field['form-attr'] ) ) { 
				$params['attr'] = array_merge( $params['attr'], explode( ' ' , $field['form-attr'] ) );
			}

			// options select
			if ( isset( $field['options'] ) ) {
				$params['options'] = $field['options'];
			}

			// description
			if ( isset( $field['form-desc'] ) || isset( $field['form-desc-en'] ) ) {
				switch ( $this->lang ) {
					case 'fr':
						$params['desc'] = $field['form-desc'];
						break;
					case 'en':
						$params['desc'] = $field['form-desc-en'];
						break;
				}
				$params['attr'][] = 'aria-describedby="form-item-desc-'.$name.'"';
			}

			// RGPD checkbox ?
			if ( isset( $field['form-rgpd'] ) ) { $params['rgpd'] = true; }

			// associé à une variable d'url
			if ( isset( $field['form-query-var'] ) ) { $params['query-var'] = $field['form-query-var']; }

			// paramètres notification
			if ( isset( $field['notification-from-email'] ) ) { $params['notification-from-email'] = true; }
			if ( isset( $field['notification-from-name'] ) ) { $params['notification-from-name'] = true; }
			if ( isset( $field['notification-not-in'] ) ) { $params['notification-not-in'] = true; }

			// ajout à la liste		
			$this->fields[$name] = $params;

		}

		$this->fields = apply_filters( 'pc_filter_form_contact_fields', $this->fields );

	}
	
	
	/*=====  FIN Préparation des champs  =====*/

	/*=============================================
	=            Validation des champs            =
	=============================================*/

	private function validation_fields() {

		foreach ( $this->fields as $name => $params ) {
							
			switch ( $params['type'] ) {
	
				case 'checkbox':

					if ( !isset( $_POST[$name] ) && isset( $params['required'] ) ) {
						$this->fields[$name]['error'] = true;

						if ( isset( $params['rgpd'] ) ) {
							$this->fields[$name]['msg-error'] = $this->texts['msg-error-field-rgpd'];
						}

					} else if ( isset( $_POST[$name] ) ) {
						$this->fields[$name]['attr'][] = 'checked';
						$this->fields[$name]['value'] = 1;
					}

					break;
	
				case 'email':

					if ( ( '' === trim( $_POST[$name] ) && isset( $params['required'] ) ) ) {
						$this->fields[$name]['error'] = true;

					} else if ( '' !== trim( $_POST[$name] ) ) {

						if ( !is_email( trim( $_POST[$name] ) ) ) {
							$this->fields[$name]['value'] = $_POST[$name];
							$this->fields[$name]['error'] = true;
							$this->fields[$name]['msg-error'] = str_replace( '{{label}}', $params['label'], $this->texts['msg-error-field-mail'] );

						} else {
							$this->fields[$name]['value'] = sanitize_email( $_POST[$name] );
							if ( isset( $params['notification-from-email'] ) ) { $this->notification['from-email'] = $this->fields[$name]['value']; }
						}	

					}

					break;
				
				case 'text':
				case 'select':

					if ( '' === trim( $_POST[$name] ) && isset( $params['required'] ) ) {
						$this->fields[$name]['error'] =  true;
					} else if ( '' !== trim( $_POST[$name] ) ) {
						$this->fields[$name]['value'] = sanitize_text_field( stripslashes($_POST[$name]) );
						if ( isset( $params['notification-from-name'] ) ) { $this->notification['from-name'] = $this->fields[$name]['value']; }
					}

					break;
				
				case 'textarea':

					if ( '' === trim( $_POST[$name] ) && isset( $params['required'] ) ) {
						$this->fields[$name]['error'] =  true;
					} else if ( '' !== trim( $_POST[$name] ) ) {
						$this->fields[$name]['value'] = sanitize_textarea_field( stripslashes($_POST[$name]) );
					}

					break;
	
			}

			// si champ en erreur
			if ( $this->fields[$name]['error'] ) {

				// css erreur
				$this->fields[$name]['css'][] = 'form-item--error';
				// aria invalid
				$this->fields[$name]['attr'][] = 'aria-invalid="true"';
				// erreur formulaire
				$this->errors['field'] = true;

			}

		}

		// si captcha en erreur
		if ( is_object( $this->captcha ) && false === $this->captcha->validate() ) { $this->errors['captcha'] = true; }

	}


	/*=====  FIN Validation des champs  =====*/

	/*=======================================
	=            Affichage label            =
	=======================================*/
	
	private function display_label( $name, $params ) {

		$label = $params['label'];
		if ( isset( $params['required'] ) ) { $label .= $this->texts['label-required']; }

		// lien CGU
		if ( isset( $params['rgpd']) ) {
			$label = str_replace( '{{cgu}}', get_privacy_policy_url(), $label );
		}
		
		echo '<label class="form-label" for="'.$name.'"><span>'.$label.'</span></label>';

	}
	
	
	/*=====  FIN Affichage label  =====*/

	/*============================================
	=            Affichage des champs            =
	============================================*/

	function display_fields() {

		foreach ( $this->fields as $name => $params ) {

			// valeur à afficher
			$value = '';

			if ( isset( $params['query-var'] ) && '' !== get_query_var( $params['query-var'] ) ) {
				$value = stripslashes( get_query_var( $params['query-var'] ) );
			} 
			if ( isset( $params['value'] ) ) { $value = $params['value']; }
		
			// affichage des champs
			echo '<li class="'.implode( ' ', $params['css'] ).'">';

				// label si
				if ( in_array( $params['type'], array( 'text', 'email', 'textarea', 'select', 'captcha' ) ) ) {
					$this->display_label( $name, $params );
				}
				
				echo '<div class="form-item-inner">';

					switch ( $params['type'] ) {

						case 'text':
						case 'email':
							echo '<input type="'.$params['type'].'" id="'.$name.'" name="'.$name.'" value="'.$value.'" '.implode( ' ', $params['attr'] ).'/>';
							break;

						case 'textarea':
							echo '<textarea id="'.$name.'" name="'.$name.'" '.implode( ' ', $params['attr'] ).'>'.$value.'</textarea>';
							break;

						case 'select':
							echo '<select id="'.$name.'" name="'.$name.'" '.implode( ' ', $params['attr'] ).'>';
								echo '<option value=""></option>';
								foreach ( $params['options'] as $option_label => $option_value ) {
									echo '<option value="'.$option_value.'" '.selected($value,$option_value,false).'>'.$option_label.'</option>';
								}
							echo '</select>';
							break;
						
						case 'checkbox':
							echo '<input class="visually-hidden" type="checkbox" name="'.$name.'" id="'.$name.'" value="1" '.implode( ' ', $params['attr'] ).' />';
							$this->display_label( $name, $params );
							break;

					}

					// description/aide
					if ( isset( $params['desc'] ) ) { echo '<p id="form-item-desc-'.$name.'" class="form-item-desc">'.$params['desc'].'</p>'; }

				echo '</div>';

			echo '</li>';

		}

	}


	/*=====  FIN Affichage des champs  =====*/

	/*==============================================
	=            Notification par email            =
	==============================================*/

	private function send_notification()  {

		/*----------  Contenu  ----------*/
		
		$notification_content = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head><body>';

		$body_content = apply_filters( 'pc_filter_form_contact_notification_content_start', '<p><em>Formulaire publié sur la page :</em> <a href="'.$this->pc_post->permalink.'" title="Voir la page">'.$this->pc_post->title.'</a></p>' );
		
		// champs
		if ( apply_filters( 'pc_filter_form_contact_notification_fields_display', true ) ) {
			
			foreach ( $this->fields as $name => $params ) {
				
				if ( !isset( $params['notification-not-in'] ) && isset( $params['value'] ) ) {
			
					switch ( $params['type'] ) {

						case 'checkbox':
							$body_content .= '<p><strong>'.$params['label'].' :</strong> oui</p>';
							break;
						
						default:
							$body_content .= '<p><strong>'.$params['label'].' :</strong> '.$params['value'].'</p>';
							break;

					}
			
				}
			
			}

		} // FIN if display field
		
		$body_content = apply_filters( 'pc_filter_form_contact_notification_content_end', $body_content );
		$notification_content .= $body_content.'</body></html>';


		/*----------  Entêtes  ----------*/
		
		$this->notification['headers'] = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: '.$this->notification['from-name'].' <'.$this->notification['from-email'].'>',
		);


		/*----------  Envoi  ----------*/

		$this->notification = apply_filters( 'pc_filter_form_contact_notification', $this->notification );

		$notification_sent = wp_mail(
			$this->notification['to-email'],
			html_entity_decode( $this->notification['to-subject'] ),
			$notification_content,
			$this->notification['headers']
		);

		// si erreur
		if ( !$notification_sent ) {
			$this->errors['notification'] = true;
		}

	}


	/*=====  FIN Notification par email  =====*/
	
	/*==============================================
	=            Enregistrement du post            =
	==============================================*/

	// private function insert_post() {

	// 	// champs
	// 	$post_metas = array();
	// 	foreach ( $this->fields as $name => $params ) {
	// 		if ( isset( $params['value'] ) ) {
	// 			$post_metas[$name] = $params['value'];
	// 			if ( 'email' == $params['type'] ) { $post_title = $params['value'];	}
	// 		}
	// 	}
	// 	// page d'origine
	// 	$post_metas['contact-from-page'] = $this->pc_post->id;

	// 	// post
	// 	$insert_post = wp_insert_post(
	// 		array(
	// 			'post_author'	=> 1,
	// 			'post_title'	=> $post_title,
	// 			'post_status'	=> 'publish',
	// 			'post_type'		=> FORM_CONTACT_POST_SLUG,
	// 			'meta_input'	=> $post_metas
	// 		)
	// 	);

	// 	// si erreur
	// 	if ( !$insert_post ) { $this->errors['post'] = true; }

	// }


	/*=====  FIN Enregistrement du post  =====*/

	/*================================================
	=            Validation du formulaire            =
	================================================*/
	
	private function validation_form() {

		// validaton des champs
		$this->validation_fields();

		if ( $this->errors['field'] || $this->errors['captcha'] ) { 
			
			// meta title
			add_filter( 'pc_filter_seo_metas', array( $this, 'display_meta_title_messages' ), 10, 1 );


		} else {
			
			// envoi notification
			$this->send_notification();

			if ( !$this->errors['notification'] ) {

				// création du post
				// $this->insert_post();

				// if ( !$this->errors['post'] ) {

					// validation finale
					$this->done = true;

					// meta title
					add_filter( 'pc_filter_seo_metas', array( $this, 'display_meta_title_messages' ), 10, 1 );

					// reset des valeurs
					foreach ( $this->fields as $name => $params ) {
						unset( $this->fields[$name]['value'] );
						if ( 'checkbox' == $params['type'] ) { 
							$this->fields[$name]['attr'] = array_diff( $this->fields[$name]['attr'], array('checked') );}
					}
					
				// }

			}

		}

	}
	
	
	/*=====  FIN Validation du formulaire  =====*/

	/*==============================================
	=            Affichage des messages            =
	==============================================*/

	private function get_formated_msg_error( $msg ) {

		return '<br/>'.$msg.',';

	}
	
	private function display_content_messages() {

		$texts = $this->texts;

		if ( in_array( true, $this->errors ) ) {

			if ( $this->errors['field'] || $this->errors['captcha'] ) {
				$message_error = '<strong>'.$texts['msg-error-field'].'&nbsp;:</strong>';
			}
	
			if ( $this->errors['field'] ) {
	
				foreach ( $this->fields as $params ) {
					if ( $params['error'] ) {
						$message_error .=  ( isset( $params['msg-error'] ) ) ? $this->get_formated_msg_error( $params['msg-error'] ) : $this->get_formated_msg_error( str_replace( '{{label}}', $params['label'], $texts['msg-error-field-default'] ) );
					}
				}

			}			
	
			if ( $this->errors['captcha'] ) {
	
				$message_error .= $this->get_formated_msg_error( $this->captcha->msg_error );

			}

			if ( $this->errors['notification'] || $this->errors['post'] ) {
	
				$message_error = $texts['msg-error-actions'].'.';
	
			}

			echo pc_display_alert_msg( $message_error, 'error', 'block' );

	
		} else if ( $this->done ) {
	
			echo pc_display_alert_msg( $texts['msg-done'].'.', 'success', 'block' );
	
		}
	
	
	}

	public function display_meta_title_messages( $seo_metas ) {

		$texts = $this->texts;

		if ( in_array( true, $this->errors ) ) {

			if ( $this->errors['field'] || $this->errors['captcha'] ) {
				
				$seo_metas['title'] = $this->texts['msg-error-field'].' / '.$seo_metas['title'];

			} else {

				$seo_metas['title'] =  $this->get_formated_msg_error( $texts['msg-error-actions'] );

			}

		} else if ( $this->done ) {

			$seo_metas['title'] = $this->texts['msg-done'].' / '.$seo_metas['title'];

		}

		return $seo_metas;

	}
	
	
	/*=====  FIN Affichage des messages  =====*/

	/*============================================
	=            Affichage formulaire            =
	============================================*/
	
	public function display_form() {

		echo '<div id="form-contact" class="'.implode( ' ', $this->css['form-container'] ).'">';

		echo '<form method="POST" action="#form-contact" aria-label="'.$this->texts['form-aria-label'].'">';
		
			$this->display_content_messages();

			wp_nonce_field( basename( __FILE__ ), 'none-pc-contact-form' );

			echo '<ul class="form-list reset-list">';

				/*----------  Champs  ----------*/
				
				$this->display_fields();


				/*----------  Captcha  ----------*/
				
				global $settings_pc;

				if ( 'hcaptcha' == $settings_pc['contactform-captcha'] && is_object( $this->captcha ) ) {
					
					echo '<li class="form-item form-item--captcha form-item--hcaptcha'.( $this->errors['captcha'] ? ' form-item--error' : '').'">';
						echo '<span class="label-like form-label" aria-hidden="true">'.$this->captcha->get_field_label_text().$this->texts['label-required'].'</span>';
						$this->captcha->display();
					echo '</li>';

				} else {

					echo '<li class="form-item form-item--captcha form-item--mathcaptcha'.( $this->errors['captcha'] ? ' form-item--error' : '').'">';

						$this->display_label( 'form-captcha', array(
							'label'		=> $this->captcha->get_field_label_text(),
							'required'	=> true
						) );

						echo '<div class="form-item-inner">'.$this->captcha->get_field_inputs().'</div>';
						
					echo '</li>';

				}
				

				/*----------  Submit  ----------*/
				
				echo '<li class="form-item form-item--submit">';
					echo '<button type="submit" title="'.$this->texts['submit-title'].'" class="'.implode( ' ', $this->css['button-submit'] ).'"><span class="text">'.$this->texts['submit-txt'].'</span></button>';
				echo '</li>';

			echo '</ul>';

		echo '</form>';
		echo '</div>';

	}
	
	
	/*=====  FIN Affichage formulaire  =====*/

}