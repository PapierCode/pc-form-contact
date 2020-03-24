<?php
/**
 * 
 * Administration des posts Messages (formulaire de contact)
 * 
 ** Liste de posts
 ** Détail du post
 * 
 */

/*=====================================
=            Liste de post            =
=====================================*/

/*----------  Recherche dans les métas  ----------*/

add_filter( 'posts_join', 'pc_post_contact_admin_search_join' );

	function pc_post_contact_admin_search_join( $join ){

		global $pagenow, $wpdb;

		if ( is_admin() && $pagenow == 'edit.php' && ! empty( $_GET['post_type'] ) && $_GET['post_type'] == CONTACT_POST_SLUG && ! empty( $_GET['s'] ) ) {
			$join .= 'LEFT JOIN ' . $wpdb->postmeta . ' ON ' . $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
		}

		return $join;

	}

add_filter( 'posts_where', 'pc_post_contact_admin_search_where' );

	function pc_post_contact_admin_search_where( $where ){

		global $pagenow, $wpdb;

		if ( is_admin() && $pagenow == 'edit.php' && ! empty( $_GET['post_type'] ) && $_GET['post_type'] == CONTACT_POST_SLUG && ! empty( $_GET['s'] ) ) {

			$where = preg_replace( "/\(\s*" . $wpdb->posts . ".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/", "(" . $wpdb->posts . ".post_title LIKE $1) OR (" . $wpdb->postmeta . ".meta_value LIKE $1)", $where );

		}

		return $where;
	}


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

add_filter( 'post_row_actions', 'pc_post_contact_row_actions', 10, 2 );

	function pc_post_contact_row_actions( $actions, $post ) {

		if ( $post->post_type == CONTACT_POST_SLUG ) {
			unset($actions['edit']);
			$actions['display'] = '<a href="'.get_edit_post_link($post->ID).'">Afficher</a>';
			ksort($actions);
		}
		return $actions;

	}



/*=====  FIN Liste de post  =====*/

/*======================================
=            Détail du post            =
======================================*/

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


/*----------  Ajout métaboxe date & suppression ----------*/

add_action( 'admin_init', function() {

	add_meta_box(
		'post-contact-infos',
		'Détails et suppression',
		'pc_post_contact_box',
		array( CONTACT_POST_SLUG ),
		'side',
		'high'
	);

} );

function pc_post_contact_box( $post ) {

	$page_from_id = get_post_meta( $post->ID, 'contact-from-page', true );
	
	echo '<p><strong>Envoyé le '.get_the_date('d F Y',$post->ID).'</strong>.</p>';
	echo '<p>Depuis la page : <a href="'.get_the_permalink( $page_from_id ).'" title="Voir la page">'.get_the_title( $page_from_id ).'</a></p>';
	echo '<p style="padding-top:1em;border-top:1px solid #ccd0d4"><a class="button button-primary" href="'.get_delete_post_link($post->ID).'" title="Placer dans la corbeille">Supprimer</a></p>';

}


/*=====  FIN Détail du post  =====*/