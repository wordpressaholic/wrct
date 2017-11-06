<?php
/*
 * Plugin Name: WooCommerce Responsive Course Tables
 * Plugin URI: http://woocommercecoursetables.com
 * Description: Makes it simple to display your WooCommerce catalog of courses in beautiful responsive tables (with variation options).
 * Author: Kartik Gahlaut
 * Author URI: http://kartik.webfixfast.com
 * Version: 1.0.0
 *
 * Text Domain: wrct
 * Domain Path: /languages/
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
 exit; // Exit if accessed directly
}

/* record plugin version, run db updates if necessary */
add_action( 'admin_init', 'wrct_plugin_version_update' );
function wrct_plugin_version_update(){
  update_option("wrct_version", "1.0.0");
}

/* load plugin textdomain. */
add_action( 'plugins_loaded', 'wrct_load_textdomain' );
function wrct_load_textdomain() {
  load_plugin_textdomain( 'wrct', false, basename( dirname( __FILE__ ) ) . '/languages' );
}

/* settings page */
add_action('admin_menu', 'wrct_register_settings_page', 100);
function wrct_register_settings_page( ) {
  $parent_slug = "edit.php?post_type=wrct";
  $page_title = __( 'WC Course Tables Settings', 'wrct' );
  $menu_title = __( 'Settings', 'wrct' );
  $capability = "publish_wc_course_tables";
  $menu_slug = "wrct-settings";
  $function = "wrct_settings_page";

  add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
}

/*print the settings page*/
$wrct_settings_default = array(
  // related products
  "wrct-replace-related-products" => 0,
  "wrct-related-products-shortcode" => "[wrct related-products='true' max-posts='5']",
  // cross sells
  "wrct-replace-cross-sells" => 0,
  "wrct-cross-sells-shortcode" => "[wrct cross-sells='true' max-posts='5' columns='name:Course, price: Cost']",
  // up sells
  "wrct-replace-up-sells" => 0,
  "wrct-up-sells-shortcode" => "[wrct up-sells='true' max-posts='5']",
);

function wrct_settings_page( ){
  include( 'settings/settings.php' );
}

function wrct_get_settings_val( $key ){
  if( get_option( $key ) !== false ){
    $val = sanitize_text_field( get_option( $key ) );
  }else{
    global $wrct_settings_default;
    $val = $wrct_settings_default[ $key ];
  }
  return $val;
}

function wrct_settings_val_checked( $key ){
  $val = wrct_get_settings_val( $key );
  if( $val ){
    echo esc_html('checked="checked"');
  }
}

/* replace WC templates */
add_filter( "wc_get_template", "wrct_replace_wc_template", 10, 5 );
function wrct_replace_wc_template( $located, $template_name, $args, $template_path, $default_path ){
  $template_dir = plugin_dir_path( __FILE__ ) .'wc-templates/';

  // related
  if( $template_name === "single-product/related.php" && get_option( 'wrct-replace-related-products' ) ){
    $located = $template_dir . 'related.php';
  }

  // cross-sells
  if( $template_name === "cart/cross-sells.php" && get_option( 'wrct-replace-cross-sells' ) ){
    $located = $template_dir . 'cross-sells.php';
  }

  // up-sells
  if( $template_name === "single-product/up-sells.php" && get_option( 'wrct-replace-up-sells' ) ){
    $located = $template_dir . 'up-sells.php';
  }

  return $located;
}

/* register wrct cpt */
add_action( 'init', 'wrct_register_posttype' );
function wrct_register_posttype() {
  register_post_type( 'wrct',
    array(
      'labels' => array(
        'name' => __( 'WC Course Tables', 'wrct' ),
        'singular_name' => __( 'WC Course Table', 'wrct' ),
        'menu_name' => __( 'WC Course Tbls', 'wrct' ),
        'add_new' => __( 'Add New Table', 'wrct' ),
      ),
      'description' => __( 'Easily display your WooCommerce products (courses) and variations in responsive tables.', 'wrct' ),
      'public' => true,
      'has_archive' => true,
      'menu_icon' => 'dashicons-editor-justify',
      'rewrite' => array('slug' => 'course-table'),
      'capability_type' => 'post',
      'capabilities' => array(
          'edit_post' => 'edit_wc_course_table',
          'edit_posts' => 'edit_wc_course_tables',
          'edit_others_posts' => 'edit_others_wc_course_tables',
          'publish_posts' => 'publish_wc_course_tables',
          'read_post' => 'read_wc_course_table',
          'read_private_posts' => 'read_private_wc_course_tables',
          'delete_post' => 'delete_wc_course_table',
      ),
      'map_meta_cap' => true,
      'supports'=> array(),
      'hierarchical' => false,
      'show_in_nav_menus' => true,
      'publicly_queryable' => false,
      'exclude_from_search' => true,
      'can_export' => true,
    )
  );
}

/* flush rewrites upon activation */
register_activation_hook( __FILE__, 'wrct_activate' );
function wrct_activate() {
  wrct_register_posttype();
  flush_rewrite_rules();
}

/* redirect to table editor */
add_action('plugins_loaded', 'wrct_redirect_to_table_editor');
function wrct_redirect_to_table_editor( ) {
  global $pagenow;

  // edit
  if($pagenow == 'post.php' && isset($_GET['post']) && isset($_GET['action']) && $_GET['action'] == 'edit'){
    $post_id = (int) $_GET['post'];
    $post = get_post_type( $post_id );
    if($post === 'wrct'){
      wp_redirect( admin_url( '/edit.php?post_type=wrct&page=wrct-edit&post_id=' . $post_id ) );
      exit;
    }
  }

  // add
  if($pagenow == 'post-new.php' && isset( $_GET['post_type'] ) && $_GET['post_type'] == 'wrct'){
    wp_redirect(admin_url('/edit.php?post_type=wrct&page=wrct-edit'));
    exit;
  }

}

/* plugin's edit page */
define('WRCT_CAP', 'activate_plugins');
add_action('admin_menu', 'wrct_hook_edit_page');
function wrct_hook_edit_page(){
  add_submenu_page( 'edit.php?post_type=wrct', 'WC Course Tables', 'Add New Table', WRCT_CAP, 'wrct-edit', 'wrct_editor_page' );
  if( class_exists( 'WooCommerce' ) ) { // check if WC is installed
    add_action( 'admin_enqueue_scripts', 'wrct_enqueue_admin_scripts' );
  }
}

/* highlight the WC Course Tables menu item when editing an existing wrct table post */
add_action('admin_menu', 'wrct_correct_menu_highlight');
function wrct_correct_menu_highlight(){
  if(
    isset( $_GET['post_type'] ) &&
    $_GET['post_type'] === 'wrct' &&
    isset( $_GET['page'] ) &&
    $_GET['page'] === 'wrct-edit' &&
    ! empty( $_GET['post_id'] )
  ){
    global $submenu_file;
    $submenu_file = "edit.php?post_type=wrct";
  }
}

/* create plugin page */
function wrct_editor_page(){
  if( ! class_exists( 'WooCommerce' ) ) return;

  // editing an old table
  if( ! empty( $_GET['post_id'] )){
    $post_id = (int) $_GET['post_id'];
  } else {
    if( ! isset( $post_id ) ){
      $post_id = wp_insert_post( array( 'post_type'=> 'wrct' ) );
    }
  }

  // use saved $data
  $saved = get_post_meta( $post_id, 'wrct', true );
  if( $saved ){
    $data = json_decode($saved, true);
  }else{
    // case where new wrct post is being created
    $data = false;
  }

  // query default
  if( ! isset( $data["query"] ) ){
    $data["query"] = array(
      "categories" => array(),
      "max_posts" => 50,
    );
  }

  // select the first category if none selected
  if( ! isset( $data["query"]["categories"] ) || ! is_array( $data["query"]["categories"] ) || ! count( $data["query"]["categories"] ) ){
    $data["query"]["categories"] = array( );
    $cat_args = array(
        'orderby'    => 'name',
        'order'      => 'asc',
        'hide_empty' => false,
    );
    $product_categories = get_terms( 'product_cat', $cat_args );
    if( count( $product_categories ) ){
      $data["query"]["categories"][] = $product_categories[0]->name;
    }
  }

  require(plugin_dir_path( __FILE__ ) . 'class-wrct-get-wc-data.php');

  $instance = new WRCT_Get_WC_Data($data);
  $data =& $instance->data;

  $data["courses"] = array(); // no need

  // present $data to JSON to editor
  wrct_esc_attr( $data );
  ?>
  <script>
    var wrct = {
        model: {},
        view: {},
        controller: {},
        data: <?php echo json_encode($data) ?>,
      };
  </script>
  <?php
  // editor template
  require(plugin_dir_path( __FILE__ ) . 'editor/editor.php');
}

