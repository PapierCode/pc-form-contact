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


	/*=====  FIN Création  ======*/

	/*==============================================
	=            Affichage dans l'admin            =
	==============================================*/

	/*----------  Actions groupées  ----------*/	
	
	add_filter( 'bulk_actions-edit-'.CONTACT_POST_SLUG, 'pc_post_contact_bluk_actions' );

		function pc_post_contact_bluk_actions( $actions ) {

			unset($actions['edit']);
			return $actions;

		}


	/*----------  Liens "Tous", "Publiés",...  ----------*/
	
	add_filter( 'views_edit-'.CONTACT_POST_SLUG, 'pc_post_contact_view_links' );
	
		function pc_post_contact_view_links( $views ) {

			unset($views['publish']);
			return $views;

		}
	

	/*----------  Colonnes  ----------*/
	
	add_filter( 'manage_'.CONTACT_POST_SLUG.'_posts_columns', 'pc_post_contact_columns' );

		function pc_post_contact_columns( $columns ) {
			
			$columns['title'] = 'E-mail';
			unset($columns['date']);
			$columns['send'] = 'Envoyé le';
			$columns['last-name'] = 'Nom';
			return $columns;

		}

	add_action( 'manage_'.CONTACT_POST_SLUG.'_posts_custom_column', 'pc_post_contact_columns_content', 10, 2);

		function pc_post_contact_columns_content( $column, $post_id ) {

			switch ($column) {
				case 'send':
					echo get_the_date('d F Y',$post_id);
					break;
				
				case 'last-name':
					echo get_post_meta( $post_id, 'contact-last-name', true );
					break;
			}

		}


	/*----------  Liens sous le titre  ----------*/
	
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


	/*----------  Pas de métaboxe Publier  ----------*/
	
	add_action( 'admin_menu' , 'pc_post_contact_remove_metabox' );

		function pc_post_contact_remove_metabox() {
			remove_meta_box( 'submitdiv' , CONTACT_POST_SLUG , 'normal' );
		}
	

	/*----------  Pas de titre dans le détail du post  ----------*/
	
	add_filter( 'admin_body_class', 'pc_post_contact_body_classes', 10, 1 );

		function pc_post_contact_body_classes( $classes ) {

			global $pagenow;
			$screen = get_current_screen();
			if ( $pagenow =='post.php' && $screen->post_type == CONTACT_POST_SLUG ) {
				$classes .= ' pc-post-contact';
				
			}
			return $classes;

		}
	
	add_action( 'admin_head', 'pc_post_contact_head_style' );
		
		function pc_post_contact_head_style() { 
		
			echo '<style>.pc-post-contact #post-body-content { display:none }</style>';
		
		}


	/*----------  Métaboxe date & suppression ----------*/
	
	add_action( 'admin_init', function() {

		add_meta_box(
			'post-contact-infos',
			'Date et suppression',
			'pc_post_contact_box',
			array( CONTACT_POST_SLUG ),
			'side',
			'high'
		);
	
	} );

	function pc_post_contact_box( $post ) {

		echo '<p>Envoyé le <strong>'.get_the_date('d F Y',$post->ID).'</strong>.</p>';
		echo '<p><a class="button button-primary" href="'.get_delete_post_link($post->ID).'" title="Placer dans la corbeille">Supprimer</a></p>';

	}
	
	

/*=====  FIN Affichage dans l'admin  =====*/

} // FIN if news-active && class_exists(PC_Add_Custom_Post)
