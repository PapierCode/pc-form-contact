<?php

/**
*
* Message du formulaire du contact
*
** Création
** Affichage dans l'admin
*
**/


if ( class_exists( 'PC_Add_Custom_Post' ) ) {

	/*================================
	=            Création            =
	================================*/

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


	/*----------  Champs  ----------*/
	
	include 'form-contact_settings.php';


	/*=====  FIN Création  ======*/

	/*==============================================
	=            Affichage dans l'admin            =
	==============================================*/

	// pas d'actions groupées
	add_filter( 'bulk_actions-edit-'.CONTACT_POST_SLUG, 'pc_post_contact_bluk_actions' );

		function pc_post_contact_bluk_actions( $actions ) {

			unset($actions['edit']);
			return $actions;

		}

	// liens "Tous", "Publiés",...
	add_filter( 'views_edit-'.CONTACT_POST_SLUG, 'pc_post_contact_view_links' );
	
		function pc_post_contact_view_links( $views ) {

			unset($views['publish']);
			return $views;

		}
	
	// colonnes
	add_filter( 'manage_'.CONTACT_POST_SLUG.'_posts_columns', 'pc_post_contact_columns' );

		function pc_post_contact_columns( $columns ) {
			
			$columns['title'] = 'Référence';
			unset($columns['date']);
			$columns['send'] = 'Envoyé le';
			$columns['mail'] = 'E-mail';
			return $columns;

		}

	// contenu des colonnes
	add_action( 'manage_'.CONTACT_POST_SLUG.'_posts_custom_column', 'pc_post_contact_columns_content', 10, 2);

		function pc_post_contact_columns_content( $column, $post_id ) {

			switch ($column) {
				case 'send':
					echo get_the_date('d F Y',$post_id);
					break;
				
				case 'mail':
					echo get_post_meta( $post_id, 'contact-mail' ,true );
					break;
			}

		}

	// liens sous le titre
	add_filter( 'post_row_actions', 'remove_row_actions', 10, 2 );
		function remove_row_actions( $actions, $post )
		{
			if ( $post->post_type == CONTACT_POST_SLUG ) {
				unset($actions['edit']);
				$actions['display'] = '<a href="'.get_edit_post_link($post->ID).'">Afficher</a>';
				ksort($actions);
			}
			return $actions;
		}

	// pas de metaboxe Publier
	add_action( 'admin_menu' , 'pc_post_contact_remove_metabox' );

		function pc_post_contact_remove_metabox() {
			remove_meta_box( 'submitdiv' , CONTACT_POST_SLUG , 'normal' );
		}
	

/*=====  FIN Affichage dans l'admin  =====*/

} // FIN if news-active && class_exists(PC_Add_Custom_Post)