/* esc data fields */
function wrct_esc_attr( &$info ){
  foreach( $info as $key=> &$val ){
    if( is_string( $val ) && ! in_array( $key, array( "heading", "css" ) ) ){
      $val = esc_attr( $val );
    }else if( is_array( $val ) ){
      wrct_esc_attr( $val );
    }
  }
}


/* save table data */
add_action( 'wp_ajax_wrct_save_editor_data', 'wrct_save_editor_data' );
function wrct_save_editor_data() {
  if(
    ! empty($_POST['wrct_data'] ) &&
    ! empty( $_POST['wrct_post_id'] ) &&
    wp_verify_nonce( $_POST['wrct_nonce'], 'wrct' ) &&
    current_user_can( 'edit_wc_course_table' , (int) $_POST['wrct_post_id'] )
  ){
    $post_id = (int) $_POST['wrct_post_id'];
    update_post_meta( $post_id, 'wrct', $_POST['wrct_data'] );
    $my_post = array(
        'ID'=> $post_id,
        'post_title'=> (string) $_POST['wrct_title'],
        'post_status'=> 'publish',
    );
    wp_update_post($my_post);
    echo "success: table data saved.";
  }
  wp_die();
}

/* ensure woocommerce is installed */
function wrct_woocommerce_not_installed_warning() {

  if(class_exists( 'WooCommerce' )) return;

  ?>
  <div class="notice notice-error wrct-needs-woocommerce">
      <p>
        <?php _e( 'WooCommerce Responsive Course Tables needs the WooCommerce plugin to be installed and activated on your site!', 'wrct' ); ?>
        <a href="<?php echo get_admin_url( false, "/plugin-install.php?s=woocommerce&tab=search&type=term" ); ?>" target="_blank"><?php _e( 'Install now?', 'wrct' ) ?></a>
      </p>
  </div>
  <style media="screen">
    .wp-admin.post-type-wrct #posts-filter,
    .wp-admin.post-type-wrct .subsubsub,
    #menu-posts-wrct .wp-submenu,
    #menu-posts-wrct:after {
      display: none;
    }

    .wp-admin.post-type-wrct .wrct-needs-woocommerce {
      margin-top: 10px;
    }

    .wp-admin.post-type-wrct .wrct-needs-woocommerce p {
      font-size: 18px;
    }

    .plugin-card-woocommerce {
      border: 4px solid #03A9F4;
      animation: wrct-pulse 1s infinite;
    }

    .plugin-card-woocommerce:hover {
      animation: none;
    }

    @-webkit-keyframes wrct-pulse {
      0% {
        -webkit-box-shadow: 0 0 0 0 rgba(3,169,244, 1);
      }
      70% {
          -webkit-box-shadow: 0 0 0 15px rgba(3,169,244, 0);
      }
      100% {
          -webkit-box-shadow: 0 0 0 0 rgba(3,169,244, 0);
      }
    }
    @keyframes wrct-pulse {
      0% {
        -moz-box-shadow: 0 0 0 0 rgba(3,169,244, 1);
        box-shadow: 0 0 0 0 rgba(3,169,244, 1);
      }
      70% {
          -moz-box-shadow: 0 0 0 15px rgba(3,169,244, 0);
          box-shadow: 0 0 0 15px rgba(3,169,244, 0);
      }
      100% {
          -moz-box-shadow: 0 0 0 0 rgba(3,169,244, 0);
          box-shadow: 0 0 0 0 rgba(3,169,244, 0);
      }
    }
  </style>
  <?php
}
add_action( 'admin_notices', 'wrct_woocommerce_not_installed_warning' );

/* back end scripts */
add_action( 'admin_enqueue_scripts', 'wrct_enqueue_admin_scripts' );
function wrct_enqueue_admin_scripts (){
  if( ! isset($_GET['page']) || ! in_array( $_GET['page'], array( 'wrct-edit', 'wrct-settings' ) ) ) return;

  // font awesome
  wp_enqueue_style( 'fontawesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css' );

  // editor files
  if( $_GET['page'] === "wrct-edit" ){
    // css
    //-- editor
    wp_enqueue_style( 'wrct-editor',  plugin_dir_url( __FILE__ ) . 'editor/assets/css/editor.css' );
    //-- spectrum
    wp_enqueue_style( 'spectrum',  plugin_dir_url( __FILE__ ) . 'editor/assets/css/spectrum.min.css' );

    // js
    //-- editor
    wp_enqueue_script( 'wrct-model',  plugin_dir_url( __FILE__ ) . 'editor/assets/js/model.js', array('jquery'), null, true );
    wp_enqueue_script( 'wrct-view',  plugin_dir_url( __FILE__ ) . 'editor/assets/js/view.js', array('jquery', 'wrct-model'), null, true );
    wp_enqueue_script( 'wrct-controller',  plugin_dir_url( __FILE__ ) . 'editor/assets/js/controller.js', array('jquery', 'wrct-model', 'wrct-view'), null, true );
    wp_enqueue_script( 'wrct-editor',  plugin_dir_url( __FILE__ ) . 'editor/assets/js/editor.js', array('jquery', 'wrct-model', 'wrct-view', 'wrct-controller'), null, true );
    //-- spectrum
    wp_enqueue_script( 'spectrum',  plugin_dir_url( __FILE__ ) . 'editor/assets/js/spectrum.min.js', array('jquery'), null, true );

  }

  // settings files
  if( $_GET['page'] === "wrct-settings" ){
    // css
    wp_enqueue_style( 'wrct-settings',  plugin_dir_url( __FILE__ ) . 'settings/settings.css' );
    // js
    wp_enqueue_script( 'wrct-settings',  plugin_dir_url( __FILE__ ) . 'settings/settings.js', array('jquery') );
  }

  // jquery ui
  wp_enqueue_script( 'jquery-ui-sortable', 'jquery', null, true );

  // iris color picker
  wp_enqueue_style( 'wp-color-picker' );
  wp_enqueue_script( 'wp-color-picker' );
}

add_action( 'admin_print_scripts', 'wrct_admin_print_scripts' );
function wrct_admin_print_scripts(){
  ?>
  <style media="screen">
    #menu-posts-wrct .wp-submenu li:nth-child(3){
      display: none;
    }
  </style>
  <?php
}

/* front end scripts */
add_action('wp_enqueue_scripts', 'wrct_enqueue_scripts');
function wrct_enqueue_scripts (){
  // scripts
  wp_enqueue_script( 'wrct',  plugin_dir_url( __FILE__ ) . 'wrct.js', 'jquery', 1, true );
  wp_localize_script( 'wrct', 'wrct_i18n', array(
    'ajax_url' => admin_url( 'admin-ajax.php' ),
    'i18n_no_matching_variations_text' => esc_attr__( 'Sorry, no products matched your selection. Please choose a different combination.', 'woocommerce' ),
    'i18n_make_a_selection_text'       => esc_attr__( 'Please select some product options before adding this product to your cart.', 'woocommerce' ),
    'i18n_unavailable_text'            => esc_attr__( 'Sorry, this product is unavailable. Please choose a different combination.', 'woocommerce' ),
  ) );

  // styles
  wp_enqueue_style( 'fontawesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css' );
  wp_enqueue_style( 'wrct',  plugin_dir_url( __FILE__ ) . 'wrct.css' );
}


/* wrct ajax shortcode */
add_action( 'wp_ajax_wrct_ajax', 'wrct_ajax' );
add_action( 'wp_ajax_nopriv_wrct_ajax', 'wrct_ajax' );
function wrct_ajax( ){
  $atts = get_transient( "wrct-ajax-" . sanitize_key( $_POST['wrct-ajax-key'] ) );
  if( ! $atts ){
    wp_send_json( "error: transient expired" );
  }
  wp_send_json( wrct_shortcode( $atts ) );
}

