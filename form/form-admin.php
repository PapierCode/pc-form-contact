<?php

/**
 * 
 * [PC] Form Contact : paramètres administrables
 * 
 */


if ( class_exists('PC_Add_Admin_Page') ) {


    /*----------  Champs  ----------*/

    $form_contact_settings_fields = array(        
        array(
            'title'     => 'Formulaire de contact',
            'id'        => 'contact-form',
            'prefix'    => 'form',
            'fields'    => array(
                array(
                    'type'      => 'text',
                    'label_for' => 'for',
                    'label'     => 'Destinataires',
                    'desc'      => '1 ou plusieurs e-mails séparés par des virgules, sans espaces.',
                    'css'       => 'width:100%;',
                    'attr'      => 'placeholder="contact@mon-site.fr,devis@gmail.com"',
                    'required'  => true
                ),
                array(
                    'type'      => 'text',
                    'label_for' => 'subject',
                    'label'     => 'Sujet de l\'e-mail',
                    'css'       => 'width:100%;',
                    'attr'      => 'placeholder="Formulaire de contact"',
                    'required'  => true
                )
            )
        )
    );
    
    $form_contact_settings_fields = apply_filters( 'pc_filter_form_contact_settings_fields', $form_contact_settings_fields );
    
    
    /*----------  Déclaration  ----------*/
    
    $form_contact_settings_declaration = new PC_Add_Admin_Page(
        'Paramètres du formulaire de contact',
        'edit.php?post_type='.FORM_CONTACT_POST_SLUG,
        'Paramètres',
        'form-contact-settings',
        $form_contact_settings_fields,
        'editor',
        '',
        'dashicons-clipboard',
        'pc_form_contact_settings_sanitize'
    );
    
    
    /*----------  Traitement  ----------*/
    
    function pc_form_contact_settings_sanitize( $datas ) {

        $datas = apply_filters( 'pc_filter_form_contact_settings_sanitize', $datas );
    
        global $form_contact_settings_fields;
        return pc_sanitize_settings_fields( $form_contact_settings_fields, $datas );
    
    }
    
    
} // FIN if class_exists(PC_Add_Admin_Page)