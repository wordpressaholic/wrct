<!DOCTYPE html>
<html>

  <head>
    <meta charset="utf-8">
    <title>WC Tables Tutorial</title>
    <link href="<?php echo str_replace( 'documentation.php', 'documentation.css', $_SERVER['REQUEST_URI'] ); ?>" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Raleway:300,400,600,900" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
  </head>

  <body>

  <h1>WooCommerce Responsive Course Tables <span>Documentation</span></h1>

  <div class="index">
    <div class="heading">Table of contents</div>
    <a class="index-item" href="#creating-table">Creating a table<i class="fa fa-angle-right"></i></a>
    <a class="index-item" href="#shortcode-attributes">Shortcode attributes<i class="fa fa-angle-right"></i></a>
    <a class="index-item" href="#column-widths">Adjusting column widths<i class="fa fa-angle-right"></i></a>
    <a class="index-item" href="#responsiveness">Mobile and tablet responsiveness<i class="fa fa-angle-right"></i></a>
    <a class="index-item" href="#icons">Insert icons in column headings and buttons<i class="fa fa-angle-right"></i></a>
    <a class="index-item" href="#price-buttons-text">Replace price and buttons with text<i class="fa fa-angle-right"></i></a>
    <a class="index-item" href="#replace-woocommerce-templates">Replace WooCommerce related producs / up-sell / cross-sell section with tables<i class="fa fa-angle-right"></i></a>
  </div>

  <div class="heading" id="creating-table">Creating a table</div>
  <p>
    There are <span class="highlight">two ways of creating a table</span>:
  </p>

  <div class="subsection">
    <div class="heading">Backend table editor</div>
    <pre>[wrct id="123"]</pre>
    <p>
      The backend table editor will give you a shortcode with an id to display your table. <span class="highlight">You can overwrite the table settings with shortcode attributes</span> listed in the section below. This lets you easily re-use tables for different requirements:
    </p>
    <pre>[wrct id="123" category="category 1, category 2"]</pre>
  </div>

  <div class="subsection">
    <div class="heading">Direct shortcode</div>
    <pre>[wrct category="category 1, category 2"]</pre>
    <p>You <span class="highlight">can directly use the [wrct] shortcode with attributes</span> without an "id" to create customized tables on the fly. This way you can skip the backend table editor entirely if you want.
    </p>
  </div>

  <div class="heading" id="shortcode-attributes">Shortcode attributes</div>

  <!-- <p>
    These attributes give you additional options over the backend table editor. Keep in mind they will overwrite the settings of the backend table editor.
  </p> -->

  <table cellspacing="0">
    <thead>
      <tr>
        <th>Attribute label</th>
        <th>Description</th>
      </tr>
    </thead>
    <tbody>

      <tr>
        <td>id</td>
        <td>
          <span>
            [wrct id="123"]
          </span>
          This attribute is only necessary if you are displaying a table created in the backend using the plugin editor. If you are directly using the plugin shortcode [wrct] then do not use this attribute.</td>
      </tr>

      <tr>
        <td>category / categories</td>
        <td>
          <span>
            [wrct categories="WC category a, WC category b, WC category c"]
          </span>
          Comma separated names of WooCommerce categories that the table needs to display.
        </td>
      </tr>

      <tr>
        <td>columns</td>
        <td>
          <span>
            [wrct columns="Featured image: no heading, Name: Course name, Attribute 1: Some heading, Price: Cost, Buttons: no heading"]
          </span>
          Enter column names separated by commas. You can use the colon symbol ':' next to a column name to give it a custom heading. Enter "no heading" if you wish the column heading to appear blank in the table. The default columns are Name, Featured Image, Buttons and Price. To add columns based on WooCommerce product attributes, just enter the attribute name as a column and give it a heading.
      </tr>

      <tr>
        <td>selection-style</td>
        <td>
          <span>
            [wrct selection-style="radio / drop-down"]
          </span>
          Choose whether to display the variation options as radio buttons (radio) or drop-down boxes (drop-down) by entering the option you want here.
        </td>
      </tr>

      <tr>
        <td>search</td>
        <td>
          <span>
            [wrct search="on / off"]
          </span>
          Toggle the search field above the table with this attribute.
        </td>
      </tr>

      <tr>
        <td>search-label</td>
        <td>
          <span>
            [wrct search-label="Find course: "]
          </span>
          Customize the label text for the search field.
        </td>
      </tr>

      <tr>
        <td>search-placeholder</td>
        <td>
          <span>
            [wrct search-placeholder="Course name"]
          </span>
          Customize the placeholder text for the search input box.
        </td>
      </tr>

      <tr>
        <td>clear-search</td>
        <td>
          <span>
            [wrct clear-search="Clear search?"]
          </span>
          Customize the text for the clear search link next to the input box.
        </td>
      </tr>

      <tr>
        <td>no-search-results</td>
        <td>
          <span>
            [wrct no-search-results="Sorry! No results found for that entry. Please %clear search% and try again"]
          </span>
          Custom message to be shown when search returns no results. Text between % symbols will be turned into clear search link.
        </td>
      </tr>

      <!-- <td>filters</td>
      <td>
        <span>
          [wrct filters="on / off"]
        </span>
        Toggle filters field above the table with this attribute. The template for filters can be copied to your child theme and edited.
      </td>
    </tr> -->

      <tr>
        <td>sorting</td>
        <td>
          <span>
            [wrct sorting="on / off" sort-enabled-columns="name, price, "]
          </span>
          Toggle sorting with this attribute. By default sorting will be enabled for columns other than featured image, quantity and buttons. To specific which columns sorting should work with use the sort-enabled-columns attribute.
        </td>
      </tr>

      <tr>
        <td>pagination</td>
        <td>
          <span>
            [wrct pagination="on / off" max-posts="10"]
          </span>
          Toggle pagination with this attribute. To control how many courses are shown at a single go use "max-posts" (next attribute).
        </td>
      </tr>

      <tr>
        <td>max-posts</td>
        <td>
          <span>
            [wrct max-posts="10"]
          </span>
          Limits the maximum number of courses that can be shown on the table. Especially important when using pagination.
        </td>
      </tr>

      <!-- <tr>
        <td>button-1-enable / button-2-enable / button-3-enable</td>
        <td>
          <span>
            [wrct button-2-enable="on / off"]
          </span>
          By default 2 buttons are enabled in the buttons column. With this attribute you can enable and disable any of the three possible buttons.
        </td>
      </tr> -->

      <tr>
        <td>button-1-label / button-2-label / button-3-label</td>
        <td>
          <span>
            [wrct button-1-label="More info" button-2-label="Add to cart"]
          </span>
          Set the text label for buttons. Max 3 buttons can be set.
        </td>
      </tr>

      <tr>
        <td>button-1-link / button-2-link / button-3-link</td>
        <td>
          <span>
            [wrct button-1-link="%post%" button-2-link="%to-cart%"]
          </span>
          The links for the buttons. Codes are - <br>More info: %post% <br>Add to cart: %cart% <br>Add to cart via AJAX: %ajax-cart% <br>Add product and go to cart: %to-cart% <br>Add product and go to checkout: %checkout%
        </td>
      </tr>

      <tr>
        <td>button-1-target / button-2-target / button-3-target</td>
        <td>
          <span>
            [wrct button-1-target="_self / _blank"]
          </span>
          By default the links will open on a new tab. To open on the same tab, enter '_self' as this attribute's value.
        </td>
      </tr>

      <tr>
        <td>course-ids</td>
        <td>
          <span>
            [wrct course-ids="course-ids-number / current"]
          </span>
          Enter the course id number if you wish to display only one particular course in the table. If you are placing the table shortcode in the post content then you can just enter current instead.
        </td>
      </tr>

      <tr>
        <td>theme</td>
        <td>
          <span>
            [wrct theme="blank / black / orange"]
          </span>
          The plugin comes with 3 preset themes. You can enter the name of any one in the theme attribute.
        </td>
      </tr>

      <tr id="columns-width">
        <td>columns-width</td>
        <td>
          <span>
            [wrct columns-width="Name: 200, Buttons: 300, Attribute 1: 150"]
          </span>
          Use this attribute to set widths for specified columns in non-responsive layout mode. Enter column names (in any order), for which you want a width set, each name followed by a colon ":" and then the width in pixels for it. Then enter a comma "," and the next column-width set and so on.
        </td>
      </tr>

      <tr id="columns-min-width">
        <td>columns-min-width</td>
        <td>
          <span>
            [wrct columns-min-width="Name: 200, Buttons: 250, Attribute 1: 100"]
          </span>
          Use this attribute to force specified table columns to maintain a certain minimum width in non-responsive layout mode. Enter column names (in any order), for which you want a minimum width, each name followed by a colon ":" and then the minimum width in pixels for it. Then enter a comma "," and the next column-minimum width set and so on.
        </td>
      </tr>

      <tr id="columns-max-width">
        <td>columns-max-width</td>
        <td>
          <span>
            [wrct columns-max-width="Name: 300, Buttons: 350, Attribute 1: 200"]
          </span>
          Use this attribute to restrict the width of specified below a certain maximum in non-responsive layout mode. Enter column names (in any order), for which you want a maximum width, each name followed by a colon ":" and then the maximum width in pixels for it. Then enter a comma "," and the next column-maximum width set and so on.
        </td>
      </tr>

      <tr>
        <td>css</td>
        <td>
          <span>
            general css: [wrct css="custom css code "]
          </span>
          <span>
            responsive layout css: [wrct css="@media(min-width:%breakpoint%){ ... css code ... }"]
          </span>
          <span>
            non-responsive layout css: [wrct css="@media(max-width:%breakpoint%){ ... css code ... }"]
          </span>
          You can enter any custom CSS in this attribute. %breakpoint% will be interpreted as the responsive breakpoint entered in the ui editor. Be careful about the quotation marks you use in your CSS code. If you use the same quotation marks inside the attribute value as outside it, you will break the shortcode.
        </td>
      </tr>

      <tr>
        <td>course-name-link</td>
        <td>
          <span>
            [wrct course-name-link="on / off"]
          </span>
          Turns the course name into a link. It will link to the WooCommerce product page for the course. To link elsewhere, create a new custom field on the course WC product page with name: wrct-link-course-name, and value: custom link.
        </td>
      </tr>

      <tr>
        <td>select</td>
        <td>
          <span>
            [wrct select="Select option"]
          </span>
          Enter custom text to replace the '-Select-' option in the drop-down fields. It is the first option in the select boxes and its purpose is to prompt users to select an option.
        </td>
      </tr>

      <tr>
        <td>reset</td>
        <td>
          <span>
            [wrct reset="Reset options"]
          </span>
          Enter custom text to replace the '-Reset all-' option in the drop-down fields. It is the first option in the select boxes and replaces 'select' once an option has already been selected. It resets all options.
        </td>
      </tr>

      <tr>
        <td>select-options-prompt</td>
        <td>
          <span>
            [wrct select-options-prompt="Please select required options in order to proceed with your booking!"]
          </span>
          Enter custom text to replace the prompt for users to select all required variation options for a product before proceeding to cart/checkout.
        </td>
      </tr>

      <tr>
        <td>query-args</td>
        <td>
          <span>
            [wrct query-args="posts_per_page=5"]
          </span>
          Developers can control which courses are displayed using inline query args for this attribute. Refrence: <a href="https://codex.wordpress.org/Class_Reference/WP_Query" target="_blank" rel="nofollow">WP_Query</a>.</td>
      </tr>

    </tbody>
  </table>

  <div class="heading" id="column-widths">Adjusting column widths</div>
  <p>
    The way HTML tables work, it might appear arbitrary how they assign widths to different columns.
  </p>
  <p>
    But with WRCT's tables you can assign width rules for the columns to meet your design needs. This makes it simple to deal with excessively narrow or broad columns in your tables.
  </p>
  <p>
    There are 3 shortcode attributes that deal with column widths and will help set rules for your table column widths. You can use any or a combination of these shortcode attributes:
  </p>
  <ul>
    <li><a href="#columns-width">columns-width</a></li>
    <li><a href="#columns-min-width">columns-min-width</a></li>
    <li><a href="#columns-max-width">columns-max-width</a></li>
  </ul>

  <div class="heading" id="responsiveness">Mobile and tablet responsiveness</div>
  <p>
    On mobile phones your tables will automatically display in a responsive layout with each table row displayed vertically instead of horizontally. Headings appear on the left and corresponding values on the right.
  </p>
  <p>
    You can choose at which screen width your tables switch to this responsive layout. Go to your table editor > styling panel and scroll down to find the option "Responsive layout below".
    This option lets you input the screen width in pixels, below which the table will switch to responsive mode. Its deafult value is 750 pixels.
    Accordingly, the tables by default switch to responsive layout mode on screens that are narrower than 750 pixels like mobiles.
  </p>
  <p>
    It follows that if you need this responsive layouts to be applied on tablets as well, change the value from the default 750 pixels to something higher like 1000 pixels.
  </p>
  <p>
    If you want the responsive layout to be applied on wider laptop/desktop screens as well then in crease the breakpoint to a much higher value like 3000 pixels.
  </p>
  <p>
    But there is another way to deal with tablet responsiveness. If you are finding that your table columns are getting too squished on tablets beacuse of the narrower screen then
    you can force a minimum width upon the table beneath which the table will become horizontally scrollable instead of squeezing its columns to fit in. Do this by going to your table
    editor > styling panel > "Table minimum width" option and giving it a numeric value.
  </p>

  <div class="heading" id="icons">Insert icons in column headings and buttons</div>
  <p>
    WRCT uses the Font Awesome icon library to generate icons. You can browse its 650+ icons <a href="http://fontawesome.io/icons/" target="_blank">here</a> and use any of them in your table column headings or buttons labels.
  </p>
  <p>
    Use the code %icon-name% in your table editor > columns > heading input boxes and table editor > buttons > Button label input boxes. Replace icon-name in %icon-name% with the icon name you found in the <a href="http://fontawesome.io/icons/" target="_blank">font awesome icon library</a>.
  </p>
  <p>
    So, for example, the code for the address-book icon will be %address-book% and you can place this in the column headings input boxes or the buttons label input boxes in the table efitor to make the address book icon appear in your table.
  </p>

  <div class="heading" id="price-buttons-text">Replace price and buttons with text</div>
  <p>
    If you want some text like "Call +999 999 999 for price" too appear in your price field instead of the actual price, then you can achieve this by going to the WooCommerce product page editor for the course and add a
    <a href="https://codex.wordpress.org/Custom_Fields#Usage" target="_blank">custom field</a> with the name "wrct-price-text" and its value should the text you want instead of price on your table.
  </p>
  <p>
    In the same way, if you want to replace the buttons of a course with some text go to the course's WooCommerce product editor page and set a custom field called "wrct-buttons-text" and in the value field enter the text you want instead of the buttons.
  </p>
  <p>
    If the text you are entering is long enough, it might widen your price / buttons column in your table too much. To control the width of the column either use the line break HTML tag <?php echo htmlentities("<br/>"); ?>
    in between the text where you need it to break into a new line, or else check the documentation on <a href="#column-widths">adjusting column widths</a>.
  </p>

  <div class="heading" id="replace-woocommerce-templates">Replace WooCommerce related producs / up-sell / cross-sell section with tables</div>
  <p>
    You can replace the default product grids that appear in these sections with WRCT tables. The same products will appear as tables instead of the default WooCommerce grid system.
  </p>
  <p>
    To replace any of these sections, go to WP Admin > WC Course Tables > Settings and check the relevant replacement options. You will find default replacment shortcodes already available for each section. If you delete a shortcode and leave the input box empty, the built-in default shortcode for that section will be used.
  </p>
  <p>
    You can cutomize the look of each of these section tables. There are two ways to do this:
  </p>
    <ul>
      <li>
        Use the [wrct] <a href="#shortcode-attributes">shortcode attributes</a> to modify how the table will appear. Just add the attributes to the default shortcode already provided for the section.
      </li>
      <li>
        Create a table with the backend editor and use its shortcode for the section. But, do not forget:
      </li>
    </ul>
  <p>
    <span class="highlight">You must add the relevant attribute for the section: related-products="true" / up-sell="true" / cross-sell="true"</span> in the shortcode for it to work for that section.
  </p>
  <p>
    Normally, using this system simply replaces the WooCommerce template for that section with a WRCT template which looks very similar except it inserts the relevant WRCT table where the WC products grids would otherwise appear.
  </p>
  <p>
    However, for some themes, or in the presence of some plugins WRCT may not be able to replace the section because the theme / plugin has customized the relevant WooCommerce section on its own. In that case you will need to place the WRCT shortcode directly in the code. While doing so, refer to the the WRCT plugin folder > wc-template > relevant section file. There you can see the code that WRCT uses to replace the relevant product grid in between /* wrct code begins */ and /* wrct code ends */. The WRCT code replaces everything in between woocommerce_product_loop_start() and woocommerce_product_loop_end().
  </p>

  </body>
</html>