/* wrct shortcode */
add_shortcode( 'wrct', 'wrct_shortcode' );
$wrct_temp_id = 1;
$wrct_shortcode_atts = array( );
function wrct_shortcode( $atts ){

  global $wrct_shortcode_atts;
  $wrct_shortcode_atts = $atts;

  // icon
  if(isset($atts['icon'])){
    return '<i data-wrct-icon-name="'. $atts['icon'] .'" class="fa fa-'. strtolower( $atts['icon'] ) .'"></i>';
  }

  // image
  if(isset($atts['image'])){
    return '<img src="'. strtolower( $atts['image'] ) .'"/>';
  }

  // AJAX transient
  ksort( $atts );
  //-- create transient key using attributes, so this shortcode produces the same unique key on each instance
  $transient_key = "";
  if ( is_array( $atts ) && ! empty( $atts ) ) {
    foreach ( $atts as $key => $value ) {
      $transient_key .= trim( $key ) . trim( $value );
    }
    $transient_key = md5( $transient_key );
  }
  //-- update atts in the transient whenever this table is printed
  if( ! wp_doing_ajax( ) ){
    set_transient( "wrct-ajax-". $transient_key, $atts, 12 * HOUR_IN_SECONDS );
  }

  // id
  if(isset($atts['id'])){

    $id = (int) $atts['id'];

    $post = get_post($id);

    if( ! $post || $post->post_status !== 'publish' ){
      return;
    }

    $json_data = get_post_meta($atts['id'], 'wrct', true);

    if(! $json_data){
    // no data for the id
      return '<div class="warning wrct-warning">'. __( 'Sorry, there is no WooCommerce table related to this id. Perhaps you did not hit the \'Save Settings\' button below the table editor?', 'wrct' ) .'</div>';
    }

  }else{
    global $wrct_temp_id;
    $id = $wrct_temp_id++;
    $json_data = false;

  }

  $data = json_decode( $json_data, true );

  if( ! $data || ! is_array( $data ) ){
    $data = array();
  }

  if( ! isset( $data['courses'] ) ){
    $data['courses'] = array();
  }

  if( ! isset( $data['columns'] ) ){
    $data['columns'] = array();
  }

  if( ! isset( $data['styling'] ) ){
    $data['styling'] = array();
  }

  if( ! isset( $data['query'] ) ){
    $data['query'] = array();
  }

  // theme
  if(! empty($atts['theme'])){
    $atts['theme'] = strtolower($atts['theme']);
    $themes = array(
      'orange'=> '{"css":"","header-bg-color":"#f16237","header-text-color":"#ffffff","odd-rows-bg-color":"#f7f7f7","even-rows-bg-color":"#ffffff","link-button-bg":"#ffffff","cart-checkout-button-text-color":"#ffffff","cart-checkout-button-bg":"#81ca00","link-button-text-color":"#000000","header-border-bottom-color":"#ce5f2f","rows-bg-color-responsive-odd":"#ffffff","rows-bg-color-odd":"#f9f9f9","rows-bg-color-even":"#ffffff","rows-bg-color-responsive-even":"#f9f9f9","header-bg-color-responsive-odd":"#f16237","header-bg-color-responsive-even":"#f76c42","price-alternate-text-color":"#bf5130","buttons-alternate-text-color":"#bf5130"}',
      'black'=> '{"css":"","header-bg-color":"#565656","header-text-color":"#ffffff","odd-rows-bg-color":"#f7f7f7","even-rows-bg-color":"#ffffff","link-button-bg":"#ffffff","cart-checkout-button-bg":"#4c4c4c","cart-checkout-button-text-color":"#e5e5e5","link-button-text-color":"#0a0a0a","header-text-color-responsive":"#ffffff","border-color":"","rows-bg-color-responsive-odd":"#f7f7f7","rows-bg-color-responsive-even":"#ffffff","header-bg-color-responsive-odd":"#595959","header-bg-color-responsive-even":"#7c7c7c","header-border-bottom-width":"2"}',
      'blank'=> '{"css":"","header-bg-color":"#f7f7f7","header-text-color":"#000000","odd-rows-bg-color":"#f7f7f7","even-rows-bg-color":"#ffffff","link-button-bg":"","cart-checkout-button-bg":"","cart-checkout-button-text-color":"","link-button-text-color":"","price-color":"#0a0000","price-alternate-text-color":"#0a0202","buttons-alternate-text-color":"#0a0000","rows-bg-color-odd":"#fbfbfb","header-text-color-responsive":"","border-color":"","header-bg-color-responsive-odd":"#f9f9f9","header-bg-color-responsive-even":"#ffffff","header-border-bottom-color":"#262626","header-border-bottom-width":"4","rows-bg-color-responsive-odd":"#f9f9f9","rows-bg-color-responsive-even":"#ffffff"}',

    );

    if( ! empty( $themes [ $atts['theme'] ] ) ){
      $styling = json_decode($themes[$atts['theme']], true);
      $preserve_css = $data['styling']['css'];
      $data['styling'] = array_merge($styling, $data['styling']) ;
      $data['styling']['css'] = $preserve_css;
    }
  }

  // columns
  if(isset($atts['columns'])){
    // arranges $data['columns'] in the way sc wants
    add_filter( 'initial_data_in_wrct_get_wc_data_' . $id, 'wrct_arrange_columns_initial', 10, 1 );
    // removes columns from $data['columns'] that sc does not want
    add_filter( 'eventual_data_in_wrct_get_wc_data_' . $id, 'wrct_arrange_columns_eventual', 10, 1 );

  }

  // columns widths
  if( isset( $atts['columns-width'] ) ){

    $atts['columns-width'] = trim( $atts['columns-width'] );

    if( $atts['columns-width'] ){

      $data['styling']['columns-width'] = array( );
      $widths = explode( ',', $atts['columns-width'] );

      foreach( $widths as $key => $val ){
        $column_info = array_map( 'trim', explode( ':', $val ) );

        if( 2 === count( $column_info ) ){
          $data['styling']['columns-width'][ strtolower( $column_info[0] ) ] = intval( $column_info[1] );
        }
      }

    }

  }

  // columns min widths
  if( isset( $atts['columns-min-width'] ) ){

    $atts['columns-min-width'] = trim( $atts['columns-min-width'] );

    if( $atts['columns-min-width'] ){

      $data['styling']['columns-min-width'] = array( );
      $min_widths = explode( ',', $atts['columns-min-width'] );

      foreach( $min_widths as $key => $val ){
        $column_info = array_map( 'trim', explode( ':', $val ) );

        if( 2 === count( $column_info ) ){
          $data['styling']['columns-min-width'][ strtolower( $column_info[0] ) ] = intval( $column_info[1] );
        }
      }

    }

  }

  // columns max widths
  if( isset( $atts['columns-max-width'] ) ){

    $atts['columns-max-width'] = trim( $atts['columns-max-width'] );

    if( $atts['columns-max-width'] ){

      $data['styling']['columns-max-width'] = array( );
      $max_widths = explode( ',', $atts['columns-max-width'] );

      foreach( $max_widths as $key => $val ){
        $column_info = array_map( 'trim', explode( ':', $val ) );

        if( 2 === count( $column_info ) ){
          $data['styling']['columns-max-width'][ strtolower( $column_info[0] ) ] = intval( $column_info[1] );
        }
      }

    }

  }

  // course id
  $course_ids = false;
  if( ! empty( $atts['course-ids'] ) ){
    $course_ids = trim( $atts['course-ids'] );
    if( ! $course_ids ){
      $course_ids = false;
    }
  }

  // buttons default
  $buttons_default = array();

  //-- enable
  if( ! empty( $atts['button-1-enable'] ) ){
    $buttons_default["enable1"] = $atts['button-1-enable'];
  }

  if( ! empty( $atts['button-2-enable'] ) ){
    $buttons_default["enable2"] = $atts['button-2-enable'];
  }

  if( ! empty( $atts['button-3-enable'] ) ){
    $buttons_default["enable3"] = $atts['button-3-enable'];
  }

  //-- links
  if( ! empty( $atts['button-1-link'] ) ){
    $buttons_default["link1"] = $atts['button-1-link'];
  }

  if( ! empty( $atts['button-2-link'] ) ){
    $buttons_default["link2"] = $atts['button-2-link'];
  }

  if( ! empty( $atts['button-3-link'] ) ){
    $buttons_default["link3"] = $atts['button-3-link'];
  }

  //-- labels
  if( ! empty( $atts['button-1-label'] ) ){
    $buttons_default["label1"] = $atts['button-1-label'];
  }

  if( ! empty( $atts['button-2-label'] ) ){
    $buttons_default["label2"] = $atts['button-2-label'];
  }

  if( ! empty( $atts['button-3-label'] ) ){
    $buttons_default["label3"] = $atts['button-3-label'];
  }

  //-- targets
  if( ! empty( $atts['button-1-target'] ) ){
    $buttons_default["target1"] = $atts['button-1-target'];
  }

  if( ! empty( $atts['button-2-target'] ) ){
    $buttons_default["target2"] = $atts['button-2-target'];
  }

  if( ! empty( $atts['button-3-target'] ) ){
    $buttons_default["target3"] = $atts['button-3-target'];
  }

  // custom strings
  $custom_strings = array();
  //-- "clear all"
  if( ! empty( $atts['reset'] ) ){
    $custom_strings['reset'] = $atts['reset'];
  }
  //-- "select an option"
  if( ! empty( $atts['select'] ) ){
    $custom_strings['select'] = $atts['select'];
  }
  //-- "no search results"
  if( ! empty( $atts['no-search-results'] ) ){
    $custom_strings['no-search-results'] = $atts['no-search-results'];
  }
  //-- "select options"
  if( ! empty( $atts['select-options-prompt'] ) ){
    $custom_strings['select-options-prompt'] = $atts['select-options-prompt'];
  }

  // query args
  $query_args = isset($atts['query-args']) ? $atts['query-args'] : false;

  // max posts
  $max_posts = isset($atts['max-posts']) ? $atts['max-posts'] : false;

  // category
  if( isset( $atts['categories'] ) ){
    $atts['category'] = $atts['categories'];
  }
  $category = isset($atts['category']) ? $atts['category'] : false;

  // selection style
  $selection_style = isset( $data['styling']['selection-style'] ) ? $data['styling']['selection-style'] : 'drop-down';
  $selection_style = isset( $atts['selection-style'] ) && in_array( $atts['selection-style'], array( 'radio', 'drop-down' ) ) ? $atts['selection-style'] : $selection_style;

  // misc bool attrs
  //-- defaults
  $bool_attrs = array(
    'force-select' => false,
    'pagination' => false,
    'filtering' => false,
    'search' => false,
    'sorting' => false,
  );
  //-- truthy values
  $enable_arr = array( 'on', 'true', 'enabled', 'yes' );
  //-- get values from atts or editor
  foreach( $bool_attrs as $key => $val ){
    if( isset( $atts[ $key ] ) ){
    // use shortcode value if available
      $bool_attrs[ $key ] = in_array( strtolower( $atts[ $key ] ), $enable_arr );
    }else if( isset( $data['query'][$key] ) ){
    // get value from editor if available
      $bool_attrs[ $key ] = $data['query'][$key];
    }
  }
  //-- turn to $variables
  $force_select = $bool_attrs['force-select'];
  extract( $bool_attrs );

  // sort enabled columns
  $sort_enabled_columns = ! empty( $atts['sort-columns'] ) ? array_map( 'trim', explode( ",", $atts['sort-columns'] ) ) : array();
  $data['query']['sort-enabled-columns'] = $sort_enabled_columns;

  // default orderby
  $default_orderby = isset( $atts['default-orderby'] ) ? $atts['default-orderby'] : false;

  // link course name
  $course_name_link = (isset($atts['course-name-link']) && ( in_array( strtolower( $atts['course-name-link'] ), array( 'on', 'true', 'enabled', 'yes' ) ) ) );

  // related courses
  $related_products = false;
  if( isset( $atts['related-products'] ) ){
    $related_products = $atts['related-products'];
  }

  // up-sells
  $up_sells = false;
  if( isset( $atts['up-sells'] ) ){
    $up_sells = $atts['up-sells'];
  }

  // cross-sells
  $cross_sells = false;
  if( isset( $atts['cross-sells'] ) ){
    $cross_sells = $atts['cross-sells'];
  }

  // get table data
  require('class-wrct-get-wc-data.php');
  $instance = new WRCT_Get_WC_Data( $data, $query_args, $max_posts, $category, $id, $search, $pagination, $sorting, $sort_enabled_columns, $default_orderby, $filtering, $buttons_default, $course_ids, $related_products, $up_sells, $cross_sells );
  $data =& $instance->data;

  // check for empty table and cause
  if( empty( $data['courses'] ) ){
    // search did not return results
    if( $search && isset($_GET['wrct-search-' . $id]) ){
      $no_search_results = true;
    }
  }else{
    $no_search_results = false;
  }

  // get table markup
  if( ! empty( $data['courses'] ) ){
    require('class-wrct-create-table.php');
    $instance = new WRCT_Create_Table($data, $custom_strings, $id, $transient_key, $selection_style, $force_select, $course_name_link);
    $table =& $instance->table;

  }else{

  // print issues
    if( $no_search_results ){

      if( ! empty( $custom_strings['no-search-results'] ) ){
        $message = $custom_strings['no-search-results'];
      }else{
        $message = __( 'No search results found. %Clear search% and try again?', 'wrct' );
      }

      $open_tag = "<a href='". get_page_link() ."#wrct-". $id ."'>";
      $close_tag = "</a>";

      $occurances = substr_count( $message, "%" );

      if( $occurances === 2 ){
        $message_arr = explode( "%", $message );
        $message = $message_arr[0] . $open_tag . $message_arr[1] . $close_tag . $message_arr[2];
      }

      $table = "<div class='wrct-no-search-results-message'>". $message ."</div>";

    }else{

      if( ! empty( $custom_strings['no-courses'] ) ){
        $message = $custom_strings['no-courses'];
      }else{
        $message = __( 'Sorry, no courses were found for this table. Please select a valid WooCommerce category with products assigned to it.', 'wrct' );
      }

      $table = "<div class='wrct-no-products'>". $message ."</div>";
    }

  }

  ob_start();

  // css
  $style = '';
  $table_selector = "#wrct-". $id ." .wrct-table.wrct-table-" . $id;
  $responsive_layout_below = ! empty( $data['styling'][ 'responsive-layout-below' ] ) ? $data['styling'][ 'responsive-layout-below' ] : 750;

  // echo "<script>console.log(". json_encode($data['styling']) .")</script>";

  $style .= '<style>';

  // above responsive
  $style .= ' @media(min-width: '. $responsive_layout_below .'px){ ';


  if( ! empty( $data['styling']['table-minimum-width'] ) ){
    $style .= ' '. $table_selector . ' { min-width: '. $data['styling']['table-minimum-width'] .'px; } ';
  }

  if( ! empty( $data['styling']['rows-bg-color-even'] ) ){
    $style .= ' '. $table_selector . ' .wrct-row.wrct-even { background: '. $data['styling']['rows-bg-color-even'] .'; } ';
  }

  if( ! empty( $data['styling']['rows-bg-color-odd'] ) ){
    $style .= ' '. $table_selector . ' .wrct-row.wrct-odd { background: '. $data['styling']['rows-bg-color-odd'] .'; } ';
  }

  if( ! empty( $data['styling']['header-bg-color'] ) ){
    $style .= ' '. $table_selector . ' .wrct-heading-row { background: '. $data['styling']['header-bg-color'] .'; } ';
  }

  if( ! empty( $data['styling']['header-border-bottom-color'] ) ){
    $style .= ' '. $table_selector . ' .wrct-heading-row { border-bottom-color: '. $data['styling']['header-border-bottom-color'] .'; } ';
  }

  if( isset( $data['styling']['header-border-bottom-width'] ) ){
    $style .= ' '. $table_selector . ' .wrct-heading-row { border-bottom-width: '. $data['styling']['header-border-bottom-width'] .'px; } ';
  }

  if( ! empty( $data['styling']['header-text-color'] ) ){
    $style .= ' '. $table_selector . ' .wrct-heading-row { color: '. $data['styling']['header-text-color'] .'; } ';
  }

  if( ! empty( $data['styling']['header-font-size'] ) ){
    $style .= ' '. $table_selector . ' .wrct-heading { font-size: '. $data['styling']['header-font-size'] .'px; } ';
  }

  if( ! empty( $data['styling']['header-padding'] ) ){
    $style .= ' '. $table_selector . ' .wrct-heading { padding: '. $data['styling']['header-padding'] .'; } ';
  }

  if( ! empty( $data['styling']['cell-padding'] ) ){
    $style .= ' '. $table_selector . ' .wrct-cell { padding: '. $data['styling']['cell-padding'] .'; } ';
  }

  if( ! empty( $data['styling']['cell-text-color'] ) ){
    $style .= ' '. $table_selector . ' .wrct-cell { color: '. $data['styling']['cell-text-color'] .'; } ';
  }

  if( ! empty( $data['styling']['cell-font-size'] ) ){
    $style .= ' '. $table_selector . ' .wrct-cell { font-size: '. $data['styling']['cell-font-size'] .'px; } ';
  }

  if( ! empty( $data['styling']['column-min-width'] ) ){
    $style .= ' '. $table_selector .' .wrct-cell { min-width: '. $data['styling']['column-min-width'] .'px; } ';
  }

  if( ! empty( $data['styling']['border-table-remove'] ) ){
    $style .= ' '. $table_selector .' { border-width: 0!important; } ';
  }
  if( ! empty( $data['styling']['border-horizontal-remove'] ) ){
    $style .= ' '. $table_selector .' .wrct-heading, '. $table_selector .' .wrct-cell { border-bottom-width: 0!important; } ';
  }

  if( ! empty( $data['styling']['border-vertical-remove'] ) ){
    $style .= ' '. $table_selector .' .wrct-heading, '. $table_selector .' .wrct-cell { border-right-width: 0!important; } ';
  }

  if( ! empty( $data['styling']['border-color'] ) ){
    $style .= ' '. $table_selector .', '. $table_selector .' th, '. $table_selector .' tr, '. $table_selector .' td { border-color: '. $data['styling']['border-color'] .'; } ';
  }

  if( ! empty( $data['styling']['border-width'] ) ){
    ob_start();
    ?>

    <?php echo $table_selector; ?>  {
      border-width: <?php echo $data['styling']['border-width']; ?>px;
    }

    <?php echo $table_selector; ?> th{
      border-right-width: <?php echo $data['styling']['border-width']; ?>px;
    }

    <?php echo $table_selector; ?> td{
      border-right-width: <?php echo $data['styling']['border-width']; ?>px!important;
      border-bottom-width: <?php echo $data['styling']['border-width']; ?>px!important;
    }

    <?php
    $style .= ob_get_clean();
  }

  if( ! empty( $data['styling']['vertical-align'] ) ){
    $style .= ' '. $table_selector .' th, '. $table_selector .' td { vertical-align: '. $data['styling']['vertical-align'] .'; } ';
  }

  if( ! empty( $data['styling']['columns-width'] ) ){
    foreach( $data['styling']['columns-width'] as $column_name => $column_width ){
      $style .= ' '. $table_selector .' [data-wrct-name="'. $column_name .'"] { width: '. $column_width .'px; } ';
    }
  }

  if( ! empty( $data['styling']['columns-min-width'] ) ){
    foreach( $data['styling']['columns-min-width'] as $column_name => $column_min_width ){
      $style .= ' '. $table_selector .' [data-wrct-name="'. $column_name .'"] { min-width: '. $column_min_width .'px; } ';
    }
  }

  if( ! empty( $data['styling']['columns-max-width'] ) ){
    foreach( $data['styling']['columns-max-width'] as $column_name => $column_max_width ){
      $style .= ' '. $table_selector .' [data-wrct-name="'. $column_name .'"] { max-width: '. $column_max_width .'px; } ';
    }
  }

  $style .= ' } ';


  // below responsive
  $style .= ' @media(max-width: '. $responsive_layout_below .'px){ ';

  if( ! empty( $data['styling']['header-bg-color-responsive-even'] ) ){
    $style .= ' '. $table_selector . ' tr td:nth-child(even):before { background: '. $data['styling']['header-bg-color-responsive-even'] .'; } ';
  }

  if( ! empty( $data['styling']['header-bg-color-responsive-odd'] ) ){
    $style .= ' '. $table_selector . ' tr td:nth-child(odd):before { background: '. $data['styling']['header-bg-color-responsive-odd'] .'; } ';
  }

  if( ! empty( $data['styling']['header-font-size-responsive'] ) ){
    $style .= ' '. $table_selector . '.wrct-responsive-layout .wrct-cell .wrct-cell-label { font-size: '. $data['styling']['header-font-size-responsive'] .'px; } ';
  }

  if( ! empty( $data['styling']['header-text-color-responsive'] ) ){
    $style .= ' '. $table_selector . '.wrct-responsive-layout .wrct-cell .wrct-cell-label { color: '. $data['styling']['header-text-color-responsive'] .'; } ';
  }

  if( ! empty( $data['styling']['header-width-responsive'] ) ){
    $width_string = trim( $data['styling']['header-width-responsive'] );

    $pixels = false;
    $percentage = false;

    if( substr( $width_string, -2 ) === "px" ){
      $pixels = true;
      $width = (int) substr( $width_string, 0, -2 );
    } else if( substr( $width_string, -1 ) === "%" ){
      $percentage = true;
      $width = (int) substr( $width_string, 0, -1 );
    } else {
      $width = (int) $width_string;
      if( $width < 100 ){
        $percentage = true;
      }else{
        $pixels = true;
      }

    }

    if( $percentage ){
      $cell_width = ( 100 - $width ) . "%";
      $width .= "%";
    }else if( $pixels ){
      $cell_width = "calc(100% - ".$width."px)";
      $width .= "px";
    }

    $style .= ' '. $table_selector . '.wrct-responsive-layout .wrct-cell .wrct-cell-label { width: '. $width .'; } ';
    $style .= ' '. $table_selector . '.wrct-responsive-layout .wrct-cell:before { width: '. $width .'; } ';
    $style .= ' '. $table_selector . '.wrct-responsive-layout .wrct-cell .wrct-cell-val { width: '. $cell_width .'; } ';
  }

  if( ! empty( $data['styling']['rows-bg-color-responsive-even'] ) ){
    $style .= ' '. $table_selector . '.wrct-responsive-layout tr td:nth-child(even) { background: '. $data['styling']['rows-bg-color-responsive-even'] .'; } ';
  }

  if( ! empty( $data['styling']['rows-bg-color-responsive-odd'] ) ){
    $style .= ' '. $table_selector . '.wrct-responsive-layout tr td:nth-child(odd) { background: '. $data['styling']['rows-bg-color-responsive-odd'] .'; } ';
  }

  if( ! empty( $data['styling']['cell-text-color-responsive'] ) ){
    $style .= ' '. $table_selector . '.wrct-responsive-layout .wrct-cell { color: '. $data['styling']['cell-text-color-responsive'] .'; } ';
  }

  if( ! empty( $data['styling']['cell-font-size-responsive'] ) ){
    $style .= ' '. $table_selector . '.wrct-responsive-layout .wrct-cell { font-size: '. $data['styling']['cell-font-size-responsive'] .'px; } ';
  }

  if( ! empty( $data['styling']['cell-font-size-responsive'] ) ){
    $style .= ' '. $table_selector . '.wrct-responsive-layout .wrct-cell { font-size: '. $data['styling']['cell-font-size-responsive'] .'px; } ';
  }

  //-- border width responsive
  if( isset( $data['styling']['border-width-responsive'] ) ){
    ob_start();
    ?>
      <?php echo $table_selector; ?>.wrct-responsive-layout .wrct-cell {
      	border-top-width: 0;
      	border-right-width: <?php echo $data['styling']['border-width-responsive']; ?>px;
      	border-bottom-width: 0;
      	border-left-width: <?php echo $data['styling']['border-width-responsive']; ?>px;
      }

      <?php echo $table_selector; ?>.wrct-responsive-layout .wrct-cell.wrct-responsive-last-cell,
      <?php echo $table_selector; ?>.wrct-responsive-layout.wrct-responsive-layout-accordion .wrct-row:not(.wrct-responsive-layout-accordion-expand) .wrct-cell.wrct-responsive-first-cell {
      	border-bottom-width: <?php echo $data['styling']['border-width-responsive']; ?>px;
      }

      <?php echo $table_selector; ?>.wrct-responsive-layout .wrct-cell.wrct-responsive-first-cell {
      	border-top-width: <?php echo $data['styling']['border-width-responsive']; ?>px;
      }
    <?php
    $style .= ob_get_clean();
  }

  //-- border radius responsive
  if( isset( $data['styling']['border-radius-responsive'] ) ){
    ob_start();
    ?>
      <?php echo $table_selector; ?>.wrct-responsive-layout .wrct-cell.wrct-responsive-first-cell {
      	border-top-left-radius: <?php echo $data['styling']['border-radius-responsive']; ?>px;
      	border-top-right-radius: <?php echo $data['styling']['border-radius-responsive']; ?>px;
      }

      <?php echo $table_selector; ?>.wrct-responsive-layout .wrct-cell.wrct-responsive-last-cell,
      <?php echo $table_selector; ?>.wrct-responsive-layout .wrct-row:not(.wrct-responsive-layout-accordion-expand) .wrct-cell.wrct-responsive-first-cell {
      	border-bottom-left-radius: <?php echo $data['styling']['border-radius-responsive']; ?>px;
      	border-bottom-right-radius: <?php echo $data['styling']['border-radius-responsive']; ?>px;
      }
    <?php

    $style .= ob_get_clean();
  }

  //-- border color responsive
  if( ! empty( $data['styling']['border-color-responsive'] ) ){
    ob_start();
    ?>
    <?php echo $table_selector; ?>.wrct-responsive-layout .wrct-cell {
      border-color: <?php echo $data['styling']['border-color-responsive']; ?>;
    }
    <?php
    $style .= ob_get_clean();
  }

  //-- name border bottom width responsive
  if( ! empty( $data['styling']['name-border-bottom-width-responsive'] ) ){
    ob_start();
    ?>
      <?php echo $table_selector; ?>.wrct-responsive-layout .wrct-row.wrct-responsive-layout-accordion-expand .wrct-cell[data-wrct-name="name"] {
      	border-bottom-width: <?php echo $data['styling']['name-border-bottom-width-responsive']; ?>px;
      	border-bottom-style: solid;
      }
    <?php
    $style .= ob_get_clean();
  }

  //-- name border bottom color responsive
  if( ! empty( $data['styling']['name-border-bottom-color-responsive'] ) ){
    ob_start();
    ?>
      <?php echo $table_selector; ?>.wrct-responsive-layout .wrct-row.wrct-responsive-layout-accordion-expand .wrct-cell[data-wrct-name="name"] {
      	border-bottom-color: <?php echo $data['styling']['name-border-bottom-color-responsive']; ?>;
      }
    <?php
    $style .= ob_get_clean();
  }

  //-- middle border width responsive
  if( isset( $data['styling']['middle-border-width-responsive'] ) ){
    ob_start();
    ?>
      <?php echo $table_selector; ?>.wrct-responsive-layout .wrct-cell:before {
      	border-right-width: <?php echo $data['styling']['middle-border-width-responsive']; ?>px;
      }
    <?php
    $style .= ob_get_clean();
  }

  //-- middle border color responsive
  if( ! empty( $data['styling']['middle-border-color-responsive'] ) ){
    ob_start();
    ?>
      <?php echo $table_selector; ?>.wrct-responsive-layout .wrct-cell:before {
      	border-right-color: <?php echo $data['styling']['middle-border-color-responsive']; ?>;
      }
    <?php
    $style .= ob_get_clean();
  }

  //-- full width name cell responsive
  if( ! empty( $data['styling']['name-full-width-responsive'] ) || ! empty( $data['styling']['enable-responsive-layout-accordion'] ) ){
    ob_start();
    ?>
      <?php echo $table_selector; ?>.wrct-responsive-layout [data-wrct-name="name"]:before,
      <?php echo $table_selector; ?>.wrct-responsive-layout [data-wrct-name="name"] .wrct-cell-label {
      	display: none!important;
      }
      <?php echo $table_selector; ?>.wrct-responsive-layout [data-wrct-name="name"] .wrct-cell-val {
      	width: 100%;
      }
    <?php
    $style .= ob_get_clean();
  }

  //-- name background color responsive
  if( ! empty( $data['styling']['name-bg-color-responsive'] ) ){
    ob_start();
    ?>
      <?php echo $table_selector; ?>.wrct-responsive-layout [data-wrct-name="name"]:before,
      <?php echo $table_selector; ?>.wrct-responsive-layout [data-wrct-name="name"] .wrct-cell-val {
      	background: <?php echo $data['styling']['name-bg-color-responsive']; ?>;
      }
    <?php
    $style .= ob_get_clean();
  }

  //-- name text color responsive
  if( ! empty( $data['styling']['name-text-color-responsive'] ) ){
    ob_start();
    ?>
      <?php echo $table_selector; ?>.wrct-responsive-layout [data-wrct-name="name"] .wrct-cell-val {
        color: <?php echo $data['styling']['name-text-color-responsive']; ?>;
      }
    <?php
    $style .= ob_get_clean();
  }

  //-- columns gap vertical
  if( ! isset( $data['styling']['responsive-layout-columns-gap-vertical'] ) ){
    $data['styling']['responsive-layout-columns-gap-vertical'] = 30;
  }
  $style .= ' '. $table_selector . '.wrct-responsive-layout .wrct-row { margin-bottom:' . $data['styling']['responsive-layout-columns-gap-vertical'] . 'px; } ';

  $style .= ' } ';


  // element styles
  if( ! empty( $data['styling']['header-hide'] ) ){
    $style .= ' '. $table_selector . ' .wrct-heading-row { display: none; } ';
  }

  if( ! empty( $data['styling']['link-button-bg'] ) ){
    $style .= ' '. $table_selector . ' .wrct-button { background: '. $data['styling']['link-button-bg'] .'; } ';
  }

  if( ! empty( $data['styling']['link-button-text-color'] ) ){
    $style .= ' '. $table_selector . ' .wrct-button { color: '. $data['styling']['link-button-text-color'] .'; } ';
  }

  if( ! empty( $data['styling']['cart-checkout-button-bg'] ) ){
    $style .= ' '. $table_selector . ' .wrct-button[data-wrct-link-code="%cart%"] { background: '. $data['styling']['cart-checkout-button-bg'] .'; } ';
    $style .= ' '. $table_selector . ' .wrct-button[data-wrct-link-code="%ajax-cart%"] { background: '. $data['styling']['cart-checkout-button-bg'] .'; } ';
    $style .= ' '. $table_selector . ' .wrct-button[data-wrct-link-code="%to-cart%"] { background: '. $data['styling']['cart-checkout-button-bg'] .'; } ';
    $style .= ' '. $table_selector . ' .wrct-button[data-wrct-link-code="%checkout%"] { background: '. $data['styling']['cart-checkout-button-bg'] .'; } ';
  }

  if( ! empty( $data['styling']['cart-checkout-button-text-color'] ) ){
    $style .= ' '. $table_selector . ' .wrct-button[data-wrct-link-code="%cart%"] { color: '. $data['styling']['cart-checkout-button-text-color'] .'; } ';
    $style .= ' '. $table_selector . ' .wrct-button[data-wrct-link-code="%ajax-cart%"] { color: '. $data['styling']['cart-checkout-button-text-color'] .'; } ';
    $style .= ' '. $table_selector . ' .wrct-button[data-wrct-link-code="%to-cart%"] { color: '. $data['styling']['cart-checkout-button-text-color'] .'; } ';
    $style .= ' '. $table_selector . ' .wrct-button[data-wrct-link-code="%checkout%"] { color: '. $data['styling']['cart-checkout-button-text-color'] .'; } ';
  }

  if( ! empty( $data['styling']['price-color'] ) ){
    $style .= ' '. $table_selector . ' [data-wrct-name="price"] .wrct-cell-val { color: '. $data['styling']['price-color'] .'; } ';
  }

  if( ! empty( $data['styling']['price-alternate-text-color'] ) ){
    $style .= ' '. $table_selector . ' [data-wrct-name="price"] .wrct-price-text { color: '. $data['styling']['price-alternate-text-color'] .'; } ';
  }

  if( ! empty( $data['styling']['buttons-alternate-text-color'] ) ){
    $style .= ' '. $table_selector . ' [data-wrct-name="buttons"] .wrct-buttons-text { color: '. $data['styling']['buttons-alternate-text-color'] .'; } ';
  }

  if( ! empty( $data['styling']['css'] ) ){
    $style .= $data['styling']['css'];
  }

  if( ! empty( $atts['css'] ) ){
    $style .= $atts['css'];
  }

  $style .= '</style>';

  $style = str_replace( array( "%breakpoint%", "\r", "\n", "\t", "  ", "   " ) , array( $responsive_layout_below . "px", "" ), $style );

  // if($filters){
  //   ob_start();
  //   $filters = ob_get_clean();
  //
  // }else{
  //   $filters = '';
  // }

  if( $search ){
    if( ! empty( $atts['search-label'] ) ){
      $search_label = $atts['search-label'];
    }else{
      $search_label = "";
    }

    if( ! empty( $atts['search-placeholder'] ) ){
      $search_placeholder = $atts['search-placeholder'];
    }else{
      $search_placeholder = __("Search course", 'wrct');
    }

    if( ! empty( $atts['clear-search'] ) ){
      $clear_search = $atts['clear-search'];
    }else{
      $clear_search = "";
    }

    $search_keyword = '';
    if( ! empty( $_REQUEST['wrct-search-'. $id] ) ){
      $search_keyword = esc_attr( sanitize_text_field( stripslashes( $_GET['wrct-search-'. $id] ) ) );
    }

    ob_start();
    ?>
    <form class="wrct-search-form <?php if( ! empty( $search_keyword ) ) echo 'wrct-search-clear-enabled'; ?>" action="<?php echo get_page_link(); ?>#wrct-<?php echo $id; ?>" method="GET">
      <?php if( ! empty( $search_label ) ) { ?>
        <label class="wrct-search-label" for="wrct-search"><?php echo sanitize_text_field( $search_label ) ?></label>
      <?php } ?>
      <input class="wrct-search-input" type="search" name="wrct-search-<?php echo $id; ?>" placeholder="<?php echo $search_placeholder; ?>" value="<?php echo $search_keyword; ?>">
      <button class="wrct-search-submit" type="submit" value="">
        <i class="wrct-search-submit-icon wrct-icon"></i>
      </button>
      <?php if( ! empty( $search_keyword ) ) { ?>
        <button class="wrct-search-clear" type="submit" name="wrct-search-<?php echo $id; ?>" value="">
          <i class="wrct-search-clear-icon wrct-icon"></i>
        </button>
      <?php } ?>
    </form>
    <?php
    $search = ob_get_clean();

  }else{
    $search = '';
  }

  if( $sorting ){
    if( ! empty( $atts['sorting-label'] ) ){
      $sorting_label = $atts['sorting-label'];
    }else{
      // $sorting_label = __("Sort by: ", 'wrct');
      $sorting_label = "";
    }

    $default_orderby = $default_orderby ? $default_orderby : "menu_order";

    $orderby = isset( $data['query']['orderby'] ) ? wc_clean( $data['query']['orderby'] ) : $default_orderby;

    if( $orderby === 'title' ){
      $orderby = 'name';
    }

    if( $data['query']['order'] == 'DESC' ){
      $orderby .= '-desc';
    }

    if( in_array( $orderby, array('rating-desc', 'rating-asc') ) ){
      $orderby .= 'rating';
    }

    $catalog_orderby_options = array(
			$default_orderby => __( 'Default sorting', 'wrct' ),
			'popularity' => __( 'Sort by popularity', 'wrct' ),
			'rating'=> __( 'Sort by average rating', 'wrct' ),
			'date'       => __( 'Sort by newness', 'wrct' ),
			'price'      => __( 'Sort by price: low to high', 'wrct' ),
			'price-desc' => __( 'Sort by price: high to low', 'wrct' ),
		);

    if ( 'no' === get_option( 'woocommerce_enable_review_rating' ) ) {
			unset( $catalog_orderby_options['rating'] );
		}

    $already_available_options = array_keys( $catalog_orderby_options );

    foreach( $data['columns'] as $key=> $col_info ){
      if( in_array( $col_info['name'], $already_available_options ) ){
        continue;
      }
      if( ! empty( $col_info['sorting'] ) && empty( $col_info['hide'] ) ){
        $col_heading = $col_info['heading'] ? strtolower( preg_replace( '/%[\s\S]+?%/', '', $col_info['heading'] ) ) : $col_info['name']; // strip icons
        $catalog_orderby_options[ $col_info['name'] ] = __( 'Sort by ', 'wrct' ) . $col_heading . ': ' . __( 'asc', 'wrct' );
        $catalog_orderby_options[ $col_info['name'] . '-desc' ] = __( 'Sort by ', 'wrct' ) . $col_heading . ': ' . __( 'desc', 'wrct' );
      }
    }

		$catalog_orderby_options = apply_filters( 'wrct_catalog_orderby', $catalog_orderby_options, $data );

    ob_start();
    ?>
    <form class="wrct-sorting-form" action="#wrct-<?php echo $id; ?>" method="GET">
      <select class="wrct-sorting-input" name="wrct-orderby-<?php echo $id; ?>">
        <?php foreach ( $catalog_orderby_options as $id1 => $name ) : ?>
    			<option value="<?php echo esc_attr( $id1 ); ?>" <?php selected( $orderby, $id1 ); ?>><?php echo esc_html( $name ); ?></option>
    		<?php endforeach; ?>
      </select>
      <?php wc_query_string_form_fields( null, array( 'wrct-orderby-'. $id, 'submit' ) ); ?>
    </form>
    <?php
    $sorting = ob_get_clean();

  }else{
    $sorting = '';
  }

  if($pagination){

    add_filter( 'paginate_links', 'wrct_paginate_links' );

    $big = 1000000;
    $args = array(
    	'base'               => '?wrct-paged-'. $id .'=%#%',
    	'format'             => '?wrct-paged-'. $id .'=%#%',
    	'total'              => $data['loop']['max_num_pages'],
    	'current'            => $data['query']['paged'],
    	'show_all'           => false,
    	'end_size'           => 2,
    	'mid_size'           => 2,
    	'prev_next'          => false,
    	// 'prev_text'          => __('« Previous'),
    	// 'next_text'          => __('Next »'),
      'prev_text'          => false,
    	'next_text'          => false,
    	'type'               => 'plain',
    	'add_args'           => false,
    	'add_fragment'       => '#wrct-'. $id,
    	'before_page_number' => '',
    	'after_page_number'  => ''
    );
    $pagination = '<div class="wrct-pagination">' . paginate_links( $args ) . '</div>';

    remove_filter( 'paginate_links', 'wrct_paginate_links' );

  }else{
    $pagination = '';
  }

  // loading
  $loading = '<div class="wrct-loading-screen">';
    $loading .= '<div class="wrct-loading-indicator">';
      //-- symbol
      $loading .= '<div class="wrct-loading-symbol">';
        $loading .= '<svg version="1.1" id="loader-1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="40px" height="40px" viewBox="0 0 40 40" enable-background="new 0 0 40 40" xml:space="preserve"><path opacity="0.2" fill="#000" d="M20.201,5.169c-8.254,0-14.946,6.692-14.946,14.946c0,8.255,6.692,14.946,14.946,14.946 s14.946-6.691,14.946-14.946C35.146,11.861,28.455,5.169,20.201,5.169z M20.201,31.749c-6.425,0-11.634-5.208-11.634-11.634 c0-6.425,5.209-11.634,11.634-11.634c6.425,0,11.633,5.209,11.633,11.634C31.834,26.541,26.626,31.749,20.201,31.749z"></path><path fill="#000" d="M26.013,10.047l1.654-2.866c-2.198-1.272-4.743-2.012-7.466-2.012h0v3.312h0 C22.32,8.481,24.301,9.057,26.013,10.047z" transform="rotate(72 20 20)"><animateTransform attributeType="xml" attributeName="transform" type="rotate" from="0 20 20" to="360 20 20" dur="0.5s" repeatCount="indefinite"></animateTransform></path></svg>';
      $loading .= '</div>';
      //-- text
      $loading .= '<div class="wrct-loading-text">';
        $loading .= __( 'Loading...', 'wrct' );
      $loading .= '</div>';

    $loading .= '</div>';
  $loading .= '</div>';

  $forms = $filters . $search . $sorting;
  if( $forms ){
    $forms = '<div class="wrct-forms">'. $forms .'</div>';
  }
  ?>
  <div class="wrct-table-scroll-wrapper">
    <?php echo $table; ?>
  </div>
  <?php
  $table = ob_get_clean();
  // $table = do_shortcode($table);

  ob_start();
  ?>
  <script type="text/javascript">
    if( typeof wrct_init !== "undefined" && typeof jQuery !== "undefined" ){
      wrct_init( jQuery("#wrct-<?php echo $id ?>") );
    }
  </script>
  <?php
  $js = ob_get_clean();

  return '<div id="wrct-'. $id .'" class="wrct wrct-'. $id .' wrct-pre-init" data-wrct-ajax-key="'. $transient_key .'">' . $style . $forms . $table . $pagination . $loading .'</div>'. $js;
}

