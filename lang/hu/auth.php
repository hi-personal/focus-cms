<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Hitelesítési nyelvi sorok
    |--------------------------------------------------------------------------
    |
    | Az alábbi nyelvi sorokat a hitelesítés során használjuk különböző üzenetek
    | megjelenítésére a felhasználó számára. Ezeket szabadon módosíthatod az
    | alkalmazásod igényei szerint.
    |
    */

    'failed' => 'Ezek a hitelesítő adatok nem egyeznek az adatbázisunkban szereplőkkel.',
    'password' => 'A megadott jelszó helytelen.',
    'throttle' => 'Túl sok sikertelen bejelentkezési kísérlet. Kérlek, próbáld újra :seconds másodperc múlva.',

];
/*

// Register Custom Post Type
function post() {

	$labels = array(
		'name'                  => _x( 'Post', 'Post Type General Name', 'post' ),
		'singular_name'         => _x( 'Post', 'Post Type Singular Name', 'post' ),
		'menu_name'             => __( 'Posts', 'post' ),
		'name_admin_bar'        => __( 'Post', 'post' ),
		'archives'              => __( 'Item Archives', 'post' ),
		'attributes'            => __( 'Item Attributes', 'post' ),
		'parent_item_colon'     => __( 'Parent Item:', 'post' ),
		'all_items'             => __( 'All Items', 'post' ),
		'add_new_item'          => __( 'Add New Item', 'post' ),
		'add_new'               => __( 'Add New', 'post' ),
		'new_item'              => __( 'New Item', 'post' ),
		'edit_item'             => __( 'Edit Item', 'post' ),
		'update_item'           => __( 'Update Item', 'post' ),
		'view_item'             => __( 'View Item', 'post' ),
		'view_items'            => __( 'View Items', 'post' ),
		'search_items'          => __( 'Search Item', 'post' ),
		'not_found'             => __( 'Not found', 'post' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'post' ),
		'featured_image'        => __( 'Featured Image', 'post' ),
		'set_featured_image'    => __( 'Set featured image', 'post' ),
		'remove_featured_image' => __( 'Remove featured image', 'post' ),
		'use_featured_image'    => __( 'Use as featured image', 'post' ),
		'insert_into_item'      => __( 'Insert into item', 'post' ),
		'uploaded_to_this_item' => __( 'Uploaded to this item', 'post' ),
		'items_list'            => __( 'Items list', 'post' ),
		'items_list_navigation' => __( 'Items list navigation', 'post' ),
		'filter_items_list'     => __( 'Filter items list', 'post' ),
	);
	$args = array(
		'label'                 => __( 'Post', 'post' ),
		'description'           => __( 'Post Description', 'post' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
		'taxonomies'            => array( 'category', 'post_tag' ),
		'hierarchical'          => true,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'post',
	);
	register_post_type( 'post', $args );

}
add_action( 'init', 'post', 0 );

*/