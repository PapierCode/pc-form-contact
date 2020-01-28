<?php
/**
 * 
 * Fonctions d'affichage des champs
 * 
 ** Afficher un label
 ** Afficher un champ text, number, email ou textarea
 ** Afficher un champ checkbox
 * 
 */

/*=========================================
=            Afficher un label            =
=========================================*/

/**
 * 
 * @param string    $for        attribut for
 * @param string    $txt        texte du label
 * 
 */

function pc_form_display_label( $for, $datas ) {

    global $form_contact_texts;

    $txt = ( isset($datas['form-label']) ) ? $datas['form-label'] : $datas['label'];
    if ( isset($datas['required']) && $datas['required'] == true ) { $txt .= $form_contact_texts['label-required']; }

    echo '<label for="'.$for.'">'.$txt.'</label>';

}


/*=====  FIN Afficher un label  =====*/

/*=========================================================================
=            Afficher un champ text, number, email ou textarea            =
=========================================================================*/

/**
 * 
 * @param string    $name               attribut name et id
 * @param array     $datas
 *                  string  type        text/email/number/textearea
 *                  string  label       texte du label
 *                  bool    required    champ obligatoire
 *                  string  form-label  label pour l'affiche public
 *                  string  form-css    classes css spéciales
 *                  string  form-attr   attributs supplémentaires
 *                  bool    form-error  champ en erreur
 *                  string  form-desc   description/aide
 * @param bool      $email_sent         email envoyé
 * 
 */

function pc_form_display_field_input_textarea( $name, $datas, $mail_sent = false ) {

    /*----------  Configuration  ----------*/ 
    
    // si des propriétés ne sont pas définies
    $datas = array_merge(
        array(
            'type'		    => 'text',
            'label' 	    => 'Sans nom',
            'required' 	    => false,
            'form-css'      => '',
            'form-attr'     => '',
            'form-error'	=> false,
            'form-desc'     => '',
        ),
        $datas
    );

    // type de champ
    $type = $datas['type'];

    // valeur du champ
    $value = ( !$mail_sent && !empty($_POST) ) ?  $_POST[$name] : '';

    // classes css du container : défaut
    $css = 'form-item form-item--'.$type.' form-item--'.$name;
    // classes css du container : erreur
    if ( $datas['form-error'] ) { $css .= ' form-item--error'; } 
    // classes css du container : custom
    if ( $datas['form-css'] != '' ) { $css .= ' '.$datas['form-css']; }

    // attributs custom
    $other = ( $datas['form-attr'] != '' ) ? ' '.$datas['form-attr'] : '';

    // champ obligatoire
    $other .= ( $datas['required'] ) ? ' required' : '';
    
    // accessibilité
    $other .= ( $datas['form-desc'] != '' ) ? ' aria-describedby="desc-'.$name.'"' : '';
    $other .= ( $datas['form-error'] ) ? ' aria-invalid="true"' : '';


    /*----------  affichage  ----------*/
    
    echo '<li class="'.$css.'">';

        // label
        pc_form_display_label( $name, $datas );
        
        echo '<div>';

            // input type text/number/email
            if ( $type == 'text' || $type == 'number' || $type == 'email' ) {

                echo '<input type="'.$type.'" id="'.$name.'" name="'.$name.'" value="'.$value.'"'.$other.'/>';

            // textarea
            } else if ( $type == 'textarea' ) {

                echo '<textarea id="'.$name.'" name="'.$name.'"'.$other.'>'.$value.'</textarea>';

            }

            // description/aide
            if ( $datas['form-desc'] != '' ) { echo '<p id="desc-'.$name.'">'.$datas['form-desc'].'</p>'; }

        echo '</div>';

	echo '</li>';

}


/*=====  FIN Afficher un champ text, number, email ou textarea  =====*/

/*==================================================
=            Afficher un champ checkbox            =
==================================================*/

/**
 * 
 * @param string    $name               attribut name et id
 * @param array     $datas
 *                  string  label       texte du label
 *                  bool    required    champ obligatoire
 *                  string  form-label  label pour l'affichage public
 *                  string  form-css    classes css spéciales
 *                  string  form-attr   attributs supplémentaires
 *                  bool    form-error  champ en erreur
 *                  string  form-desc   description/aide
 * @param bool      $email_sent         email envoyé
 * 
 */

function pc_form_display_field_checkbox( $name, $datas, $mail_sent = false ) {

    /*----------  Configuration  ----------*/ 
    
    // si des propriétés ne sont pas définies
    $datas = array_merge(
        array(
            'label' 	    => 'Sans nom',
            'required' 	    => false,
            'form-css'      => '',
            'form-attr'     => '',
            'form-error'	=> false,
            'form-desc'     => '',
        ),
        $datas
    );

    // classes css du container : défaut
    $css = 'form-item form-item--checkbox form-item--'.$name;
    // classes css du container : erreur
    if ( $datas['form-error'] ) { $css .= ' form-item--error'; } 
    // classes css du container : custom
    if ( $datas['form-css'] != '' ) { $css .= ' '.$datas['form-css']; }

    // attributs custom
    $other = ( $datas['form-attr'] != '' ) ? ' '.$datas['form-attr'] : '';

    // champ obligatoire
    $other .= ( $datas['required'] ) ? ' required' : '';

    // coché
    $other .= ( !$mail_sent && isset($_POST[$name]) ) ? ' checked' : '';

    // accessibilité
    $other .= ( $datas['form-desc'] != '' ) ? ' aria-describedby="desc-'.$name.'"' : '';
    $other .= ( $datas['form-error'] ) ? ' aria-invalid="true"' : '';


    /*----------  Affichage  ----------*/

    echo '<li class="'.$css.'">';
        echo '<div>';
            echo '<input type="checkbox" name="'.$name.'" id="'.$name.'" value="1"'.$other.' />';
            pc_form_display_label( $name, $datas );
        echo '</div>';
        if ( $datas['form-desc'] != '' ) { echo '<p id="desc-'.$name.'">'.$datas['form-desc'].'</p>'; };
	echo '</li>';

}


/*=====  FIN Afficher un champ checkbox  =====*/