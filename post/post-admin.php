<?php
/**
 * 
 * [PC] Form Contact : administration
 * 
 ** Recherche
 ** Liste de posts
 ** Détail du post
 * 
 */

/*=================================
=            Recherche            =
=================================*/

add_filter( 'posts_join', 'pc_form_contact_edit_admin_search_join' );

	function pc_form_contact_edit_admin_search_join( $join ){

		global $pagenow, $wpdb;

		if ( is_admin() && $pagenow == 'edit.php' && ! empty( $_GET['post_type'] ) && $_GET['post_type'] == FORM_CONTACT_POST_SLUG && ! empty( $_GET['s'] ) ) {
			$join .= 'LEFT JOIN ' . $wpdb->postmeta . ' ON ' . $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
		}

		return $join;

	}

add_filter( 'posts_where', 'pc_form_contact_edit_admin_search_where' );

	function pc_form_contact_edit_admin_search_where( $where ){

		global $pagenow, $wpdb;

		if ( is_admin() && $pagenow == 'edit.php' && ! empty( $_GET['post_type'] ) && $_GET['post_type'] == FORM_CONTACT_POST_SLUG && ! empty( $_GET['s'] ) ) {

			$where = preg_replace( "/\(\s*" . $wpdb->posts . ".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/", "(" . $wpdb->posts . ".post_title LIKE $1) OR (" . $wpdb->postmeta . ".meta_value LIKE $1)", $where );

		}

		return $where;
	}


/*=====  FIN Recherche  =====*/

/*=====================================
=            Liste de post            =
=====================================*/

/*----------  Actions groupées  ----------*/	

add_filter( 'bulk_actions-edit-'.FORM_CONTACT_POST_SLUG, 'pc_form_contact_edit_bluk_actions' );

	function pc_form_contact_edit_bluk_actions( $actions ) {

		unset($actions['edit']);
		return $actions;

	}


/*----------  Liens "Tous", "Publiés",...  ----------*/

add_filter( 'views_edit-'.FORM_CONTACT_POST_SLUG, 'pc_form_contact_edit_view_links' );

	function pc_form_contact_edit_view_links( $views ) {

		unset($views['publish']);
		return $views;

	}


/*----------  Colonnes  ----------*/

add_filter( 'manage_'.FORM_CONTACT_POST_SLUG.'_posts_columns', 'pc_form_contact_edit_manage_posts_columns' );

	function pc_form_contact_edit_manage_posts_columns( $columns ) {
		
		$columns['title'] = 'E-mail';
		unset($columns['date']);
		$columns['send'] = 'Envoyé le';
		$columns['last-name'] = 'Nom';
		return $columns;

	}

add_action( 'manage_'.FORM_CONTACT_POST_SLUG.'_posts_custom_column', 'pc_form_contact_edit_manage_posts_custom_columns', 10, 2);

	function pc_form_contact_edit_manage_posts_custom_columns( $column, $post_id ) {

		switch ($column) {
			case 'send':
				echo get_the_date('d F Y',$post_id);
				break;
			
			case 'last-name':
				echo get_post_meta( $post_id, 'contact-last-name', true );
				break;
		}

	}


/*----------  Actions individuelles  ----------*/

add_filter( 'post_row_actions', 'pc_form_contact_edit_posts_row_actions', 10, 2 );

	function pc_form_contact_edit_posts_row_actions( $actions, $post ) {

		if ( $post->post_type == FORM_CONTACT_POST_SLUG ) {
			unset($actions['edit']);
			$actions['display'] = '<a href="'.get_edit_post_link($post->ID).'">Afficher</a>';
			ksort($actions);
		}
		return $actions;

	}


/*----------  Accès aux détails pour les clients  ----------*/
 
add_action( 'admin_init', 'pc_form_contact_edit_capabilities', 999 );

	function pc_form_contact_edit_capabilities() {
		
		$users = array( 'editor', 'shop_manager' );

		foreach ( $users as $user ) {
			$role = get_role( $user );
			if ( is_object( $role ) ) {
				$role->add_cap( 'edit_others_messages' );
			}
		}

	};


/*=====  FIN Liste de post  =====*/

/*======================================
=            Détail du post            =
======================================*/

/*----------  Pas de titre dans le détail du post  ----------*/

add_action( 'init', 'pc_form_contact_remove_post_type_support' );

function pc_form_contact_remove_post_type_support() {

	remove_post_type_support( FORM_CONTACT_POST_SLUG, 'title' );

}


/*----------  Pas de métaboxe Publier  ----------*/

add_action( 'admin_menu' , 'pc_form_contact_remove_metaboxe' );

	function pc_form_contact_remove_metaboxe() {
		remove_meta_box( 'submitdiv' , FORM_CONTACT_POST_SLUG , 'normal' );
	}


/*----------  Ajout métaboxe date & suppression ----------*/

add_action( 'admin_init', function() {

	add_meta_box(
		'post-contact-infos',
		'Date & origine',
		'pc_form_contact_metabox_origin',
		array( FORM_CONTACT_POST_SLUG ),
		'side',
		'high'
	);
	add_meta_box(
		'post-contact-trash',
		'Suppression',
		'pc_form_contact_metabox_trash',
		array( FORM_CONTACT_POST_SLUG ),
		'side',
		'low'
	);

} );

function pc_form_contact_metabox_origin( $post ) {

	$page_from_id = get_post_meta( $post->ID, 'contact-from-page', true );
	
	echo '<p>Envoyé le <strong>'.get_the_date('d F Y',$post->ID).'</strong>.</p>';
	echo '<p>Depuis la page : <a href="'.get_the_permalink( $page_from_id ).'" title="Voir la page">'.get_the_title( $page_from_id ).'</a></p>';

}

function pc_form_contact_metabox_trash( $post ) {
	if ( current_user_can( 'delete_others_posts' ) ) {
		echo '<p><a class="button button-primary" href="'.get_delete_post_link($post->ID).'">Mettre à la corbeille</a></p>';
	}

}


/*=====  FIN Détail du post  =====*/