<?php
/*function redirect_pagination_to_404() {
    global $wp_query;

    if ( $wp_query->max_num_pages > 1 && is_paged() ) {
        status_header( 404 );
        nocache_headers();
        include( get_query_template( '404' ) );
        die;
    }
}
add_action( 'template_redirect', 'redirect_pagination_to_404' );*/

// Disable users and comments
function disable_rest_api_endpoints() {
    if (is_admin()) {
        return;
    }
    
    if (isset($_SERVER['REQUEST_URI'])) {
        $request_uri = $_SERVER['REQUEST_URI'];
        
        // Disable comments endpoint
        if (strpos($request_uri, '/wp-json/wp/v2/comments') !== false) {
            wp_die('REST API comments endpoint is disabled.', 'REST API Disabled', array('response' => 403));
        }
        
        // Disable users endpoint
        if (strpos($request_uri, '/wp-json/wp/v2/users') !== false) {
            wp_die('REST API users endpoint is disabled.', 'REST API Disabled', array('response' => 403));
        }
    }
}
add_action('init', 'disable_rest_api_endpoints');

// Disable xmlrpc
add_filter('xmlrpc_enabled', '__return_false');

add_action( 'wp_enqueue_scripts', 'salesgen_theme_enqueue_styles' );
function salesgen_theme_enqueue_styles() {
	wp_enqueue_style( 'child-style',
		get_stylesheet_directory_uri() . '/style.css',
		'bm-flatsome-child',
		wp_get_theme()->get('Version').'.'.rand(1,9999)
	);
}

// Free Shipping
function my_hide_shipping_when_free_is_available( $rates ) {
	$free = array();
	foreach ( $rates as $rate_id => $rate ) {
		if ( 'free_shipping' === $rate->method_id ) {
			$free[ $rate_id ] = $rate;
			break;
		}
	}
	return ! empty( $free ) ? $free : $rates;
}
add_filter( 'woocommerce_package_rates', 'my_hide_shipping_when_free_is_available', 100 );
// End Free Shipping

// Edit WooCommerce dropdown menu item of shop page
function my_woocommerce_catalog_orderby( $orderby ) {
    unset($orderby["price"]);
    unset($orderby["price-desc"]);
    return $orderby;
}
add_filter( 'woocommerce_catalog_orderby', 'my_woocommerce_catalog_orderby', 20 );

add_filter( 'woocommerce_product_tabs', 'salesgen_remove_product_tabs', 98 );

function salesgen_remove_product_tabs( $tabs ) {
	if(is_product()){
		unset( $tabs['additional_information'] );   // Remove the additional information tab
		unset( $tabs['reviews'] );   // Remove the additional information tab
	}
	return $tabs;
}
// Change WooCommerce "Related products" text
add_filter('gettext', 'change_rp_text', 10, 3);
add_filter('ngettext', 'change_rp_text', 10, 3);
function change_rp_text($translated, $text, $domain)
{
     if ($text === 'Related products' && $domain === 'woocommerce') {
         $translated = esc_html__('YOU MAY ALSO LIKE', $domain);
     }
     return $translated;
}
// Move Related Product
remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
add_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 9);
add_filter( 'woocommerce_product_csv_importer_check_import_file_path', '__return_false' );

add_filter( 'woocommerce_product_tabs', 'salesgen_new_product_tab' );
function salesgen_new_product_tab( $tabs ) {
// Adds the new tab
	$tabs['shipping_tab'] = array(
		'title'     => __( 'Shipping & Manufacturing Info', 'woocommerce' ),
		'priority'  => 10,
		'callback'  => 'salesgen_new_product_shipping_tab_content'
	);

	return $tabs;
}

function salesgen_new_product_shipping_tab_content(){
	echo do_shortcode( '[block id="shipping"]' );
}

/**
*   Change Proceed To Checkout Text in WooCommerce
*   Add this code in your active theme functions.php file
**/
function woocommerce_button_proceed_to_checkout() {
	
   $checkout_url = WC()->cart->get_checkout_url();
   ?>
   <a href="<?php echo $checkout_url; ?>" class="checkout-button button alt wc-forward">
   <svg xmlns="http://www.w3.org/2000/svg" fill="white" width="18" height="18" style="margin-bottom: -2px;" viewBox="0 0 8 8">
      <path d="M3 0c-1.1 0-2 .9-2 2v1h-1v4h6v-4h-1v-1c0-1.1-.9-2-2-2zm0 1c.56 0 1 .44 1 1v1h-2v-1c0-.56.44-1 1-1z" transform="translate(1)"></path>
   </svg>
  <?php _e( 'Checkout Details', 'woocommerce' ); ?></a>
  
   <?php
}

//force terms checkbox on checkout
add_action( 'template_redirect', 'salesgen_remove_my_action', 99 );
function salesgen_remove_my_action(){
	/* Quay tro ve checkout default thi xoa bo comment nay
	remove_action( 'woocommerce_checkout_terms_and_conditions', 'wc_checkout_privacy_policy_text', 20 );
	remove_action( 'woocommerce_checkout_terms_and_conditions', 'wc_terms_and_conditions_page_content', 30 );
	remove_action( 'woocommerce_after_cart_table', 'woocommerce_cross_sell_display' );
	remove_action( 'flatsome_product_box_actions', 'flatsome_lightbox_button' );
	add_action( 'woocommerce_after_cart', 'woocommerce_cross_sell_display' );
    remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
	add_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 9 );

	//coupons
	remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
	add_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_coupon_form', 30 );

	//payments
	remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );

	add_action( 'woocommerce_before_order_notes', 'woocommerce_checkout_payment', 90 );
	add_action( 'woocommerce_before_order_notes', 'salesgen_custom_heading_payment', 89 );

	remove_action( 'woocommerce_checkout_terms_and_conditions', 'wc_checkout_privacy_policy_text', 20 );
	remove_action( 'woocommerce_checkout_terms_and_conditions', 'wc_terms_and_conditions_page_content', 30 );
	remove_action( 'woocommerce_checkout_after_order_review', 'wc_checkout_privacy_policy_text', 1 );


	add_action( 'woocommerce_before_order_notes', 'wc_checkout_privacy_policy_text', 91 );
	add_action( 'woocommerce_before_order_notes', 'wc_terms_and_conditions_page_content', 92 );


	add_filter( 'woocommerce_enable_order_notes_field', '__return_false', 9999 );
	//modify filter position  
	remove_action( 'flatsome_category_title_alt', 'wc_setup_loop' );
	remove_action( 'flatsome_category_title_alt', 'woocommerce_result_count', 20 );
	remove_action( 'flatsome_category_title_alt', 'woocommerce_catalog_ordering', 30 );
	remove_action( 'flatsome_category_title', 'flatsome_shop_loop_tools_breadcrumbs', 10 );

	add_action( 'flatsome_category_filter', 'wc_setup_loop' );
	add_action( 'flatsome_category_filter', 'woocommerce_result_count', 20 );
	add_action( 'flatsome_category_filter', 'woocommerce_catalog_ordering', 30 );
	add_action( 'flatsome_category_breadcumb', 'flatsome_shop_loop_tools_breadcrumbs', 10 );
  	*/
	
	//categroy description
	remove_action( 'woocommerce_archive_description', 'woocommerce_product_archive_description', 10 );
	remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10 );

	add_action( 'flatsome_products_after', 'salesgen_products_archive_desc', 10 );

	add_action('flatsome_category_sublist', 'sg_display_subcategories_list', 10 ); 
}