// initially arranges $data['columns'] based on user input in shortcode. Hooked into filter 'initial_data_in_wrct_get_wc_data_' . $id in class-wrct-get-wc-data.php
function wrct_arrange_columns_initial($data){

  $columns =& $data['columns']; // current column arrangement (not shortcode)

  global $wrct_shortcode_atts;
  $column_arrangement = trim( $wrct_shortcode_atts['columns'] ); // arrangement that user wants

  // breakdown the sc attribute columns to understand it
  if( ! empty( $column_arrangement ) ){

    $columns = array();

    $column_arrangement = explode(',', $column_arrangement);

    foreach( $column_arrangement as $key => $val ){
      $column_info = array_map( 'trim', explode( ':', $val ) );

      $column_name = $column_info[0];

      if( ! isset( $column_info[1] ) ){
        $column_heading = $column_name;

      }else{
        $column_info[1] = trim( $column_info[1] );
        $column_heading = ( empty( $column_info[1] ) || "no heading" === strtolower( trim( $column_info[1] ) ) ) ? "" : $column_info[1];
      }

      $columns[] = array(
        'name' => strtolower( $column_name ),
        'heading' => $column_heading,
      );
    }
  }

  return $data;

}

// influences the $data['columns'] based on user input in shortcode. Hooked into filter 'eventual_data_in_wrct_get_wc_data_' . $id in class-wrct-get-wc-data.php
function wrct_arrange_columns_eventual($data){

  $columns =& $data['columns']; // arrangement that we have

  global $wrct_shortcode_atts;
  $column_arrangement = trim( $wrct_shortcode_atts['columns'] ); // arrangement that user wants

  // breakdown the sc attribute columns to understand it
  if( ! empty( $column_arrangement ) ){

    $column_arrangement = explode(',', $column_arrangement);

    $permitted_columns = array();

    foreach($column_arrangement as $key=> $val){
      $column_info = array_map( 'trim', explode( ':', $val ) );
      $permitted_columns[] = strtolower( $column_info[0] );
    }

    $_columns = array();

    foreach( $columns as $key => $column_data ){
      if( in_array( $column_data['name'], $permitted_columns ) ){
        $_columns[] = $column_data;
      }
    }

    $columns = $_columns;
  }

  return $data;

}

