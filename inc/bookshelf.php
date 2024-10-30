<?php

// ---------------------------------------------------------------------------------
// ----- BOOKSHELF SHORTCODE -------------------------------------------------------
// ---------------------------------------------------------------------------------
add_shortcode( 'bookshelf', 'mbcl_publication_bookshelf' );
function mbcl_publication_bookshelf( $atts ){

    extract( shortcode_atts( array(
		'cols' => '6',
        'search' => 'yes'
    ), $atts ) );


    ob_start();

    $search_value = (isset($_POST["seek"])) ? sanitize_text_field( $_POST["seek"] ) : null;

	$publication_args = [
		'post_type'         => 'mbcl_publication',
		'posts_per_page'    => 2000,
		'post_status'       => 'publish',
        'order'             => 'asc',
        'orderby'           => 'title',
        's'                 => $search_value
    ];

    if( isset($search_value) && is_numeric($search_value) && strlen($search_value) > 5 ){

        $publication_args['meta_query'] = array(
            array(
                'key' => 'mbcl_publication_barcode',
                'value' => $search_value,
                'compare' => '='
            ),
        );
        unset($publication_args['s']);
    }

	// The Query
	$publication_query = new WP_Query( $publication_args ); ?>

    <?php
    if( isset($search_value) && count($publication_query->posts) == 1 ){
        $url = get_permalink($publication_query->posts[0]->ID);
        echo '<script>window.location.href = "'.$url.'"</script>';
    }
    ?>

    <?php if( strtolower($search) != 'no' ){ ?>
    <div class="publication-search">
        <form action="" method="POST">
            <input type="text" name="seek" id="seek" value="<?php if( !empty($search_value) ){ echo $search_value; } ?>" /> <input type="submit" value="Search">
        </form>
        <?php if( !empty($search_value) ){ ?><a href="<?php echo get_the_permalink(); ?>" class="publication-search-reset">Reset</a><?php } ?>
    </div>
    <?php } ?>

    <div class="publication-bookshelf">

        <?php if ( $publication_query->have_posts() ) : while ($publication_query->have_posts() ) : $publication_query->the_post(); ?>

            <div class="bookshelf-book" data-barcode="<?php echo esc_attr( get_post_meta( get_the_ID(), 'mbcl_publication_barcode', true ) ); ?>" data-available="<?php echo esc_attr( get_post_meta( get_the_ID(), 'mbcl_publication_count_available', true ) ); ?>" style="width: calc(100% / <?php echo $cols; ?>);">

                <?php
                $publication_cover_image_url = esc_url( get_post_meta( get_the_ID(), 'mbcl_publication_cover_image_url', true ) );
                if( get_the_post_thumbnail_url() ){ $publication_cover_image_url = get_the_post_thumbnail_url(); }
                if( $publication_cover_image_url ){ ?>
                    <p class="bookshelf-book-cover"><a href="<?php echo get_the_permalink(); ?>"><img src="<?php echo $publication_cover_image_url; ?>" alt="<?php echo get_the_title(); ?> book cover" title="<?php echo get_the_title(); ?>"></a></p>
                <?php } else { ?>
                    <p class="bookshelf-book-cover"><a href="<?php echo get_the_permalink(); ?>"><img src="<?php echo plugins_url( '../images/antique-blank-book-cover.jpg', __FILE__ ); ?>" alt="Missing cover for <?php echo get_the_title(); ?>" title="<?php echo get_the_title(); ?>"></a></p>
                <?php } ?>

            </div>

            <?php wp_reset_postdata(); ?>

        <?php endwhile; else : ?>

            <?php if( !empty($search_value) ){ ?>

                <p>No publications found with <code><?php echo $search_value; ?></code>. Please try another search.</p>

            <?php } else { ?>

                <p>No publications found.</p>

            <?php } ?>

        <?php endif; ?>

    </div>

    <?php
    return ob_get_clean();
}