function salesgen_products_archive_desc(){
	$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
		if ( $paged > 1) {
			return;
		}
		woocommerce_taxonomy_archive_description();
		woocommerce_product_archive_description();
}

// Remove Order Notes Field
add_filter( 'woocommerce_checkout_fields' , 'remove_order_notes' );

function remove_order_notes( $fields ) {
     unset($fields['order']['order_comments']);
     return $fields;
}

function salesgen_custom_heading_payment(){
?>
<div id="checkout_custom_heading"><div style="flex:none;"><h3 style="padding-top:0;">Payment Info</h3></div><div class="ta-right"><img class="ssl-secu perfmatters-lazy loaded" src="https://cdn.32pt.com/public/sl-retail/assets/logos/ssl-seal.svg" alt="Checkout 1" title="Checkout 1" width="48" height="22" data-src="https://cdn.32pt.com/public/sl-retail/assets/logos/ssl-seal.svg" data-was-processed="true"><noscript><img class="ssl-secu"
	src="https://cdn.32pt.com/public/sl-retail/assets/logos/ssl-seal.svg" alt="Checkout 1" title="Checkout 1" width="48" height="22"></noscript>
	<img class="norton-secu perfmatters-lazy loaded" src="https://i1.wp.com/cdn.32pt.com/public/sl-retail/assets/logos/norton-seal.png?resize=53%2C30&amp;ssl=1" alt="Checkout 2" title="Checkout 2" width="53" height="30" data-src="https://i1.wp.com/cdn.32pt.com/public/sl-retail/assets/logos/norton-seal.png?resize=53%2C30&amp;ssl=1" data-was-processed="true"><noscript><img class="norton-secu" src="https://i1.wp.com/cdn.32pt.com/public/sl-retail/assets/logos/norton-seal.png?resize=53%2C30&#038;ssl=1" alt="Checkout 2"
	title="Checkout 2" width="53" height="30"  data-recalc-dims="1"></noscript></div></div>
<?php
}

add_filter('woocommerce_get_price_html', 'sn_hide_variation_price', 99, 2);
function sn_hide_variation_price( $v_price, $v_product ) {
	$v_product_types = array( 'variable');
	//echo $v_price;
	if ( in_array ( $v_product->get_type(), $v_product_types ) && is_product() && (strpos($v_price, "&ndash;") !== false) ) {
		return '';
	}
	// return regular price
	return $v_price;
}


add_action( 'woocommerce_after_variations_table', 'woocommerce_single_variation_add_to_cart_button', 20 );

//add_action( 'init', 'remove_sn_actions' );

function remove_sn_actions() {
    remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20);
}


// Wrapper quantity input & add to cart button - start

add_action('woocommerce_before_add_to_cart_quantity', 'sg_add_to_cart_quantity_wrapper_open', 2);
add_action('woocommerce_after_add_to_cart_button', 'sg_add_to_cart_quantity_wrapper_close', 93);
function sg_add_to_cart_quantity_wrapper_open() {
	echo '<div class="sg_wrapper_add_to_cart_quantity">';
}

function sg_add_to_cart_quantity_wrapper_close() {
	echo '</div>';
}
// Wrapper quantity input & add to cart button - end


// Displaying the subcategories after category title

function sg_display_subcategories_list() {
	
    if ( is_product_category() ) {
		$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
		if ( $paged > 1) {
			return;
		}
        $term_id  = get_queried_object_id();
        $taxonomy = 'product_cat';

        // Get subcategories of the current category
        $terms    = get_terms([
            'taxonomy'    => $taxonomy,
            'hide_empty'  => false,
            'parent'      => $term_id
        ]);
      
        if ( count($terms) > 0 ) {
            $ids = array();
            foreach ( $terms as $term ) {
            $ids[] = $term->term_id;
            }
          
			echo do_shortcode('[gap height="10px"][ux_product_categories style="push" type="row" col_spacing="normal" columns="5" ids="'.implode(',',$ids).'"]');

        }
    }
}

// short code hieen thi tags trong description
add_shortcode('thien_display_title', 'podcustome_display_title');
function podcustome_display_title(){
    global $product;
    if (isset($product)) {
        return $product->get_name();
    }
    return;
}

// short code hien thi title information trong description
add_shortcode('thien_display_title_info', 'podcustome_display_title_info');
function podcustome_display_title_info(){
    global $product;
    if (isset($product)) {
        if(strpos($product->get_name(), 'T-Shirt') !== false){
        	$str=str_replace(' T-Shirt', '', $product->get_name());
            return $str;
        }
        else if(strpos($product->get_name(), 'Shirt') !== false){
        	$str=str_replace(' Shirt', '', $product->get_name());
            return $str;
        }
    }
    return;
}

// short code hieen thi tags trong description
add_shortcode('thien_display_tags', 'podcustome_display_tags');
function podcustome_display_tags(){
    global $product;
    $output = array();

    if (isset($product)) {
        $terms = wp_get_post_terms(get_the_id(), 'product_tag');
        foreach( $terms as $term) {
            $term_link = get_term_link( $term );
            $output[] = '<a href="' . esc_url( $term_link ) . '">' . $term->name . '</a>';//$term_name;
        }
        return implode(", ", $output);
    }
    return;
}

