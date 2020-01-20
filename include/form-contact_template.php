<?php

/**
*
* Formulaire de contact
*
**/

// traitement du form
include 'form-contact_validation.php';
// recaptcha
$captcha = new PC_recaptcha( $settings_pc['google-recaptcha-site'], $settings_pc['google-recaptcha-secret'] );

?>

<h2 id="form-contact"><?= $form_contact_settings['title']; ?></h2>

<?php // erreur ou validation de l'envoi
if( $form_contact_global_error ) { echo '<p class="msg msg--error msg--block">Le formulaire contient des erreurs.</p>'; }
if( $form_contact_mail_sent ) { echo '<p class="msg msg--success msg--block">Le message est envoyé.</p>'; }
if( $form_contact_mail_sent_error ) { echo '<p class="msg msg--error msg--block">Une erreur est survenue, merci de valider à nouveau le formulaire.</p>'; }
?>

<form class="<?= $form_contact_settings['css']; ?>" method="POST" action="#form-contact">

	<ul class="form-list reset-ul">

	<?php // affichage des champs

		foreach ($form_contact_fields as $id => $datas) {

			if ( $datas['type'] == 'text' || $datas['type'] == 'number' || $datas['type'] == 'email' || $datas['type'] == 'textarea' ) {

				pc_form_display_field_input_textarea( $id, $datas, $form_contact_mail_sent );

			} else if ( $datas['type'] == 'checkbox' ) {

				pc_form_display_field_checkbox( $id, $datas, $form_contact_mail_sent );

			} // FIN if $datas['type']	

		} // FIN foreach $form_contact_fields

	?>
		
		<li class="form-item form-item--captcha">
			<span class="form-label label-like <?php if($form_contact_spam_error) echo 'msg msg--error'; ?>" aria-hidden="true"><?= $form_contact_settings['label-recaptcha'].$form_contact_settings['label-required']; ?></span>
			<?php
				//echo $captcha->script();
				//echo $captcha->html();
			?>
		</li>
		
		<li class="form-item form-item--submit">
			<button type="submit" title="<?= $form_contact_settings['submit-title']; ?>" class="reset-btn form-submit"><span class="form-submit-inner"><?= $form_contact_settings['submit-txt']; ?></span></button>
		</li>

	</ul>

</form>
