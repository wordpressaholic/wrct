<?php
/**
 * Related Products
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( $related_products ) : ?>

	<section class="related products">

		<h2><?php esc_html_e( 'Related products', 'woocommerce' ); ?></h2>

		<?php woocommerce_product_loop_start(); ?>

		<?php
      /* wrct code begins */
      $wrct_shortcode = get_option( 'wrct-related-products-shortcode' );
      if( ! $wrct_shortcode ){
        global $wrct_settings_default;
        $wrct_shortcode = $wrct_settings_default[ 'wrct-related-products-shortcode' ];
      }
      echo do_shortcode( $wrct_shortcode );
      /* wrct code ends */
    ?>

		<?php woocommerce_product_loop_end(); ?>

	</section>

<?php endif;

wp_reset_postdata();
