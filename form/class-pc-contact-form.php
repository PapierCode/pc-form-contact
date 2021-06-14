<?php

class PC_Contact_Form {

	public $pc_post;			// [object] post en cours
	private $captcha;			// [object] captcha

	private $texts;				// [array] textes, exceptés les champs
	private $css;				// [array] classes css, exceptés les champs
	private $notification;		// [array] paramètres notification

	public $fields = array();	// [array] liste & paramètres des champs

	public $errors;				// [array][bool] types d'errreurs
	public $done;				// [bool] terminé


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

		/*----------  Textes divers  ----------*/
		
		$this->texts = apply_filters( 'pc_filter_form_contact_texts', array(
			'label-required'	=> '&nbsp;<span class="form-label-required">(obligatoire)</span>',
			'label-captcha'		=> 'Protection contre les spams',
			'submit-txt'		=> 'Envoyer',
			'submit-title'		=> 'Envoyer un e-mail',
			'msg-error'			=> 'Le formulaire contient des erreurs',
			'msg-done'			=> 'Le message est envoyé'
		) );

		/*----------  CSS containers  ----------*/
		
		$this->css = apply_filters( 'pc_filter_form_contact_css_classes', array(
			'form-container' => array( 'form', 'form--contact' ),
			'button-submit'	=> array( 'form-submit', 'button', 'button--xl', 'button--color-1', 'button--red' )
		) );

		/*----------  Configuration des champs  ----------*/
		
		$this->prepare_fields( $post_fields );

		/*----------  Notification  ----------*/
		
		global $settings_form_contact;
		$to_email = ( isset( $settings_form_contact['form-for'] ) && '' != $settings_form_contact['form-for'] ) ? $settings_form_contact['form-for'] : 'contact@papier-code.fr';
		$to_subject = ( isset( $settings_form_contact['form-subject'] ) && '' != $settings_form_contact['form-subject'] )? $settings_form_contact['form-subject'] : 'Contact depuis '.get_bloginfo( 'name' );

		$this->notification = array(
			'to-email' 		=> $to_email,	// email du destinataire
			'to-subject' 	=> $to_subject,	// sujet de l'email
			'from-name'		=> 'Sans nom',	// nom de l'expéditeur
			'from-email'	=> '',			// email de l'expéditeur
			'content'		=> ''			// contenu de l'email
		);

		/*----------  Validaiton du formulaire  ----------*/
		