// removes other woocommerce arguments from the pagination links
function wrct_paginate_links( $link ) {
    $remove = array( 'add-to-cart', 'variation_id', 'product_id', 'quantity' );
    foreach( $_GET as $key=> $val ){
      if( substr( $key, 0, 10 ) === 'attribute_' ){
        $remove[] = $key;
      }
    }
    return remove_query_arg( $remove, $link );
}

add_action( 'wp_ajax_wrct_fetch_columns', 'wrct_fetch_columns' );
function wrct_fetch_columns(){

  if( ! wp_verify_nonce( $_POST['wrct_nonce'], 'wrct' ) || empty( $_POST['wrct_query'] ) ){
    wp_die();
  }

  require('class-wrct-get-wc-data.php');

  $query = json_decode(stripslashes($_POST['wrct_query']), true);
  $columns = json_decode(stripslashes($_POST['wrct_columns']), true);

  $data = array(
    'query'=> $query,
    'columns'=> $columns,
    'courses'=> array(),
  );

  $instance = new WRCT_Get_WC_Data($data);
  $result = $instance->data;

  // lighten the load
  $result['courses'] = array();
  $result['loop'] = array();
  $result['query'] = $query;

  $result['token'] = (int) $_POST['wrct_token'];

  echo json_encode($result);
  wp_die();
}

