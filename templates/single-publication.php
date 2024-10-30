<?php get_header(); ?>

<?php if (have_posts()): while (have_posts()) : the_post(); ?>

	<?php
		$total_copies = (int)get_post_meta( get_the_ID(), 'mbcl_publication_count', true );
		$copies_available = (int)get_post_meta( get_the_ID(), 'mbcl_publication_count_available', true );
		$publication_barcode = esc_html( get_post_meta( get_the_ID(), 'mbcl_publication_barcode', true) );
	?>

	<h1 class="post-title"><?php the_title(); ?></h1>

	<?php if( isset($_GET['success']) && $_GET['success'] == 'true' && isset($_GET['message']) ){ ?>
		<div class="publication-transaction-success-messages">
		<?php echo esc_html( $_GET['message'] ); ?>
		</div>
	<?php } ?>

	<div class="publication-single">

		<div class="publication-single-secondary">

			<?php
			$publication_cover_image_url = esc_url( get_post_meta( get_the_ID(), 'mbcl_publication_cover_image_url', true ) );
			if( get_the_post_thumbnail_url() ){ $publication_cover_image_url = get_the_post_thumbnail_url(); }
			if( $publication_cover_image_url ){ ?>
				<div class="publication-single-cover"><img src="<?php echo $publication_cover_image_url; ?>" alt="<?php echo get_the_title(); ?> book cover"></div>
			<?php } else { ?>
				<div class="publication-single-cover"><img src="<?php echo plugins_url( '../images/antique-blank-book-cover.jpg', __FILE__ ); ?>" alt="Missing cover for <?php echo get_the_title(); ?>"></div>
			<?php } ?>

		</div>

		<div class="publication-single-main">

			<p class="publication-availability"><span class="publication-availability-status <?php if( $copies_available == 0 ){ echo 'unavailable'; } ?>"><span class="publication-number-available"><?php echo $copies_available; ?></span>/<?php echo $total_copies; ?> available</span></p>

			<?php the_content(); ?>

			<p>By: <?php echo esc_html( get_post_meta( get_the_ID(), 'mbcl_publication_author_first_name', true ) ); ?> <?php echo esc_html( get_post_meta( get_the_ID(), 'mbcl_publication_author_last_name', true ) ); ?><br/>
			Barcode/ISBN: <?php echo $publication_barcode; ?></p>

			<p><a href="https://openlibrary.org<?php echo esc_html( get_post_meta( get_the_ID(), 'mbcl_publication_openlibrary_key', true ) ); ?>" class="button" target="_blank">Learn more</a></p>

		</div>

		<div class="publication-single-operations">

			<div class="publication-transaction-messages">
			</div>

			<div class="publication-transaction-form transaction-checkout" <?php if( $copies_available == 0 ){ echo 'style="display: none;"'; } ?>>
				<h3>Check out</h3>
				<form action="" method="POST" name="create-transaction">
					<input type="date" name="transaction-date" id="transaction-date" value="<?php echo date("Y-m-d"); ?>" /><br/>
					<input type="text" name="transaction-name" id="transaction-name" placeholder="Name"><br/>
					<input type="hidden" name="transaction-publication-id" id="transaction-publication-id" value="<?php echo get_the_ID(); ?>">
					<input type="hidden" name="transaction-publication-barcode" id="transaction-publication-barcode" value="<?php echo $publication_barcode; ?>">
					<?php if( $copies_available > 1 ){ ?>
						<select name="transaction-publication-copies" id="transaction-publication-copies">
							<option value="">Number of copies</option>
							<?php for ($i=1; $i <= $copies_available; $i++) {
								echo '<option value="'.$i.'">'.$i.'</option>';
							}?>
						</select>
					<?php } else { ?>
						<input type="hidden" name="transaction-publication-copies" id="transaction-publication-copies" value="1">
					<?php } ?>
					<input type="hidden" name="transaction_type" value="transaction_checkout">
					<input type="hidden" name="action" value="create_mbcl_transaction" style="display: none; visibility: hidden; opacity: 0;">
					<input type="submit" name="transaction-complete-checkout" id="transaction-complete-checkout" value="Check out">
				</form>
			</div>

			<div class="publication-transaction-form transaction-checkin" <?php if( $total_copies - $copies_available == 0 ){ echo 'style="display: none;"'; } ?>>
				<h3>Check in</h3>
				<form action="" method="POST" name="create-transaction">
					<input type="date" name="transaction-date" id="transaction-date" value="<?php echo date("Y-m-d"); ?>" /><br/>
					<input type="text" name="transaction-name" id="transaction-name" placeholder="Full name"><br/>
					<input type="hidden" name="transaction-publication-id" id="transaction-publication-id" value="<?php echo get_the_ID(); ?>">
					<input type="hidden" name="transaction-publication-barcode" id="transaction-publication-barcode" value="<?php echo $publication_barcode; ?>">
					<?php if( ($total_copies - $copies_available) > 1 ){ ?>
						<select name="transaction-publication-copies" id="transaction-publication-copies">
							<option value="">Number of copies</option>
							<?php for ($i=1; $i <= ($total_copies - $copies_available); $i++) {
								echo '<option value="'.$i.'">'.$i.'</option>';
							}?>
						</select>
					<?php } else { ?>
						<input type="hidden" name="transaction-publication-copies" id="transaction-publication-copies" value="1">
					<?php } ?>
					<input type="hidden" name="transaction_type" value="transaction_checkin">
					<input type="hidden" name="action" value="create_mbcl_transaction" style="display: none; visibility: hidden; opacity: 0;">
					<input type="submit" name="transaction-complete-checkout" id="transaction-complete-checkout" value="Check in">
				</form>
			</div>

		</div>

	</div>

	<?php if( is_user_logged_in() ){ ?>

		<hr style="margin: 2em 0;">

		<div class="publication-transaction-log">
			<h2>Transaction log</h2>

			<?php
			$transaction_args = [
				'post_type' => 'mbcl_transaction',
				'posts_per_page' => 50,
				'post_status' => 'publish',
				'orderby'		=> 'date',
				'order'			=> 'desc',
				'post_author'	=> get_post()->post_author,
				'meta_query' => array(
					array(
						'key' => 'mbcl_transaction_publication_id',
						'value' => get_the_ID(),
						'compare' => '='
					),
				)
			];

			// The Query
			$transaction_query = new WP_Query( $transaction_args ); ?>

			<?php if ( $transaction_query->have_posts() ) { ?>

				<table class="publication-transaction-log-list">

					<thead>
						<tr>
							<td>Borrower</td>
							<td>Checkout date</td>
							<td>Checkin date</td>
							<td>Copies</td>
						</tr>
					</thead>
					<tbody>

					<?php while ($transaction_query->have_posts() ) : $transaction_query->the_post(); ?>

					<?php
						$checkout_date = esc_html( get_post_meta( get_the_ID(), 'mbcl_transaction_checkout_date', true) );
						$checkin_date = esc_html( get_post_meta( get_the_ID(), 'mbcl_transaction_checkin_date', true) );
					?>

						<tr>
							<td><?php echo esc_html( get_post_meta( get_the_ID(), 'mbcl_transaction_user', true) ); ?></td>
							<td><?php echo ($checkout_date) ? '⬆️ '.date("F j, Y", strtotime( $checkout_date ) ) : ''; ?></td>
							<td><?php echo ($checkin_date) ? '⬇️ '.date("F j, Y", strtotime( $checkin_date ) ) : ''; ?></td>
							<td><?php echo (int)get_post_meta( get_the_ID(), 'mbcl_transaction_publication_copies', true ); ?></td>
						</tr>

						<?php wp_reset_postdata(); ?>

					<?php endwhile; ?>

					</tbody>

				</table>

			<?php } else { ?>

				<p>No transactions found for <em><?php echo get_the_title(); ?></em></p>

			<?php } ?>
		</div>

	<?php } ?>

<?php endwhile; ?>
<?php else: ?>

	<h1><?php _e( 'Sorry, no publications to display.', 'classroom_library' ); ?></h1>

<?php endif; ?>

<?php get_footer(); ?>
