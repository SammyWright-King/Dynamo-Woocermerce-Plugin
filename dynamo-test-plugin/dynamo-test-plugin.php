<?php

/**
 * @package
 * @author Muyiwa
 *
 * @wordpress-plugin
 * Plugin Name: Dynamo Test Plugin
 * Plugin URI: localhost
 * Author: Muyiwa
 * Author Name: Olumuyiwa
 * Description: This plugin is only a basic implementation of social login
 * Author URI: http://localhost
 * Version: 0.0.1
 * License: MIT
 * Text-Domain: dynamo-test-plugin
 */

if(! in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) return;

add_action('plugins_loaded', 'Dynamo_payment_init', 11);

function Dynamo_payment_init(){
    if(class_exists('WC_Payment_Gateway')){
        class WC_Dynamo_Payment_Gateway extends WC_Payment_Gateway {
            public function __construct(){
                $this->id = 'dynamo_payment';
                $this->has_fields = false;
                $this->method_title = __('Dynamo Payment', 'dynamo-test-plugin');
                $this->method_description = __('Dynamo local payment', 'dynamo-test-plugin');

                $this->title = $this->get_option('title');
                $this->description = $this->get_option('description');

                $this->init_form_fields();
                $this->init_settings();

                add_action('woocommerce_update_options_payment_gateways_'. $this->id, array($this, 'process_admin_options'));
                add_action('woocommerce_thank_you'. $this->id, array($this, 'success_page'));
            }

            /**
             * setting the form fields for the gateway plugin
             */
            public function init_form_fields(){
                $this->form_fields = apply_filters('dynamo_payment_fields', array(
                    'enabled' => array(
                        'title' => __('Enable/Disable', 'dynamo pay'),
                        'type' => 'checkbox',
                        'label' => __('Enable or Disable Payment'),
                        'default' => 'no'
                    ),
                    'title' => array(
                        'title' => __('Dynamo Payment Gateway', 'dynamo pay'),
                        'type' => 'text',
                        'description' => __('Add a title for the Dynamo Payment Gateway', 'dynamo pay'),
                        'desc_tip' => true,
                        'default' => __('Dynamo Payment Gateway', 'dynamo pay')
                    ),
                    'description' => array(
                        'title' => __('Dynamo Payment Description', 'dynamo pay'),
                        'type' => 'textarea',
                        'description' => __('Enter a description for the dynamo payment gateway', 'dynamo pay'),
                        'desc_tip' => true
                    )
                ));
            }

            /**
             * process payment
             */
            function process_payment($order_id){
                global $woocommerce;
                $order = new WC_Order($order_id);

                //$order = wc_get_order($order_id);

                //update order status
                $order->update_status('on-hold', __('Awaiting Payment', 'dynamo pay'));

                //$this->make_payment($order);

                //update order available product
                $order->reduce_order_stock();

                $woocommerce->cart->empty_cart();

                //return result
                return array(
                    'result' => "success",
                    'redirect' => $this->get_return_url($order)
                );
            }

            /**
             * private function to make actual payment
             */
            private function make_payment($order){
                return true;
            }

            /**
             * success page
             */
            public function success_page(){
                echo wpautop('Payment Successful');
            }
        }
    }
}

add_filter('woocommerce_payment_gateways', 'add_dynamo_payment_gateway');

function add_dynamo_payment_gateway($gateways){
    $gateways[] = 'WC_Dynamo_Payment_Gateway';
    return $gateways;
}