<?php

/**
*
** Sauvegarde du formulaire de contact
*
**/

/*----------  Prévenir la création d'un post à la même seconde  ----------*/    

$post_title = current_time('timestamp');

$similarEntries = get_posts( array(
    'post_type' => CONTACT_POST_SLUG,
    'title?' => $post_title,
    'posts_per_page'=>-1,
) );


$post_contact_metas_to_save = array();
foreach ($form_contact_fields as $id => $datas) {
    if ( isset( $datas['form-value'] ) ) {
        $post_contact_metas_to_save[$id] = $datas['form-value'];
    }
}


$post_contact_save = wp_insert_post(
    array(
        'post_author'	=> 1,
        'post_title'	=> $post_title,
        'post_status'	=> 'publish',
        'post_type'		=> CONTACT_POST_SLUG,
        'meta_input'	=> $post_contact_metas_to_save
    )
);