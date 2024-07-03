<?php

/*
Plugin Name: Section Concert
Author: Sylvain Barrellon
Author URI: https://www.example.com
Description: add a new 'Concert' section just below 'Articles'
*/

defined('ABSPATH') or die('Nothing to see here.');



/**
 * Register a custom post called 'concert'
 */
function sc_concert_init(){
    $labels = [
        'name'                  => _x( 'Concerts', 'Post type general name', 'section-concert' ),
		'singular_name'         => _x( 'Concert', 'Post type singular name', 'section-concert' ),
		'menu_name'             => _x( 'Concerts', 'Admin Menu text', 'section-concert' ),
		'name_admin_bar'        => _x( 'Concert', 'Add New on Toolbar', 'section-concert' ),
		'add_new'               => __( 'Add New', 'section-concert' ),
		'add_new_item'          => __( 'Add New Concert', 'section-concert' ),
		'new_item'              => __( 'New Concert', 'section-concert' ),
		'edit_item'             => __( 'Edit Concert', 'section-concert' ),
		'view_item'             => __( 'View Concert', 'section-concert' ),
		'all_items'             => __( 'All Concerts', 'section-concert' ),
		'search_items'          => __( 'Search Concerts', 'section-concert' ),
		'parent_item_colon'     => __( 'Parent Concerts:', 'section-concert' ),
		'not_found'             => __( 'No Concerts found.', 'section-concert' ),
		'not_found_in_trash'    => __( 'No Concerts found in Trash.', 'section-concert' ),
		'featured_image'        => _x( 'Concert Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'section-concert' ),
		'set_featured_image'    => _x( 'Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'section-concert' ),
		'remove_featured_image' => _x( 'Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'section-concert' ),
		'use_featured_image'    => _x( 'Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'section-concert' ),
		'archives'              => _x( 'Concert archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'section-concert' ),
		'insert_into_item'      => _x( 'Insert into Concert', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'section-concert' ),
		'uploaded_to_this_item' => _x( 'Uploaded to this Concert', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'section-concert' ),
		'filter_items_list'     => _x( 'Filter Concerts list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'section-concert' ),
		'items_list_navigation' => _x( 'Concerts list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'section-concert' ),
		'items_list'            => _x( 'Concerts list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'section-concert' ),
	
    ];

    $args = [
		'labels'             => $labels,
        'description'        => 'Concert custom post type.',
        'menu_icon'          => 'dashicons-format-audio',
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'concert' ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => 5,
		'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' ),
        'show_in_rest'       => true
	];

    register_post_type('concert', $args);
}

add_action('init', 'sc_concert_init');




/**
 * add a new meta_box to the concert post type
 */
function sc_add_meta_box(){
    
    add_meta_box(
        'date_concert',
        __('Concert\'s date', 'section-concert'),
        'sc_render_meta_box_content',
        'concert', 
        'advanced', 
        'high'
    );
    
}

add_action('add_meta_boxes', 'sc_add_meta_box');


/**
 * handle the validation of the post action for concert custom post
 */
function save( $post_id ) {
 
    /*
     * We need to verify this came from the our screen and with proper authorization,
     * because save_post can be triggered at other times.
     */

    // Check if our nonce is set.
    if ( ! isset( $_POST['sc_custom_box_nonce'] ) ) {
        return $post_id;
    }

    $nonce = $_POST['sc_custom_box_nonce'];

    // Verify that the nonce is valid.
    if ( ! wp_verify_nonce( $nonce, 'sc_custom_box' ) ) {
        return $post_id;
    }

    /*
     * If this is an autosave, our form has not been submitted,
     * so we don't want to do anything.
     */
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return $post_id;
    }

    // Check the user's permissions.
    if ( 'page' == $_POST['post_type'] ) {
        if ( ! current_user_can( 'edit_page', $post_id ) ) {
            return $post_id;
        }
    } else {
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return $post_id;
        }
    }

    /* OK, it's safe for us to save the data now. */

    // Sanitize the user input.
    $mydata = sanitize_text_field( $_POST['concert-date'] );

    // Update the meta field.
    update_post_meta( $post_id, 'my_meta_concert_date', $mydata );
}

add_action('save_post', 'save');



/**
 * Display the content of the concert meta_box
 */
function sc_render_meta_box_content($post){
    wp_nonce_field('sc_custom_box', 'sc_custom_box_nonce');


    $value = get_post_meta($post->ID, 'my_meta_concert_date', true);
    // var_dump($value);
    // die;

    ?>
        <p>S'il vous plait, entrez la date du concert.</p>
        <label for="concert-date"> Date du concert</label>
        <input type="date" id="concert-date" name="concert-date" value="<?php echo(esc_attr($value));?>">
    <?php


}



/**
 * Defins the columns of the list of the concert custom posts and their ordrer
 */
function sc_custom_columns_list($columns){
    $customColumns = [
        'concert_date' => 'Date du concert',
        'title' => $columns['title'],
        'date'=> $columns['date']
    ];

    return $customColumns;
}

add_filter('manage_concert_posts_columns', 'sc_custom_columns_list');


/**
 * Define the value in the custom columns created before
 */
function sc_custom_column_values($columns, $post_id){

    switch($columns){
        case 'concert_date' :
            echo get_post_meta($post_id, 'my_meta_concert_date', true);
            break;
    }
}

add_action('manage_concert_posts_custom_column', 'sc_custom_column_values', 10, 2);



/**
 * Add the sort option to a custom column
 */
function sc_sortable_columns($columns){
    $columns['concert_date'] = 'concert_date';
    return $columns;
}

add_filter('manage_edit-concert_sortable_columns', 'sc_sortable_columns');


/**
 * load the textdomain
 */
function sc_load_text_domain(){
    load_plugin_textdomain('section-concert', false, dirname(plugin_basename(__FILE__)).'/languages');
}

add_action('init', 'sc_load_text_domain');