// short code hieen thi category trong description
add_shortcode('thien_display_category', 'podcustome_display_category');
function podcustome_display_category(){
    // Get the product categories set in the product
    $terms = get_the_terms($post->ID, 'product_cat');

    // Check that there is at leat one product category set for the product
    if(sizeof($terms) > 0){
        // Get Catelory Sub
        $term = reset($terms);
        // Get the term link (button link)
        $link = get_term_link( $term, 'product_cat' );
         return '<a href="' . esc_url( $link ) . '">' . $term->name . '</a>';//$term_name;
     }
}

// Shortcode hien thi anh attachment trong description
add_shortcode('thien_display_attachment_images', 'thien_display_attachment_images');
function thien_display_attachment_images() {
    global $product;
    if (isset($product)){
        $output = array();
        $title = $product->get_name();
        $image_id = $product->get_image_id();
        $image_src = wp_get_attachment_url($image_id);
        $html = '<img class="size-medium wp-image-287 aligncenter" src="'.$image_src.'" alt="'.$title.'" title="'.$title.'" width="400" height="400" />';
        $output[] = $html;
        $attachment_ids = $product->get_gallery_attachment_ids();
        foreach($attachment_ids as $attachment_id){
            $image_src = wp_get_attachment_url($attachment_id);
            $html = '<img class="size-medium wp-image-287 aligncenter" src="'.$image_src.'" alt="'.$title.'" title="'.$title.'" width="400" height="400" />';
            // $html = wp_get_attachment_image($attachment_id);
            $output[] = $html;
        }
        return implode(",",$output);
    }
    return;
}

add_shortcode('thien_display_focus_key', 'rm_shortcode_focus_keyword');

function rm_shortcode_focus_keyword() {
    return get_post_meta(get_queried_object_id(), 'rank_math_focus_keyword', true);
}

// Back to collection Anynee
add_shortcode('an_button_back_collection','add_button_back_collection');
function add_button_back_collection(){
    // Get the product categories set in the product
    $terms = get_the_terms($post->ID, 'product_cat');

    // Check that there is at leat one product category set for the product
    if(sizeof($terms) > 0){
        // Get Catelory Sub
        $term = reset($terms);
        // Get the term link (button link)
        $link = get_term_link( $term, 'product_cat' );

        // Text Back to Collection
        $text = __('Back to Collection');
		
        // Out put
        return '<p style="text-align: center; class="large primary button"><a href="'.esc_url($link).'" title="'.$text.'" class="button primary '.$term->slug.'">'.$text.'</a></p>';
    }
}
add_action('wp_footer', function(){?>
    <script>
    function radio_checked(){
        jQuery.each(jQuery('div input[type="radio"]:checked'),function(){
        var label=jQuery(this).siblings('label').text();
        var help=jQuery(this).parent('div').parent('div');
        console.log(help.siblings('.wcpa_helptext').length);
        if(help.siblings('.wcpa_helptext').length==0){
            help.before(('<span class="wcpa_helptext">'+label+'</span>'));
        }
        else{
            help.siblings('.wcpa_helptext').text(label);
        }
    });
    }
    jQuery(document).ready(function(){
        radio_checked();
    });
    jQuery('div input[type="radio"]').change(function(){
        radio_checked();
    });
    </script>
<?php  });
/**
 * Plugin Name: Button Top
 * Plugin URI: http://dev.com
 * Description: Plugin Button Top
 */
