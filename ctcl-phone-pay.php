<?php
/*
 Plugin Name:CTCL Phone Pay
 Plugin URIhttps://github.com/ujw0l/ ctcl-phone-pay/blob/main/ ctcl-phone-pay.php
 Description: CT commerce lite phone pay payment addon
 Version: 1.1.0
 Author: Ujwol Bastakoti
 Author URI:https://ujw0l.github.io/
 Text Domain:  ctcl-phone-pay
 License: GPLv2
*/
if(class_exists('ctclBillings')){

    class ctclPhonePay extends ctclBillings{

    /**
     * Payment id
     */
    public $paymentId = 'ctcl_phone_pay';

    /**
     * Payment name
     */
    public $paymentName;

    /**
     * Setting Fields
     */
    public $settingFields = 'ctcl_phone_pay_setting';

    /**
     * Stripe file path
     */
    public $phonePayFilePath;

    public function __construct(){
        $this->phonePayFilePath = plugin_dir_url(__FILE__);
        $this->paymentName = __('Pay by phone','ctcl-phone-pay');
        self::displayOptionsUser();
        self::adminPanelHtml();
        self::registerOptions();
        self::requiredWpAction();
        self::callRequiredFilters();
        self::addRequiredAjax();
        register_deactivation_hook(__FILE__,  array($this,'phonePayDeactivate'));
      
    }

    /**
     * Run on deactivation
     * 
     */

     public function phonePayDeactivate(){

          delete_option('ctcl_activate_phone_pay');
          delete_option('ctcl_phone_pay_phone_number');
     }
    /**
     * All required filters
     */

     public function callRequiredFilters(){
        add_filter('ctcl_process_payment_'.$this->paymentId ,array($this,'processPayment'));
        add_filter('ctcl_additional_tab',array($this,'addAdminTab'),20);
        add_filter('ctcl_custom_email_body',array($this,'generateEmailBody'),10,2);

     }

     /**
      * All required ajax
      */

      public function addRequiredAjax(){

        add_action('wp_ajax_getPhoneOrderDetail',array($this,'getPhoneOrderDetail'));
        add_action('wp_ajax_phonePaymarkPaid',array($this,'markPaid'));


      }

      /**
       * Get order detail
       */
      public function getPhoneOrderDetail(){

        $orderProcessing = new ctclProcessing();
        $detail = json_decode($orderProcessing->getOrderDetail( absint($_POST['orderId'])),TRUE);
        
echo '<fieldset class="ctcl-phone-pay-detail">';
echo "<input type='hidden' id='ctcl-pay-phone-order-id' value='{$detail['order_id']}' />";
echo "<legend class='dashicons-before dashicons-phone'>".__('Phone pay detail for','ctcl-phone-pay')." : {$detail['order_id']}</legend>";
echo "<div class='ctcl-phone-pay-row'><span>".__("Customer Name ",'ctcl-phone-pay')." : </span><span>{$detail['ctcl-co-first-name']} {$detail['ctcl-co-last-name']}</span></div>";
echo "<div class='ctcl-phone-pay-row'><span>".__("Phone Number ",'ctcl-phone-pay')." : </span><span>{$detail['checkout-phone-number']}</span></div>";
echo "<div class='ctcl-phone-pay-row'><span>".__("Amount to be charged ",'ctcl-phone-pay')." : </span><span>{$detail['sub-total']}</span></div>";
echo "<div class='ctcl-phone-pay-row ctcl-phone-pay-button-row'>";
echo '<span>';
submit_button( __( 'Open Order Detail', 'ctcl-phone-pay' ), 'primary ctcl-pay-phone-open-detail','submit',false);
echo '</span><span>';
submit_button( __( 'Mark Paid', 'ctcl-phone-pay' ), 'primary ctcl-pay-phone-mark-paid','submit',false);
echo "</span></div>";
echo '</fieldset>';

wp_die();
      }

      /**
       * Generate emailbody with phone info
       * 
       * @param $val email body to be modified
       * @param $data Order data array 
       */
      public function generateEmailBody($val,$data){


        if('ctcl_phone_pay' == $data['payment_option']):

        $body = '<div style="margin-left:auto;margin-right:auto;display:block;padding:20px;">';
        $body .= '<p>'.__('Hello','ctcl-phone-pay').', '.$data['ctcl-co-first-name'].'</p>';
        $body .= '<p>'.__('Thank you for your purchase, your purchase details are as follows :','ctcl-phone-pay').'</p>';
        $body .= '<p>'.__('Order id ').' :'.$data['order_id'].'</p>';
        $body .= '<p>'.__('Your order details :','ctcl-phone-pay').'</p>';
        $body .= '<div style="width:600px;">';
        $body .= '<div style="padding-top:10px;border-bottom:1px solid rgba(255,255,255,1);display:table;height:30px;background-color:rgba(0,0,0,0.1);width:600px;text-align:center;">';
        $body .= '<span style="display:table-cell;height:30px;width:200px;" >'.__('Products','ctcl-phone-pay').'</span>';
        $body .= '<span style="display:table-cell;height:30px;width:300px;" >'.__('Variation','ctcl-phone-pay').'</span>';
        $body .='<span style="display:table-cell;height:30px;width:50px;" >'.__('Qty').'</span>';
        $body .='<span style="display:table-cell;height:30px;width:100px;" >'.__('Item Total').'</span></div>';
        foreach($data['products'] as $key=>$value):
            $product = json_decode(stripslashes($value),TRUE);
            $body .= "<div style='border-bottom:1px solid rgba(255,255,255,1);display:table;height:30px;background-color:rgba(0,0,0,0.1);width:600px;text-align:center;padding-top:10px;'>";
            $body .="<span  style='display:table-cell;width:200px'>{$product['itemName']}</span>";
            $body .="<span  style='display:table-cell;width:300px'>{$product['vari']}</span>";
            $body.="<span style='display:table-cell;width:50px;'>{$product['quantity']}</span>";
            $body .="<span style='display:table-cell;width:100px;' >{$product['itemTotal']}</span></div>";
        endforeach;
        $body .='<div style="padding-top:10px;border-bottom:1px solid rgba(255,255,255,1);height:30px;display:table;background-color:rgba(0,0,0,0.1);width:600px;text-align:center;">';
        $body .='<span style="display:table-cell;width:500px;text-align:right;" >'.__('Tax Rate ','ctcl-phone-pay').' : </span>';
        $body.='<span style="display:table-cell;">'.get_option('ctcl_tax_rate').' % </span></div>';
        $body .='<div style="padding-top:10px;border-bottom:1px solid rgba(255,255,255,1);height:30px;display:table;background-color:rgba(0,0,0,0.1);width:600px;text-align:center;">';
        $body .='<span style="display:table-cell;width:500px;text-align:right;">'.__('Shipping Cost ','ctcl-phone-pay').' ('.get_option('ctcl_currency').') : </span>';
        $body .= '<span style="display:table-cell;" >'.$data['shipping-total'].'</span></div>';
        $body .='<div style="padding-top:10px;border-bottom:1px solid rgba(255,255,255,1);height:30px;display:table;background-color:rgba(0,0,0,0.1);width:600px;text-align:center;">';
        $body.='<span style="display:table-cell;width:500px;text-align:right;" >'.__('Sub Total ','ctcl-phone-pay').' ('.get_option('ctcl_currency').') : </span>';
        $body.='<span style="display:table-cell;" >'.$data['sub-total'].'</span></div>';
        $body .='</div>';
        $body .= "<div style='font-size:15px;margin-top:15px'; ><p>".__('Please provide payment infomation only to call from number : ','ctcl-phone-pay')." ".get_option('ctcl_phone_pay_phone_number')."</p></div>";
        $body .= "<div style='font-size:15px;margin-top:15px';'><p>".__('Shipping Note :','ctcl-phone-pay')."</p>{$data['shipping_note']}</div>";
        $body .='</div>';
        return $body;
        else :
            return '';
        endif;
      }
      /**
       * Mark phone pay paid
       */
      public function markPaid(){

        global $wpdb;

        $orderProcessing = new ctclProcessing();
        $detail = json_decode($orderProcessing->getOrderDetail( absint($_POST['orderId'])),TRUE);
        $detail['phone_payment_status'] = 'complete';
        $update = $wpdb->update($wpdb->prefix.'ctclOrders',array('orderDetail'=>json_encode($detail)),array('orderId'=>absint($_POST['orderId'])));
        if(1==$update):
            echo "success";
        else:
            echo "failed";
        endif;  

        wp_die();
      }

     /**
      * Create tab on admin panel
      *@param $val array of filtered content
      *
      *@return $val modified and merged array
      */

     public function addAdminTab($val){
         
            return  array_merge($val,array('pay_by_phone'=>array(
                'id'=>$this->paymentId,
                'icon'=>'phone',
                'name'=>__('Phone Pay','ctcl-phone-pay'),
                'callback_func'=> array($this, 'createTabContent')
            )
        
            ));
     }

     /**
      * Create tab content
      */
      public function createTabContent(){

        global $wpdb;
        $count = 0;
        $listHtml = '';
        $pendingOrders = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ctclOrders  WHERE orderStatus= 'pending' AND orderDetail LIKE '%{$this->paymentId}%' ",ARRAY_A );
?>
<fieldset class="pending-phone-payment-fieldset">
    <legend class="dashicons-before dashicons-money pending-phone-payment-legend"><?=__('Pending Payments','ctcl-phone-pay')?></legend>
<?php
    if(1<= count(  $pendingOrders)):

        foreach($pendingOrders as $key=>$order):
            $orderDetail = json_decode($order['orderDetail'],TRUE);
            if( 'pending' == $orderDetail['phone_payment_status']):
                $listHtml .="<tr id='ctcl-pending-phone-order-{$order['orderId']}' class='ctcl-pending-phone-order' >";
                $listHtml .="<td>{$order['orderId']}</td>";
                $listHtml.="<td>".date('m/d/Y',$order['orderId'])."</td>";
                $listHtml.="<td>{$orderDetail['checkout-phone-number']}</td>";
                $listHtml.="<td>{$orderDetail['sub-total']}</td>";
                $listHtml.= "<td><a href='Javascript:void(0)' class='ctcl-get-phone-pay-data' data-order-id='{$order['orderId']}'>".__('Click Here','ctcl-phone-pay')."</a></td>";
                $listHtml."</tr>";
                $count++;
            endif;
        endforeach;

        if(1<=$count):
?>

<table class="wp-list-table widefat fixed striped media ctcl-phone-pay-orders">
    <thead>
        <tr>
            <td class="ctcl-pending-order-head"><?=__('Order Id','ctcl-phone-pay')?></td>
            <td class="ctcl-pending-order-head"><?=__('Order Date','ctcl-phone-pay')?></td>
            <td class="ctcl-pending-order-head"><?=__('Phone Number','ctcl-phone-pay')?></td>
            <td class="ctcl-pending-order-head"><?=__('Total','ctcl-phone-pay')?></td>
            <td class="ctcl-pending-order-head"><?=__('Open Detail','ctcl-phone-pay')?></td>
</thead>
<?=$listHtml?>
</table>
        </fieldset>
        <?php
    else:
        echo "<div>".__('There are no any pending phone payment right now.','ctcl-phone-pay')."</div>";
    endif;
else:
    echo "<div>".__('There are no any pending phone payment right now','ctcl-phone-pay')."</div>";
endif;
      }

    /**
     * register form options
     */
public function registerOptions(){
    register_setting($this->settingFields,'ctcl_activate_phone_pay');
    register_setting($this->settingFields,'ctcl_phone_pay_phone_number');
}
/**
 * 
 */
public function displayOptionsUser(){

    if('1'== get_option('ctcl_activate_phone_pay')):
        add_filter('ctcl_payment_options',function($val){
            array_push($val,array(
                                    'id'=>$this->paymentId,
                                    'name'=>$this->paymentName,
                                    'html'=>$this->frontendHtml()
            ));
            return $val; 
        },10,2);
    endif;
}

/**
 * Required wp actions
 */
public function requiredWpAction(){
    add_action( 'admin_enqueue_scripts', array($this,'enequeAdminJs' ));
    add_action( 'admin_enqueue_scripts', array($this,'enequeAdmincss' ));
    add_action( 'wp_enqueue_scripts', array($this,'enequeFrontendJs' ));
    add_action( 'wp_enqueue_scripts', array($this,'enequeFrontendCss' ));
}
/**
   * Eneque admin JS files
   */

  public function enequeAdminJs(){
         wp_enqueue_script('ctclAdminPhonePayJs', "{$this->phonePayFilePath}js/{$this->paymentId}_admin.js",array('ctclAdminJs','ctclJsMasonry','ctclJsOverlay'));
         wp_localize_script('ctclAdminPhonePayJs','ctclPhonePayParams',array(
             'markSucess'=>__("Marked paid sucessfully.",'ctcl-phone-pay'),
             'markFail'=>__("Could not be mark paid at this time",'ctcl-phone-pay'),
            ));
}



/**
   * Eneque admin JS files
   */

  public function enequeAdmincss(){
        wp_enqueue_style('ctclPhonePayCss', "{$this->phonePayFilePath}css/{$this->paymentId}_admin.css",array());   
}

   /**
   * Eneque frontend JS files
   */

  public function enequeFrontendJs(){
    if('1'== get_option('ctcl_activate_phone_pay')):
    wp_enqueue_script('ctclFrontEndPhonePayJs', "{$this->phonePayFilePath}js/{$this->paymentId}.js",array());
    endif;
}

   /**
   * Eneque frontend CSS files
   */

  public function enequeFrontendCss(){
    if('1'== get_option('ctcl_activate_phone_pay')):
        wp_enqueue_style( 'ctclPhonePayCss', "{$this->phonePayFilePath}css/{$this->paymentId}.css"); 
    endif;
}


      /**
     * Create admin panel content
     */
    public function adminPanelHtml(){

        add_filter('ctcl_admin_billings_html',function($val){
            $activate =  '1'=== get_option('ctcl_activate_phone_pay')? 'checked':'';
          
            $html = '<div class="ctcl-content-display ctcl-phone-pay-settings">';
            $html .=  '<div class="ctcl-business-setting-row"><label for"ctcl-activate-phone-pay"  class="ctcl-activate-phone-pay">'.__('Activate Phone Pay :','ctcl-phone-pay').'</label>';
            $html .= "<span><input id='ctcl-activate-phone-pay' {$activate} type='checkbox' name='ctcl_activate_phone_pay' value='1'></span></div>";

            $html .=  '<div class="ctcl-business-setting-row"><label for"ctcl-phone-pay-phone-number"  class="ctcl-phone-pay-phone-number-label">'.__('Phone Number:','ctcl-phone-pay').'</label>';
            $html .= "<span><input id='ctcl-phone-pay-phone-number' type='text' required name='ctcl_phone_pay_phone_number' value='".get_option('ctcl_phone_pay_phone_number')."'><i>".__("Number you will call from to collect billing info.",'ctcl-phone-pay')."</i></span></div>";
            $html .= '</div>';
            array_push($val,array(
                                    'settingFields'=>$this->settingFields,
                                    'formHeader'=>__("Phone Pay",'ctcl-phone-pay'),
                                    'formSetting'=>'ctcl_payment_setting',
                                    'html'=>$html
                                 )
                                );
      return $val;
        },40,1);
    }

    /**
     * Process payment 
     * 
     * @param $val value from filter to be modified
     * 
     * @return $val return modified array
     */

     public function processPayment($val){

        $val['charge_result'] = TRUE;
        $val['phone_payment_status'] = 'pending';
        return $val;
     }

    /**
      * html for frontend
      */
      public function frontendHtml(){
      return '<div id=" ctcl-phone-pay-number">'.__('Please provide payment info to call from :','ctcl-phone-pay').' '.get_option('ctcl_phone_pay_phone_number') . '</div>';
      }

    }
    new ctclPhonePay();
}else{

    add_thickbox();
   /**
    * If main plugin CTC lite is not installed
    */
    add_action( 'admin_notices', function(){
        echo '<div class="notice notice-error is-dismissible"><p>';
         _e( 'CTCL Phone pay plugin requires CTC Lite plugin installed and activated to work, please do so first.', 'ctcl-phone-pay' );
         echo '<a href="'.admin_url('plugin-install.php').'?tab=plugin-information&plugin=ctc-lite&TB_iframe=true&width=640&height=500" class="thickbox">'.__('Click Here to install it','ctcl-phone-pay').' </a>'; 
        echo '</p></div>';
    } );
}