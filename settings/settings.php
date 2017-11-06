<?php
  if ( ! defined( 'ABSPATH' ) ) {
  	exit; // Exit if accessed directly
  }
?>

<div class="wrct-editor-clear"></div>
<h1 class="wrct-page-title dashicons-before dashicons-editor-justify">
  <?php _e( "WooCommerce Course Tables Settings", "wrct" ); ?>
</h1>

<div class="wrct-editor-clear"></div>

<?php
  global $wrct_settings_default;

  // save settings
  $nonce_action = "wrct-settings";
  if( isset( $_POST['wrct-settings-nonce'] ) && wp_verify_nonce( $_POST['wrct-settings-nonce'], $nonce_action ) ){

    foreach( $wrct_settings_default as $option=> $val ){
      if( isset( $_POST[$option] ) ){
        $val = sanitize_text_field( stripslashes( $_POST[$option] ) );
      }
      update_option( $option, $val );
      // delete_option( $option );
    }

    // save notice
    echo '<div id="message" class="wrct-settings-notice updated notice notice-success"><p><i class="fa fa-check"></i>'. __( "Settings saved.", 'wrct' ) .'</p></div>';
  }
?>

<form class="wrct-settings-form" method="POST">
  <div class="wrct-settings-heading">
    <?php _e( "Replace templates", 'wrct' ); ?>
  </div>
  <?php $doc_link = plugins_url('wrct/documentation.php'); ?>
  <a class="wrct-settings-doc-link" href="<?php echo $doc_link ?>#replace-woocommerce-templates" target="_blank"><?php _e( "Read about it in docs", 'wrct' ); ?></a>
  <!-- related products -->
  <div class="wrct-option-row">
    <div class="wrct-option-label">
      <?php _e( "Replace WC 'related products' section?", 'wrct' ); ?>
    </div>
    <div class="wrct-option-value">
      <input class="wrct-input-toggle" type="checkbox" name="wrct-replace-related-products" value="1" <?php wrct_settings_val_checked("wrct-replace-related-products") ?>>
    </div>
  </div>

  <div class="wrct-option-row">
    <div class="wrct-option-label">
      <?php _e( "Replace WC 'related products' with shortcode", 'wrct' ); ?>:
    </div>
    <div class="wrct-option-value">
      <textarea class="wrct-input-shortcode" type="text" name="wrct-related-products-shortcode" placeholder="<?php echo $wrct_settings_default['wrct-related-products-shortcode'] ?>"><?php echo wrct_get_settings_val( "wrct-related-products-shortcode" ) ?></textarea>
    </div>
  </div>

  <!-- cross-sells -->
  <div class="wrct-option-row">
    <div class="wrct-option-label">
      <?php _e( "Replace WC 'cross-sells' section?", 'wrct' ); ?>
    </div>
    <div class="wrct-option-value">
      <input class="wrct-input-toggle" type="checkbox" name="wrct-replace-cross-sells" value="1" <?php wrct_settings_val_checked("wrct-replace-cross-sells") ?>>
    </div>
  </div>

  <div class="wrct-option-row">
    <div class="wrct-option-label">
      <?php _e( "Replace WC 'cross-sells' with shortcode", 'wrct' ); ?>:
    </div>
    <div class="wrct-option-value">
      <textarea class="wrct-input-shortcode" type="text" name="wrct-cross-sells-shortcode" placeholder="<?php echo $wrct_settings_default['wrct-cross-sells-shortcode'] ?>"><?php echo wrct_get_settings_val( "wrct-cross-sells-shortcode" ) ?></textarea>
    </div>
  </div>

  <!-- up-sells -->
  <div class="wrct-option-row">
    <div class="wrct-option-label">
      <?php _e( "Replace WC 'up-sells' section?", 'wrct' ); ?>
    </div>
    <div class="wrct-option-value">
      <input class="wrct-input-toggle" type="checkbox" name="wrct-replace-up-sells" value="1" <?php wrct_settings_val_checked("wrct-replace-up-sells") ?>>
    </div>
  </div>

  <div class="wrct-option-row">
    <div class="wrct-option-label">
      <?php _e( "Replace WC 'up-sells' with shortcode", 'wrct' ); ?>:
    </div>
    <div class="wrct-option-value">
      <textarea class="wrct-input-shortcode" type="text" name="wrct-up-sells-shortcode" placeholder="<?php echo $wrct_settings_default['wrct-up-sells-shortcode'] ?>"><?php echo wrct_get_settings_val( "wrct-up-sells-shortcode" ) ?></textarea>
    </div>
  </div>

  <div class="wrct-hidden-settings">
    <input type="hidden" name="wrct-settings-nonce" value="<?php echo wp_create_nonce( $nonce_action ); ?>">
  </div>

  <button type="submit" class="wrct-save button button-primary button-large"><?php _e( "Save settings", "wrct" ); ?></button>
</form>
