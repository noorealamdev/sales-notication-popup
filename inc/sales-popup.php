<?php

// Time ago order
function get_timeago( $ptime )
{
    $etime = time() - $ptime;

    if( $etime < 1 )
    {
        return 'less than 1 second ago';
    }

    $a = array( 12 * 30 * 24 * 60 * 60  =>  'year',
        30 * 24 * 60 * 60       =>  'month',
        24 * 60 * 60            =>  'day',
        60 * 60             =>  'hour',
        60                  =>  'minute',
        1                   =>  'second'
    );

    foreach( $a as $secs => $str )
    {
        $d = $etime / $secs;

        if( $d >= 1 )
        {
            $r = round( $d );
            return '' . $r . ' ' . $str . ( $r > 1 ? 's' : '' ) . ' ago';
        }
    }
}


//IP Lookup
//function get_ip_detail($ip){
//    $ip_response = file_get_contents('http://ip-api.com/json/'.$ip);
//    $ip_array=json_decode($ip_response);
//    return  $ip_array;
//}

//$user_ip=$_SERVER['REMOTE_ADDR'];
//$ip_array= get_ip_detail($user_ip);
//echo $country_name=$ip_array->country;
//echo $city=$ip_array->city;



function get_ajax_orders() {

    // The Query
    $args = array(
        'limit' => 15,
        'return' => 'ids',
        'status' => 'completed'
    );
    $query = new WC_Order_Query( $args );
    $orders = $query->get_orders();


    if ($orders) {
        $orders_array = array();
        foreach ($orders as $order_id) {
            $order = wc_get_order($order_id);
            $order_date = new WC_Order($order_id);
            $order_time = $order_date->get_date_paid();
            $customer_ip = get_post_meta( $order_id, '_customer_ip_address', true );

            $country_name = '';
            $city_name = '';
            if ($customer_ip) {
                $geo     = unserialize(file_get_contents("http://www.geoplugin.net/php.gp?ip=$customer_ip"));
                $country_name = $geo["geoplugin_countryName"];
                $city_name = $geo["geoplugin_city"];

                //$country_name = get_location_by_ip($customer_ip, 'country');
                //$city_name = get_location_by_ip($customer_ip, 'city');
            }


            $items = $order->get_items();

            if ($items) {
                foreach ($items as $item) {
                    $product_id = $item->get_product_id();
                    $product_name = $item->get_name();
                    //$product_url = get_permalink($product_id);
                    $product_img_url = get_the_post_thumbnail_url($product_id, 'small');

                    // get image from course
                    // $if_has_course = tutor_utils()->product_belongs_with_course( $product_id );
                    // if ($if_has_course) {
                    //     $course_id = $if_has_course->post_id;
                    //     $product_img_url = get_the_post_thumbnail_url($course_id, 'small');
                    // }

                }

                $newArray = array(
                    'product_name' => $product_name,
                    'product_image' => $product_img_url,
                    //'product_url' => $product_url,
                    'first_name' => $order->get_billing_first_name(),
                    'last_name' => $order->get_billing_last_name(),
                    'time_ago' => get_timeago( strtotime($order_time) ),
                    'country_name' => $country_name,
                    'city_name' => $city_name
                );

                array_push($orders_array, $newArray);
            }
        }

    }

    echo json_encode($orders_array);

    exit; // exit ajax call(or it will return useless information to the response)
}

// Fire AJAX action for both logged in and non-logged in users
add_action('wp_ajax_get_ajax_orders', 'get_ajax_orders');
add_action('wp_ajax_nopriv_get_ajax_orders', 'get_ajax_orders');



function inject_html_to_wp_body() { ?>

    <script>

        (function ($) {

            function startPopup() {
                $.ajax({
                    type: 'POST',
                    url: '<?php echo admin_url('admin-ajax.php');?>',
                    dataType: "json", // add data type
                    data: { action : 'get_ajax_orders' },
                    success: function( orders ) {
                        //console.log(orders);
                        for (let i=0; i < orders.length; i++){

                            let html = '';
                            html += '<div id="sales__report'+i+'" class="sales__report"> <a href="JavaScript:void(0)" class="sales__item show__notification"> <div class="customer__thumb">';

                            if (orders[i].product_image) {
                                html += '<img src="' + orders[i].product_image + '" width="70" height="70">';
                            }                            

			                // let productFullName = orders[i].product_name;
                            // let productShortName;
                            // if (productFullName.length > 29) {
                            //     productShortName = productFullName.substring(0, 29).concat('...');
                            // }
                            // else {
                            //     productShortName = orders[i].product_name;
                            // }


                            html += '</div> <div class="sales__info"> <h4 class="product__name customer__info"><span>'+orders[i].first_name+' '+orders[i].last_name.charAt(0)+'. </span>';

                            if (orders[i].country_name) {
                                html += '<span class="customer__from"><em>from</em> '+orders[i].city_name+', '+orders[i].country_name+' <span class="purchased-text-1" style="display: none">purchased</span></span>';
                            }

                            html += '</h4> <div class="customer__name"><span class="purchased-text-2">Purchased </span><strong>'+orders[i].product_name+'</strong> </div> <div class="purchase_time"> '+orders[i].time_ago+' </div> </div> </a> </div>';
                            
                            //$('body').append(html);
                            setTimeout(function () {
                                $('body').append(html);
                                $('#sales__report'+i+'').fadeIn('slow');
                                //console.log('running popup', i);
                                setTimeout(function () {
                                    $('#sales__report'+i+'').fadeOut('slow');
                                }, 5000);

                            }, i*10000);




                        }//for loop

                    }
                });//ajax end

            }
            startPopup()
            //setTimeout(startPopup, 3000);

        })(jQuery);

    </script>

<?php };

add_action('wp', 'page_check');
function page_check() {
    if (!is_page(30) && !is_page(31) && !is_page(21885)) {
        add_action( 'wp_body_open', 'inject_html_to_wp_body' );
    }
}