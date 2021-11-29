<?php

/**
*
* [PC] Form Contact : template formulaire
*
**/


global $pc_contact_form;

if ( is_object( $pc_contact_form ) ) {

	do_action( 'pc_action_before_form_contact', $pc_contact_form );

	$pc_contact_form->display_form();

	do_action( 'pc_action_after_form_contact', $pc_contact_form );
	
}
