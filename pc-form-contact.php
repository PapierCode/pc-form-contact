<?php

/*
Plugin Name: [PC] Form Contact
Plugin URI: www.papier-code.fr
Description: Formulaire de contact
Version: 1.0.0
Author: Papier Codé
*/


add_action('plugins_loaded', function() { // en attente du plugin [PC] Tools
   
    /*================================
    =            Includes            =
    ================================*/

    // création du post
    define('CONTACT_POST_SLUG', 'contact');
	include 'include/post.php';
	// administration des posts
	include 'include/post-admin.php';
	
    // page de paramètres administrables
    include 'include/settings-admin.php';
    global $settings_form_contact;
    $settings_form_contact = get_option('form-contact-settings-option');

	// fonctions utiles
	include 'include/fn-display-fields.php';
	include 'include/fn-validation.php';

	// configuration des champs du post et du formulaire
	include 'include/settings.php';
    
    
    /*=====  FIN Includes  =====*/

    /*========================================================
    =            Ajout de l'option dans les pages            =
    ========================================================*/
        
    add_filter( 'pc_filter_page_content_from', 'pc_add_form_contact_to_page', 10, 1 );

        function pc_add_form_contact_to_page( $page_content_from ) {

            $page_content_from['contactform'] = array(
                'Formulaire de contact',
                dirname( __FILE__ ).'\include\template.php'
            );

            return $page_content_from;
            
        }

    
    /*=====  FIN Ajout de l'option dans les pages  =====*/

    /*================================================
    =            Traitement du formulaire            =
    ================================================*/
        
    // avant le chargement de la template    
    add_action( 'template_redirect', 'pc_form_contact_validation', 999 );

        function pc_form_contact_validation() {
            

            /*----------  Conditions d'affichage  ----------*/            

            if ( !is_page() || get_post_meta( get_the_id(), 'content-from', true ) != 'contactform' ) { return; }

            
            /*===================================================
            =            Configuration du formulaire            =
            ===================================================*/
            
            // $settings_pc : configuration projet, cf. plugin [PC] Custom WP
            // $form_contact_datas : paramètres formulaire, cf. settings.php
            global $settings_pc, $settings_project, $form_contact_datas;
            
            // création du captcha
            global $form_contact_captcha; // pour utilisation dans la template
			$form_contact_captcha = new PC_recaptcha( $settings_pc['google-recaptcha-site'], $settings_pc['google-recaptcha-secret'] );
            

            /*=====  FIN Configuration du formulaire  =====*/

            /*=====================================================
            =            Si le formulaire a été validé            =
            =====================================================*/

            if ( !empty( $_POST ) ) {

                /*----------  Vérification des champs obligatoires  ----------*/                    
                
                // si le navigateur n'a pas gèré l'attribut required
                $form_contact_datas = pc_form_contact_required_fields( $form_contact_datas );
                // si erreur captcha
                if ( $form_contact_captcha->isValid( $_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR'] ) === false ) {
                    $form_contact_datas['errors']['spam-error'] = true;
                    $form_contact_datas['errors']['global-error'] = true;
                }
                

                /*----------  Si pas d'erreur  ----------*/

                if ( !$form_contact_datas['errors']['global-error'] ) {

                
                    /*----------  Préparation des valeurs  ----------*/
                    
                    $form_contact_datas['fields'] = pc_form_contact_sanitize( $form_contact_datas['fields'] );
                
                    
                    /*----------  envoi de l'email, enregistrement du post et validation  ----------*/
                    
                    if ( pc_form_contact_notification( $form_contact_datas['fields'] ) ) { 
                    
                        // enregistrement du post
                        pc_form_contact_save_post( $form_contact_datas['fields'] );
                        // validation
                        $form_contact_datas['errors']['mail-sent'] = true;
                        // affichage dans la meta title
                        add_filter( 'pc_filter_meta_title', function() {
                            global $form_contact_texts;
                            return $form_contact_texts['msg-mail-sent'];
                        } );
                        
                    } else {

                        // problème lors de l'envoi de l'email
                        $form_contact_datas['errors']['mail-sent-error'] = true;
                        // affichage dans la meta title
                        add_filter( 'pc_filter_meta_title', function() {
                            global $form_contact_texts;
                            return $form_contact_texts['msg-mail-fail'];
                        } );

                    }
                    

                } // FIN if !form_contact_global_error                    

            } /*=====  FIN Si le formulaire a été validé  =====*/


        } // FIN pc_form_contact_validation()

    /*=====  FIN Traitement du formulaire  =====*/

}); // FIN plugins_loaded