add_action('flatsome_footer', function () {
    ob_start(); ?>
    <button href="#top" class="back-to-top scroll-to-top round right" id="top-link">
        <div class="svg-container"><!--?xml version="1.0" encoding="UTF-8" standalone="no"?-->
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 42.67 64" height="1em" width="0.67em"
                 class="svg-class">
                <defs>
                    <style>.cls-1 {
                            fill: currentColor;
                        }</style>
                </defs>
                <title>Asset 45</title>
                <g id="Layer_2" data-name="Layer 2">
                    <g id="Layer_1-2" data-name="Layer 1">
                        <path class="cls-1"
                              d="M19.57.78.78,19.5a2.67,2.67,0,0,0,3.77,3.78L18.67,9.21V61.33a2.67,2.67,0,1,0,5.33,0V9L38.11,23.27a2.67,2.67,0,1,0,3.78-3.76L23.35.79a2.67,2.67,0,0,0-3.78,0Z"></path>
                    </g>
                </g>
            </svg>
        </div>
    </button>
    <style>
		.round {
			border-radius: 3px;
		}
		.back-to-top .right{
			right: 20px;
    		left: unset;
		}
        .scroll-to-top {
            position: fixed;
            bottom: 8.68vh !important;
            z-index: 201;
			margin-left: 0.12em;
			margin-right: 0.12em;
			min-width: 2.5em;
			padding-left: 0.8em;
			padding-right: 0.8em;
			display: inline-block;
            border: 2px solid currentColor;
    		background-color: white;
            transition: background 0.2s;
        }
        .scroll-to-top > div {
            font-size: 16px;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
       
        @media only screen and (max-width: 48em) {
            /*************** ADD MOBILE ONLY CSS HERE  ***************/
            .scroll-to-top {
                bottom: 11.68vh !important;
            }
        }
    </style>
    <?php
    echo ob_get_clean();
});
add_shortcode( 'bm_sizeguide_black', 'bm_size_chart' );
//add_action('woocommerce_before_add_to_cart_quantity', 'bm_size_chart', 1);
function bm_size_chart() {
 ?>
 <div class="bm_size_chart_wrp">
  <a href="#sizechart" target="_self" class="button primary is-xsmall" style="border-radius:99px;">
  <span>View Size Chart</span>
  </a>
 </div>
 
 <?php
}
/*
add_action( 'woocommerce_after_add_to_cart_button', 'cutoffchristmas2022',95);
function cutoffchristmas2022() {
	if ( is_product() ) 
	{
		echo '<div class="product-block product-block--cut_off_information">
<div class="cut_off--wrapper mg_top-12 product"> <div class="cut_off--header"> <p class="cut_off-title"><svg width="24" style="transform: scaleX(-1);" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"> <path d="M1 12.5V17.5C1 17.7652 1.10536 18.0196 1.29289 18.2071C1.48043 18.3946 1.73478 18.5 2 18.5H3C3 19.2956 3.31607 20.0587 3.87868 20.6213C4.44129 21.1839 5.20435 21.5 6 21.5C6.79565 21.5 7.55871 21.1839 8.12132 20.6213C8.68393 20.0587 9 19.2956 9 18.5H15C15 19.2956 15.3161 20.0587 15.8787 20.6213C16.4413 21.1839 17.2044 21.5 18 21.5C18.7956 21.5 19.5587 21.1839 20.1213 20.6213C20.6839 20.0587 21 19.2956 21 18.5H22C22.2652 18.5 22.5196 18.3946 22.7071 18.2071C22.8946 18.0196 23 17.7652 23 17.5V5.5C23 4.70435 22.6839 3.94129 22.1213 3.37868C21.5587 2.81607 20.7956 2.5 20 2.5H11C10.2044 2.5 9.44129 2.81607 8.87868 3.37868C8.31607 3.94129 8 4.70435 8 5.5V7.5H6C5.53426 7.5 5.07493 7.60844 4.65836 7.81672C4.24179 8.025 3.87944 8.32741 3.6 8.7L1.2 11.9C1.17075 11.9435 1.14722 11.9905 1.13 12.04L1.07 12.15C1.02587 12.2615 1.00216 12.3801 1 12.5ZM17 18.5C17 18.3022 17.0586 18.1089 17.1685 17.9444C17.2784 17.78 17.4346 17.6518 17.6173 17.5761C17.8 17.5004 18.0011 17.4806 18.1951 17.5192C18.3891 17.5578 18.5673 17.653 18.7071 17.7929C18.847 17.9327 18.9422 18.1109 18.9808 18.3049C19.0194 18.4989 18.9996 18.7 18.9239 18.8827C18.8482 19.0654 18.72 19.2216 18.5556 19.3315C18.3911 19.4414 18.1978 19.5 18 19.5C17.7348 19.5 17.4804 19.3946 17.2929 19.2071C17.1054 19.0196 17 18.7652 17 18.5ZM10 5.5C10 5.23478 10.1054 4.98043 10.2929 4.79289C10.4804 4.60536 10.7348 4.5 11 4.5H20C20.2652 4.5 20.5196 4.60536 20.7071 4.79289C20.8946 4.98043 21 5.23478 21 5.5V16.5H20.22C19.9388 16.1906 19.5961 15.9435 19.2138 15.7743C18.8315 15.6052 18.418 15.5178 18 15.5178C17.582 15.5178 17.1685 15.6052 16.7862 15.7743C16.4039 15.9435 16.0612 16.1906 15.78 16.5H10V5.5ZM8 11.5H4L5.2 9.9C5.29315 9.7758 5.41393 9.675 5.55279 9.60557C5.69164 9.53615 5.84475 9.5 6 9.5H8V11.5ZM5 18.5C5 18.3022 5.05865 18.1089 5.16853 17.9444C5.27841 17.78 5.43459 17.6518 5.61732 17.5761C5.80004 17.5004 6.00111 17.4806 6.19509 17.5192C6.38907 17.5578 6.56725 17.653 6.70711 17.7929C6.84696 17.9327 6.9422 18.1109 6.98079 18.3049C7.01937 18.4989 6.99957 18.7 6.92388 18.8827C6.84819 19.0654 6.72002 19.2216 6.55557 19.3315C6.39112 19.4414 6.19778 19.5 6 19.5C5.73478 19.5 5.48043 19.3946 5.29289 19.2071C5.10536 19.0196 5 18.7652 5 18.5ZM3 13.5H8V16.28C7.40983 15.7526 6.63513 15.4797 5.84469 15.5209C5.05425 15.5621 4.31212 15.914 3.78 16.5H3V13.5Z" fill="#0C94A4"></path></svg> <span>CHRISTMAS 2022 CUT-OFF DATE</span></p> <p class="cut_off-sub_title">Orders placed after below cutoff dates <strong>may not</strong> be delivered in time for Christmas:</p></div> <ul class="cut_off--content"> <li>US & EU Orders: <strong>Dec 5th</strong></li> <li>International Orders: <strong>Nov 30th</strong></li></ul> <div class="cut_off--footer"> <p class="cut_off-sub_title">It is still a great gift 🎄🎁 for new year eve or special gift for your beloved ones.</p></div>
</div></div>';
    }
}
*/
add_action( 'woocommerce_after_add_to_cart_button', 'bmdeliveryship',96);
function bmdeliveryship() {	
	if ( is_product() ) 
	{ 	
$dt = date("Y-m-d");
$categories = get_the_terms( get_the_ID(), 'product_cat' );

    if ( $categories && ! is_wp_error( $categories ) ) {
        $category_slugs = wp_list_pluck( $categories, 'slug' );

        if (  in_array( 'black-hoodie', $category_slugs ) || in_array( 'black-sweatshirt', $category_slugs ) || in_array( 'black-t-shirts', $category_slugs ) || in_array( 'white-hoodie', $category_slugs ) || in_array( 'white-t-shirts', $category_slugs ) || in_array( 'white-sweatshirt', $category_slugs ) || in_array( 'poster', $category_slugs ) ) {
            $songayxulymin=3;
			$songayxulymax=5;
			$songayshipmin=5; 
			$songayshipmax=10;
        } else {
            $songayxulymin=3;
			$songayxulymax=5;
			$songayshipmin=12; 
			$songayshipmax=18;
        }
    }

$dtfisrt=	date('M d', strtotime(date("Y-m-d") ));
$dateShip = date('M d', strtotime( "$dt + $songayxulymin weekday" )) . ' - ' . date('M d', strtotime( "$dt + $songayxulymax weekday" ));
$dateDelivered = date('M d', strtotime( "$dt + $songayshipmin weekday" )) . ' - ' . date('M d', strtotime( "$dt + $songayshipmax weekday" ));
echo '
<div class="shipping-info">
    <div id="ywcdd_info_shipping_date"><span class="ywcdd_shipping_message"><span class="est-desc">Estimated arrival:</span> Place your order today (standard shipping method) to receive the product(s) within <span class="ywcdd_date_info shipping_date">'.$dateDelivered.'</span></span><p class="fulfillinfo">Products are fulfilled and shipping from the <span class="middle-svg"><svg xmlns="http://www.w3.org/2000/svg" id="flag-icons-us" viewBox="0 0 640 480">
  <g fill-rule="evenodd">
    <g stroke-width="1pt">
      <path fill="#bd3d44" d="M0 0h912v37H0zm0 73.9h912v37H0zm0 73.8h912v37H0zm0 73.8h912v37H0zm0 74h912v36.8H0zm0 73.7h912v37H0zM0 443h912V480H0z"></path>
      <path fill="#fff" d="M0 37h912v36.9H0zm0 73.8h912v36.9H0zm0 73.8h912v37H0zm0 73.9h912v37H0zm0 73.8h912v37H0zm0 73.8h912v37H0z"></path>
    </g>
    <path fill="#192f5d" d="M0 0h364.8v258.5H0z"></path>
    <path fill="#fff" d="m30.4 11 3.4 10.3h10.6l-8.6 6.3 3.3 10.3-8.7-6.4-8.6 6.3L25 27.6l-8.7-6.3h10.9zm60.8 0 3.3 10.3h10.8l-8.7 6.3 3.2 10.3-8.6-6.4-8.7 6.3 3.3-10.2-8.6-6.3h10.6zm60.8 0 3.3 10.3H166l-8.6 6.3 3.3 10.3-8.7-6.4-8.7 6.3 3.3-10.2-8.7-6.3h10.8zm60.8 0 3.3 10.3h10.8l-8.7 6.3 3.3 10.3-8.7-6.4-8.7 6.3 3.4-10.2-8.8-6.3h10.7zm60.8 0 3.3 10.3h10.7l-8.6 6.3 3.3 10.3-8.7-6.4-8.7 6.3 3.3-10.2-8.6-6.3h10.7zm60.8 0 3.3 10.3h10.8l-8.8 6.3 3.4 10.3-8.7-6.4-8.7 6.3 3.4-10.2-8.8-6.3h10.8zM60.8 37l3.3 10.2H75l-8.7 6.2 3.2 10.3-8.5-6.3-8.7 6.3 3.1-10.3-8.4-6.2h10.7zm60.8 0 3.4 10.2h10.7l-8.8 6.2 3.4 10.3-8.7-6.3-8.7 6.3 3.3-10.3-8.7-6.2h10.8zm60.8 0 3.3 10.2h10.8l-8.7 6.2 3.3 10.3-8.7-6.3-8.7 6.3 3.3-10.3-8.6-6.2H179zm60.8 0 3.4 10.2h10.7l-8.8 6.2 3.4 10.3-8.7-6.3-8.6 6.3 3.2-10.3-8.7-6.2H240zm60.8 0 3.3 10.2h10.8l-8.7 6.2 3.3 10.3-8.7-6.3-8.7 6.3 3.3-10.3-8.6-6.2h10.7zM30.4 62.6l3.4 10.4h10.6l-8.6 6.3 3.3 10.2-8.7-6.3-8.6 6.3L25 79.3 16.3 73h10.9zm60.8 0L94.5 73h10.8l-8.7 6.3 3.2 10.2-8.6-6.3-8.7 6.3 3.3-10.3-8.6-6.3h10.6zm60.8 0 3.3 10.3H166l-8.6 6.3 3.3 10.2-8.7-6.3-8.7 6.3 3.3-10.3-8.7-6.3h10.8zm60.8 0 3.3 10.3h10.8l-8.7 6.3 3.3 10.2-8.7-6.3-8.7 6.3 3.4-10.3-8.8-6.3h10.7zm60.8 0 3.3 10.3h10.7l-8.6 6.3 3.3 10.2-8.7-6.3-8.7 6.3 3.3-10.3-8.6-6.3h10.7zm60.8 0 3.3 10.3h10.8l-8.8 6.3 3.4 10.2-8.7-6.3-8.7 6.3 3.4-10.3-8.8-6.3h10.8zM60.8 88.6l3.3 10.2H75l-8.7 6.3 3.3 10.3-8.7-6.4-8.7 6.3 3.3-10.2-8.6-6.3h10.7zm60.8 0 3.4 10.2h10.7l-8.8 6.3 3.4 10.3-8.7-6.4-8.7 6.3 3.3-10.2-8.7-6.3h10.8zm60.8 0 3.3 10.2h10.8l-8.7 6.3 3.3 10.3-8.7-6.4-8.7 6.3 3.3-10.2-8.6-6.3H179zm60.8 0 3.4 10.2h10.7l-8.7 6.3 3.3 10.3-8.7-6.4-8.6 6.3 3.2-10.2-8.7-6.3H240zm60.8 0 3.3 10.2h10.8l-8.7 6.3 3.3 10.3-8.7-6.4-8.7 6.3 3.3-10.2-8.6-6.3h10.7zM30.4 114.5l3.4 10.2h10.6l-8.6 6.3 3.3 10.3-8.7-6.4-8.6 6.3L25 131l-8.7-6.3h10.9zm60.8 0 3.3 10.2h10.8l-8.7 6.3 3.2 10.2-8.6-6.3-8.7 6.3 3.3-10.2-8.6-6.3h10.6zm60.8 0 3.3 10.2H166l-8.6 6.3 3.3 10.3-8.7-6.4-8.7 6.3 3.3-10.2-8.7-6.3h10.8zm60.8 0 3.3 10.2h10.8l-8.7 6.3 3.3 10.3-8.7-6.4-8.7 6.3 3.4-10.2-8.8-6.3h10.7zm60.8 0 3.3 10.2h10.7L279 131l3.3 10.3-8.7-6.4-8.7 6.3 3.3-10.2-8.6-6.3h10.7zm60.8 0 3.3 10.2h10.8l-8.8 6.3 3.4 10.3-8.7-6.4-8.7 6.3L329 131l-8.8-6.3h10.8zM60.8 140.3l3.3 10.3H75l-8.7 6.2 3.3 10.3-8.7-6.4-8.7 6.4 3.3-10.3-8.6-6.3h10.7zm60.8 0 3.4 10.3h10.7l-8.8 6.2 3.4 10.3-8.7-6.4-8.7 6.4 3.3-10.3-8.7-6.3h10.8zm60.8 0 3.3 10.3h10.8l-8.7 6.2 3.3 10.3-8.7-6.4-8.7 6.4 3.3-10.3-8.6-6.3H179zm60.8 0 3.4 10.3h10.7l-8.7 6.2 3.3 10.3-8.7-6.4-8.6 6.4 3.2-10.3-8.7-6.3H240zm60.8 0 3.3 10.3h10.8l-8.7 6.2 3.3 10.3-8.7-6.4-8.7 6.4 3.3-10.3-8.6-6.3h10.7zM30.4 166.1l3.4 10.3h10.6l-8.6 6.3 3.3 10.1-8.7-6.2-8.6 6.2 3.2-10.2-8.7-6.3h10.9zm60.8 0 3.3 10.3h10.8l-8.7 6.3 3.3 10.1-8.7-6.2-8.7 6.2 3.4-10.2-8.7-6.3h10.6zm60.8 0 3.3 10.3H166l-8.6 6.3 3.3 10.1-8.7-6.2-8.7 6.2 3.3-10.2-8.7-6.3h10.8zm60.8 0 3.3 10.3h10.8l-8.7 6.3 3.3 10.1-8.7-6.2-8.7 6.2 3.4-10.2-8.8-6.3h10.7zm60.8 0 3.3 10.3h10.7l-8.6 6.3 3.3 10.1-8.7-6.2-8.7 6.2 3.3-10.2-8.6-6.3h10.7zm60.8 0 3.3 10.3h10.8l-8.8 6.3 3.4 10.1-8.7-6.2-8.7 6.2 3.4-10.2-8.8-6.3h10.8zM60.8 192l3.3 10.2H75l-8.7 6.3 3.3 10.3-8.7-6.4-8.7 6.3 3.3-10.2-8.6-6.3h10.7zm60.8 0 3.4 10.2h10.7l-8.8 6.3 3.4 10.3-8.7-6.4-8.7 6.3 3.3-10.2-8.7-6.3h10.8zm60.8 0 3.3 10.2h10.8l-8.7 6.3 3.3 10.3-8.7-6.4-8.7 6.3 3.3-10.2-8.6-6.3H179zm60.8 0 3.4 10.2h10.7l-8.7 6.3 3.3 10.3-8.7-6.4-8.6 6.3 3.2-10.2-8.7-6.3H240zm60.8 0 3.3 10.2h10.8l-8.7 6.3 3.3 10.3-8.7-6.4-8.7 6.3 3.3-10.2-8.6-6.3h10.7zM30.4 217.9l3.4 10.2h10.6l-8.6 6.3 3.3 10.2-8.7-6.3-8.6 6.3 3.2-10.3-8.7-6.3h10.9zm60.8 0 3.3 10.2h10.8l-8.7 6.3 3.3 10.2-8.7-6.3-8.7 6.3 3.4-10.3-8.7-6.3h10.6zm60.8 0 3.3 10.2H166l-8.4 6.3 3.3 10.2-8.7-6.3-8.7 6.3 3.3-10.3-8.7-6.3h10.8zm60.8 0 3.3 10.2h10.8l-8.7 6.3 3.3 10.2-8.7-6.3-8.7 6.3 3.4-10.3-8.8-6.3h10.7zm60.8 0 3.3 10.2h10.7l-8.6 6.3 3.3 10.2-8.7-6.3-8.7 6.3 3.3-10.3-8.6-6.3h10.7zm60.8 0 3.3 10.2h10.8l-8.8 6.3 3.4 10.2-8.7-6.3-8.7 6.3 3.4-10.3-8.8-6.3h10.8z"></path>
  </g>
</svg> <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" id="flag-icons-eu" viewBox="0 0 640 480">
  <defs>
    <g id="d">
      <g id="b">
        <path id="a" d="m0-1-.3 1 .5.1z"></path>
        <use xlink:href="#a" transform="scale(-1 1)"></use>
      </g>
      <g id="c">
        <use xlink:href="#b" transform="rotate(72)"></use>
        <use xlink:href="#b" transform="rotate(144)"></use>
      </g>
      <use xlink:href="#c" transform="scale(-1 1)"></use>
    </g>
  </defs>
  <path fill="#039" d="M0 0h640v480H0z"></path>
  <g fill="#fc0" transform="translate(320 242.3) scale(23.7037)">
    <use xlink:href="#d" width="100%" height="100%" y="-6"></use>
    <use xlink:href="#d" width="100%" height="100%" y="6"></use>
    <g id="e">
      <use xlink:href="#d" width="100%" height="100%" x="-6"></use>
      <use xlink:href="#d" width="100%" height="100%" transform="rotate(-144 -2.3 -2.1)"></use>
      <use xlink:href="#d" width="100%" height="100%" transform="rotate(144 -2.1 -2.3)"></use>
      <use xlink:href="#d" width="100%" height="100%" transform="rotate(72 -4.7 -2)"></use>
      <use xlink:href="#d" width="100%" height="100%" transform="rotate(72 -5 .5)"></use>
    </g>
    <use xlink:href="#e" width="100%" height="100%" transform="scale(-1 1)"></use>
  </g>
</svg></span></p></div>
    <div class="fulfillment_timeline_date">
        <div class="time">
            <div class="icon-holder">
                <span class="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none" aria-hidden="true" focusable="false">
                        <path d="M10.3126 10.2524L12.1726 6.80245L10.8451 5.48245L7.53761 2.17495C7.43156 2.07363 7.29053 2.01709 7.14386 2.01709C6.99719 2.01709 6.85616 2.07363 6.75011 2.17495C6.65938 2.27881 6.60938 2.41204 6.60938 2.54995C6.60938 2.68786 6.65938 2.82109 6.75011 2.92495L9.84761 6.49495L9.31511 7.02745L4.89011 2.70745C4.83647 2.6537 4.77276 2.61106 4.70262 2.58197C4.63248 2.55288 4.55729 2.53791 4.48136 2.53791C4.40543 2.53791 4.33024 2.55288 4.2601 2.58197C4.18996 2.61106 4.12625 2.6537 4.07261 2.70745V2.70745C3.97079 2.81475 3.91404 2.95703 3.91404 3.10495C3.91404 3.25287 3.97079 3.39515 4.07261 3.50245L8.21261 8.12995L7.68761 8.66245L3.82511 4.82995C3.77306 4.77213 3.70945 4.7259 3.63838 4.69425C3.56732 4.66261 3.4904 4.64625 3.41261 4.64625C3.33482 4.64625 3.2579 4.66261 3.18683 4.69425C3.11577 4.7259 3.05215 4.77213 3.00011 4.82995V4.82995C2.90938 4.93381 2.85938 5.06704 2.85938 5.20495C2.85938 5.34286 2.90938 5.47609 3.00011 5.57995L6.60011 9.74995L6.07511 10.2824L3.00011 7.67995C2.89556 7.59068 2.76259 7.54163 2.62511 7.54163C2.48763 7.54163 2.35466 7.59068 2.25011 7.67995V7.67995C2.19636 7.73359 2.15373 7.7973 2.12463 7.86744C2.09554 7.93758 2.08057 8.01277 2.08057 8.0887C2.08057 8.16463 2.09554 8.23982 2.12463 8.30996C2.15373 8.38009 2.19636 8.44381 2.25011 8.49745L5.02511 11.2649L9.27011 15.5099C9.40942 15.6494 9.57485 15.7601 9.75695 15.8355C9.93904 15.911 10.1342 15.9499 10.3314 15.9499C10.5285 15.9499 10.7237 15.911 10.9058 15.8355C11.0879 15.7601 11.2533 15.6494 11.3926 15.5099L15.1426 11.7974L15.5476 11.3924C15.7446 11.195 15.8827 10.9465 15.9463 10.6749C16.0099 10.4033 15.9965 10.1194 15.9076 9.85495L13.5001 3.88495L13.0501 4.03495C12.9466 4.07147 12.8557 4.13672 12.7879 4.22305C12.7202 4.30939 12.6785 4.41324 12.6676 4.52245L12.9676 7.59745L10.8451 10.7849L10.3126 10.2524Z" fill="#222222"></path>
                    </svg>  
                </span>
            </div>
            <p class="date">'.$dtfisrt.'</p>
            <button class="edd-description" aria-describedby="tooltip" data-tippy-content="We will take '.$songayxulymin.'-'.$songayxulymax.' business days to prepare it for shipment.">Order placed</button>
        </div>
        <div class="time">
            <div class="icon-holder">
                <span class="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path d="M21.868,11.5l-4-7A1,1,0,0,0,17,4H5A1,1,0,0,0,4,5V6H2A1,1,0,1,0,2,8H6a1,1,0,0,1,0,2H3a1,1,0,0,0,0,2H5a1,1,0,1,1,0,2H4v3a1,1,0,0,0,1,1H6.05a2.5,2.5,0,0,0,4.9,0h4.1a2.5,2.5,0,0,0,4.9,0H21a1,1,0,0,0,1-1V12A1,1,0,0,0,21.868,11.5ZM8.5,19A1.5,1.5,0,1,1,10,17.5,1.5,1.5,0,0,1,8.5,19Zm5.488-8V6h1.725l2.845,5h-4.57ZM17.5,19A1.5,1.5,0,1,1,19,17.5,1.5,1.5,0,0,1,17.5,19Z">
                        </path>
                    </svg>
                </span>
            </div>
            <p class="date">'.$dateShip.'</p>
            <button class="edd-description" aria-describedby="tooltip" data-tippy-content="We puts your order in the mail.">Order ships</button>
        </div>
        <div class="time">
            <div class="icon-holder">
                <span class="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path d="M21,9.25A1.25,1.25,0,0,0,19.75,8H12.41l4.29-4.29a1,1,0,0,0-1.41-1.41L12,5.59,10.71,4.29A1,1,0,0,0,9.29,5.71L11.59,8H4.25A1.25,1.25,0,0,0,3,9.25V15H4v5.75A1.25,1.25,0,0,0,5.25,22h13.5A1.25,1.25,0,0,0,20,20.75V15h1ZM19,10v3H13V10ZM5,10h6v3H5ZM6,20V15h5v5Zm12,0H13V15h5Z">
                        </path>
                    </svg>
                </span>
            </div>
            <p class="date">'.$dateDelivered.'</p>
            <button class="edd-description" aria-describedby="tooltip" data-tippy-content="Estimated to arrive at your doorstep '.$dateDelivered.'">Delivered!</button>
        </div>
    </div>
</div>
';
}
}

add_shortcode( 'bm_sizeguide_white', 'bm_size_chart_white' );
//add_action('woocommerce_before_add_to_cart_quantity', 'bm_size_chart', 1);
function bm_size_chart_white() {
 ?>
 <div class="bm_size_chart_wrp">
  <a href="#sizechart" target="_self" class="button primary is-xsmall" style="border-radius:99px;">
  <span>View Size Chart</span>
  </a>
 </div>

 <?php
}

/*
Remove Jetpack Related Posts from WooCommerce product pages
*/

add_filter('wp', function () {

  // Check if Jetpack Related Posts is active
  if (class_exists('Jetpack_RelatedPosts')) {

    // Initialise Jetpack Related Posts
    $jprp = Jetpack_RelatedPosts::init();
    
    // Only remove related posts on WooCommerce product page
    if (is_singular('product')) {

      // Remove 
      remove_filter('the_content', array($jprp, 'filter_add_target_to_dom'), 40);

    }

  }
  
}, 20);
/**
 * Function to disable WooCommerce breadcrumbs
 */
function remove_wc_breadcrumbs() {
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0 );
}
add_action( 'init', 'remove_wc_breadcrumbs' );
function breadcrum2(){
    return rank_math_the_breadcrumbs();
}
add_action('flatsome_breadcrumb','breadcrum2',29);
function breadcrum(){
    array_shift($breadcrumb);
    return $breadcrumb;
}
add_action('woocommerce_get_breadcrumb','breadcrum',99);
/**
* Optimize WooCommerce Scripts
* Remove WooCommerce Generator tag, styles, and scripts from non WooCommerce pages.
*/
add_action( 'wp_enqueue_scripts', 'child_manage_woocommerce_styles', 99 );
function child_manage_woocommerce_styles() {
//remove generator meta tag
    remove_action( 'wp_head', array( $GLOBALS['woocommerce'], 'generator' ) );
    //first check that woo exists to prevent fatal errors
    if ( function_exists( 'is_woocommerce' ) )
    {
        //dequeue scripts and styles
        if ( ! is_woocommerce() && ! is_cart() && ! is_checkout() )
        {
        wp_dequeue_style( 'woocommerce_frontend_styles' );
        wp_dequeue_style( 'woocommerce_fancybox_styles' );
        wp_dequeue_style( 'woocommerce_chosen_styles' );
        wp_dequeue_style( 'woocommerce_prettyPhoto_css' );
        wp_dequeue_script( 'wc_price_slider' );
        wp_dequeue_script( 'wc-single-product' );
        wp_dequeue_script( 'wc-add-to-cart' );
        wp_dequeue_script( 'wc-cart-fragments' );
        wp_dequeue_script( 'wc-checkout' );
        wp_dequeue_script( 'wc-add-to-cart-variation' );
        wp_dequeue_script( 'wc-single-product' );
        wp_dequeue_script( 'wc-cart' );
        wp_dequeue_script( 'wc-chosen' );
        wp_dequeue_script( 'woocommerce' );
        wp_dequeue_script( 'prettyPhoto' );
        wp_dequeue_script( 'prettyPhoto-init' );
        wp_dequeue_script( 'jquery-blockui' );
        wp_dequeue_script( 'jquery-placeholder' );
        wp_dequeue_script( 'fancybox' );
        wp_dequeue_script( 'jqueryui' );
        }
    }
}