// wrct (icon) shortcodes interpreted in attributes table on single product page
// add_filter('woocommerce_attribute', 'wrct_shortcodes_on_attributes');
// function wrct_shortcodes_on_attributes($content){
//   if( strpos($content, 'wrct') !== false ){
//     $content = do_shortcode($content);
//   };
//   return $content;
// }

// on the 'All' wrct page insert column to show shortcodes
add_filter( 'manage_wrct_posts_columns' , 'wrct_add_custom_columns' );
function wrct_add_custom_columns( $columns ) {
  $key = 'title';
  $offset = array_search( $key, array_keys( $columns ) ) + 1;
  $columns = array_merge (
      array_slice( $columns, 0, $offset ),
      array( 'shortcode' => __( 'Shortcode', 'wrct' ) ),
      array_slice( $columns, $offset, null )
  );
  return $columns;
}

//-- helper
add_action( 'manage_wrct_posts_custom_column' , 'wrct_custom_columns', 10, 2 );
function wrct_custom_columns( $column, $post_id ) {
  if($column == 0){
    echo '[wrct id="'.$post_id.'"]';
  }
}

// remove inline editor buttons from 'ALL Tables' page
add_filter('post_row_actions', 'wrct_row_buttons', 10, 2);
function wrct_row_buttons($actions, $post) {
  if ($post->post_type=='wrct'){
    unset($actions['inline hide-if-no-js'], $actions['view']);
  }
  return $actions;
}

