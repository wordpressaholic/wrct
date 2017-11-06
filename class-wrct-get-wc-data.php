<?php
/*
 * WRCT Get WC Data
 * This class will take a data array containing structure and information
 * for the table and it will update that object's values with current WC info
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if( ! class_exists('WRCT_Get_WC_Data') ){

class WRCT_Get_WC_Data
{
  // user defined data structure for the table
  public $data;

  // additional query args provided by user
  public $query_args;

  // if user wants a specific category
  public $category;

	// column names that are reserved
	private $reserved_column_names_arr = array( 'name', 'price', 'buttons', 'featured image', 'quantity', 'stock', 'rating' );

  // constructor
  function __construct(
    $data= false,
    $query_args= false,
    $max_posts= false,
    $category= false,
    $id= false,
    $search= false,
    $pagination= false,
    $sorting= false,
    $sort_enabled_columns= false,
		$default_orderby= false,
    $filtering= false,
    $buttons_default= false,
    $course_ids= false,
    $related_products= false,
    $up_sells= false,
    $cross_sells= false
  ) {

    if( ! $data ){
      $data = array();
    }

    $data['courses'] = array();

    if( empty( $data['columns'] ) ){

      $data['columns'] = array(
        array(
          'heading'=> __( 'Course', 'wrct' ),
          'name'=> 'name',
        ),
        array(
          'heading'=> __( 'Price', 'wrct' ),
          'name'=> 'price',
        ),
        array(
          'heading'=> '',
          'name'=> 'buttons',
        ),
      );

    }

    if( empty( $data['buttons'] ) ){

      $data['buttons'] = array(
        'button-1-enable' => 'on',
        'button-1-label' => __( 'More info', 'wrct' ),
        'button-1-link' => '%post%',
        'button-1-target' => '',

        'button-2-enable' => 'on',
        'button-2-label' => __( 'Add to cart', 'wrct' ),
        'button-2-link' => '%ajax-cart%',
        'button-2-target' => '',

        'button-3-enable' => 'off',
        'button-3-label' => __( 'Checkout', 'wrct' ),
        'button-3-link' => '%to-cart%',
        'button-3-target' => '',
      );

    }

    if( empty( $data['styling'] ) ){
      $data['styling'] = array();
    }

    // query args from ui
    if( empty( $data['query'] ) ){
      $data['query'] = array( );
    }

    if( ! empty( $data['query']['categories'] ) ){
      $this->category = $data['query']['categories'];
    }

    if( ! empty( $data['query']['max_posts'] ) ){
      $this->max_posts = $data['query']['max_posts'];
    }

		if( ! empty( $data['query']['course_ids'] ) ){
      $this->course_ids = $data['query']['course_ids'];
    }

		if( ! empty( $data['query']['query_args'] ) ){
      $this->query_args = $data['query']['query_args'];
    }

		if( ! empty( $data['query']['default_orderby'] ) ){
      $this->default_orderby = $data['query']['default_orderby'];
    }

    $this->data = apply_filters( 'initial_data_in_wrct_get_wc_data_' . $id, $data );

    // make column names lowercase
    foreach( $this->data[ 'columns' ] as $key=> &$column ){
      $column['name'] = strtolower( $column['name'] );
    }

    $this->search = $search;
    $this->pagination = $pagination;

    // shortcode args supersede ui args
    $this->query_args = $query_args ? $query_args : $this->query_args;
    $this->max_posts = $max_posts ? $max_posts : $this->max_posts;
    $this->category = $category ? $category : $this->category;
    $this->id = $id ? $id : $this->id;

    $this->sorting = $sorting ? $sorting : $this->sorting;
    $this->sort_enabled_columns = $sort_enabled_columns ? $sort_enabled_columns : $this->sort_enabled_columns;
    $this->default_orderby = $default_orderby ? $default_orderby : $this->default_orderby;
    $this->filtering = $filtering ? $filtering : $this->filtering;
    $this->course_ids = $course_ids ? $course_ids: $this->course_ids;
    $this->related_products = $related_products;
    $this->up_sells = $up_sells;
    $this->cross_sells = $cross_sells;

		$more_info_string = __( "More info", "wrct" );
		$add_to_cart_string = __( "Add to cart", "wrct" );
		$checkout_string = __( "Checkout", "wrct" );

		if( count( $buttons_default ) ){

      $this->buttons_default = array(
        'enable1' => isset( $buttons_default['enable1'] ) ? $buttons_default['enable1'] : ( isset( $data['buttons']['button-1-enable'] ) ? $data['buttons']['button-1-enable'] : "on" ),
        'enable2' => isset( $buttons_default['enable2'] ) ? $buttons_default['enable2'] : ( isset( $data['buttons']['button-2-enable'] ) ? $data['buttons']['button-2-enable'] : "on" ),
        'enable3' => isset( $buttons_default['enable3'] ) ? $buttons_default['enable3'] : ( isset( $data['buttons']['button-3-enable'] ) ? $data['buttons']['button-3-enable'] : "off" ),

				'link1' => isset( $buttons_default['link1'] ) ? $buttons_default['link1'] : ( isset( $data['buttons']['button-1-link'] ) ? $data['buttons']['button-1-link'] : "%post%" ),
        'link2' => isset( $buttons_default['link2'] ) ? $buttons_default['link2'] : ( isset( $data['buttons']['button-2-link'] ) ? $data['buttons']['button-2-link'] : "%ajax-cart%" ),
        'link3' => isset( $buttons_default['link3'] ) ? $buttons_default['link3'] : ( isset( $data['buttons']['button-3-link'] ) ? $data['buttons']['button-3-link'] : "%checkout%" ),

				'label1' => isset( $buttons_default['label1'] ) ? $buttons_default['label1'] : ( isset( $data['buttons']['button-1-label'] ) ? $data['buttons']['button-1-label'] : $more_info_string ),
        'label2' => isset( $buttons_default['label2'] ) ? $buttons_default['label2'] : ( isset( $data['buttons']['button-2-label'] ) ? $data['buttons']['button-2-label'] : $add_to_cart_string ),
        'label3' => isset( $buttons_default['label3'] ) ? $buttons_default['label3'] : ( isset( $data['buttons']['button-3-label'] ) ? $data['buttons']['button-3-label'] : $checkout_string ),

				'target1' => isset( $buttons_default['target1'] ) ? $buttons_default['target1'] : ( isset( $data['buttons']['button-1-target'] ) ? $data['buttons']['button-1-target'] : "" ),
        'target2' => isset( $buttons_default['target2'] ) ? $buttons_default['target2'] : ( isset( $data['buttons']['button-2-target'] ) ? $data['buttons']['button-2-target'] : "" ),
        'target3' => isset( $buttons_default['target3'] ) ? $buttons_default['target3'] : ( isset( $data['buttons']['button-3-target'] ) ? $data['buttons']['button-3-target'] : "" ),
      );

    }else{

			$this->buttons_default = array(
        'enable1' => isset( $data['buttons']['button-1-enable'] ) ? $data['buttons']['button-1-enable'] : "on",
        'enable2' => isset( $data['buttons']['button-2-enable'] ) ? $data['buttons']['button-2-enable'] : "on",
        'enable3' => isset( $data['buttons']['button-3-enable'] ) ? $data['buttons']['button-3-enable'] : "off",

				'link1' => isset( $data['buttons']['button-1-link'] ) ? $data['buttons']['button-1-link'] : "%post%",
        'link2' => isset( $data['buttons']['button-2-link'] ) ? $data['buttons']['button-2-link'] : "%ajax-cart%",
        'link3' => isset( $data['buttons']['button-3-link'] ) ? $data['buttons']['button-3-link'] : "%checkout%",

				'label1' => isset( $data['buttons']['button-1-label'] ) ? $data['buttons']['button-1-label'] : $more_info_string,
        'label2' => isset( $data['buttons']['button-2-label'] ) ? $data['buttons']['button-2-label'] : $add_to_cart_string,
        'label3' => isset( $data['buttons']['button-3-label'] ) ? $data['buttons']['button-3-label'] : $checkout_string,

				'target1' => isset( $data['buttons']['button-1-target'] ) ? $data['buttons']['button-1-target'] : "",
        'target2' => isset( $data['buttons']['button-2-target'] ) ? $data['buttons']['button-2-target'] : "",
        'target3' => isset( $data['buttons']['button-3-target'] ) ? $data['buttons']['button-3-target'] : "",
      );

		}

    $cache_old_columns = $this->data['columns'];

    $this->update();

    $this->maintain_column_order( $cache_old_columns, $this->data['columns'] );

    $this->data = apply_filters( 'eventual_data_in_wrct_get_wc_data_' . $id, $this->data );
  }

  // update the data object with current WC data
  public function update( ) {
    $data =& $this->data;
    $query_args =& $this->query_args;
    $max_posts =& $this->max_posts;
    $category =& $this->category;
    $id =& $this->id;
    $search =& $this->search;
    $pagination =& $this->pagination;
    $sorting =& $this->sorting;
    $sort_enabled_columns =& $this->sort_enabled_columns;
    $default_orderby =& $this->default_orderby;
    $filtering =& $this->filtering;
    $buttons_default =& $this->buttons_default;
    $course_ids =& $this->course_ids;
    $related_products =& $this->related_products;
    $up_sells =& $this->up_sells;
    $cross_sells =& $this->cross_sells;

    $placeholder_img = wc_placeholder_img_src();

    $args = array(
      'post_type' => 'product',
      'posts_per_page' => false,
			'post_status' => 'publish',
    );

		// additional inline args by user
    $args = wp_parse_args( $query_args, $args );

    // max posts
    if( ! empty( $max_posts ) ){
      $args['posts_per_page'] = $max_posts;
    }else if( empty( $args['posts_per_page'] ) ){
      $args['posts_per_page'] = -1;
    }

    // pagination
    if( $pagination ){
      if(empty($args['posts_per_page'])){
        $args['posts_per_page'] = 10;
      }
      $paged = 1;
      if( ! empty( $_REQUEST['wrct-paged-'. $id] ) ){
        $paged = (int)$_REQUEST['wrct-paged-'. $id];
      }
      $args['paged'] = $paged;
			$data['query']['paged'] = $paged;
    }

    // search
    if( $search && ! empty( $_REQUEST['wrct-search-'. $id] ) ){
      $args['s'] = sanitize_text_field( urldecode( $_REQUEST['wrct-search-'. $id] ) );
    }

    // category
    if( empty( $category ) ){
      // if no cat is given, use first cat with any posts
      $cat_args = array(
          'orderby'    => 'name',
          'order'      => 'ASC',
          'hide_empty' => true,
      );

      $product_categories = get_terms( 'product_cat', $cat_args );

      if( ! empty($product_categories) ){
        $category = $product_categories[0]->name;
      }
    }

    if( ! empty( $category ) ){

      if( ! isset( $args['tax_query'] ) ){
        $args['tax_query'] = array();
      }

      if( ! is_array( $category ) ){
        $category = explode(",", $category);
      }

      $args['tax_query'][] = array(
        'taxonomy' => 'product_cat',
        'field'    => 'name',
        'terms'    => $this->category,
      );

    }

		// show relates posts
		if( $related_products ){
			global $post;
			$course_ids = wc_get_related_products( $post->ID, $args['posts_per_page'] );
		}

		// show cross sells
		if( $cross_sells ){
			$course_ids = WC()->cart->get_cross_sells( );
		}

		// show up sells
		if( $up_sells ){
			global $post;
			$product = wc_get_product($post);
			$course_ids = $product->get_upsell_ids();
		}

		// print courses specified by ids
    if( $course_ids ){
      if( ! is_array($course_ids) && trim( $course_ids ) === 'current' ){
        global $post;
				if( isset( $post ) && get_post_type( $post ) === "product" ){
        	$args[ 'post__in' ] = array( $post->ID );
				}
      }else if( ! is_array( $course_ids ) ){
        $args[ 'post__in' ] = explode(",", $course_ids);
        $args[ 'post__in' ] = array_map( 'trim', $args[ 'post__in' ] );
      }else{
				$args[ 'post__in' ] = $course_ids;
			}
      $args[ 'orderby' ] = 'post__in';

			// taxonomy query will limit to courses in those cats
			unset( $args['tax_query'] );
    }

		// sorting
		$orderby_value = $default_orderby ? $default_orderby : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby' ) );

		if( $sorting && ! empty( $_REQUEST['wrct-orderby-'. $id] ) ){
			$orderby_value = isset( $_REQUEST['wrct-orderby-'. $id] ) ? sanitize_text_field( $_REQUEST['wrct-orderby-'. $id] ) : $orderby_value;
			$order = ! empty( $_REQUEST['wrct-order-'. $id] ) ?  sanitize_text_field( $_REQUEST['wrct-order-'. $id] ) : '';
		}

		$orderby_value = explode( '-', $orderby_value );
		$orderby       = esc_attr( $orderby_value[0] );
		if( empty( $order ) ){
			$order = ! empty( $orderby_value[1] ) ? $orderby_value[1] : 'ASC';
		}

		$orderby = strtolower( $orderby );
		$order   = strtoupper( $order );
		$args['meta_key'] = '';

		if( $orderby == 'name' ){
			$orderby = 'title';
		}

		if( $orderby == 'price' && ! $order ){
			$order = 'ASC';
		}

		if( $sorting ){
			$unsortable_columns = array( 'featured image', 'buttons', 'quantity', 'stock' );
			$always_sortable_columns = array( 'name', 'rating', 'price' );
			foreach( $data['columns'] as $key=>& $col_info ){
				if(
					! in_array( $col_info['name'], $unsortable_columns ) && // not an unsortable column
					( empty( $sort_enabled_columns ) || in_array( $col_info['name'], $sort_enabled_columns ) ) &&  // within user determined sort columns arr if that is defined
					( in_array( $col_info['name'], $always_sortable_columns ) || ( isset( $col_info['type'] ) && $col_info['type'] === 'custom field' ) )
				){
					$col_info['sorting'] = true;
				}
			}
			unset( $col_info );
		}

		switch ( $orderby ) {
			case 'rand' :
				$args['orderby']  = 'rand';
				break;
			case 'date' :
				$args['orderby']  = 'date ID';
				$args['order']    = ( 'ASC' === $order ) ? 'ASC' : 'DESC';
				break;
			case 'price' :
				if ( 'DESC' === $order ) {
					add_filter( 'posts_clauses', array( $this, 'order_by_price_desc_post_clauses' ) );
				} else {
					add_filter( 'posts_clauses', array( $this, 'order_by_price_asc_post_clauses' ) );
				}
				break;
			case 'popularity' :
				$args['meta_key'] = 'total_sales';

				// Sorting handled later though a hook
				add_filter( 'posts_clauses', array( $this, 'order_by_popularity_post_clauses' ) );
				break;
			case 'rating' :
				$order = 'DESC'; // forced
				$args['meta_key'] = '_wc_average_rating';
				$args['orderby']  = array(
					'meta_value_num' => $order,
					'ID'             => 'ASC',
				);
				break;
			case 'title' :
				$args['orderby'] = 'title';
				$args['order']   = ( 'DESC' === $order ) ? 'DESC' : 'ASC';
				break;
			default:
				$col_info = $this->get_column_by_name( $orderby );
				if( $col_info && ! empty( $col_info['sorting'] ) ){
					$args['orderby'] = $orderby;
					$args['order'] = $order;

					// custom field
					if( isset( $col_info['type'] ) && $col_info['type'] == 'custom field' ){
						$args['meta_key'] = $col_info['name'];
						if( $col_info['val_type'] == 'number' ){
							$args['orderby'] = 'meta_value_num';
						}else{
							$args['orderby'] = 'meta_value';
						}
					}

				}
		}

		// WRCT_Create_Table will need these $vars;
		$data['query']['orderby'] = $orderby;
		$data['query']['order'] = $order;

    global $woocommerce;
    global $wp;

		$current_url = home_url( $wp->request ) . "/";
    $cart_url = wc_get_cart_url();
    $checkout_url = wc_get_checkout_url();

    $loop = new WP_Query($args);

		$this->remove_filters_posts_clauses();

    if ( $loop->have_posts() ) {

    	$course_index = 0;

      while ( $loop->have_posts() ){
        $loop->the_post();

        global $post;
        $product = wc_get_product( $post );

				// echo "<pre>";
				// var_dump($product);
				// echo "</pre>";

        $data['courses'][$course_index] = array(
          '__id'=> $post->ID,
          '__link'=> get_permalink( $post->ID ),
          'name'=> $post->post_title,
          '_price'=> get_post_meta($post->ID, "_price", true),
          'buttons'=> $buttons_default,
          'featured image'=> ( has_post_thumbnail( $post->ID ) ? get_the_post_thumbnail_url( $post->ID, 'thumbnail' ) : $placeholder_img ),
        );

        $thumbnail_id = get_post_thumbnail_id( $post->ID );
        if($thumbnail_id){
          $data['courses'][$course_index]['__alt'] = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);
        }

        // interpret button link special codes
        if($data['courses'][$course_index]['buttons']){
          // more info
          $data['courses'][$course_index]['buttons']['%post%'] = get_the_permalink($post->ID);

          if( isset( $_REQUEST[ "wrct-paged-" . $id ] ) ){
            $link_args[ "wrct-paged-" . $id ] = (int) $_REQUEST[ "wrct-paged-" . $id ];
          }

					// cart
          $data['courses'][$course_index]['buttons']['%cart%'] = $current_url;
					// ajax cart
          $data['courses'][$course_index]['buttons']['%ajax-cart%'] = $current_url;
          // add and go to cart
          $data['courses'][$course_index]['buttons']['%to-cart%'] = $cart_url;
          // add and go to checkout
          $data['courses'][$course_index]['buttons']['%checkout%'] = $checkout_url;
        }

				// get custom fields
				foreach( $data['columns'] as $col => &$col_info ){
					if( isset( $col_info['type'] ) && $col_info['type'] === 'custom field' ){
						$cf_val = get_post_meta( $post->ID, $col_info['name'], true );
						$data['courses'][$course_index][ $col_info['name'] ] = $cf_val;
					}
				}

        // get all course attributes
        $attributes = $product->get_attributes( );
				$variable_attributes = [];
        // save each of those attributes to the data object
        foreach( $attributes as $key=> $val ){
          // update course value for attribute
          $data[ 'courses' ][ $course_index ][ strtolower( $val['name'] ) ] = $val[ 'value' ];
          // create attribute column if it does not exist
          $col_exists = false;
          foreach( $data['columns'] as $col_index=> &$col_info ){
            if( strtolower( $col_info['name'] ) === strtolower( $val['name'] ) ){
              $col_info[ '__wanted' ] = true;
              $col_exists = true;
              break;
            }
          }
          if( ! $col_exists){
            $data['columns'][] = array(
              'name'=> strtolower( $val['name'] ),
              'heading'=> $val['name'],
              '__wanted'=> true,
            );
          }
					// gather variable attribute
					if( $val['variation'] ){
						$variable_attributes[] = $key;
					}
        }

				$col_info[ '__variable_attributes' ] = $variable_attributes;

				// rating
				$data['courses'][$course_index]['__average-rating'] = $product->get_average_rating();

        // get the prices for variations of the product
        $data['courses'][$course_index]['__default-attributes'] = array();
        $data['courses'][$course_index]['__default-variation-id'] = false;
        $data['courses'][$course_index]['__variation-attributes'] = array();

        if( $product->is_type( 'variable' ) && $product->has_child() ){

					$data['courses'][$course_index]['__max-variation-price'] = $product->get_variation_regular_price("max");
					$data['courses'][$course_index]['__min-variation-price'] = $product->get_variation_sale_price("min");

          // save default values
          $data['courses'][$course_index]['__default-attributes'] = $product->get_default_attributes();
          $data['courses'][$course_index]['__variation-attributes'] = array_map( "strtolower", array_keys( $product->get_variation_attributes() ) );
          // get an array of the default product attributes for comparison with the variations to arrive at the default variation
          // $default_attributes_of_product = array();
          // foreach($data['courses'][$course_index]['__default-attributes'] as $key=> $val){
          //   $default_attributes_of_product['attribute_' . $key] = $val;
          // }

          $data['courses'][$course_index]['__variations'] = $product->get_available_variations();

					// echo "<script>console.log(". json_encode($data['courses'][$course_index]['__variations']) .");</script>";

					// $unset_variations = array();

					// qualify variations and collect some additional data
          // foreach($variations_arr as $key=> $variation){
					//
					// 	$variation_product = wc_get_product( $variation["variation_id"] );
					//
					// 	if(
					// 			$variation_product->get_stock_status() !== "instock" ||
					// 			$variation_product->get_catalog_visibility() !== "visible" ||
					// 			$variation_product->get_status() !== "publish" ||
					// 			! $variation_product->has_enough_stock() ||
					// 			! $variation_product->is_purchasable()
					// 	){
					// 		$unset_variations[] = $key;
					// 	}else{
					// 		$variations_arr[$key]['__stock'] = $variation_product->get_stock_quantity();
					// 	}
					//
          // }

					// remove unqualified variations
					// foreach( $unset_variations as $unset_key ){
					// 	unset( $variations_arr[ $unset_key ] );
					// }

					// get the default variation
          // foreach($variations_arr as $key=> $variation){
            // check if this is the default variation, so we can use it as default for attrs and price
          //   if($default_attributes_of_product === $variation['attributes']){
					// 		$data['courses'][$course_index]['__default-variation-id'] = $variation['variation_id'];
          //   }
          // }

					// if no variation is set as default, use the first one as default
					// if( ! $data['courses'][$course_index]['__default-variation-id'] && ! empty( $variations_arr[0] ) ){
					// 	$data['courses'][$course_index]['__psuedo-default-variation-id'] = $variations_arr[0]['variation_id'];
					// 	foreach ($variations_arr[0]['attributes'] as $key1 => $value1) {
					// 		$data['courses'][$course_index]['__psuedo-default-attributes'][substr($key1, 10)] = $value1;
					// 	}
					// 	$var_product = wc_get_product($variations_arr[0]['variation_id']);
					// 	$data['courses'][$course_index]['price'] = $var_product->get_price();
					// 	$data['courses'][$course_index]['__regular-price'] = $var_product->get_regular_price();
					// 	$data['courses'][$course_index]['__sale-price'] = $var_product->get_sale_price();
					// 	$data['courses'][$course_index]['__stock'] = $var_product->get_stock_quantity();
					// }

        }else{
          // simple product
          $data['courses'][$course_index]['price'] = $product->get_price();
          $data['courses'][$course_index]['__regular-price'] = $product->get_regular_price();
          $data['courses'][$course_index]['__sale-price'] = $product->get_sale_price();
          $data['courses'][$course_index]['__stock'] = $product->get_stock_quantity();
        }

        // custom fields
        $data['courses'][$course_index]['__cf'] = array();

        $price_text = get_post_meta($product->get_id(), 'wrct-price-text', true);
        if($price_text){
          $data['courses'][$course_index]['__cf']['wrct-price-text'] = $price_text;
        }

        $buttons_text = get_post_meta($product->get_id(), 'wrct-buttons-text', true);
        if($buttons_text){
          $data['courses'][$course_index]['__cf']['wrct-buttons-text'] = $buttons_text;
        }

				$course_link = get_post_meta($product->get_id(), 'wrct-link-course-name', true);
        if($course_link){
          $data['courses'][$course_index]['__cf']['wrct-link-course-name'] = $course_link;
        }

        $course_index++;
      }

			wp_reset_postdata();
    }

    $data['loop'] = array('max_num_pages' => $loop->max_num_pages );
    wp_reset_postdata();
    $data['columns'] = $this->remove_unwanted_columns($data['columns']);

  }

  // get array of IDs from the courses array
  public function get_course_ids($courses){
    $ids = array();
    foreach($courses as $key=> $val){
      $ids[] = $val['__id'];
    }
    return $ids;
  }

  // goes over columns and removes all without 'wanted' field
  function remove_unwanted_columns($columns){
    $columns_ = $columns;
    foreach($columns_ as $key=> $col_info){
			// skip primary columns
      if( in_array( $col_info['name'], $this->reserved_column_names_arr ) ){
        continue;
      }
			// skip custom fields
			if( $col_info['type'] === 'custom field' ){
        continue;
      }

			// remove columns with no values
      if(empty( $col_info['__wanted'] )){
          unset($columns[$key]);
      }else{
        unset($columns[$key]['__wanted']);
      }
    }
    $columns = array_values($columns); // removes missed keys, else JSON converts columns to object
    return $columns;
  }

  // attempts to mimic order of older columns
  function maintain_column_order( $old_columns, &$new_columns ){

    $ordered_columns = array( );
    $remaining_columns = array( );

    foreach( $old_columns as $key => &$old_column ){
      foreach( $new_columns as $key2 => &$new_column ){
        if( $new_column['name'] === $old_column['name'] ){
          // match
          $new_column['ordered'] = true;
          $ordered_columns[] = $new_column;
        }
      }
    }

    foreach( $new_columns as $key => &$new_column ){
      if( ! isset( $new_column['ordered'] ) ){
        $remaining_columns[] = $new_column;
      }
    }

    foreach( $ordered_columns as $key => &$ordered_column ){
      unset( $ordered_column['ordered'] );
    }

    if( count( $remaining_columns ) ){

      $count = count( $ordered_columns );
      // iterate over last two elements of ordered columns in reverse
      $key = $count - 1; // last key of ordered columns

      while( $key ){

        $column =& $ordered_columns[ $key ];
        if( ! in_array( $column['name'], array( 'buttons', 'price' ) ) ){
          break;
        }

        --$key;
      }

      // remaining columns need to be dumped at $ordered_columns[ $key ] on onwards
      array_splice( $ordered_columns, $key + 1, 0, $remaining_columns );

    }

    $new_columns = $ordered_columns;

  }

	public function add_filters_posts_clauses(){
//		add_filter( 'posts_clauses', array( $this, 'order_by_price_asc_post_clauses' ) );
	}

	public function remove_filters_posts_clauses(){
		remove_filter( 'posts_clauses', array( $this, 'order_by_price_asc_post_clauses' ) );
		remove_filter( 'posts_clauses', array( $this, 'order_by_price_desc_post_clauses' ) );
		remove_filter( 'posts_clauses', array( $this, 'order_by_popularity_post_clauses' ) );
	}

	public function order_by_price_asc_post_clauses( $args ) {
		global $wpdb;
		$args['join']    .= " INNER JOIN ( SELECT post_id, min( meta_value+0 ) price FROM $wpdb->postmeta WHERE meta_key='_price' GROUP BY post_id ) as price_query ON $wpdb->posts.ID = price_query.post_id ";
		$args['orderby'] = " price_query.price ASC ";
		return $args;
	}

	public function order_by_price_desc_post_clauses( $args ) {
		global $wpdb;
		$args['join']    .= " INNER JOIN ( SELECT post_id, max( meta_value+0 ) price FROM $wpdb->postmeta WHERE meta_key='_price' GROUP BY post_id ) as price_query ON $wpdb->posts.ID = price_query.post_id ";
		$args['orderby'] = " price_query.price DESC ";
		return $args;
	}

	public function order_by_popularity_post_clauses( $args ) {
		global $wpdb;
		$args['orderby'] = "$wpdb->postmeta.meta_value+0 DESC, $wpdb->posts.post_date DESC";
		return $args;
	}

	private function get_column_by_name($name){
		foreach( $this->data['columns'] as $col_info ){
			if( $col_info['name'] === $name ){
				return $col_info;
			}
		}

		return false;
	}

}

}
?>
