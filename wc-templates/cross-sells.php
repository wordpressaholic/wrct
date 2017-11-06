<?php
/**
 * Cross-sells
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( $cross_sells ) : ?>

	<div class="cross-sells">

		<h2><?php _e( 'You may be interested in&hellip;', 'woocommerce' ) ?></h2>

		<?php woocommerce_product_loop_start(); ?>

		<?php
      /* wrct code begins */
      $wrct_shortcode = get_option( 'wrct-cross-sells-shortcode' );
      if( ! $wrct_shortcode ){
        global $wrct_settings_default;
        $wrct_shortcode = $wrct_settings_default[ 'wrct-cross-sells-shortcode' ];
      }
      echo do_shortcode( $wrct_shortcode );
      /* wrct code ends */
    ?>

		<?php woocommerce_product_loop_end(); ?>

	</div>

<?php endif;

wp_reset_postdata();
