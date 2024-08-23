<?php
function homey_enqueue_styles() {

    // enqueue parent styles
    wp_enqueue_style('homey-parent-theme', get_template_directory_uri() .'/style.css');

    // enqueue child styles
    wp_enqueue_style('homey-child-theme', get_stylesheet_directory_uri() .'/style.css', array('homey-parent-theme'));

    wp_enqueue_script('custom-period-ajax', get_stylesheet_directory_uri() . '/js/custom.js', array('jquery'), '1.0.0', true);

}
add_action('wp_enqueue_scripts', 'homey_enqueue_styles');


/*-----------------------------------------------------------------------------------*/
/*  Save listing custom periods
/*-----------------------------------------------------------------------------------*/
add_action( 'wp_ajax_homey_add_custom_period_bulk', 'homey_add_custom_period_bulk' );
if(!function_exists('homey_add_custom_period_bulk')) {
    function homey_add_custom_period_bulk() {
        global $current_user;

        $custom_period_prices = isset( $_POST['custom_prices'] ) ? $_POST['custom_prices'] : [];
        
        $listing_id     = intval(isset($_POST['listing_id']) ? $_POST['listing_id'] : 0);
        $current_user   = wp_get_current_user();
        $userID         = $current_user->ID;
        
        $local = homey_get_localization();
        
        $current_period_meta_array = get_post_meta($listing_id, 'homey_custom_period', true);
        if(empty($current_period_meta_array)) {
            $current_period_meta_array = array();
        }
        
        if ( !is_user_logged_in() ) {   
            echo json_encode(array(
                'success' => false,
                'message' => $local['kidding_text']
            ));
            wp_die();
        }
        if($userID === 0 ) {
            echo json_encode(array(
                'success' => false,
                'message' => $local['kidding_text']
            ));
            wp_die();
        }
        
        if ( !empty( $custom_period_prices ) ) {
            foreach ($custom_period_prices as $key => $value) {
                $allowded_html  = array();
                $period_meta    = array();
                $start_date     = wp_kses  ( convert_date( $value['start'] ), $allowded_html );
                $end_date       = wp_kses  ( convert_date( $value['end'] ), $allowded_html );
                $night_price    = floatval ( isset($value['price']) ? $value['price'] : 0 );
                $guest_price    = floatval ( isset($_POST['additional_guest_price']) ? $_POST['additional_guest_price'] : 0 );
                $weekend_price  = floatval ( isset($_POST['weekend_price']) ? $_POST['weekend_price'] : 0 );
                
                $the_post = get_post( $listing_id);
                
                $period_meta['night_price'] = $night_price; 
                $period_meta['weekend_price'] = $weekend_price;
                $period_meta['guest_price'] = $guest_price;

                $start_date         =  date('d-m-Y', custom_strtotime($start_date));
                $end_date           =  date('d-m-Y', custom_strtotime($end_date));
                
                $start_date         =  new DateTime($start_date);
                $start_date_unix    =  $start_date->getTimestamp();
                
                $end_date           =  new DateTime($end_date);
                $end_date_unix      =  $end_date->getTimestamp();
                
                $current_period_meta_array[$start_date_unix] = $period_meta;

                $start_date->modify('tomorrow');
                $start_date_unix =   $start_date->getTimestamp();

                while ($start_date_unix <= $end_date_unix) {
                    $current_period_meta_array[$start_date_unix] = $period_meta;
                    //print 'memx '.memory_get_usage ().' </br>/';
                    $start_date->modify('tomorrow');
                    $start_date_unix =   $start_date->getTimestamp();
                }
            }

            update_post_meta($listing_id, 'homey_custom_period', $current_period_meta_array );
        }
        
        echo json_encode(array(
            'success' => true,
            'message' => 'success'
        ));
        wp_die();
    }
}

function convert_date( $inputDate ) {
    $dateTimeObj = DateTime::createFromFormat('d/m/Y', $inputDate);
    $formattedDate = $dateTimeObj->format('Y-m-d');

    return $formattedDate;
}

/*-----------------------------------------------------------------------------------*/
/*  End Save listing custom periods
/*-----------------------------------------------------------------------------------*/

?>