/* ajax add to cart */
add_action( 'wp_ajax_wrct_add_to_cart', 'wrct_add_to_cart' );
add_action( 'wp_ajax_nopriv_wrct_add_to_cart', 'wrct_add_to_cart' );
function wrct_add_to_cart(){

  if( $_POST['return_notice'] == "false" ){
    wp_die();
  }

  // success
  if( wc_notice_count('success') ){
    ob_start();
    woocommerce_mini_cart();
    $mini_cart = ob_get_clean();

    $data = array(
      'success' => true,
      'notice' => $notice,
      'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', array(
          'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>',
        )
      ),
      'cart_hash' => apply_filters( 'woocommerce_add_to_cart_hash', WC()->cart->get_cart_for_session() ? md5( json_encode( WC()->cart->get_cart_for_session() ) ) : '', WC()->cart->get_cart_for_session() ),
    );

  // error
  }else{
		$data = array(
			'error' => true,
      'notice' => $notice,
			'product_url' => apply_filters( 'woocommerce_cart_redirect_after_error', get_permalink( $_REQUEST['product_id'] ), $_REQUEST['product_id'] ),
		);

  }

  // get notice markup
  $data['notice'] = "";
  if( wc_notice_count() ){
    ob_start();
    wc_print_notices();
    $data['notice'] = ob_get_clean();
  }

	wp_send_json( $data );
}