/**
* Remove display notice
* Remove default sorting drop-down from WooCommerce
*/
function removedefault(){
    remove_action( 'flatsome_category_title_alt', 'woocommerce_result_count', 20 );
    remove_action( 'flatsome_category_title_alt', 'woocommerce_catalog_ordering', 30 );
}
add_action('init','removedefault');

add_filter('wp_feed_cache_transient_lifetime', function () {
 return 900;
});

//Tu dong update focus keyword rankmath
function update_focus_keywords_rankmath()
{
	if(is_admin()){
		// Get draft products.
		$args = array(
			'status' => 'draft',
			 'limit' => -1 
		);
		$products = wc_get_products($args);

		foreach($products as $product){
			// Checks if Rank Math keyword already exists and only updates if it doesn't have it
			$rank_math_keyword = get_post_meta($product->post->ID, 'rank_math_focus_keyword', true );
			 if ($rank_math_keyword == "" || $rank_math_keyword == NULL || $rank_math_keyword == "Not Set"){
				 update_post_meta($product->post->ID,'rank_math_focus_keyword',strtolower(get_the_title($product->post->ID)));
			}		
		}
	}
}
add_action('init', 'update_focus_keywords_rankmath');

add_filter( 'rank_math/seo_analysis/postmeta_table_limit', function( $limit ) { return 1000000; } );

