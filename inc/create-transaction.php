<?php

// ---------------------------------------------------------------------------------
// ----- CREATE TRANSACTION AJAX FUNCTION ------------------------------------------
// ---------------------------------------------------------------------------------
add_action("wp_ajax_create_mbcl_transaction", "create_mbcl_transaction");
add_action("wp_ajax_nopriv_create_mbcl_transaction", "create_mbcl_transaction");

function create_mbcl_transaction(){

    if ( !wp_verify_nonce( $_POST['security'], "create_mbcl_transaction_nonce")) {
        exit("No naughty business please");
    }

    $publication_id = (int)$_POST['transaction-publication-id'];
	$number_copies = (int)$_POST['transaction-publication-copies'];
	$transaction_name = sanitize_text_field( $_POST['transaction-name'] );
	$transaction_publication_barcode = sanitize_text_field( $_POST['transaction-publication-barcode'] );
	$transaction_type = sanitize_text_field( $_POST['transaction_type'] );
	$transaction_date = sanitize_text_field( $_POST['transaction-date'] );

    $response = [];
	$response['errors'] = 0;
	$response['publication_count'] = (int)get_post_meta( $publication_id, 'mbcl_publication_count', true );
	$response['transaction_type'] = $transaction_type;

	// check for errors before processing (aka validate inputs)
    if( empty($transaction_date) ){
		$transaction_date = date("Y-m-d");
	}

	if( empty( $number_copies ) ){
		$number_copies = 1;
	}

	if( empty( $transaction_name ) ){
		$response['errors']++;
		$response['message'] = 'Who are you? Make sure you enter a name!';
	}

    if( empty( $publication_id ) ){
		$response['errors']++;
		$response['message'] = 'Hmm... Something happened and we weren\'t able to tell what publication you\'re checking out.';
	}

    if( $response['errors'] == 0 ){
		// create transaction
		$transaction_args = array(
			'post_title'    => date("U") . " - " . $transaction_name . " - " . get_the_title( $publication_id ),
			'post_status'   => 'publish',
			'post_author'   => get_post($publication_id)->post_author,
			'post_type'		=> 'mbcl_transaction',
			'meta_input'   => array(
				'mbcl_transaction_user' => $transaction_name,
				'mbcl_transaction_publication_id' => $publication_id,
				'mbcl_transaction_publication_barcode' => $transaction_publication_barcode,
				'mbcl_transaction_publication_copies' => $number_copies,
			),
		);

		// Insert the post into the database
		if( $transaction_id = wp_insert_post( $transaction_args ) ){

            // reduce publication availability and add date if checkout
			if( $transaction_type == "transaction_checkout" ){

				$number_available = get_post_meta( $publication_id, 'mbcl_publication_count_available', true );

				$number_available = $number_available - $number_copies;

				update_post_meta( $publication_id, 'mbcl_publication_count_available', $number_available );
				update_post_meta( $transaction_id, 'mbcl_transaction_checkout_date', $transaction_date );

				$response['number_available'] = $number_available;
				$response['message'] = 'Successfully checked out!</p>';
			}


			// increase publication availability and add date if checkin
			if( $transaction_type == "transaction_checkin" ){

				$number_available = get_post_meta( $publication_id, 'mbcl_publication_count_available', true );

				$number_available = $number_available + $number_copies;

				update_post_meta( $publication_id, 'mbcl_publication_count_available', $number_available );
				update_post_meta( $transaction_id, 'mbcl_transaction_checkin_date', $transaction_date );

				$response['number_available'] = $number_available;
				$response['message'] = 'Successfully checked in!</p>';
			}

		}

	}

    return wp_send_json_success($response);

}


// ---------------------------------------------------------------------------------
// ----- HELPERS -------------------------------------------------------------------
// ---------------------------------------------------------------------------------
function javascript_variables(){ ?>
    <script type="text/javascript">
        var ajax_url = '<?php echo admin_url( "admin-ajax.php" ); ?>';
        var ajax_nonce = '<?php echo wp_create_nonce( "create_mbcl_transaction_nonce" ); ?>';
    </script><?php
}
add_action ( 'wp_head', 'javascript_variables' );