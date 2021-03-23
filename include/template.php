<?php

/**
*
* Formulaire de contact
*
**/

global $form_contact_datas, $form_contact_texts ;

?>

<div id="form-contact" class="<?= $form_contact_datas['css']; ?>">

<?php // erreur ou validation de l'envoi
if( $form_contact_datas['errors']['global-error'] ) {
	echo pc_display_alert_msg( $form_contact_texts['msg-field-error'], 'error', 'block' );
}
if( $form_contact_datas['errors']['mail-sent'] ) { 
	echo pc_display_alert_msg( $form_contact_texts['msg-mail-sent'], 'success', 'block' );
}
if( $form_contact_datas['errors']['mail-sent-error'] ) {
	echo pc_display_alert_msg( $form_contact_texts['msg-mail-fail'], 'error', 'block' );
}
?>

<form method="POST" action="#form-contact">

	<ul class="form-list reset-list">

		<?php // affichage des champs

		foreach ($form_contact_datas['fields'] as $id => $datas) {

			if ( $datas['type'] == 'text' || $datas['type'] == 'number' || $datas['type'] == 'email' || $datas['type'] == 'textarea' ) {

				pc_form_contact_display_field_input_textarea( $id, $datas, $form_contact_datas['errors']['mail-sent'] );

			} else if ( $datas['type'] == 'checkbox' ) {

				pc_form_contact_display_field_checkbox( $id, $datas, $form_contact_datas['errors']['mail-sent'] );

			} // FIN if $datas['type']	

		} // FIN foreach $form_contact_datas['fields']

		if ( $form_contact_datas['recaptacha'] ) { ?>
			
			<li class="form-item form-item--captcha">
				<span class="form-label label-like <?php if($form_contact_datas['errors']['spam-error']) echo 'msg msg--error'; ?>" aria-hidden="true"><?= $form_contact_texts['label-recaptcha'].$form_contact_texts['label-required']; ?></span>
				<?php
					echo $form_contact_datas['recaptacha']->script();
					echo $form_contact_datas['recaptacha']->html();
				?>
			</li>

		<?php } ?>
		
		<li class="form-item form-item--submit">
			<?php $btn_css_classes = apply_filters( 'pc_filter_form_contact_button_css', array( 'form-submit', 'button', 'button--xl', 'button--color-1' ) ); ?>
			<button type="submit" title="<?= $form_contact_texts['submit-title']; ?>" class="<?= implode( ' ', $btn_css_classes ); ?>"><span class="form-submit-inner"><?= $form_contact_texts['submit-txt']; ?></span></button>
		</li>

	</ul>

</form>

</div>
