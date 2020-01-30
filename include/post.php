<?php

/**
 * 
 * Création du post Messages
 * 
 */


if ( class_exists( 'PC_Add_Custom_Post' ) ) {


	/*----------  Labels  ----------*/

	$post_contact_labels = array (
	    'name'                  => 'Messages',
	    'singular_name'         => 'Message',
	    'menu_name'             => 'Messages',
	    'add_new'               => 'Ajouter un message',
	    'add_new_item'          => 'Ajouter un message',
	    'new_item'              => 'Ajouter un message',
	    'edit_item'             => 'Afficher le message',
	    'all_items'             => 'Tous les messages',
		'not_found'             => 'Aucun message',
		'search_items'			=> 'Rechercher dans les messages'
	);


	/*----------  Configuration  ----------*/

	$post_contact_args = array(
	    'menu_position'         => 27,
		'menu_icon'             => 'dashicons-email-alt',
		'show_in_nav_menus'     => false,
	    'supports'              => array( 'title' ),
        'has_archive'		    => false,
        'exclude_from_search'   => true,
		'publicly_queryable'    => false,
		'capabilities' => array(
			'create_posts' => false
		),
		'map_meta_cap' => true,
	);


	/*----------  Déclaration  ----------*/

	$post_contact_declaration = new PC_Add_Custom_Post( CONTACT_POST_SLUG, $post_contact_labels, $post_contact_args );


} // FIN if class_exists(PC_Add_Custom_Post)