add_filter( 'rank_math/sitemap/enable_caching', '__return_false');

/**
* WP-Tang Toc Web
* Edits by Viet Minh
*/
add_action( 'after_setup_theme','wptangtoc_xoa_style_global_css');
function wptangtoc_xoa_style_global_css(){
remove_action('wp_enqueue_scripts', 'wp_enqueue_global_styles');
remove_action('wp_footer', 'wp_enqueue_global_styles', 1);
remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');
remove_action('in_admin_header', 'wp_global_styles_render_svg_filters');
}

$filters = array('the_content', 'the_title', 'wp_title', 'comment_text');
foreach($filters as $filter) {
	$priority = has_filter($filter, 'capital_P_dangit');
	if($priority !== false) {
		remove_filter($filter, 'capital_P_dangit', $priority);
	}
}

// REMOVE WP EMOJI
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');

remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );

//disable gg font
add_action('template_redirect', 'wptangtoc_disable_google_fonts');
function wptangtoc_disable_google_fonts() {
	ob_start('wptangtoc_disable_google_fonts_regex');
}

function wptangtoc_disable_google_fonts_regex($html) {
	$html = preg_replace('/<link[^<>]*\/\/fonts\.(googleapis|google|gstatic)\.com[^<>]*>/i', '', $html);
	return $html;
}

