<?php
  if ( ! defined( 'ABSPATH' ) ) {
  	exit; // Exit if accessed directly
  }
?>

<div class="wrct-editor-clear"></div>
<h1 class="wrct-page-title dashicons-before dashicons-editor-justify">
  <?php _e( "WooCommerce Course Tables", "wrct" ); ?>
</h1>

<div class="wrct-editor-clear"></div>
<span class="wrct-table-title-label"><?php _e( "Table name", "wrct" ); ?>:</span>
<input type="text" class="wrct-table-title" name="" value="<?php echo (isset($_GET['post_id']) ? get_the_title( (int) $_GET['post_id'] ) : ''); ?>" placeholder="Enter name here..." />
<br>
<span class="wrct-sc-display-label"><?php _e( "Shortcode", "wrct" ); ?>:</span>
<input class="wrct-sc-display" value="<?php esc_html_e( '[wrct id="'. $post_id .'"]' ); ?>" />
<a href="<?php echo plugins_url('wrct/documentation.php#shortcode-attributes'); ?>" target="_blank" class="wrct-sc-details-link"><i class="fa fa-cog"></i></a>
<div class="editor wrct-editor">

  <!-- Editor tabs -->
  <div class="wrct-tab-label courses-tab wrct-courses-tab active" data-wrct-tab="courses">
    <i class="wrct-tab-label-notice-icon fa fa-exclamation-circle"></i>
    <span class="wrct-tab-label-text"><i class="wrct-tab-label-icon fa fa-book"></i><?php _e( "Courses", "wrct" ); ?></span>
  </div>
  <div class="wrct-tab-label columns-tab wrct-columns-tab" data-wrct-tab="columns">
    <i class="wrct-tab-label-notice-icon fa fa-exclamation-circle"></i>
    <span class="wrct-tab-label-text"><i class="wrct-tab-label-icon fa fa-columns"></i><?php _e( "Columns", "wrct" ); ?></span>
  </div>
  <div class="wrct-tab-label styling-tab wrct-styling-tab " data-wrct-tab="styling">
    <i class="wrct-tab-label-notice-icon fa fa-exclamation-circle"></i>
    <span class="wrct-tab-label-text"><i class="wrct-tab-label-icon fa fa-paint-brush"></i><?php _e( "Styling", "wrct" ); ?></span>
  </div>
  <div class="wrct-tab-label styling-tab wrct-buttons-tab" data-wrct-tab="buttons">
    <i class="wrct-tab-label-notice-icon fa fa-exclamation-circle"></i>
    <span class="wrct-tab-label-text"><i class="wrct-tab-label-icon fa fa-hand-pointer-o"></i><?php _e( "Buttons", "wrct" ); ?></span>
  </div>

  <!-- Editor content -->
  <div class="tab-content wrct-editor-tab-courses  wrct-tab-content active" data-wrct-tab="courses">

    <div class="wrct-option-row">
      <div class="wrct-option-label" style="width: 100%"><?php _e( "Select courses by category", "wrct" ); ?>:</div>
      <div class="wrct-category-options">

        <?php
          // get WC categories
          $cat_args = array(
              'orderby'    => 'name',
              'order'      => 'asc',
              'hide_empty' => false,
          );

          $product_categories = get_terms( 'product_cat', $cat_args );

          if( ! empty($product_categories) ){
            foreach ($product_categories as $key => $category) {
              $checked = "";
              if( ! empty( $data['query'] ) && ! empty( $data['query']['categories'] ) && is_array( $data['query']['categories'] ) ){
                if( in_array( $category->name, $data['query']['categories'] ) ){
                  $checked = "checked";
                }
              }
              ?>
                <div class="wrct-category">
                  <input type="checkbox" value="<?php echo $category->name; ?>" <?php echo $checked ?> />
                  <span><?php echo $category->name; ?></span>
                </div>
              <?php
            }

          } else {

            ?>
            <div class="wrct-no-categories wrct-editor-notice">
              <strong>
              <?php _e( "You do not have any WooCommerce (WC) categories setup!", "wrct" ); ?>
              </strong>
              <span>
              <?php _e( "Please setup at least one WC category, and assign it product(s). After that you will have the option to select this category here and display its products in your table.", "wrct" ); ?>
              </span>
              <span>
              <?php _e( "To understand how to setup WC categories please visit this link:", "wrct" ); ?>
              </span>
              <a href="https://docs.woocommerce.com/document/managing-product-taxonomies/" target="_blank">docs.woocommerce.com/document/managing-product-taxonomies/</a>
            </div>
            <?php

          }
        ?>

      </div>

      <div class="wrct-set-categories-request-message wrct-editor-notice">
        <?php _e( "You must select at least one WooCommerce category to show in your tables.", "wrct" ); ?>
      </div>
      <div class="wrct-using-course-ids-message wrct-editor-notice">
        <?php _e( "Categories are disabled since you have selected courses individually by ID.", "wrct" ); ?>
      </div>

    </div>

    <!-- max-posts -->
    <div class="wrct-option-row">
      <div class="wrct-option-label"><?php _e( "Show maximum", "wrct" ); ?>:</div>
      <div class="wrct-input">
        <input class="wrct-max-posts" type="number" min="-1" placeholder="<?php _e( "number of courses", "wrct" ); ?>" value="<?php if( isset( $data['query'] ) && is_array( $data['query'] ) && isset( $data['query']['max_posts'] ) ) echo $data['query']['max_posts']; ?>"/>
      </div>
    </div>

    <!-- select individual courses -->
    <div class="wrct-option-row">
      <div class="wrct-option-label"><?php _e( "Select courses by ID", "wrct" ); ?>:</div>
      <div class="wrct-input">
        <textarea class="wrct-course-ids" placeholder="<?php _e( "enter comma separated course IDs", "wrct" ); ?>"><?php echo isset( $data['query']['course_ids'] ) ? trim( $data['query']['course_ids'] ) : ''; ?></textarea>
        <!-- <select class="wrct-course-ids" multiple="multiple"  placeholder="<?php _e( "enter course name(s) or product ID(s)", "wrct" ); ?>" > -->
          <?php
          /*
            if( ! isset( $data['query']['course_ids'] ) ){
              $data['query']['course_ids'] = array();
            }

            $products_loop = new WP_Query(
              array(
                'post_type' => 'product',
                'posts_per_page' => -1
              )
            );
            while( $products_loop->have_posts() ){
              $products_loop->the_post();
              global $post;
              $product = wc_get_product($post);
              $product_id = $product->get_id();
  						echo '<option value="' . esc_attr( $product_id ) . '" '. ( in_array( $product_id, $data['query']['course_ids'] ) ? ' selected="selected" ' : '' ) .' >' . wp_kses_post( $product->get_formatted_name( ) ) . '</option>';
            }
          */
          ?>
        </select>
      </div>
    </div>

    <!-- default orderbying -->
    <div class="wrct-option-row">
      <div class="wrct-option-label"><?php _e( "Initially sort by", "wrct" ); ?>:</div>
      <div class="wrct-input">
        <select class="wrct-default-orderby">
          <?php if( ! isset( $data['query']['default_orderby'] ) ) $data['query']['default_orderby'] = 'menu_order'; ?>
          <option value="menu_order" <?php if( $data['query']['default_orderby'] === "menu_order" ) echo " selected='selected' "; ?> ><?php _e( 'Default', 'wrct' ); ?></option>
          <option value="popularity" <?php if( $data['query']['default_orderby'] === "popularity" ) echo " selected='selected' "; ?> ><?php _e( 'Popularity', 'wrct' ); ?></option>
          <option value="rating" <?php if( $data['query']['default_orderby'] === "rating" ) echo " selected='selected' "; ?> ><?php _e( 'Average rating', 'wrct' ); ?></option>
          <option value="date" <?php if( $data['query']['default_orderby'] === "date" ) echo " selected='selected' "; ?> ><?php _e( 'Newness', 'wrct' ); ?></option>
          <option value="price" <?php if( $data['query']['default_orderby'] === "price" ) echo " selected='selected' "; ?> ><?php _e( 'Price: low to high', 'wrct' ); ?></option>
          <option value="price-desc" <?php if( $data['query']['default_orderby'] === "price-desc" ) echo " selected='selected' "; ?> ><?php _e( 'Price: high to low', 'wrct' ); ?></option>
        </select>
      </div>
    </div>

    <!-- pagination -->
    <div class="wrct-option-row">
      <div class="wrct-option-label"><?php _e( "Pagination", "wrct" ); ?>:</div>
      <div class="wrct-input">
        <input class="wrct-pagination" type="checkbox" value="on" <?php echo ! empty( $data['query']['pagination'] ) ? "checked='checked'" : "" ?> /> <span>Enable</span>
      </div>
    </div>

    <!-- search -->
    <div class="wrct-option-row">
      <div class="wrct-option-label"><?php _e( "Search", "wrct" ); ?>:</div>
      <div class="wrct-input">
        <input class="wrct-search" type="checkbox" value="on" <?php echo ! empty( $data['query']['search'] ) ? "checked='checked'" : "" ?> /> <span>Enable</span>
      </div>
    </div>

    <!-- sorting -->
    <div class="wrct-option-row">
      <div class="wrct-option-label"><?php _e( "Sorting", "wrct" ); ?>:</div>
      <div class="wrct-input">
        <input class="wrct-sorting" type="checkbox" value="on" <?php echo ! empty( $data['query']['sorting'] ) ? "checked='checked'" : "" ?> /> <span>Enable</span>
      </div>
    </div>

    <!-- query args -->
    <div class="wrct-option-row">
      <div class="wrct-option-label"><?php _e( "Query args", "wrct" ); ?>:</div>
      <div class="wrct-input">
        <input class="wrct-query-args" type="text" value="<?php if( isset( $data['query'] ) && is_array( $data['query'] ) && isset( $data['query']['query_args'] ) ) echo $data['query']['query_args']; ?>"/>
      </div>
    </div>

  </div>

  <div class="tab-content wrct-editor-tab-columns wrct-tab-content" data-wrct-tab="columns">

    <!-- Fetch columns request-->
    <div class="wrct-fetch-columns-request-message wrct-editor-notice">
      <?php _e( "Based on your changed course selection the column information may need to be updated from the server.", "wrct" ); ?>
      <a href="#" class="wrct-action"><?php _e( "Update Now?", "wrct" ); ?></a>
    </div>

    <!-- Fetch columns ongoing-->
    <div class="wrct-fetch-columns-ongoing-message wrct-editor-notice">
      <?php _e( "Updated from server. Please wait.", "wrct" ); ?>
      <i class="fa fa-refresh fa-spin"></i>
    </div>

    <!-- Fetch columns failed-->
    <div class="wrct-fetch-columns-failure-message wrct-editor-notice">
      <?php _e( "Sorry! Column information could not be fetched from the server.", "wrct" ); ?>
      <a href="#" class="wrct-action"><?php _e( "Try again?", "wrct" ); ?></a>
    </div>

    <!-- column config row template -->
    <div class="wrct-template column-config-row" data-wrct-name="" data-wrct-heading="">
      <!-- position -->
      <div class='wrct-column-position'>
        <i class='wrct-move-column-up fa fa-angle-up'></i>
        <i class='wrct-move-column-down fa fa-angle-down'></i>
      </div>
      <!-- name -->
      <div class="name" title="<?php _e( "column name", "wrct" ); ?>: "></div>
      <!-- heading -->
      <div class="heading" title='Column heading text'><input class="wrct-column-heading-option" type="text" value=""></div>
      <!-- hide -->
      <div class="hide" title="<?php _e( "Hide column", "wrct" ); ?>">
        <input class="wrct-column-hide-option" type="checkbox" />
        <span><?php _e( "Hide column", "wrct" ); ?></span>
      </div>
    </div>

    <div class="columns-config-wrapper wrct-sortable">
    </div>

    <!-- enable additional columns -->
    <div class="wrct-additional-columns-options">
      <div class="wrct-heading">
        <?php _e( "Enable additional columns", "wrct" ); ?>:
      </div>

      <!-- featured image -->
      <div class="wrct-additional-column">
        <input type="checkbox" data-wrct-heading="<?php _e( "Image", "wrct" ); ?>" value="featured image" <?php if( wrct_get_column_index_by_name("featured image", $data) !== false ) echo ' checked="checked" ' ?> />
        <span><?php _e( "Featured image", "wrct" ); ?></span>
      </div>

      <!-- quantity -->
      <div class="wrct-additional-column">
        <input type="checkbox" data-wrct-heading="<?php _e( "Quantity", "wrct" ); ?>" value="quantity" <?php if( wrct_get_column_index_by_name("quantity", $data) !== false ) echo ' checked="checked" ' ?> />
        <span><?php _e( "Quantity", "wrct" ); ?></span>
      </div>

      <!-- ratings -->
      <div class="wrct-additional-column">
        <input type="checkbox" data-wrct-heading="<?php _e( "Rating", "wrct" ); ?>" value="rating" <?php if( wrct_get_column_index_by_name("average rating", $data) !== false ) echo ' checked="checked" ' ?> />
        <span><?php _e( "Average rating", "wrct" ); ?></span>
      </div>

      <!-- stock / seats left -->
      <div class="wrct-additional-column">
        <input type="checkbox" data-wrct-heading="<?php _e( "Seats left", "wrct" ); ?>" value="stock" <?php if( wrct_get_column_index_by_name("stock", $data) !== false ) echo ' checked="checked" ' ?> />
        <span><?php _e( "Seats left", "wrct" ); ?></span>
      </div>

    </div>

    <!-- add custom field column -->
    <div class="wrct-add-custom-field-column">

      <!-- toggle -->
      <div class="wrct-button wrct-custom-field-details-toggle">
        <?php _e( "Add custom field", "wrct" ); ?>
      </div>

      <div class="wrct-custom-field-details">
        <!-- cf name -->
        <div class="wrct-custom-field-details-row">
          <span><?php _e( "Custom field name", "wrct" ); ?>:</span>
          <input type="text" name="wrct-cf-name" value="">
          <span><?php _e( "Required", "wrct" ); ?></span>
        </div>
        <!-- cf heading -->
        <div class="wrct-custom-field-details-row">
          <span><?php _e( "Column heading", "wrct" ); ?>:</span>
          <input type="text" name="wrct-cf-heading" value="">
        </div>
        <!-- cf type -->
        <div class="wrct-custom-field-details-row">
          <span><?php _e( "Value type", "wrct" ); ?>:</span>
          <select class="" name="wrct-cf-val-type">
            <option value="text"><?php _e( "Text", "wrct" ); ?></option>
            <option value="number"><?php _e( "Number", "wrct" ); ?></option>
          </select>
        </div>
        <!-- add button -->
        <div class="wrct-button wrct-submit-custom-field">
          <?php _e( "Add column", "wrct" ); ?>
        </div>
        <!-- cancel button -->
        <div class="wrct-button wrct-cancel-custom-field">
          <?php _e( "Cancel", "wrct" ); ?>
        </div>

      </div>

    </div>

    <!-- responsive summary template: begin -->
    <div class="wrct-responsive-summary-template">
      <div class="wrct-heading">
        <?php _e( "Responsive summary template:", "wrct" ); ?>
      </div>
      <textarea class="wrct-responsive-summary-template-input" rows="8" cols="80"><?php if( isset( $data['query']['responsive_summary_template'] ) ) echo esc_attr( $data['query']['responsive_summary_template'] ); ?></textarea>
      <div class="wrct-button wrct-trigger-next-message">
        <?php _e( "Show keywords", "wrct" ); ?>
      </div>
      <div class="wrct-help-message">
        <i class="wrct-message-close"></i>

        <div class="wrct-item">
          <div class="wrct-item-left">
            <?php _e( "Rating", "wrct" ); ?>
          </div>
          <div class="wrct-item-right">
            %rating%
          </div>
        </div>

        <div class="wrct-item">
          <div class="wrct-item-left">
            <?php _e( "Price", "wrct" ); ?>
          </div>
          <div class="wrct-item-right">
            %price%
          </div>
        </div>

        <div class="wrct-item">
          <div class="wrct-item-left">
            <?php _e( "Price range", "wrct" ); ?>
          </div>
          <div class="wrct-item-right">
            %price-range%
          </div>
        </div>

        <div class="wrct-item">
          <div class="wrct-item-left">
            <?php _e( "Sales price", "wrct" ); ?>
          </div>
          <div class="wrct-item-right">
            %sale-price%
          </div>
        </div>

        <div class="wrct-item">
          <div class="wrct-item-left">
            <?php _e( "Regular price", "wrct" ); ?>
          </div>
          <div class="wrct-item-right">
            %regular-price%
          </div>
        </div>

        <div class="wrct-item">
          <div class="wrct-item-left">
            <?php _e( "Stock", "wrct" ); ?>
          </div>
          <div class="wrct-item-right">
            %stock%
          </div>
        </div>

        <div class="wrct-item">
          <div class="wrct-item-left">
            <?php _e( "Custom field", "wrct" ); ?>
          </div>
          <div class="wrct-item-right">
            %cf: custom field key%
          </div>
        </div>

        <div class="wrct-item">
          <div class="wrct-item-left">
            <?php _e( "Attribute", "wrct" ); ?>
          </div>
          <div class="wrct-item-right">
            %attr: attribut%
          </div>
        </div>

        <div class="wrct-item">
          <div class="wrct-item-left">
            <?php _e( "Course name", "wrct" ); ?>
          </div>
          <div class="wrct-item-right">
            %title%
          </div>
        </div>

        <div class="wrct-item">
          <div class="wrct-item-left">
            <?php _e( "Space", "wrct" ); ?>
          </div>
          <div class="wrct-item-right">
            <?php echo htmlentities("&nbsp;"); ?>
          </div>
        </div>

      </div>
    </div>
    <!-- responsive summary template: end -->

  </div>

  <div class="tab-content wrct-editor-tab-styling wrct-tab-content" data-wrct-tab="styling">
    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-selection-style"><?php _e( "Selection style", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <select id="wrct-selection-style">
          <option value="drop-down"><?php _e( "Drop-down", "wrct" ); ?></option>
          <option value="radio"><?php _e( "Radio", "wrct" ); ?></option>
        </select>
      </div>
     </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-theme"><?php _e( "Apply theme", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <select id="wrct-theme" data-wrct-no>
          <option value="none" selected><?php _e( "None", "wrct" ); ?></option>
          <option value="blank"><?php _e( "Blank", "wrct" ); ?></option>
          <option value="orange"><?php _e( "Orange", "wrct" ); ?></option>
          <option value="black"><?php _e( "Black", "wrct" ); ?></option>
        </select>
      </div>
     </div>

     <!-- heading row -->
     <div class="wrct-heading">
       <?php _e( "Heading row", "wrct" ); ?>
     </div>

     <div class="wrct-styling-row">
       <div class="wrct-left">
         <label for="wrct-header-hide"><?php _e( "Hide", "wrct" ); ?>:</label>
       </div>
       <div class="wrct-right">
         <input type="checkbox" id="wrct-header-hide" value="1" />
       </div>
     </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-header-bg-color"><?php _e( "Background color", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="text" id="wrct-header-bg-color" class="wrct-color-picker" value="" />
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-header-text-color"><?php _e( "Text color", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="text" id="wrct-header-text-color" class="wrct-color-picker">
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-header-font-size"><?php _e( "Font size", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="number" id="wrct-header-font-size" value="16" min="0" /> px
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-header-border-bottom-color"><?php _e( "Border bottom color", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="text" id="wrct-header-border-bottom-color" value="#555" class="wrct-color-picker" />
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-header-border-bottom-width"><?php _e( "Border bottom width", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="number" id="wrct-header-border-bottom-width" value="4" min="0" /> px
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-header-padding"><?php _e( "Padding", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="text" id="wrct-header-padding" placeholder="15px 5px" value="15px 5px" min="0" />
      </div>
    </div>

    <!-- Rows -->
    <div class="wrct-heading">
      <?php _e( "Rows", "wrct" ); ?>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-rows-bg-color-odd"><?php _e( "Background color – odd", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="text" id="wrct-rows-bg-color-odd" class="wrct-color-picker">
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-rows-bg-color-even"><?php _e( "Background color – even", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="text" id="wrct-rows-bg-color-even" class="wrct-color-picker">
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-cell-font-size"><?php _e( "Font size", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="number" id="wrct-cell-font-size" value="16" min="0" /> px
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-cell-padding"><?php _e( "Padding", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="text" id="wrct-cell-padding" placeholder="20px 10px" value="20px 10px" min="0" />
      </div>
    </div>

    <!-- cell label - responsive -->
    <div class="wrct-heading">
      <?php _e( "Cell label – Responsive", "wrct" ); ?>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-cell-label-hide-responsive"><?php _e( "Hide", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="checkbox" id="wrct-cell-label-hide-responsive" value="1" />
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-header-bg-color-responsive-odd"><?php _e( "Background color – odd", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="text" id="wrct-header-bg-color-responsive-odd" class="wrct-color-picker" value="">
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-header-bg-color-responsive-even"><?php _e( "Background color – even", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="text" id="wrct-header-bg-color-responsive-even" class="wrct-color-picker" value="">
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-header-text-color-responsive"><?php _e( "Text color", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="text" id="wrct-header-text-color-responsive" class="wrct-color-picker">
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-header-font-size-responsive"><?php _e( "Font size", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="number" id="wrct-header-font-size-responsive" value="16" min="0" /> px
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-header-width-responsive"><?php _e( "Width", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="text" id="wrct-header-width-responsive" value="40%" min="0" /> % / px
      </div>
    </div>


    <!-- responsive cell value -->
    <div class="wrct-heading">
      <?php _e( "Cell value – Responsive", "wrct" ); ?>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-rows-bg-color-responsive-odd"><?php _e( "Background color – odd", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="text" id="wrct-rows-bg-color-responsive-odd" class="wrct-color-picker">
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-rows-bg-color-responsive-even"><?php _e( "Background color – even", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="text" id="wrct-rows-bg-color-responsive-even" class="wrct-color-picker">
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-cell-text-color-responsive"><?php _e( "Text color", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="text" id="wrct-cell-text-color-responsive" class="wrct-color-picker">
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-cell-font-size-responsive"><?php _e( "Font size", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="number" id="wrct-cell-font-size-responsive" value="16" min="0" /> px
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-name-full-width-responsive"><?php _e( "Make name cell full-width", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="checkbox" id="wrct-name-full-width-responsive" value="1">
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-name-bg-color-responsive"><?php _e( "Name cell background color", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="text" id="wrct-name-bg-color-responsive" class="wrct-color-picker">
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-name-text-color-responsive"><?php _e( "Name cell text color", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="text" id="wrct-name-text-color-responsive" class="wrct-color-picker">
      </div>
    </div>

    <!-- Borders -->
    <div class="wrct-heading">
      <?php _e( "Borders", "wrct" ); ?>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-border-horizontal-remove"><?php _e( "Remove horizontal borders", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="checkbox" id="wrct-border-horizontal-remove" value="1">
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-border-vertical-remove"><?php _e( "Remove vertical borders", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="checkbox" id="wrct-border-vertical-remove" value="1">
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-border-table-remove"><?php _e( "Remove outer borders", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="checkbox" id="wrct-border-table-remove" value="1">
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-border-color"><?php _e( "Border color", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="text" id="wrct-border-color" class="wrct-color-picker">
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-border-width"><?php _e( "Border width", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="number" id="wrct-border-width" value="1" min="0" /> px
      </div>
    </div>

    <!-- Borders – responsive -->
    <div class="wrct-heading">
      <?php _e( "Borders – responsive", "wrct" ); ?>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-border-color-responsive"><?php _e( "Outer border color", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="text" id="wrct-border-color-responsive" class="wrct-color-picker">
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-border-width-responsive"><?php _e( "Outer border width", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="number" id="wrct-border-width-responsive" value="1" min="0" /> px
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-border-radius-responsive"><?php _e( "Border radius", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="number" id="wrct-border-radius-responsive" value="4" min="0" /> px
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-name-border-bottom-color-responsive"><?php _e( "Name border color bottom", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="text" id="wrct-name-border-bottom-color-responsive" class="wrct-color-picker">
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-name-border-bottom-width-responsive"><?php _e( "Name border bottom width", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="number" id="wrct-name-border-bottom-width-responsive" value="1" min="0" /> px
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-remove-other-name-borders-responsive"><?php _e( "Remove other name borders", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="checkbox" id="wrct-remove-other-name-borders-responsive" value="1">
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-middle-border-color-responsive"><?php _e( "Middle border color", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="text" id="wrct-middle-border-color-responsive" class="wrct-color-picker">
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-middle-border-width-responsive"><?php _e( "Middle border width", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="number" id="wrct-middle-border-width-responsive" value="1" min="0" /> px
      </div>
    </div>


    <!-- Buttons -->
    <div class="wrct-heading">
      <?php _e( "Buttons", "wrct" ); ?>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-link-button-bg"><?php _e( "More info button background", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="text" id="wrct-link-button-bg" class="wrct-color-picker" value="">
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-link-button-text-color"><?php _e( "More info button text color", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="text" id="wrct-link-button-text-color" class="wrct-color-picker" value="">
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-cart-checkout-button-bg"><?php _e( "Cart/Checkout button background", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="text" id="wrct-cart-checkout-button-bg" class="wrct-color-picker" value="">
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-cart-checkout-button-text-color"><?php _e( "Cart/Checkout button text color", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="text" id="wrct-cart-checkout-button-text-color" class="wrct-color-picker" value="">
      </div>
    </div>


    <!-- Responsiveness -->
    <div class="wrct-heading">
      <?php _e( "Responsiveness", "wrct" ); ?>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-responsive-layout-below"><?php _e( "Responsive layout below", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="number" id="wrct-responsive-layout-below" value="850" min="0" /> px
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-responsive-layout-3-columns-above"><?php _e( "Responsive layout 3 columns above", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="number" id="wrct-responsive-layout-3-columns-above" min="0" /> px
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-responsive-layout-2-columns-above"><?php _e( "Responsive layout 2 columns above", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="number" id="wrct-responsive-layout-2-columns-above" min="0" /> px
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-responsive-layout-columns-gap-horizontal"><?php _e( "Responsive layout columns gap horizontal", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="number" id="wrct-responsive-layout-columns-gap-horizontal" value="15" min="0" /> px
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-responsive-layout-columns-gap-vertical"><?php _e( "Responsive layout columns gap vertical", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="number" id="wrct-responsive-layout-columns-gap-vertical" value="30" min="0" /> px
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-enable-responsive-layout-accordion"><?php _e( "Enable responsive layout accordion", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="checkbox" id="wrct-enable-responsive-layout-accordion" value="1">
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-table-minimum-width"><?php _e( "Table minimum width", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="number" id="wrct-table-minimum-width" value="" min="0" /> px
      </div>
    </div>


    <!-- Misc. -->
    <div class="wrct-heading">
      <?php _e( "Misc.", "wrct" ); ?>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-vertical-align"><?php _e( "Vertical align", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <select type="checkbox" id="wrct-vertical-align">
          <option value="top"><?php _e( "Top", "wrct" ); ?></option>
          <option value="middle"><?php _e( "Middle", "wrct" ); ?></option>
          <option value="bottom"><?php _e( "Bottom", "wrct" ); ?></option>
        </select>
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-column-min-width"><?php _e( "Columns minimum width", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="number" id="wrct-column-min-width" value=""> px
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-price-color"><?php _e( "Price color", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="text" id="wrct-price-color" class="wrct-color-picker" value="">
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-price-alternate-text-color"><?php _e( "Price alternate text color", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="text" id="wrct-price-alternate-text-color" class="wrct-color-picker" value="">
      </div>
    </div>

    <div class="wrct-styling-row">
      <div class="wrct-left">
        <label for="wrct-buttons-alternate-text-color"><?php _e( "Buttons alternate text color", "wrct" ); ?>:</label>
      </div>
      <div class="wrct-right">
        <input type="text" id="wrct-buttons-alternate-text-color" class="wrct-color-picker" value="">
      </div>
    </div>

    <textarea class="wrct-styling" id="wrct-css" placeholder="<?php _e( "Enter custom CSS here...", "wrct" ); ?>"><?php echo isset($data['styling']['css']) ? $data['styling']['css'] : ''; ?></textarea>

  </div>

  <!-- Buttons tab -->
  <div class="tab-content wrct-tab-content wrct-editor-tab-buttons" data-wrct-tab="buttons">

    <!-- Button 1 -->
    <div class="wrct-option-row">
      <div class="wrct-option-label wrct-heading"><?php _e( "Button 1", "wrct" ); ?></div>
      <div class="wrct-input">
        <select class="wrct-button-1-enable-option" >
          <option value="on" <?php if( $data['buttons']['button-1-enable'] === "on" ) echo "selected"; ?> ><?php _e( "On", "wrct" ); ?></option>
          <option value="off" <?php if( $data['buttons']['button-1-enable'] === "off" ) echo "selected"; ?> ><?php _e( "Off", "wrct" ); ?></option>
        </select>
      </div>
    </div>

    <div class="wrct-option-row">
      <div class="wrct-option-label"><?php _e( "Button 1 label", "wrct" ); ?>:</div>
      <div class="wrct-input">
        <input class="wrct-button-1-label-option" value="<?php echo $data['buttons']['button-1-label']; ?>" type="text" />
      </div>
    </div>

    <div class="wrct-option-row">
      <div class="wrct-option-label"><?php _e( "Button 1 link", "wrct" ); ?>:</div>
      <div class="wrct-input">
        <select class="wrct-button-1-link-option" >
          <option value="%post%" <?php if( $data['buttons']['button-1-link'] === "%post%" ) echo "selected"; ?> ><?php _e( "WC product page", "wrct" ); ?></option>
          <option value="%cart%" <?php if( $data['buttons']['button-1-link'] === "%cart%" ) echo "selected"; ?> ><?php _e( "Add to cart", "wrct" ); ?></option>
          <option value="%ajax-cart%" <?php if( $data['buttons']['button-1-link'] === "%ajax-cart%" ) echo "selected"; ?> ><?php _e( "Add to cart via AJAX", "wrct" ); ?></option>
          <option value="%to-cart%" <?php if( $data['buttons']['button-1-link'] === "%to-cart%" ) echo "selected"; ?> ><?php _e( "Add and go to cart", "wrct" ); ?></option>
          <option value="%checkout%" <?php if( $data['buttons']['button-1-link'] === "%checkout%" ) echo "selected"; ?> ><?php _e( "Add and go to checkout", "wrct" ); ?></option>
        </select>
      </div>
    </div>

    <div class="wrct-option-row">
      <div class="wrct-option-label"><?php _e( "Button 1 target", "wrct" ); ?>:</div>
      <div class="wrct-input">
        <select class="wrct-button-1-target-option" >
          <option value="" <?php if( $data['buttons']['button-1-target'] === "" ) echo "selected"; ?> ><?php _e( "Open link on same page", "wrct" ); ?></option>
          <option value="_blank" <?php if( $data['buttons']['button-1-target'] === "_blank" ) echo "selected"; ?> ><?php _e( "Open link on new page", "wrct" ); ?></option>
        </select>
      </div>
    </div>

    <!-- Button 2 -->
    <div class="wrct-option-row">
      <div class="wrct-option-label wrct-heading"><?php _e( "Button 2", "wrct" ); ?></div>
      <div class="wrct-input">
        <select class="wrct-button-2-enable-option" >
          <option value="on" <?php if( $data['buttons']['button-2-enable'] === "on" ) echo "selected"; ?> ><?php _e( "On", "wrct" ); ?></option>
          <option value="off" <?php if( $data['buttons']['button-2-enable'] === "off" ) echo "selected"; ?> ><?php _e( "Off", "wrct" ); ?></option>
        </select>
      </div>
    </div>

    <div class="wrct-option-row">
      <div class="wrct-option-label"><?php _e( "Button 2 label", "wrct" ); ?>:</div>
      <div class="wrct-input">
        <input class="wrct-button-2-label-option" value="<?php echo $data['buttons']['button-2-label']; ?>" type="text" />
      </div>
    </div>

    <div class="wrct-option-row">
      <div class="wrct-option-label"><?php _e( "Button 2 link", "wrct" ); ?>:</div>
      <div class="wrct-input">
        <select class="wrct-button-2-link-option" >
          <option value="%post%" <?php if( $data['buttons']['button-2-link'] === "%post%" ) echo "selected"; ?> ><?php _e( "WC product page", "wrct" ); ?></option>
          <option value="%cart%" <?php if( $data['buttons']['button-2-link'] === "%cart%" ) echo "selected"; ?> ><?php _e( "Add to cart", "wrct" ); ?></option>
          <option value="%ajax-cart%" <?php if( $data['buttons']['button-2-link'] === "%ajax-cart%" ) echo "selected"; ?> ><?php _e( "Add to cart via AJAX", "wrct" ); ?></option>
          <option value="%to-cart%" <?php if( $data['buttons']['button-2-link'] === "%to-cart%" ) echo "selected"; ?> ><?php _e( "Add and go to cart", "wrct" ); ?></option>
          <option value="%checkout%" <?php if( $data['buttons']['button-2-link'] === "%checkout%" ) echo "selected"; ?> ><?php _e( "Add and go to checkout", "wrct" ); ?></option>
        </select>
      </div>
    </div>

    <div class="wrct-option-row">
      <div class="wrct-option-label"><?php _e( "Button 2 target", "wrct" ); ?>:</div>
      <div class="wrct-input">
        <select class="wrct-button-2-target-option" >
          <option value="" <?php if( $data['buttons']['button-2-target'] === "" ) echo "selected"; ?> ><?php _e( "Open link on same page", "wrct" ); ?></option>
          <option value="_blank" <?php if( $data['buttons']['button-2-target'] === "_blank" ) echo "selected"; ?> ><?php _e( "Open link on new page", "wrct" ); ?></option>
        </select>
      </div>
    </div>

    <!-- Button 3 -->
    <div class="wrct-option-row">
      <div class="wrct-option-label wrct-heading"><?php _e( "Button 3", "wrct" ); ?></div>
      <div class="wrct-input">
        <select class="wrct-button-3-enable-option" >
          <option value="on" <?php if( $data['buttons']['button-3-enable'] === "on" ) echo "selected"; ?> ><?php _e( "On", "wrct" ); ?></option>
          <option value="off" <?php if( $data['buttons']['button-3-enable'] === "off" ) echo "selected"; ?> ><?php _e( "Off", "wrct" ); ?></option>
        </select>
      </div>
    </div>

    <div class="wrct-option-row">
      <div class="wrct-option-label"><?php _e( "Button 3 label", "wrct" ); ?>:</div>
      <div class="wrct-input">
        <input class="wrct-button-3-label-option" value="<?php echo $data['buttons']['button-3-label']; ?>" type="text" />
      </div>
    </div>

    <div class="wrct-option-row">
      <div class="wrct-option-label"><?php _e( "Button 3 link", "wrct" ); ?>:</div>
      <div class="wrct-input">
        <select class="wrct-button-3-link-option" >
          <option value="%post%" <?php if( $data['buttons']['button-3-link'] === "%post%" ) echo "selected"; ?> ><?php _e( "WC product page", "wrct" ); ?></option>
          <option value="%cart%" <?php if( $data['buttons']['button-3-link'] === "%cart%" ) echo "selected"; ?> ><?php _e( "Add to cart", "wrct" ); ?></option>
          <option value="%ajax-cart%" <?php if( $data['buttons']['button-3-link'] === "%ajax-cart%" ) echo "selected"; ?> ><?php _e( "Add to cart via AJAX", "wrct" ); ?></option>
          <option value="%to-cart%" <?php if( $data['buttons']['button-3-link'] === "%to-cart%" ) echo "selected"; ?> ><?php _e( "Add and go to cart", "wrct" ); ?></option>
          <option value="%checkout%" <?php if( $data['buttons']['button-3-link'] === "%checkout%" ) echo "selected"; ?> ><?php _e( "Add and go to checkout", "wrct" ); ?></option>
        </select>
      </div>
    </div>

    <div class="wrct-option-row">
      <div class="wrct-option-label"><?php _e( "Button 3 target", "wrct" ); ?>:</div>
      <div class="wrct-input">
        <select class="wrct-button-3-target-option" >
          <option value="" <?php if( $data['buttons']['button-3-target'] === "" ) echo "selected"; ?> ><?php _e( "Open link on same page", "wrct" ); ?></option>
          <option value="_blank" <?php if( $data['buttons']['button-3-target'] === "_blank" ) echo "selected"; ?> ><?php _e( "Open link on new page", "wrct" ); ?></option>
        </select>
      </div>
    </div>

  </div>

