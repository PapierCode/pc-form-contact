<?php

/**
*
* Formulaire de contact
*
**/

?>

<div id="form-contact" class="<?= $form_contact_datas['css']; ?>">

<?php // erreur ou validation de l'envoi
if( $form_contact_datas['errors']['global-error'] ) { echo '<p class="msg msg--error msg--block">'.$form_contact_texts['msg-field-error'].'</p>'; }
if( $form_contact_datas['errors']['mail-sent'] ) { echo '<p class="msg msg--success msg--block">'.$form_contact_texts['msg-mail-sent'].'</p>'; }
if( $form_contact_datas['errors']['mail-sent-error'] ) { echo '<p class="msg msg--error msg--block">'.$form_contact_texts['msg-mail-fail'].'</p>'; }
?>

<form method="POST" action="#form-contact">

	<ul class="form-list reset-list">

		<?php // affichage des champs

		foreach ($form_contact_datas['fields'] as $id => $datas) {

			if ( $datas['type'] == 'text' || $datas['type'] == 'number' || $datas['type'] == 'email' || $datas['type'] == 'textarea' ) {

				pc_form_display_field_input_textarea( $id, $datas, $form_contact_datas['errors']['mail-sent'] );

			} else if ( $datas['type'] == 'checkbox' ) {

				pc_form_display_field_checkbox( $id, $datas, $form_contact_datas['errors']['mail-sent'] );

			} // FIN if $datas['type']	

		} // FIN foreach $form_contact_datas['fields']

		if ( isset($form_contact_captcha) ) { ?>
			
			<li class="form-item form-item--captcha">
				<span class="form-label label-like <?php if($form_contact_datas['errors']['spam-error']) echo 'msg msg--error'; ?>" aria-hidden="true"><?= $form_contact_texts['label-recaptcha'].$form_contact_texts['label-required']; ?></span>
				<?php
					echo $form_contact_captcha->script();
					echo $form_contact_captcha->html();
				?>
			</li>

		<?php } ?>
		
		<li class="form-item form-item--submit">
			<button type="submit" title="<?= $form_contact_texts['submit-title']; ?>" class="reset-btn form-submit btn btn--xl btn--red"><span class="form-submit-inner"><?= $form_contact_texts['submit-txt']; ?></span></button>
		</li>

	</ul>

</form>

</div>
