<?php
/**
 * Plugin Name: Transmit to Slack
 * Plugin URI: https://wordpress.org/plugins/transmit-to-slack
 * Description: Transmit WooCommerce Orders to Slack
 * Author: Shir Gans
 * Author URI: https://www.ovs.co.il
 * Version: 1.0.0
 * Text Domain: transmit-to-slack
 * Domain Path: /i18n/languages/
 *
 * @package   TransmitToSlack
 * @author    Shir Gans
 * @category  WooCommerce
 *
 */

defined( 'ABSPATH' ) or exit;


add_action( 'after_setup_theme', 'transmit_to_slack', 11 );
function transmit_to_slack()
{
    /**
     * Slack WC Orders Integrations
     *
     * Transmits WC orders to Slack
     *
     * @class       Transmit_To_Slack
     * @version     1.0.0
     * @author      Shir Gans
     */
    class Transmit_To_Slack
    {
        protected $webhook_url;
        protected $swco = 'transmit_to_slack';


        /**
         * Constructor for the gateway.
         */
        public function __construct() {
            $this->webhook_url = apply_filters($this->swco.'_webhook_url', '');
            if (empty($this->webhook_url)) {
                add_action( 'admin_notices', array($this, 'error_notice') );
            } else {
                add_action('woocommerce_order_status_processing', array($this, 'transmit_order'), 100, 1);
                do_action($this->swco . '_loaded');
            }
        }

        public function error_notice(){
            ?>
            <div class="error notice">
                <p><?php _e( 'WC Orders to Slack: Ooops! Please add slack Webhook URL to code. See instructions for more information', $this->swco); ?></p>
            </div>
            <?php
        }


        public function transmit_order($order_id){
            do_action($this->swco.'_transmit_initial', $order_id);

            $order = new WC_Order($order_id);
            $fields = array();
            $header = array();



            $data= array(
                'Order ID' => $order->get_id(),
                'Order Date' => $order->get_date_created(),
                'Name' => $order->get_billing_first_name().' '.$order->get_billing_last_name(),
                'Country' => $order->get_billing_country(),
                'Total' => '('.$order->get_currency().') '.$order->get_total()
            );

            $header[] = array(
                'type'=>'plain_text',
                'text' => apply_filters($this->swco.'_message_title', ':package: NEW ORDER!'),
                'emoji' => true
            );

            foreach ($data as $key => $value){
                $fields[] = array (
                    'type' => 'mrkdwn',
                    'text' => apply_filters($this->swco.'_message_title', '*'.$key.'*: '.$value),
                );
            }

            foreach ($order->get_items()as $item){
                $product_name = str_replace("'", "", $item->get_name());
                $product_price = $item->get_total();
                $product_quantity = $item->get_quantity();

                $products[] = array (
                    'type' => 'mrkdwn',
                    'text' =>  '*'.$product_name.'*: '.'('.$order->get_currency().') '. $product_price.' (x'.$product_quantity.')'
                );
            }


            $message = array(
                'text' => 'Incoming order (#'.$order->get_id().')', # Default fallback
                'blocks' => array(
                    array ('type' => 'section', 'fields'=>$header),
                    array ('type' => 'section', 'fields'=>$fields),
                    array( 'type' => 'divider'),
                    array ('type' => 'section', 'fields'=>$products),
                    array( 'type' => 'divider')
                )
            );

            $message = apply_filters($this->swco.'_slack_message', $message);
            do_action ($this->swco.'_before_transmit', $message);

            $message_json = json_encode($message);

            $result = wp_remote_post(
                $this->webhook_url,
                array(
                    'method'		 => 'POST',
                    'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
                    'body' => $message_json,
                    'data_format' => 'body',
                )
            );

            do_action ($this->swco.'_after_transmit', $result);


        }

    }
    $Transmit_To_Slack = new Transmit_To_Slack();
}