add_filter( 'auto_update_plugin', '__return_false' );
function disable_classic_theme_styles() {
wp_deregister_style('classic-theme-styles');
wp_dequeue_style('classic-theme-styles');
}
add_filter('wp_enqueue_scripts', 'disable_classic_theme_styles', 100);
add_filter( "rank_math/snippet/rich_snippet_product_entity", function( $entity ) {
    // Return policy
    $entity['offers']['hasMerchantReturnPolicy']['@type'] = 'MerchantReturnPolicy';
    $entity['offers']['hasMerchantReturnPolicy']['applicableCountry'] = 'US';
    $entity['offers']['hasMerchantReturnPolicy']['returnPolicyCategory'] = 'https://schema.org/MerchantReturnFiniteReturnWindow';
    $entity['offers']['hasMerchantReturnPolicy']['merchantReturnDays'] = 30;
    $entity['offers']['hasMerchantReturnPolicy']['returnMethod'] = 'https://schema.org/ReturnByMail';
    $entity['offers']['hasMerchantReturnPolicy']['returnFees'] = 'https://schema.org/FreeReturn';
    
    // Shipping details
    $entity['offers']['shippingDetails'] = array(
        '@type' => 'OfferShippingDetails',
        'shippingRate' => array(
            '@type' => 'MonetaryAmount',
            'value' => '5.99',
            'currency' => 'USD'
        ),
        'deliveryTime' => array(
            '@type' => 'ShippingDeliveryTime',
            'businessDays' => array(
                '@type' => 'OpeningHoursSpecification',
                'closes' => '17:00:00',
                'dayOfWeek' => array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'),
                'opens' => '09:00:00'
            ),
            'handlingTime' => array(
                '@type' => 'QuantitativeValue',
                'minValue' => '1',
                'maxValue' => '3',
                'unitCode' => 'DAY'
            ),
            'transitTime' => array(
                '@type' => 'QuantitativeValue',
                'minValue' => '4',
                'maxValue' => '8',
                'unitCode' => 'DAY'
            )
        ),
        'shippingDestination' => array(
            '@type' => 'DefinedRegion',
            'addressCountry' => 'US'
        )
    );
    
    $entity['review'] = array(
        '@type' => 'Review',
        'reviewRating' => array(
            '@type' => 'Rating',
            'ratingValue' => '4',
            'bestRating' => '5'
        ),
        'author' => array(
            '@type' => 'Person',
            'name' => get_the_author_meta( 'display_name', get_post_field( 'post_author', get_the_ID() ) )
        )
    );
    
    $entity['aggregateRating'] = array(
        '@type' => 'AggregateRating',
        'ratingValue' => '4.8',
        'reviewCount' => '68'
    );
 return $entity;
});
// Add custom Theme Functions here