function wrct_add_to_cart_1(){

  $product_id = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_POST['product_id'] ) );
  $quantity = empty( $_POST['quantity'] ) ? 1 : wc_stock_amount( $_POST['quantity'] );

  $variation_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : '';
  $variation_attributes = ! empty( $_POST['variation_attributes'] ) ? json_decode( stripslashes( $_POST['variation_attributes'] ), true ) : '';

  $passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variation_attributes );
  $product_status = get_post_status( $product_id );

  $cart_item_key = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation_attributes );

  if ( $passed_validation && false !== $cart_item_key && 'publish' === $product_status ) {
  // success

    do_action( 'woocommerce_ajax_added_to_cart', $product_id );

    if( $_POST['return_notice'] !== "false" ){
      $notice =  wc_add_to_cart_message( array( $product_id => $quantity ), true, true );
    }else{
      wc_add_to_cart_message( array( $product_id => $quantity ), true );
      $notice = false;
    }

    ob_start();
		woocommerce_mini_cart();
		$mini_cart = ob_get_clean();

		$data = array(
      'success' => true,
      'notice' => $notice,
			'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', array(
					'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>',
				)
			),
			'cart_hash' => apply_filters( 'woocommerce_add_to_cart_hash', WC()->cart->get_cart_for_session() ? md5( json_encode( WC()->cart->get_cart_for_session() ) ) : '', WC()->cart->get_cart_for_session() ),
		);

  } else {
  // error

    if( $_POST['return_notice'] !== "false" ){
      ob_start();
      wc_print_notices();
      $notice = ob_get_clean();
    }else{
      $notice = false;
    }

		$data = array(
			'error' => true,
      'notice' => $notice,
			'product_url' => apply_filters( 'woocommerce_cart_redirect_after_error', get_permalink( $product_id ), $product_id ),
		);

  }

	wp_send_json( $data );
}

/* ajax remove cart item */
add_action( 'wp_ajax_wrct_remove_cart_item', 'wrct_remove_cart_item' );
add_action( 'wp_ajax_nopriv_wrct_remove_cart_item', 'wrct_remove_cart_item' );
function wrct_remove_cart_item(){
  $cart_item_keys_arr = isset( $_POST['cart_item_keys_arr'] ) ? json_decode( stripslashes( $_POST['cart_item_keys_arr'] ), true ) : false;

  if( ! $cart_item_keys_arr ){
    $result= "error: no cart item keys received";
  }else{
    foreach( $cart_item_keys_arr as $cart_item_key ){
      WC()->cart->remove_cart_item( $cart_item_key );
    }
    $result= "success: item(s) removed from cart";
  }

  wp_die($result);
}

function wrct_get_product_details_in_cart_including_variations( $product_id ){
  $result = array(
    "quantity" => 0,
    "cart_item_keys_arr" => array(),
  );
  $cart_contents = WC()->cart->get_cart();
  foreach($cart_contents as $cart_item_key=> $item_details){
    if( $product_id === $item_details["product_id"] ){
      $result["cart-item-keys-arr"][] = $cart_item_key;
      $result["quantity"] += $item_details["quantity"];
    }
  }
  return $result;
}

function wrct_get_column_index_by_name($name, $data){
  $index = false;
  foreach( $data["columns"] as $key => $column_details ){
    if( $column_details["name"] === $name ){
      $index = $key;
    }
  }

  return $index;
}

add_action("init", "clear_cart");
function clear_cart(){
  if( isset($_REQUEST['clear-cart']) ){
    WC()->cart->empty_cart();
  }
}

?>
