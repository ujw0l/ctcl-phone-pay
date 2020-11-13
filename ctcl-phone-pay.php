<?php
/*
 Plugin Name:CTCL Phone Pay
 Plugin URIhttps://github.com/ujw0l/ ctcl-phone-pay/blob/main/ ctcl-phone-pay.php
 Description: CT commerce lite phone pay payment addon
 Version: 1.0.0
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
      
    }

    /**
     * All required filters
     */

     public function callRequiredFilters(){
        add_filter('ctcl_process_payment_'.$this->paymentId ,array($this,'processPayment'));
        add_filter('ctcl_additional_tab',array($this,'addAdminTab'),20);

     }

     /**
      * All required ajax
      */

      public function addRequiredAjax(){

        add_action('wp_ajax_getPhoneOrderDetail',array($this,'getPhoneOrderDetail'));


      }

      /**
       * Get order detail
       */
      public function getPhoneOrderDetail(){

        $orderProcessing = new ctclProcessing();
        $detail = $orderProcessing->getOrderDetail( absint($_POST['orderId']));
        
        echo '<pre>';
        print_r($detail);

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
        echo "<div>".__('No any peding phone payment','ctcl-phone-pay')."</div>";
    endif;
else:
    echo "<div>".__('No any peding phone payment','ctcl-phone-pay')."</div>";
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
    add_action( 'wp_enqueue_scripts', array($this,'enequeFrontendCss' ));
}
/**
   * Eneque admin JS files
   */

  public function enequeAdminJs(){
         wp_enqueue_script('ctclPhonePayJs', "{$this->phonePayFilePath}js/{$this->paymentId}_admin.js",array('ctclAdminJs'));
}

/**
   * Eneque admin JS files
   */

  public function enequeAdmincss(){
        wp_enqueue_style('ctclPhonePayCss', "{$this->phonePayFilePath}css/{$this->paymentId}_admin.css",array());   
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

            $html .=  '<div class="ctcl-business-setting-row"><label for"ctcl-phone-pay-phone-number"  class="ctcl-phone-pay-phone-number-label">'.__('Business Phone Number:','ctcl-phone-pay').'</label>';
            $html .= "<span><input id='ctcl-phone-pay-phone-number' type='text' required name='ctcl_phone_pay_phone_number' value='".get_option('ctcl_phone_pay_phone_number')."'></span></div>";
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
      return '<div id=" ctcl-phone-pay-number">'.__('Provide payment info to call from :','ctcl-phone-pay').' '.get_option('ctcl_phone_pay_phone_number') . '</div>';
      }

    }
    new ctclPhonePay();
}else{

   /**
    * If main plugin CTC lite is not installed
    */
    add_action( 'admin_notices', function(){
        echo '<div class="notice notice-error is-dismissible"><p>';
         _e( 'CTCL Stripe Plugin requires CTC Lite plugin installed and activated to work, please od so first.', 'ctcl-phone-pay' );
         echo '<a href="https://wordpress.org/plugins/ctc-lite">'.__('Click Here to download it','ctcl-phone-pay').' </a>'; 
        echo '</p></div>';
    } );
}