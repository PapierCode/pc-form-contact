<?php

/**
 * 
 * Page de paramètres dans l'administration
 * 
 */


if ( class_exists('PC_Add_Admin_Page') ) {


    /*----------  Champs  ----------*/

    $settings_form_contact_fields = array(        
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
    
    $settings_form_contact_fields = apply_filters( 'pc_filter_settings_form_contact_fields', $settings_form_contact_fields );
    
    
    /*----------  Déclaration  ----------*/
    
    $settings_form_contact_declaration = new PC_Add_Admin_Page(
        'Paramètres du formulaire de contact',
        'edit.php?post_type='.CONTACT_POST_SLUG,
        'Paramètres',
        'form-contact-settings',
        $settings_form_contact_fields,
        'editor',
        '',
        'dashicons-clipboard',
        'pc_sanitize_settings_form_contact'
    );
    
    
    /*----------  Traitement  ----------*/
    
    function pc_sanitize_settings_form_contact( $datas ) {

        $datas = apply_filters( 'pc_filter_settings_form_contact_sanitize_fields', $datas );
    
        global $settings_form_contact_fields;
        return pc_sanitize_settings_fields( $settings_form_contact_fields, $datas );
    
    }
    
    
} // FIN if class_exists(PC_Add_Admin_Page)