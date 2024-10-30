<?php
/*
Plugin Name: Classroom Library
Plugin URI: https://mburnette.com/classroom-library/
Description: Classroom library directory with bookshelf, book search, barcode lookup, and check in/out.
Version: 0.1.4
Author: Marcus Burnette
Author URI: https://mburnette.com
License: GPL2
*/

//* Don't access this file directly
defined( 'ABSPATH' ) or die();


// ---------------------------------------------------------------------------------
// ----- LOAD ADMIN SCRIPTS --------------------------------------------------------
// ---------------------------------------------------------------------------------
function mbcl_load_admin_scripts() {

    wp_enqueue_script( 'mbcl_barcode_lookup', plugins_url( 'js/barcode-lookup.js', __FILE__ ), array('jquery'), time() );
    wp_enqueue_style( 'mbcl_styles', plugins_url( 'css/styles.css', __FILE__ ), null, time() );

}
add_action('admin_enqueue_scripts', 'mbcl_load_admin_scripts');


// ---------------------------------------------------------------------------------
// ----- LOAD FRONTEND SCRIPTS -----------------------------------------------------
// ---------------------------------------------------------------------------------
function mbcl_load_scripts() {

    wp_enqueue_script( 'mbcl_create_transaction', plugins_url( 'js/create-transaction.js', __FILE__ ), array('jquery'), time() );
    wp_enqueue_style( 'mbcl_styles', plugins_url( 'css/styles.css', __FILE__ ), null, time() );

}
add_action('wp_enqueue_scripts', 'mbcl_load_scripts');



// ---------------------------------------------------------------------------------
// ----- CREATE BOOKS POST TYPE ----------------------------------------------------
// ---------------------------------------------------------------------------------
include("inc/publication-cpt.php");


// ---------------------------------------------------------------------------------
// ----- CREATE TRANSACTIONS POST TYPE ---------------------------------------------
// ---------------------------------------------------------------------------------
include("inc/transaction-cpt.php");


// ---------------------------------------------------------------------------------
// ----- CREATE TRANSACTION AJAX ---------------------------------------------------
// ---------------------------------------------------------------------------------
include("inc/create-transaction.php");

// ---------------------------------------------------------------------------------
// ----- BOOKSHELF SHORTCODE -------------------------------------------------------
// ---------------------------------------------------------------------------------
include("inc/bookshelf.php");