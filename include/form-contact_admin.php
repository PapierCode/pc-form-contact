<?php

/*==================================
=            Paramètres            =
==================================*/


// si la class est disponible
if ( class_exists('PC_Add_Admin_Page') ) {

    /*----------  Communs  ----------*/
    
    // select page
    $all_pages = get_posts( array(
        'post_type' => 'page',
        'nopaging' => true,
    ) );
    $pages_list = array();
    foreach ($all_pages as $page) {
        $pages_list[$page->post_title] = $page->ID;
    }
    
    
    /*----------  Contenu  ----------*/
    
    // sections et champs associés
    global $settings_form_contact_fields;
    $settings_form_contact_fields = array(        
        array(
            'title'     => 'Conditions générales d\'utilisation',
            'id'        => 'cgu',
            'prefix'    => 'cgu',
            'fields'    => array(
                array(
                    'type'      => 'select',
                    'label_for' => 'page',
                    'label'     => 'Page des CGU',
                    'options'   => $pages_list,
                    'required'  => true
                )
            )
        ),
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
    
    
    /*----------  Création  ----------*/
    
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
    
    
    /*----------  Sanitize  ----------*/
    
    function pc_sanitize_settings_form_contact( $datas ) {

        $datas = apply_filters( 'pc_filter_settings_form_contact_sanitize_fields', $datas );
    
        global $settings_form_contact_fields;
        return pc_sanitize_settings_fields( $settings_form_contact_fields, $datas );
    
    }
    
    } // FIN if class_exists


/*=====  FIN Paramètres  =====*/

function theme_post_search_join( $join ){
    global $pagenow, $wpdb;
    if ( is_admin() && $pagenow == 'edit.php' && ! empty( $_GET['post_type'] ) && $_GET['post_type'] == CONTACT_POST_SLUG && ! empty( $_GET['s'] ) ) {
        $join .= 'LEFT JOIN ' . $wpdb->postmeta . ' ON ' . $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
    }
    return $join;
}
add_filter( 'posts_join', 'theme_post_search_join' );

function theme_search_where( $where ){
    global $pagenow, $wpdb;
    if ( is_admin() && $pagenow == 'edit.php' && ! empty( $_GET['post_type'] ) && $_GET['post_type'] == CONTACT_POST_SLUG && ! empty( $_GET['s'] ) ) {
        $where = preg_replace(
       "/\(\s*" . $wpdb->posts . ".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
       "(" . $wpdb->posts . ".post_title LIKE $1) OR (" . $wpdb->postmeta . ".meta_value LIKE $1)", $where );
    }
    return $where;
}
add_filter( 'posts_where', 'theme_search_where' );