<?php
/*
 * WRCT Create Table
 * This class will create the WRCT table based on the
 * data array provided by the user.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('WRCT_Create_Table')) {

    class WRCT_Create_Table
    {
        // user defined table structure for the table
        public $table;

        // user defined data structure for the table
        public $data;

        // custom strings by user
        public $string_data;

        // shortcode and table post id
        public $id;

        // wc currency symbol
        public $currency_symbol;

        // wc decimal separator: , / .
        public $decimal_separator;

        // wc price format
        public $price_format;

        // constructor
        function __construct($data, $custom_strings, $id, $transient_key = false, $selection_style, $force_select, $course_name_link = false)
        {
            $this->data             = $data;
            $this->custom_strings   = $custom_strings;
            $this->id               = $id;
            $this->transient_key    = $transient_key;
            $this->selection_style  = $selection_style;
            $this->force_select     = $force_select;
            $this->link_course_name = $course_name_link;
            $this->create();
        }

        // update the data object with current WC data
        public function create()
        {

            $data =& $this->data;
            $table =& $this->table;
            $custom_strings =& $this->custom_strings;
            $selection_style =& $this->selection_style;
            $force_select =& $this->force_select;
            $course_name_link =& $this->link_course_name;

            // echo "<script>console.log(". json_encode( $data ) .")</script>";

            $this->currency_symbol   = get_woocommerce_currency_symbol();
            $this->decimal_separator = wc_get_price_decimal_separator();
            $this->price_format      = get_woocommerce_price_format();

            // strings
            $strings = array(
                "choose" => __("Choose", "wrct"),
            );

            if (!empty($custom_strings)) {
                $strings = array_merge($strings, $custom_strings);
            }

            // responsive layout breakpoint
            $responsive_layout_below = !empty($data['styling']['responsive-layout-below']) ? (int) $data['styling']['responsive-layout-below'] : 750;

            // responsive layout multiple columns
            $responsive_columns_params = "";

            //-- gap horizontal
            if (!isset($data['styling']['responsive-layout-columns-gap-horizontal'])) {
                $data['styling']['responsive-layout-columns-gap-horizontal'] = 15;
            }
            $responsive_columns_params .= ' data-wrct-responsive-layout-columns-gap-horizontal="' . (int) $data['styling']['responsive-layout-columns-gap-horizontal'] . '" ';

            //-- 3 columns
            if (!empty($data['styling']['responsive-layout-3-columns-above'])) {
                $responsive_columns_params .= ' data-wrct-responsive-layout-3-columns-above="' . (int) $data['styling']['responsive-layout-3-columns-above'] . '" ';
            }

            //-- 2 columns
            if (!empty($data['styling']['responsive-layout-2-columns-above'])) {
                $responsive_columns_params .= ' data-wrct-responsive-layout-2-columns-above="' . (int) $data['styling']['responsive-layout-2-columns-above'] . '" ';
            }

            // sorting params
            $sorting_params = ' data-wrct-orderby="' . esc_html($data['query']['orderby']) . '" data-wrct-order="' . esc_html($data['query']['order']) . '" ';

            // classes for the table
            $classes = '';

            if (!empty($data['styling']['remove-other-name-borders-responsive'])) {
                $classes .= ' wrct-remove-other-name-borders-responsive';
            }

            if (!empty($data['styling']['cell-label-hide-responsive'])) {
                $classes .= ' wrct-cell-label-hide-responsive';
            }

            if (!empty($data['styling']['wrct-remove-other-name-borders-responsive'])) {
                $classes .= ' wrct-remove-other-name-borders-responsive';
            }

            foreach( array( 'pagination', 'sorting', 'filtering', 'search' ) as $facility ){
            	if( isset( $data['query'][$facility] ) ){
            		$classes .= ' wrct-'. $facility .'-enabled';
            	}
            }

            if (!empty($data['styling']['enable-responsive-layout-accordion'])) {
                $classes .= ' wrct-responsive-layout-accordion';
            }

            $table = '<table class="wrct-table wrct-table-' . $this->id . ' ' . $classes . '" data-wrct-selection-style="' . $selection_style . '" data-wrct-table-id="' . $this->id . '" ';
            $table .= 'data-wrct-currency-symbol="' . $this->currency_symbol . '" data-wrct-price-format="' . $this->price_format . '" ';
            $table .= 'data-wrct-cart-link="' . wc_get_cart_url() . '" data-wrct-strings="' . htmlentities(json_encode($strings)) . '" ';
            $table .= 'data-wrct-force-select="' . ($force_select ? 'true' : 'false') . '" data-wrct-responsive-layout-below="' . $responsive_layout_below . '" ';
            $table .= $responsive_columns_params . ' ' . $sorting_params . ' >';

            // header
            $table .= '<thead><tr class="wrct-heading-row">';
            foreach ($data['columns'] as $key => $col_info) {
                if (!empty($col_info['hide']))
                    continue;
                $data['columns'][$key]['heading'] = $this->place_icons($data['columns'][$key]['heading']);

                // limited possible sorting order
                $limited_order = false;
                if (in_array($col_info['name'], array(
                    'rating'
                ))) {
                    $limited_order = 'DESC';
                }

                // sorting icons
                $sorting_data  = '';
                $sorting_class = '';
                $sorting_icons = '';
                //-- check if sorting is permitted
                if (!empty($data['query']['sorting']) && !empty($col_info['sorting'])) {
                    $sorting_class = " wrct-sortable ";
                    $sorting_icons .= '<div class="wrct-sorting-icons">';
                    if (!$limited_order || $limited_order == 'ASC') {
                        $sorting_icons .= '<div class="wrct-sorting-asc-icon wrct-sorting-icon"></div>';
                    }
                    if (!$limited_order || $limited_order == 'DESC') {
                        $sorting_icons .= '<div class="wrct-sorting-desc-icon wrct-sorting-icon"></div>';
                    }
                    $sorting_icons .= '</div>';
                    // set asc or desc class
                    if ($data['query']['orderby'] == $col_info['name'] || ($col_info['name'] == 'name' && $data['query']['orderby'] == 'title')) {
                        if ($data['query']['order'] == 'ASC') {
                            $sorting_class .= " wrct-sorting-asc ";
                            $sorting_data .= ' data-wrct-current-order="ASC" ';
                        } else {
                            $sorting_class .= " wrct-sorting-desc ";
                            $sorting_data .= ' data-wrct-current-order="DESC" ';
                        }
                    }

                    // limited order
                    if ($limited_order) {
                        $sorting_data .= ' data-wrct-limited-order="' . $limited_order . '" ';
                    }
                }

                $table .= '<th class="wrct-heading ' . $sorting_class . '" data-wrct-name="' . $col_info['name'] . '" ' . $sorting_data . '>' . $data['columns'][$key]['heading'] . $sorting_icons . '</th>';
            }
            $table .= '</tr></thead><tbody>';

            // rows (courses)
            foreach ($data['courses'] as $key => $course_info) {

              $product = wc_get_product( $course_info['__id'] );

              // var_dump( $this->get_default_variation( $product ) );

              // stripped down variations data
              // $variations_culled = '';
              if ( ! empty( $course_info['__variations'] ) ) {

                  $available_variations = $course_info['__variations'];

                  $variations_arr = $course_info['__variations'];

                  foreach ($variations_arr as $key1 => &$val1) {
                    // capture values from this variation
                    $variable_attributes = $val1['__variable_attributes'];
                    $price               = $val1['display_price'];
                    $regular_price       = $val1['display_regular_price'];
                    $sales_price         = $price === $regular_price ? null : $price;
                    $stock               = $val1['__stock'] !== null ? $val1['__stock'] : "&#8734;";
                    $id                  = $val1['variation_id'];

                    $val1 = $val1['attributes'];

                    $url_args = "add-to-cart=" . $course_info['__id'] . "&variation_id=" . $id . "&";

                    // create new array for this variation
                    $new = array();
                    // remove "attribute_" from keys
                    foreach ($val1 as $key2 => &$val2) {
                        $url_args .= $key2 . "=" . urlencode($val2) . "&";
                        $key2_cleaned       = substr($key2, 10);
                        $new[$key2_cleaned] = $val2;
                    }

                    // feed captured values to this new array
                    $val1                    = $new;
                    $val1['__price']         = $price;
                    $val1['__regular-price'] = $regular_price;
                    $val1['__sale-price']    = $sales_price;
                    $val1['__stock']         = $stock;
                    $val1['__id']            = $id;
                    $val1['__url-args']      = rtrim($url_args, "&");
                  }

                  // $variations_culled     = 'data-wrct-variations = "' . esc_html(json_encode($variations_arr)) . '"';
                  $variations_data = 'data-product_variations="'. htmlspecialchars( wp_json_encode( $available_variations ) ) .'"';
                  $compressed_variations = 'data-wrct-compressed-variations = "' . esc_html(json_encode($this->compress_variations($variations_arr))) . '"';
                  $uncompressed_variations = 'data-wrct-uncompressed-variations = "' . esc_html(json_encode($variations_arr)) . '"';
                }else{
                  $variations_data = '';
                  $compressed_variations = '';
                  $uncompressed_variations = '';
                }

                if ($force_select) {
                    if (empty($course_info['__default-variation-id']) && isset($course_info['__psuedo-default-variation-id'])) {
                        $course_info['__default-variation-id'] = $course_info['__psuedo-default-variation-id'];
                        $course_info['__default-attributes']   = $course_info['__psuedo-default-attributes'];
                    }
                }

                // $default_variation_id = "";
                // $default_variation = false;
                // if ( ! empty( $course_info[ '__default-variation-id' ] ) ) {
                //     $default_variation_id = 'data-wrct-default-variation-id = "' . $course_info['__default-variation-id'] . '"';
                //     $default_variation = $this->get_variation( $default_variation_id, $variations_arr );
                // }

                $price_text_indicator = '';
                if (!empty($course_info['__cf']) && !empty($course_info['__cf']['wrct-price-text'])) {
                    $price_text_indicator = ' data-wrct-price-text="true" ';
                }

                $buttons_text_indicator = '';
                if (!empty($course_info['__cf']) && !empty($course_info['__cf']['wrct-buttons-text'])) {
                    $buttons_text_indicator = ' data-wrct-buttons-text="true" ';
                }

                $price_range = '';
                if (!empty($course_info['__max-variation-price']) && empty($course_info['__cf']['wrct-price-text'])) {
                    $price_range      = $this->formatted_price($course_info['__min-variation-price']) . " - " . $this->formatted_price($course_info['__max-variation-price']);
                    $data_price_range = ' data-wrct-price-range="' . $price_range . '" ';
                    $data_price_range .= ' data-wrct-min-price="' . wc_format_decimal($course_info['__min-variation-price'], false, true) . '" ';
                    $data_price_range .= ' data-wrct-max-price="' . wc_format_decimal($course_info['__max-variation-price'], false, true) . '" ';
                }
                $course_info['__price-range'] = $price_range;

                $missing_variation_attribute_column = false;
                foreach ($course_info['__variation-attributes'] as $key3 => $val3) {
                    $found = false;
                    foreach ($data['columns'] as $key4 => $val4) {
                        if ($val4['name'] === $val3) {
                            $found = true;
                        }
                    }

                    if (!$found) {
                        $missing_variation_attribute_column = true;
                    }
                }

                // begin creating row
                $row_classes = "";

                if($key % 2){
                  $row_classes .= " wrct-even ";
                }else{
                  $row_classes .= " wrct-odd ";
                }

                // $table .= '<tr class="wrct-row ' . $row_classes . '" '. $variations_data .' '. $uncompressed_variations .' ' . $compressed_variations . ' ' . $default_variation_id . ' data-wrct-product-id="' . $course_info['__id'] . '" data-wrct-_price="' . $course_info["_price"] . '" ' . $price_text_indicator . ' ' . $buttons_text_indicator . ' ' . $data_price_range . '>';
                $table .= '<tr class="wrct-row ' . $row_classes . '" '. $variations_data .' '. $uncompressed_variations .' ' . $compressed_variations . '  data-wrct-product-id="' . $course_info['__id'] . '" data-wrct-_price="' . $course_info["_price"] . '" ' . $price_text_indicator . ' ' . $buttons_text_indicator . ' ' . $data_price_range . '>';

                // columns
                foreach ($data['columns'] as $col_key => $col_info) {

                    // skip hidden columns
                    if ( ! empty( $col_info['hide'] ) ){
                        continue;
                    }

                    $name = esc_html($col_info['name']);

                    if ($name === 'name') {
                        $name_printed = true;
                    }

                    // css classes
                    $cell_classes = "";

                    // if (!$name_printed && !$name_column_hidden) {
                    //     $cell_classes .= "wrct-responsive-hide ";
                    // } else if (!$first_cell_passed) {
                    //     $cell_classes .= "wrct-responsive-first-cell ";
                    //     $first_cell_passed = true;
                    // } else if (!isset($data['columns'][$col_key + 1])) {
                    //     $cell_classes .= "wrct-responsive-last-cell ";
                    // }

                    //-- mark first cell in responsive layout mode
                    if(
                      //-- if this is the name cell
                      $col_info['name'] == 'name' ||
                      //-- or name cell does not exist and this is the first cell
                      ( ! $this->get_column('name') && ! isset( $data[ 'columns' ][ $col_key - 1 ] ) )
                    ){
                        $cell_classes .= "wrct-responsive-first-cell ";
                    }

                    //-- mark cells that come before name cell to be hidden in responsive accordion layout mode
                    if(
                      //-- if name cell exists
                      $this->get_column('name') &&
                      //-- and this cell comes before it
                      $this->get_column_key('name') > $col_key
                    ){
                        $cell_classes .= "wrct-responsive-accordion-hide ";
                    }

                    // mark last cell in responsive layout mode
                    if ( $this->last_visible_column()['name'] == $col_info['name'] ) {
                        $cell_classes .= "wrct-responsive-last-cell ";
                    }

                    $table .= '<td class="wrct-cell ' . $cell_classes . '" data-wrct-name="' . $name . '">';

                    // responsive heading
                    $col_heading = $col_info['heading'];
                    $table .= '<div class="wrct-cell-label wrct-hide wrct-responsive-show">' . ($col_info['name'] === 'name' ? $col_info['heading'] : $col_heading) . '</div>';

                    $val = isset($course_info[$col_info['name']]) ? $course_info[$col_info['name']] : '';
                    $table .= '<div class="wrct-cell-val">';

                    // buttons
                    if ($col_info['name'] === 'buttons') {

                        // if text is given instead of price, remove the cart and checkout buttons by default
                        if (is_array($val) && !empty($price_text_indicator)) {
                            foreach ($val as $key1 => &$val1) {
                                if (in_array($val1, array(
                                    '%cart%',
                                    '%ajax-cart%',
                                    '%to-cart%',
                                    '%checkout%'
                                ))) {
                                    $val1 = '';
                                }
                            }
                        }

                        // buttons replacement text
                        if ($buttons_text_indicator) {
                            $table .= '<div class="wrct-buttons-text">' . $course_info['__cf']['wrct-buttons-text'] . '</div>';
                        } else {

                            // button 1
                            if (!(isset($val['enable1']) && $val['enable1'] === 'off') && !empty($val['link1']) && !empty($val['label1'])) {
                                if (isset($val[$val['link1']])) {
                                    $link = $val[$val['link1']];
                                } else {
                                    $link = $val['link1'];
                                }
                                $target                = !empty($val['target1']) ? 'target="' . $val['target1'] . '"' : '';
                                $transaction_css_class = in_array($val['link1'], array(
                                    '%cart%',
                                    '%ajax-cart%',
                                    '%to-cart%',
                                    '%checkout%'
                                )) ? "wrct-transaction-button" : "";
                                $table .= '<a class="wrct-button ' . $transaction_css_class . '" data-wrct-link-code="' . $val['link1'] . '" data-wrct-href="' . $link . '" href="' . $link . '" ' . $target . '>' . $this->place_icons($val['label1']) . '</a>';
                            }

                            // button 2
                            if (!(isset($val['enable2']) && $val['enable2'] === 'off') && !empty($val['link2']) && !empty($val['label2'])) {
                                if (isset($val[$val['link2']])) {
                                    $link = $val[$val['link2']];
                                } else {
                                    $link = $val['link2'];
                                }
                                $target                = !empty($val['target2']) ? 'target="' . $val['target2'] . '"' : '';
                                $transaction_css_class = in_array($val['link2'], array(
                                    '%cart%',
                                    '%ajax-cart%',
                                    '%to-cart%',
                                    '%checkout%'
                                )) ? "wrct-transaction-button" : "";
                                $table .= '<a class="wrct-button ' . $transaction_css_class . '" data-wrct-link-code="' . $val['link2'] . '" data-wrct-href="' . $link . '" href="' . $link . '" ' . $target . '>' . $this->place_icons($val['label2']) . '</a>';
                            }

                            // button 3
                            if (!(isset($val['enable3']) && $val['enable3'] === 'off') && !empty($val['link3']) && !empty($val['label3'])) {
                                if (isset($val[$val['link3']])) {
                                    $link = $val[$val['link3']];
                                } else {
                                    $link = $val['link3'];
                                }
                                $target                = !empty($val['target3']) ? 'target="' . $val['target3'] . '"' : '';
                                $transaction_css_class = in_array($val['link3'], array(
                                    '%cart%',
                                    '%ajax-cart%',
                                    '%to-cart%',
                                    '%checkout%'
                                )) ? "wrct-transaction-button" : "";
                                $table .= '<a class="wrct-button ' . $transaction_css_class . '" data-wrct-link-code="' . $val['link3'] . '" data-wrct-href="' . $link . '" href="' . $link . '" ' . $target . '>' . $this->place_icons($val['label3']) . '</a>';
                            }

                            // notice
                            //-- print only if %cart% is used
                            /*
                            if (($val['link1'] === "%ajax-cart%" && $val['enable1'] === "on") || ($val['link2'] === "%ajax-cart%" && $val['enable2'] === "on") || ($val['link3'] === "%ajax-cart%" && $val['enable3'] === "on")) {
                                $product_in_cart_details = wrct_get_product_details_in_cart_including_variations($course_info['__id']);
                                if ($product_in_cart_details["quantity"]) {
                                    $notice_visible       = " style='display: block' ";
                                    $added_to_cart_string = str_replace("%number%", $product_in_cart_details["quantity"], $strings["added-to-cart"]);
                                    $cart_item_keys_arr   = esc_html((json_encode($product_in_cart_details["cart-item-keys-arr"])));
                                } else {
                                    $notice_visible       = "";
                                    $added_to_cart_string = $strings["added-to-cart"];
                                    $cart_item_keys_arr   = "";
                                }


                                $notice =  '<div class="wrct-notice" ' . $notice_visible . ' >

												<!--  added -->

												<div class="wrct-added" ' . $notice_visible . ' >
													<i class="wrct-icon fa fa-check"></i>
													<div class="wrct-text">' . $added_to_cart_string . '</div>
												</div>

												<!--  failed -->

												<div class="wrct-failed">
													<i class="wrct-icon fa fa-times"></i>
													<div class="wrct-text">' . $strings["failed-to-add-to-cart"] . '</div>
												</div>

												<!--  adding -->

												<div class="wrct-adding">
													<i class="wrct-icon fa fa-spin fa-refresh"></i>
													<div class="wrct-text">' . $strings["adding-to-cart"] . '</div>
												</div>

												<!--  removing -->

												<div class="wrct-removing">
													<i class="wrct-icon fa fa-spin fa-refresh"></i>
													<div class="wrct-text">' . $strings["removing-from-cart"] . '</div>
												</div>

												<!--  actions -->

												<div class="wrct-action" ' . $notice_visible . ' >
													<div class="wrct-cart-info" style="display: none;">
														<i class="wrct-icon fa fa-info-circle"></i>
														<div class="wrct-text">' . $strings["info"] . '</div>
													</div>
													<a class="wrct-remove-cart-item" href="' . wc_get_cart_url() . '" data-wrct-cart-item-keys-arr="' . $cart_item_keys_arr . '">
														<i class="wrct-icon fa fa-times"></i><div class="wrct-text">' . $strings["remove-from-cart"] . '</div>
													</a>
													<a class="wrct-go-to-cart" href="' . wc_get_cart_url() . '">
														<i class="wrct-icon fa fa-shopping-cart"></i><div class="wrct-text">' . $strings["go-to-cart"] . '</div>
													</a>
												</div>

											</div>';

                                $table .= str_replace(array(
                                    "\r",
                                    "\n",
                                    "\t",
                                    "  ",
                                    "   "
                                ), array(
                                    ""
                                ), $notice);

                            }

                            */

                        }

                        // price
                    } else if ($col_info['name'] === 'price') {

                        // price replacement text
                        if ($price_text_indicator) {
                            $table .= '<span class="wrct-price-text">' . $course_info['__cf']['wrct-price-text'] . '</span>';

                        // price numeric
                        } else {

                          // variable
                          if( $product->get_type() == 'variable' ){
                            $default_variation = $this->get_default_variation($product);
                            if( $default_variation ){
                              $table .= $default_variation['price_html'];
                            }else{
                              $table .= $product->get_price_html();
                            }

                          // simple
                          } else {
                            $table .= $product->get_price_html();
                          }

                        }

                        // featured image
                    } else if ($col_info['name'] === 'featured image') {
                        $alt = !empty($course_info['__alt']) ? $course_info['__alt'] : '';
                        $table .= '<img class="wrct-featured-image" src="' . $val . '" alt="' . $alt . '" />';

                        // variable - drop down
                    } else if ( strpos( $val, '|' ) !== false || in_array( $col_info['name'], $course_info['__variation-attributes'] ) ) {

                  			$attributes = $product->get_variation_attributes();
                  			$selected = false;

                        ob_start();

                        foreach ( $attributes as $attribute_name => $options ) {
                          if( $col_info['name'] !== strtolower( $attribute_name ) ){
                            continue;
                          }
                          // get selected val from defaults or url vars
                          $selected = isset( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ? wc_clean( stripslashes( urldecode( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ) ) : $product->get_variation_default_attribute( $attribute_name );
                          wc_dropdown_variation_attribute_options( array( 'class' => 'wrct-select', 'show_option_none' => $strings['choose'], 'options' => $options, 'attribute' => $attribute_name, 'product' => $product, 'selected' => $selected ) );
                        }

                        $table .= ob_get_clean();


                        // $val           = array_map('trim', explode('|', $val));
                        // $non_variation = ! in_array($col_info['name'], $course_info['__variation-attributes']);
                        //
                        // $table .= '<select class="wrct-select ' . ($non_variation ? ' wrct-non-variation ' : '') . '">';
                        //
                        // $default_attributes = isset($course_info['__default-attributes']) ? $course_info['__default-attributes'] : false;
                        //
                        // $table .= '<option class="wrct-reset-select" value>' . $strings["choose"] . '</option>';
                        //
                        // foreach ( $val as $attr_key => $attr_val ) {
                        //
                        //     if ( ! empty( $default_attributes ) ) {
                        //         // compressed attribute name
                        //         $attr_name_compressed = strtolower($col_info['name']);
                        //         $attr_name_compressed = str_replace(" ", "-", $attr_name_compressed);
                        //
                        //         if (!empty($default_attributes[$attr_name_compressed])) {
                        //             $select_val = $default_attributes[$attr_name_compressed];
                        //         }
                        //     }
                        //
                        //     // select the default variation value or first value in non variation select box
                        //     if ( ( ! empty( $select_val ) && $select_val === $attr_val ) || ( $non_variation && 0 === $attr_key ) ) {
                        //         $table .= '<option selected>' . $attr_val . '</option>';
                        //     } else {
                        //         $table .= '<option>' . $attr_val . '</option>';
                        //     }
                        //
                        // }
                        // $table .= '</select>';

                        if ( $selection_style === 'radio' ) {

                            $table .= '<div class="wrct-radio-container">';
                            $name = 'wrct-' . uniqid();
                            $options = array_map( "trim", explode( "|", $val ) );

                            foreach ( $options as $option_key => $option_val ) {

                                if ( ! empty( $selected ) && $selected === $option_val ) {
                                    $table .= '<div class="wrct-radio-set"><input class="wrct-radio-button" type="radio" value="' . $option_val . '" name="' . $name . '" checked="true"><div class="wrct-radio-label">' . $option_val . '</div></div>';
                                } else {
                                    $table .= '<div class="wrct-radio-set"><input class="wrct-radio-button" type="radio" value="' . $option_val . '" name="' . $name . '"><div class="wrct-radio-label">' . $option_val . '</div></div>';
                                }

                                $table .= '<div class="wrct-clear"></div>';
                            }

                            $table .= '</div>';

                        }

                    // name
                    } else if ($col_info['name'] === 'name') {

                        // featured image
                        if ($featured_image = $this->get_column('featured image') && empty($featured_image['hide'])) {
                            $alt = !empty($course_info['__alt']) ? $course_info['__alt'] : '';
                            $table .= '<img class="wrct-responsive-featured-image" src="' . $course_info['featured image'] . '" alt="' . $alt . '" />';
                        }

                        if ($featured_image) {
                            $table .= '<div class="wrct-adjacent">';
                        }

                        if ($course_name_link) {
                            // with course link
                            $course_link = isset($course_info['__cf']['wrct-link-course-name']) ? $course_info['__cf']['wrct-link-course-name'] : $course_info['__link'];
                            $table .= "<a class='wrct-link-course-name' href='" . $course_link . "'>" . (isset($course_info[$col_info['name']]) ? $course_info[$col_info['name']] : '') . "";
                        } else {
                            // without course link
                            $table .= (isset($course_info[$col_info['name']]) ? $course_info[$col_info['name']] : '');
                        }

                        // responsive drop-down summary
                        if (!empty($data['query']['responsive_summary_template'])) {

                            $table .= '<div class="wrct-responsive-summary">';
                            $table .= $this->parse_responsive_summary_template($data['query']['responsive_summary_template'], $course_info);
                            $table .= '</div>';
                        }

                        if ($featured_image) {
                            $table .= '</div>';
                        }

                        // responsive drop-down trigger
                        if ($featured_image) {
                            $table .= '<div class="wrct-responsive-trigger wrct-middle"></div>';
                        } else {
                            $table .= '<div class="wrct-responsive-trigger"></div>';
                        }

                    } else if ($col_info['name'] === 'rating') {

                        $table .= $this->get_rating_stars_html($course_info["__average-rating"]);

                        // $table .= "<div class='wrct-rating-number'>". $course_info["__average-rating"] ."</div>";

                    } else if ($col_info['name'] === 'quantity') {

                      $table .= woocommerce_quantity_input( array(
                				'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
                				'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
                				'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( $_POST['quantity'] ) : $product->get_min_purchase_quantity(),
                			), $product, false );

                    } else if ($col_info['name'] === 'stock') {

                        $table .= wc_get_stock_html( $product );

                    } else {

                        $table .= (isset($course_info[$col_info['name']]) ? $course_info[$col_info['name']] : '');

                    }

                    $table .= '</div>';

                    $table .= '</td>';

                }

                $table .= '</tr>';

            }

            $table .= '</tbody></table>';
        }

        // get rating starts markup
        public function get_rating_stars_html($rating = 0)
        {
            $full_stars  = floor($rating);
            $half_stars  = ceil($rating - $full_stars);
            $empty_stars = 5 - $full_stars - $half_stars;

            $markup .= "<div class='wrct-rating' title='" . $rating . "'>";
            foreach (array(
                $full_stars,
                $half_stars,
                $empty_stars
            ) as $key => $star_type) {
                while ($star_type) {
                    if ($key === 0) {
                        $markup .= "<i class='wrct-rating-star-full'></i>";
                    } else if ($key === 1) {
                        $markup .= "<i class='wrct-rating-star-half'></i>";
                    } else {
                        $markup .= "<i class='wrct-rating-star-empty'></i>";
                    }
                    --$star_type;
                }
            }
            $markup .= "</div>";

            return $markup;
        }

        // return string with %icon% replaced with Font Awesome icon html code
        public function place_icons($template, $marker = "%")
        {

            $marker = "%";

            if (!is_string($template) || strpos($template, $marker) === false) {
                return $template;
            }

            $keywords = $this->get_template_keywords($template, $marker);

            $values = array();

            foreach ($keywords as $keyword) {
                $values[] = '<i class="fa fa-' . $keyword . '""></i>';
            }

            foreach ($keywords as $key => &$val) {
                $val = $marker . $val . $marker;
            }

            return str_replace($keywords, $values, $template);

        }

        public function parse_responsive_summary_template($template = "", $course_info)
        {
            $marker = "%";

            if (!is_string($template) || strpos($template, $marker) === false) {
                return $template;
            }

            $keywords = $this->get_template_keywords($template, $marker);

            $values = array();

            foreach ($keywords as $keyword) {

                switch ($keyword) {
                    case "price-range":
                        if (!empty($course_info['__cf']['wrct-price-text'])) {
                            $values[] = "";
                        } else if (empty($course_info['__price-range'])) {
                            $values[] = $this->formatted_price($course_info['price']);
                        } else {
                            $values[] = $course_info['__price-range'] ? $course_info['__price-range'] : $course_info['__price'];
                        }
                        break;

                    case "rating":
                        $values[] = $this->get_rating_stars_html($course_info['__average-rating']);
                        break;

                    case "stock":
                        $values[] = $course_info["__stock"] !== null ? $course_info["__stock"] : "&#8734;";
                        break;

                    case "title":
                        $values[] = $course_info["name"];
                        break;

                    case "sale-price":
                        $values[] = $course_info["__sale-price"];
                        break;

                    case "regular-price":
                        $values[] = $course_info["__regular-price"];
                        break;

                    default:
                        // custom field
                        if ('cf:' == substr($keyword, 0, 3)) {
                            $cf_key   = trim(substr($keyword, 3));
                            $values[] = get_post_meta($course_info['__id'], $cf_key, true);

                            // attribute
                        } else if ('attr:' == substr($keyword, 0, 5)) {
                            $attr = trim(strtolower(substr($keyword, 5)));
                            if (isset($course_info[$attr])) {
                                $values[] = $course_info[$attr];
                            }

                            // value not determined
                        } else {
                            $values[] = "";
                        }

                }
            }

            foreach ($keywords as $key => &$val) {
                $val = $marker . $val . $marker;
            }

            return str_replace($keywords, $values, $template);

        }

        public function get_template_keywords($template, $marker = "%")
        {
            $template = html_entity_decode($template);

            $count      = strlen($template);
            $index      = 0;
            $marker_len = strlen($marker);

            $keywords            = array();
            $keyword_start_index = false;

            if ($count > ($marker_len * 2 + 1)) {
                while ($index < $count) {
                    $char = substr($template, $index, $marker_len);
                    if ($char === $marker) {
                        if (false === $keyword_start_index) {
                            // found start of a new keyword
                            $keyword_start_index = $index + $marker_len; // exclude %
                        } else {
                            // found end of the current keyword
                            $keyword_end_index   = $index - $keyword_start_index; // exclude %
                            $keywords[]          = trim(substr($template, $keyword_start_index, $keyword_end_index));
                            $keyword_start_index = false;
                        }
                    }
                    $index++;
                }
            }

            return $keywords;
        }

        // returns formatted price with currency symbol and price correctly positioned
        public function formatted_price($price)
        {
            $price = wc_format_decimal($price, false, true);
            return str_replace(array(
                '%1$s',
                '%2$s',
                '&nbsp;'
            ), array(
                $this->currency_symbol,
                $price,
                ' '
            ), $this->price_format);
        }

        // get column from $this->data via col name
        public function get_column($column_name)
        {
            foreach ($this->data['columns'] as $col_info) {
                if ($column_name === $col_info['name']) {
                    return $col_info;
                }
            }
        }

        // get last column that is not hidden
        public function last_visible_column(){
          $last_col_key = 0;

          foreach ($this->data['columns'] as $col_key => $col_info) {
              if ( empty( $col_info['hide'] ) ) {
                  $last_col_key = $col_key;
              }
          }

          return $this->data['columns'][$last_col_key];
        }

        // get column key from $this->data via col name
        public function get_column_key($column_name)
        {
            $ret_col_key = false;
            foreach ($this->data['columns'] as $col_key=> $col_info) {
                if ($column_name === $col_info['name']) {
                    $ret_col_key = $col_key;
                    break;
                }
            }

            return $ret_col_key;
        }

        public function compress_variations($variations_arr)
        {
            $c_variations_arr = array(
                'keys' => array(),
                'values' => array(),
                'variations' => array(),
            );

            $c_keys =& $c_variations_arr['keys'];
            $c_values =& $c_variations_arr['values'];
            $c_variations =& $c_variations_arr['variations'];

            foreach ($variations_arr as $index=> $variation) {

                $c_variations[]    = array();

                foreach ($variation as $key => $val) {

                    if ("__url-args" == $key) {
                        continue;
                    }

                    $key_index = array_search($key, $c_keys, true);
                    if ($key_index === false) {
                        $c_keys[] = $key;
                        $key_index = count($c_keys) - 1;
                    }

                    $val_index = array_search($val, $c_values, true);

                    // if($key === "__stock"){
                    //   echo "initial \$val: $val | \$val_index: $val_index | val at index: $c_values[$val_index]<br>";
                    // }


                    if ($val_index === false) {
                      $c_values[] = $val;
                      $val_index = count($c_values) - 1;
                    }

                    // if($key === "__stock"){
                    //   echo " \$key: $key | \$val: $val <br>";
                    //   echo " \$key_index: $key_index | \$val_index: $val_index <br><br>";
                    // }

                    $c_variations[$index][$key_index] = $val_index;
                }
            }

            return $c_variations_arr;
        }

        public function get_variation($id= false, $variations_arr= false )
        {
          if( ! $id || ! $variations_arr ) return false;

          $variation = false;

          foreach( $variations_arr as $curr_variation ){
            if( $curr_variation['__id'] === $id ){
              $variation = $curr_variation;
              break;
            }
          }

          return $variation;
        }

        public function get_default_variation ( $product )
        {

          if( $product->get_type() != "variable" ){
            return false;
          }

          $default_variation = false;
          $default_attributes = array();

          // var_dump( $product->get_variation_attributes() );

          foreach( $product->get_variation_attributes() as $attribute_name=> $attribute_options ){
            $default_attributes["attribute_" . strtolower($attribute_name)] = $product->get_variation_default_attribute( $attribute_name );
          }

          // echo "\$default_attributes <br>";
          // var_dump( $default_attributes );
          // echo "<br>";

          foreach( $product->get_available_variations() as $key=> $variation ){

            // echo "\$default_attributes <br>";
            // var_dump( $variation['attributes'] );
            // echo "<br>";

            if( $default_attributes == $variation['attributes'] ){
              $default_variation = $variation;
              break;
            }
          }

          return $default_variation;
        }

    }

}
?>