		if ( isset($_POST['none-pc-contact-form']) && wp_verify_nonce( $_POST['none-pc-contact-form'], basename( __FILE__ ) ) ) {
			$this->validation_form();
		}

	}
	
	/*=====  FIN Construct  =====*/

	/*==============================================
	=            Préparation des champs            =
	==============================================*/
	
	private function prepare_fields( $post_fields ) {
	
		foreach ( $post_fields['fields'] as $field ) {

			// suivant si conditionné à une variable d'url vide
			if ( isset( $field['form-in-if-query-var'] ) && '' == get_query_var( $field['form-query-var'] ) ) { continue; }
			// attributs id/name
			$name = $post_fields['prefix'].'-'.$field['id'];

			// paramètres de base
			$params = array(
				'type' 	=> $field['type'],
				'label'	=> ( isset( $field['form-label'] ) ) ? $field['form-label'] : $field['label'],
				'css' 	=> array(
					'form-item',
					'form-item--'.$field['type'],
					'form-item--'.$name
				),
				'attrs' => array(),
				'error' => false
			);

			// css customs
			if ( isset( $field['form-css'] ) ) {
				$params['css'] = array_merge( $params['css'], $field['form-css'] );
			}

			// obligatoire			
			if ( isset( $field['required'] ) ) {
				$params['attrs'][] = 'required';
				$params['required'] = true;
			}

			// attributs customs
			if ( isset( $field['form-attrs'] ) ) { 
				$params['attrs'] = array_merge( $params['attrs'], $field['form-attrs'] );
			}

			// description
			if ( isset( $field['form-desc'] ) ) {
				$params['desc'] = $field['form-desc'];
				$params['attrs'][] = 'aria-describedby="form-item-desc-'.$name.'"';
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

		// création captcha
		global $settings_pc;
		if ( '' != $settings_pc['google-recaptcha-site'] && '' != $settings_pc['google-recaptcha-secret'] ) {
			$this->captcha = new PC_recaptcha( $settings_pc['google-recaptcha-site'], $settings_pc['google-recaptcha-secret'] );
		}

	}
	
	
	/*=====  FIN Préparation des champs  =====*/

	/*=============================================
	=            Validation des champs            =
	=============================================*/

	private function validation_fields() {

		foreach ( $this->fields as $name => $params ) {
							
			switch ( $params['type'] ) {
	
				case 'checkbox':

					if ( !isset($_POST[$name]) && isset( $params['required'] ) ) {
						$this->fields[$name]['error'] = true;

						if ( isset( $params['rgpd'] ) ) {
							$this->fields[$name]['msg-error'] = 'Vous devez <strong>accepter les Conditions Générales d\'Utilisation</strong>.';
						}

					} else {
						$this->fields[$name]['attrs'][] = 'checked';
						$this->fields[$name]['value'] = 1;
					}

					break;
	
				case 'email':

					if ( ( '' === trim( $_POST[$name] ) && isset( $params['required'] ) ) ) {
						$this->fields[$name]['error'] = true;

					} else {

						if ( !is_email( trim( $_POST[$name] ) ) ) {
							$this->fields[$name]['value'] = $_POST[$name];
							$this->fields[$name]['error'] = true;
							$this->fields[$name]['msg-error'] = 'Le format du champ <strong>'.$params['label'].'</strong> n\'est pas valide.';

						} else {
							$this->fields[$name]['value'] = sanitize_email( $_POST[$name] );
							if ( isset( $params['notification-from-email'] ) ) { $this->notification['from-email'] = $this->fields[$name]['value']; }
						}	

					}

					break;
				
				case 'text':

					if ( '' === trim( $_POST[$name] ) && isset( $params['required'] ) ) {
						$this->fields[$name]['error'] =  true;
					} else {
						$this->fields[$name]['value'] = sanitize_text_field( $_POST[$name] );
						if ( isset( $params['notification-from-name'] ) ) { $this->notification['from-name'] = $this->fields[$name]['value']; }
					}

					break;
				
				case 'textarea':

					if ( '' === trim( $_POST[$name] ) && isset( $params['required'] ) ) {
						$this->fields[$name]['error'] =  true;
					} else {
						$this->fields[$name]['value'] = sanitize_textarea_field( $_POST[$name] );
					}

					break;
	
			}

			// si champ en erreur
			if ( $this->fields[$name]['error'] ) {

				// css erreur
				$this->fields[$name]['css'][] = 'form-item--error';
				// aria invalid
				$this->fields[$name]['attrs'][] = 'aria-invalid="true"';
				// erreur formulaire
				$this->errors['field'] = true;

			}

		}

		// si captcha en erreur
		if ( is_object( $this->captcha ) && false === $this->captcha->isValid( $_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR'] ) ) { $this->errors['captcha'] = true; }

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
				$value = get_query_var( $params['query-var'] );
			} 
			if ( isset( $params['value'] ) ) { $value = $params['value']; }
		
			// affichage des champs
			echo '<li class="'.implode( ' ', $params['css'] ).'">';

				// label si
				if ( in_array( $params['type'], array( 'text', 'email', 'textarea' ) ) ) {
					$this->display_label( $name, $params );
				}
				
				echo '<div class="form-item-inner">';

					switch ( $params['type'] ) {

						case 'text':
						case 'email':
							echo '<input type="'.$params['type'].'" id="'.$name.'" name="'.$name.'" value="'.$value.'" '.implode( ' ', $params['attrs'] ).'/>';
							break;

						case 'textarea':
							echo '<textarea id="'.$name.'" name="'.$name.'"'.implode( ' ', $params['attrs'] ).'>'.$value.'</textarea>';
							break;
						
						case 'checkbox':
							echo '<input class="visually-hidden" type="checkbox" name="'.$name.'" id="'.$name.'" value="1"'.implode( ' ', $params['attrs'] ).' />';
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

		$body_content = apply_filters( 'pc_filter_form_contact_notification_content_before_fields', '<p><em>Formulaire publié sur la page :</em> <a href="'.$this->pc_post->permalink.'" title="Voir la page">'.$this->pc_post->title.'</a></p>' );
		
		// champs
		if ( apply_filters( 'pc_filter_form_contact_notification_fields_display', true ) ) {
			
			foreach ( $this->fields as $name => $params ) {
				
				if ( !isset( $params['notification-not-in'] ) ) {
			
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
		}
		
		$body_content = apply_filters( 'pc_filter_form_contact_notification_content_after_fields', $body_content );
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

	private function insert_post() {

		// champs
		$post_metas = array();
		foreach ( $this->fields as $name => $params ) {
			if ( '' != $params['value'] ) { $post_metas[$name] = $params['value']; }
			if ( 'email' == $params['type'] ) { $post_title = $params['value'];	}
		}
		// page d'origine
		$post_metas['contact-from-page'] = $this->pc_post->id;

		// post
		$insert_post = wp_insert_post(
			array(
				'post_author'	=> 1,
				'post_title'	=> $post_title,
				'post_status'	=> 'publish',
				'post_type'		=> FORM_CONTACT_POST_SLUG,
				'meta_input'	=> $post_metas
			)
		);

		// si erreur
		if ( !$insert_post ) { $this->errors['post'] = true; }

	}


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
				$this->insert_post();

				if ( !$this->errors['post'] ) {

					// validation finale
					$this->done = true;

					// meta title
					add_filter( 'pc_filter_seo_metas', array( $this, 'display_meta_title_messages' ), 10, 1 );

					// reset des valeurs
					foreach ( $this->fields as $name => $params ) {
						unset( $this->fields[$name]['value'] );
						if ( 'checkbox' == $params['type'] ) { 
							$this->fields[$name]['attrs'] = array_diff( $this->fields[$name]['attrs'], array('checked') );}
					}
					
				}

			}

		}

	}
	
	
	/*=====  FIN Validation du formulaire  =====*/

	/*==============================================
	=            Affichage des messages            =
	==============================================*/
	
	private function display_content_messages() {

		if ( in_array( true, $this->errors ) ) {

			if ( $this->errors['field'] || $this->errors['captcha'] ) {
				$message_error = '<strong>'.$this->texts['msg-error'].'&nbsp;:</strong>';
			}
	
			if ( $this->errors['field'] ) {
	
				foreach ( $this->fields as $params ) {
					if ( $params['error'] ) {
						$message_error .= '<br/>';
						if ( isset( $params['msg-error'] ) ) {
							$message_error .=  $params['msg-error'];
						} else {
							$message_error .= 'Le champ <strong>'.$params['label'].'</strong> est obligatoire.';
						}
					}
				}

			}			

			if ( $this->errors['captcha'] ) {
				$message_error .= '<br/>Cocher la case <strong>Je ne suis pas un robot</strong>, et si nécessaire, suivez les instructions.';
			}

			if ( $this->errors['notification'] || $this->errors['post'] ) {
	
				$message_error = 'Une erreur est survenue, merci de valider à nouveau l\'<strong>antispam</strong> et le <strong>formulaire</strong>.';
	
			}

			echo pc_display_alert_msg( $message_error, 'error', 'block' );
	
		} else if ( $this->done ) {
	
			echo pc_display_alert_msg( $this->texts['msg-done'].'.', 'success', 'block' );
	
		}
	
	
	}

	public function display_meta_title_messages( $seo_metas ) {

		if ( $this->done ) {
			$seo_metas['title'] = $this->texts['msg-done'].' / '.$seo_metas['title'];
		} else {
			$seo_metas['title'] = $this->texts['msg-error'].' / '.$seo_metas['title'];
		}

		return $seo_metas;

	}
	
	
	/*=====  FIN Affichage des messages  =====*/

	/*============================================
	=            Affichage formulaire            =
	============================================*/
	
	public function display_form() {

		echo '<div id="form-contact" class="'.implode( ' ', $this->css['form-container'] ).'">';
		
		$this->display_content_messages();

		echo '<form method="POST" action="#form-contact">';

			wp_nonce_field( basename( __FILE__ ), 'none-pc-contact-form' );

			echo '<ul class="form-list reset-list">';

				// champs
				$this->display_fields();

				// captcha
				if ( is_object( $this->captcha ) ) {
					
					echo '<li class="form-item form-item--captcha'.( $this->errors['captcha'] ? ' form-item--error' : '').'">';
						echo '<span class="form-label" aria-hidden="true">';
							echo $this->texts['label-captcha'].$this->texts['label-required'];
						echo '</span>';
							echo $this->captcha->script();
							echo $this->captcha->html();
					echo '</li>';

				}
				
				// submit
				echo '<li class="form-item form-item--submit">';
					echo '<button type="submit" title='.$this->texts['submit-title'].'" class="'.implode( ' ', $this->css['button-submit'] ).'"><span class="text">'.$this->texts['submit-txt'].'</span></button>';
				echo '</li>';

			echo '</ul>';

		echo '</form>';
		echo '</div>';

	}
	
	
	/*=====  FIN Affichage formulaire  =====*/

}