</div>

<div class="wrct-sc">
  <form class="wrct-form">
    <input class="wrct-json-data" name="data" type="hidden" value="" />
    <input name="post_id" type="hidden" value="<?php echo $post_id; ?>" />
    <?php $untitled_table = __( "Untitled table", "wrct" ); ?>
    <input name="title" type="hidden" value="<?php echo ( isset($post_id) ? get_the_title( $post_id ) : $untitled_table ); ?>" />
    <input name="nonce" type="hidden" value="<?php echo wp_create_nonce( "wrct" ); ?>" />
    <button type="submit" class="wrct-save button button-primary button-large"><?php _e( "Save settings", "wrct" ); ?></button>
    <i class="wrct-saving-icon fa fa-refresh fa-spin"></i>
    <div class="wrct-save-keys">
      (Ctr/Cmd + s)
    </div>
  </form>
</div>

<div class="wrct-documentation-widget">
  <?php $doc_link = plugins_url('wrct/documentation.php') ?>
  <a class="wrct-documentation-link" href="<?php echo $doc_link; ?>" target="_blank">
    <i class="fa fa-file-text"></i>
    <?php _e( "Documentation", "wrct" ); ?>
  </a>
  <a class="wrct-documentation-link" href="<?php echo $doc_link; ?>#column-widths" target="_blank">
    <i class="fa fa-bars fa-rotate-90"></i>
    <?php _e( "Adjust column widths", "wrct" ); ?>
  </a>
  <a class="wrct-documentation-link" href="<?php echo $doc_link; ?>#responsiveness" target="_blank">
    <i class="fa fa-tablet"></i>
    <?php _e( "Responsiveness notes", "wrct" ); ?>
  </a>
  <a class="wrct-documentation-link" href="<?php echo $doc_link; ?>#shortcode-attributes" target="_blank">
    <i class="fa fa-code"></i>
    <?php _e( "Shortcode attributes list", "wrct" ); ?>
  </a>
  <a class="wrct-documentation-link" href="<?php echo $doc_link; ?>#icons" target="_blank">
    <i class="fa fa-smile-o"></i>
    <?php _e( "Icons in headings & buttons", "wrct" ); ?>
  </a>
  <a class="wrct-documentation-link" href="<?php echo $doc_link; ?>#price-buttons-text" target="_blank">
    <i class="fa fa-font"></i>
    <?php _e( "Replace price & buttons with text", "wrct" ); ?>
  </a>
</div>

<div class="wrct-footer">
  <div class="wrct-support wrct-footer-note">
    <i class="fa fa-bug"></i>
    <span><?php _e( "Found a bug? Got questions? Please reach me for support here: ", "wrct" ); ?><a href="mailto:wordpressaholic@gmail.com" target="_blank">wordpressaholic@gmail.com</a></span>
  </div>
  <div class="wrct-ratings wrct-footer-note">
    <i class="fa fa-thumbs-o-up"></i>
    <span><?php _e( "Happy with WRCT? It would be awesome if you left my plugin a 5 star rating", "wrct" ); ?> <a href="https://codecanyon.net/downloads" target="_blank"><?php _e( "here", "wrct" ); ?></a>. <?php _e( "Thank you!", "wrct" ); ?> :-)</span>
  </div>
</div>
