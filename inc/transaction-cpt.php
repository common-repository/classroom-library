<?php

// ---------------------------------------------------------------------------------
// ----- CREATE TRANSACTION POST TYPE ----------------------------------------------
// ---------------------------------------------------------------------------------
function mbcl_create_transaction_post_type() {

	/**
	 * Post Type: Transactions.
	 */

	$labels = [
		"name" => __( "Transactions", " classroom_library" ),
		"singular_name" => __( "Transaction", " classroom_library" ),
	];

	$args = [
		"label" => __( "Transactions", " classroom_library" ),
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
		"rewrite" => [ "slug" => "transaction", "with_front" => true ],
		"query_var" => true,
		"menu_icon" => "dashicons-tickets-alt",
		"supports" => [ "title", "editor", "thumbnail", "revisions", "custom-fields", "revisions" ],
		"show_in_graphql" => false,
	];

	register_post_type( "mbcl_transaction", $args );
}

add_action( 'init', 'mbcl_create_transaction_post_type' );



// add book details box to books post type
function mbcl_add_transaction_meta_boxes() {
    add_meta_box(
        "post_metadata_mbcl_transaction_post", // div id containing rendered fields
        "Transaction details", // section heading displayed as text
        "post_meta_box_mbcl_transaction_post", // callback function to render fields
        "mbcl_transaction", // name of post type on which to render fields
        "side", // location on the screen
        "high" // placement priority
    );
}
add_action( "admin_init", "mbcl_add_transaction_meta_boxes" );



function mbcl_transaction_details_fields(){
    return [
        [
            "name"          => "mbcl_transaction_checkout_date",
            "label"         => "Check-out date",
            "placeholder"   => "Date",
            "field_type"    => "date"
        ],
        [
            "name"          => "mbcl_transaction_user",
            "label"         => "Full name",
            "placeholder"   => "Full name"
        ],
        [
            "name"          => "mbcl_transaction_checkin_date",
            "label"         => "Check-in date",
            "placeholder"   => "Date",
            "field_type"    => "date"
        ],
        [
            "name"          => "mbcl_transaction_publication_id",
            "label"         => "Publication ID",
            "placeholder"   => ""
        ],
        [
            "name"          => "mbcl_transaction_publication_barcode",
            "label"         => "Publication barcode",
            "placeholder"   => ""
        ],
        [
            "name"          => "mbcl_transaction_publication_copies",
            "label"         => "Publication copies",
            "placeholder"   => ""
        ],
    ];
}

// save field value
function mbcl_save_transaction_meta_boxes(){
    global $post;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    foreach( mbcl_transaction_details_fields() as $field ){

        if( isset($post->ID) && isset($field['name']) ){
            update_post_meta( $post->ID, $field['name'], sanitize_text_field( $_POST[ $field['name'] ] ) );
        }
    }
}
add_action( 'save_post', 'mbcl_save_transaction_meta_boxes' );

// callback function to render fields
function post_meta_box_mbcl_transaction_post(){

    global $post;
    $custom = get_post_custom( $post->ID );

    foreach( mbcl_transaction_details_fields() as $field ){

        if( empty($field['field_type']) ) { $field_type = "text"; } else { $field_type = $field['field_type']; }

        $field_value = $custom[ $field['name'] ][0];
        if( empty($field_value) && $field['field_type'] == "number" ){ $field_value = 1; }

        echo '<p><label>' . esc_html($field['label']) . '</label><br/><input type="' . esc_attr($field_type) . '" id="' . esc_attr($field['name']) . '" name="' . esc_attr($field['name']) . '" value="' . esc_attr($field_value) . '" placeholder="' . esc_attr($field['placeholder']) . '"></p>';
    }
}



// ---------------------------------------------------------------------------------
// ----- TRANSACTION ADMIN ---------------------------------------------------------
// ---------------------------------------------------------------------------------
// add columns
function filter_mbcl_transactions_columns( $columns ) {

    $columns['mbcl_transaction_user'] = __( 'Borrower' );
    $columns['mbcl_transaction_checkout_date'] = __( 'Check out date' );
    $columns['mbcl_transaction_checkin_date'] = __( 'Check in date' );
    $columns['mbcl_transaction_publication'] = __( 'Publication' );
    $columns['mbcl_transaction_publication_copies'] = __( 'Copies' );
    unset($columns['date']);
    return $columns;
}
add_filter( 'manage_mbcl_transaction_posts_columns', 'filter_mbcl_transactions_columns' );


function mbcl_transaction_column( $column, $post_id ) {
    // checkout date column
    if ( 'mbcl_transaction_checkout_date' === $column ) {
        $checkout_date = esc_attr( get_post_meta( $post_id, 'mbcl_transaction_checkout_date', true ) );
        if( !empty( $checkout_date ) ){
            echo date("F j, Y", strtotime( $checkout_date ) );
        }
    }

    // borrower column
    if ( 'mbcl_transaction_user' === $column ) {
        echo esc_html( get_post_meta( $post_id, 'mbcl_transaction_user', true ) );
    }

    // checkin date column
    if ( 'mbcl_transaction_checkin_date' === $column ) {
        $checkin_date = esc_attr( get_post_meta( $post_id, 'mbcl_transaction_checkin_date', true ) );
        if( !empty( $checkin_date ) ){
            echo date("F j, Y", strtotime( $checkin_date ) );
        }
    }

    // publication column
    if ( 'mbcl_transaction_publication' === $column ) {
        $publication_id = (int)get_post_meta( $post_id, 'mbcl_transaction_publication_id', true );
        echo '<a href="'.get_the_permalink( $publication_id ).'">'.get_the_title( $publication_id ).'</a>';
    }

    // publication copies column
    if ( 'mbcl_transaction_publication_copies' === $column ) {
        echo (int)get_post_meta( $post_id, 'mbcl_transaction_publication_copies', true );
    }

}
add_action( 'manage_mbcl_transaction_posts_custom_column', 'mbcl_transaction_column', 10, 2);