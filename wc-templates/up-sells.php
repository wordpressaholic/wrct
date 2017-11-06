<?php
/**
 * Single Product Up-Sells
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( $upsells ) : ?>

	<section class="up-sells upsells products">

		<h2><?php esc_html_e( 'You may also like&hellip;', 'woocommerce' ) ?></h2>

		<?php woocommerce_product_loop_start(); ?>

    <?php
      /* wrct code begins */
      $wrct_shortcode = get_option( 'wrct-up-sells-shortcode' );
      if( ! $wrct_shortcode ){
        global $wrct_settings_default;
        $wrct_shortcode = $wrct_settings_default[ 'wrct-up-sells-shortcode' ];
      }
      echo do_shortcode( $wrct_shortcode );
      /* wrct code ends */
    ?>

		<?php woocommerce_product_loop_end(); ?>

	</section>

<?php endif;

wp_reset_postdata();
