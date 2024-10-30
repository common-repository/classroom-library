<?php

// ---------------------------------------------------------------------------------
// ----- CREATE BOOKS POST TYPE ----------------------------------------------------
// ---------------------------------------------------------------------------------
function mbcl_create_publication_post_type() {

	/**
	 * Post Type: Publications.
	 */

	$labels = [
		"name" => __( "Publications", " classroom_library" ),
		"singular_name" => __( "Publication", " classroom_library" ),
	];

	$args = [
		"label" => __( "Publications", " classroom_library" ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"rest_namespace" => "wp/v2",
		"has_archive" => false,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"delete_with_user" => false,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"can_export" => false,
		"rewrite" => [ "slug" => "publication", "with_front" => true ],
		"query_var" => true,
		"menu_icon" => "dashicons-book",
		"supports" => [ "title", "editor", "thumbnail", "revisions", "custom-fields", "revisions" ],
		"show_in_graphql" => false,
	];

	register_post_type( "mbcl_publication", $args );
}

add_action( 'init', 'mbcl_create_publication_post_type' );



// ---------------------------------------------------------------------------------
// ----- PUBLICATION POST TYPE FIELDS ----------------------------------------------
// ---------------------------------------------------------------------------------
// add book details box to books post type
function mbcl_add_publication_meta_boxes() {
    add_meta_box(
        "post_metadata_mbcl_publication_post", // div id containing rendered fields
        "Publication details", // section heading displayed as text
        "post_meta_box_mbcl_publication_post", // callback function to render fields
        "mbcl_publication", // name of post type on which to render fields
        "side", // location on the screen
        "high" // placement priority
    );
}
add_action( "admin_init", "mbcl_add_publication_meta_boxes" );



function mbcl_publication_details_fields(){
    return [
        [
            "name"          => "mbcl_publication_barcode",
            "label"         => "Barcode/ISBN",
            "placeholder"   => "Enter barcode/ISBN"
        ],
        [
            "name"          => "mbcl_publication_author_first_name",
            "label"         => "Author first name",
            "placeholder"   => "First name"
        ],
        [
            "name"          => "mbcl_publication_author_last_name",
            "label"         => "Author last name",
            "placeholder"   => "Last name"
        ],
        [
            "name"          => "mbcl_publication_cover_image_url",
            "label"         => "Cover image URL",
            "placeholder"   => "https://"
        ],
        [
            "name"          => "mbcl_publication_openlibrary_key",
            "label"         => "OpenLibrary.org key",
            "placeholder"   => "/book/XYZ"
        ],
        [
            "name"          => "mbcl_publication_count",
            "label"         => "Number of copies",
            "placeholder"   => "",
            "field_type"    => "number"
        ],
        [
            "name"          => "mbcl_publication_count_available",
            "label"         => "Number available",
            "placeholder"   => "",
            "field_type"    => "number"
        ],
    ];
}

// save field value
function mbcl_save_publication_meta_boxes(){
    global $post;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    foreach( mbcl_publication_details_fields() as $field ){

        if( isset($post->ID) ){
            update_post_meta( $post->ID, $field['name'], sanitize_text_field( $_POST[ $field['name'] ] ) );
        }
    }
}
add_action( 'save_post', 'mbcl_save_publication_meta_boxes' );

// callback function to render fields
function post_meta_box_mbcl_publication_post(){

    global $post;
    $custom = get_post_custom( $post->ID );

    foreach( mbcl_publication_details_fields() as $field ){

        if( empty( $field['field_type'] ) ) { $field_type = "text"; } else { $field_type = $field['field_type']; }

        $field_value = ( !empty($custom[ $field['name'] ][0]) ) ? $custom[ $field['name'] ][0] : '';
        if( $field_value == '' && isset($field['field_type']) == "number" ){ $field_value = 1; }

        echo '<p><label>' . esc_html($field['label']) . '</label><br/><input type="' . esc_attr($field_type) . '" id="' . esc_attr($field['name']) . '" name="' . esc_attr($field['name']) . '" value="' . esc_attr($field_value) . '" placeholder="' . esc_attr($field['placeholder']) . '"></p>';
    }
}



// ---------------------------------------------------------------------------------
// ----- PUBLICATION TEMPLATE ------------------------------------------------------
// ---------------------------------------------------------------------------------
/**
 * Assign custom template to the mbcl_publication post type.
 * Allows a theme to override the plugin template.
 *
 * @param string $template The path to the template to load.
 * @return string The modified template path.
 */
function load_mbcl_publication_template( $template ) {
    global $post;

    // Check if we're displaying a 'mbcl_publication' post type
    if ( isset($post) && 'mbcl_publication' === $post->post_type ) {
        // Look for a template in the theme for the 'mbcl_publication' post type.
        // This checks both the child theme and the parent theme.
        $theme_template = locate_template( array( 'single-publication.php' ) );

        // If a theme template is found, use it.
        if ( '' !== $theme_template ) {
            return $theme_template;
        } else {
            // No theme template found, use the plugin template instead.
            return plugin_dir_path( __FILE__ ) . '../templates/single-publication.php';
        }
    }

    // Return the original template if not a 'mbcl_publication' post.
    return $template;
}
add_filter( 'single_template', 'load_mbcl_publication_template' );



// ---------------------------------------------------------------------------------
// ----- PUBLICATION ADMIN ---------------------------------------------------------
// ---------------------------------------------------------------------------------
// add columns
function filter_mbcl_publications_columns( $columns ) {

    $columns['mbcl_publication_count_available'] = __( 'Available' );
    $columns['mbcl_publication_count'] = __( 'Count' );
    $columns['mbcl_publication_barcode'] = __( 'Barcode' );
    $columns['mbcl_publication_link'] = __( 'Link' );
    unset($columns['date']);
    return $columns;
}
add_filter( 'manage_mbcl_publication_posts_columns', 'filter_mbcl_publications_columns' );

// add column data
function mbcl_publication_column( $column, $post_id ) {
    // available column
    if ( 'mbcl_publication_count_available' === $column ) {
        echo (int)get_post_meta( $post_id, 'mbcl_publication_count_available', true );
    }

    // count column
    if ( 'mbcl_publication_count' === $column ) {
        echo (int)get_post_meta( $post_id, 'mbcl_publication_count', true );
    }

    // barcode column
    if ( 'mbcl_publication_barcode' === $column ) {
        echo esc_html( get_post_meta( $post_id, 'mbcl_publication_barcode', true ) );
    }

    // link column
    if ( 'mbcl_publication_link' === $column ) {
        echo '<a class="button-primary" href="https://openlibrary.org'. esc_html( get_post_meta( $post_id, 'mbcl_publication_openlibrary_key', true ) ).'" target="_blank">More info</a>';
    }
}
add_action( 'manage_mbcl_publication_posts_custom_column', 'mbcl_publication_column', 10, 2);