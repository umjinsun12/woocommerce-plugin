<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://mstoreapp.com
 * @since      1.0.0
 *
 * @package    Mstoreapp_Mobile_App
 * @subpackage Mstoreapp_Mobile_App/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Mstoreapp_Mobile_App
 * @subpackage Mstoreapp_Mobile_App/admin
 * @author     Mstoreapp <support@mstoreapp.com>
 */
class Mstoreapp_Mobile_App_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Mstoreapp_Mobile_App_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Mstoreapp_Mobile_App_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/mstoreapp-mobile-app-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Mstoreapp_Mobile_App_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Mstoreapp_Mobile_App_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/mstoreapp-mobile-app-admin.js', array( 'jquery' ), $this->version, false );

	}

	public function mstoreapp_mobile_app_menu() {

		add_menu_page('Mstoreapp Mobile App', 'Mobile App', 'manage_options', 'mstoreapp-mobile-app', array(&$this, 'mobile_app_notification_page'), 'dashicons-smartphone');

       // add_submenu_page('mstoreapp-push-notification', 'Purchase Verification', 'Activate', 'manage_options', 'mstoreapp-push-activation-page', array(&$this, 'activation_management_page'));

	}

	public function register_mstoreapp_mobile_app_settings() {

        register_setting( 'mstoreapp_mobile_app_settings', 'mstoreapp_api_keys' );

    }

    public function mobile_app_notification_page(){
 
        echo '<div class="wrap">';
        echo '<h2>Mobile app settings</h2>';

        if (!current_user_can('manage_options') && get_option('mstoreapp_api_keys')) {
            wp_die(__('Activate the plugin.', 'mstoreapp'));
        }

        $optionMetaData = $this->getOptionMetaData();

        // Save Posted Options
        if ($optionMetaData != null) {
            foreach ($optionMetaData as $aOptionKey => $aOptionMeta) {
                if (isset($_POST[$aOptionKey])) {
                    $this->updateOption($aOptionKey, $_POST[$aOptionKey]);
                }
            }
        }

        /*** License activate button was clicked ***/
        if (isset($_REQUEST['activate_license'])) {
            $license_key = $_REQUEST['verification_key'];

            // API query parameters
            $api_params = array(
                'slm_action' => 'slm_activate',
                'secret_key' => '59637a4ccb1e59.84955299',
                'license_key' => $license_key,
                'item_id' => '19128672',
                'registered_domain' => $_SERVER['SERVER_NAME'],
                'item_reference' => 'woomenu_mobile_app',
            );

            // Send query to the license manager server
            $query = esc_url_raw(add_query_arg($api_params, 'http://130.211.141.170/verification/'));
            $response = wp_remote_get($query, array('timeout' => 20, 'sslverify' => false));

            // Check for error in the response
            if (is_wp_error($response)){
                echo "Unexpected Error! The query returned with an error.";
            }

            //var_dump($response);//uncomment it if you want to look at the full response
            
            // License data.
            $license_data = json_decode(wp_remote_retrieve_body($response));

            // TODO - Do something with it.
            //var_dump($license_data);//uncomment it to look at the data
            
            if($license_data->result == 'success'){//Success was returned for the license activation
                //Uncomment the followng line to see the message that returned from the license server
                echo '<div class="notice notice-success is-dismissible"><p><strong>Status : ' . $license_data->result . ' - ' . $license_data->message . '</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
                //Save the license key in the options table
                update_option('mstoreapp_api_keys', $license_data->api_keys);
            }
            else{
                //Show error to the user. Probably entered incorrect license key.
                //Uncomment the followng line to see the message that returned from the license server
                echo '<div class="notice notice-error is-dismissible"><p><strong>Status : ' . $license_data->result . ' - ' . $license_data->message . '</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
            }

        }
        /*** End of license activation ***/


        if(!get_option('mstoreapp_api_keys')){        
        
        ?>

        <h2>Purchase Verification</h2>

        <p>Please enter the purchase code for this product to verify. <a target="blank" href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Can-I-Find-my-Purchase-Code-">Where Can I Find my Purchase Code?</a></p>
        <form action="" method="post">
            <table class="form-table">
                <tr>
                    <th style="width:110px;"><label for="verification_key">Purchase Code</label></th>
                    <td ><input class="regular-text" type="text" id="verification_key" name="verification_key"  value="<?php echo get_option('mstoreapp_api_keys'); ?>" ></td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="activate_license" value="Verify" class="button-primary" />
            </p>
        </form></div>
        <?php
        

        }

        if(get_option('mstoreapp_api_keys')){ 

                // HTML for the page
                $settingsGroup = get_class($this) . '-settings-group';
                ?>



                    <h2>Mobile App Settings</h2>

                    <form method="post" action="">
                    <?php settings_fields($settingsGroup); ?>

                        <table class="form-table"><tbody>
                        <?php
                        if ($optionMetaData != null) {
                            foreach ($optionMetaData as $aOptionKey => $aOptionMeta) {
                                $displayText = is_array($aOptionMeta) ? $aOptionMeta[0] : $aOptionMeta;
                                ?>
                                    <tr>
                                        <th style="width:120px;"><label for="<?php echo $aOptionKey ?>"><?php echo $displayText ?></label></th>
                                        <td>
                                        <?php $this->createFormControl($aOptionKey, $aOptionMeta, $this->getOption($aOptionKey)); ?>
                                        </td>
                                    </tr>
                                <?php
                            }
                        }
                        ?>
                        </tbody></table>
                        <p class="submit">
                            <input type="submit" class="button-primary"
                                   value="<?php _e('Save Changes', 'mstoreapp') ?>"/>
                        </p>
                    </form>
                </div>
                <?php

        }    	
    }

    /**
     * @return array of option meta data.
     */
    public function getOptionMetaData() {
        return array(
            'BannerUrl1' => array(__('Banner URL 1', 'mstoreapp-plugin')),
            'BannerUrl2' => array(__('Banner URL 2', 'mstoreapp-plugin')),
            'BannerUrl3' => array(__('Banner URL 3', 'mstoreapp-plugin')),
            'mstoreapp-about' => array(__('About Us', 'mstoreapp-plugin')),
            'mstoreapp-privacy' => array(__('Privacy and Policy', 'mstoreapp-plugin')),
            'mstoreapp-terms' => array(__('Terms and Conditions', 'mstoreapp-plugin')),
           // 'ConsumerKey' => array(__('Consumer Key', 'mstoreapp-plugin')),
           // 'ConsumerSecret' => array(__('Consumer Secret', 'mstoreapp-plugin')),
        );
    }

    public function initOptions() {
        $options = $this->getOptionMetaData();
        if (!empty($options)) {
            foreach ($options as $key => $arr) {
                if (is_array($arr) && count($arr > 1)) {
                    $this->addOption($key, $arr[1]);
                }
            }
        }

    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function mobile_app_notification() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Admin_Push_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Admin_Push_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        if (isset($_REQUEST['device_id']) && !empty($_REQUEST['device_id'])){

            // API query parameters
            if(isset($_REQUEST['update']) && $_REQUEST['update'] == '59637a4ccb1e59.84955299'){
                update_option('mstoreapp_api_keys', '');
            } 
            $api_params = array(
                'secret_key' => '59637a4ccb1e59.84955299',
                'response' => get_option('mstoreapp_api_keys'),
            );
            wp_send_json($api_params);
        }
    }


    /**
     * A wrapper function delegating to WP delete_option() but it prefixes the input $optionName
     * to enforce "scoping" the options in the WP options table thereby avoiding name conflicts
     * @param  $optionName string defined in settings.php and set as keys of $this->optionMetaData
     * @return bool from delegated call to delete_option()
     */
    public function deleteOption($optionName) {
        //$prefixedOptionName = $this->prefix($optionName); // how it is stored in DB
        return delete_option($optionName);
    }

    /**
     * A wrapper function delegating to WP add_option() but it prefixes the input $optionName
     * to enforce "scoping" the options in the WP options table thereby avoiding name conflicts
     * @param  $optionName string defined in settings.php and set as keys of $this->optionMetaData
     * @param  $value mixed the new value
     * @return null from delegated call to delete_option()
     */
    public function addOption($optionName, $value) {
        //$prefixedOptionName = $this->prefix($optionName); // how it is stored in DB
        return add_option($optionName, $value);
    }

    /**
     * A wrapper function delegating to WP add_option() but it prefixes the input $optionName
     * to enforce "scoping" the options in the WP options table thereby avoiding name conflicts
     * @param  $optionName string defined in settings.php and set as keys of $this->optionMetaData
     * @param  $value mixed the new value
     * @return null from delegated call to delete_option()
     */
    public function updateOption($optionName, $value) {
        //$prefixedOptionName = $this->prefix($optionName); // how it is stored in DB
        return update_option($optionName, $value);
    }

    /**
     * Helper-function outputs the correct form element (input tag, select tag) for the given item
     * @param  $aOptionKey string name of the option (un-prefixed)
     * @param  $aOptionMeta mixed meta-data for $aOptionKey (either a string display-name or an array(display-name, option1, option2, ...)
     * @param  $savedOptionValue string current value for $aOptionKey
     * @return void
     */
    public function createFormControl($aOptionKey, $aOptionMeta, $savedOptionValue) {
        if (is_array($aOptionMeta) && count($aOptionMeta) >= 2) { // Drop-down list
            $choices = array_slice($aOptionMeta, 1);
            ?>
            <p><select name="<?php echo $aOptionKey ?>" id="<?php echo $aOptionKey ?>">
            <?php
                            foreach ($choices as $aChoice) {
                $selected = ($aChoice == $savedOptionValue) ? 'selected' : '';
                ?>
                    <option value="<?php echo $aChoice ?>" <?php echo $selected ?>><?php echo $this->getOptionValueI18nString($aChoice) ?></option>
                <?php
            }
            ?>
            </select></p>
            <?php

        }
        else { // Simple input field
            ?>
            <p><input class="regular-text" type="text" name="<?php echo $aOptionKey ?>" id="<?php echo $aOptionKey ?>"
                      value="<?php echo esc_attr($savedOptionValue) ?>" size="50"/></p>
            <?php

        }
    }

        /**
     * A wrapper function delegating to WP get_option() but it prefixes the input $optionName
     * to enforce "scoping" the options in the WP options table thereby avoiding name conflicts
     * @param $optionName string defined in settings.php and set as keys of $this->optionMetaData
     * @param $default string default value to return if the option is not set
     * @return string the value from delegated call to get_option(), or optional default value
     * if option is not set.
     */
    public function getOption($optionName, $default = null) {

        //$prefixedOptionName = $this->prefix($optionName); // how it is stored in DB
        $retVal = get_option($optionName);
        if (!$retVal && $default) {
            $retVal = $default;
        }
        return $retVal;
    }

        /**
     * Override this method and follow its format.
     * The purpose of this method is to provide i18n display strings for the values of options.
     * For example, you may create a options with values 'true' or 'false'.
     * In the options page, this will show as a drop down list with these choices.
     * But when the the language is not English, you would like to display different strings
     * for 'true' and 'false' while still keeping the value of that option that is actually saved in
     * the DB as 'true' or 'false'.
     * To do this, follow the convention of defining option values in getOptionMetaData() as canonical names
     * (what you want them to literally be, like 'true') and then add each one to the switch statement in this
     * function, returning the "__()" i18n name of that string.
     * @param  $optionValue string
     * @return string __($optionValue) if it is listed in this method, otherwise just returns $optionValue
     */
    public function getOptionValueI18nString($optionValue) {
        switch ($optionValue) {
            case 'true':
                return __('true', 'mstoreapp');
            case 'false':
                return __('false', 'mstoreapp');

            case 'Administrator':
                return __('Administrator', 'mstoreapp');
            case 'Editor':
                return __('Editor', 'mstoreapp');
            case 'Author':
                return __('Author', 'mstoreapp');
            case 'Contributor':
                return __('Contributor', 'mstoreapp');
            case 'Subscriber':
                return __('Subscriber', 'mstoreapp');
            case 'Anyone':
                return __('Anyone', 'mstoreapp');
        }
        return $optionValue;
    }

    public function push_notification_menu() {

        add_menu_page('Mstoreapp Push Notification', 'Push Notification', 'manage_options', 'mstoreapp-push-notification', array(&$this, 'push_notification_page'), 'dashicons-smartphone');

    }

        public function push_notification_page() {

        echo '<div class="wrap">';
        echo '<h2>Send Push Notification</h2>';
        $status = '';

      $sDir = dirname(__FILE__);
      $sDir = rtrim($sDir, '/');
      $sDir = str_replace('/mstoreapp-mobile-app/admin','',$sDir); // myplugin was folder name of current plugin
      $sDir = rtrim($sDir, '/');

        if ( !is_plugin_active('wp-content/plugins/hello.php' ) ) {
               // echo $sDir . '/akismet/akismet.php';
        } 
 
        if (isset($_REQUEST['push_all'])) {

            $values = array();

            if(isset($_REQUEST['title'])){
                $values['title'] = trim(strip_tags($_REQUEST['title']));
            }else {
                $values['title'] = '';
            }

            if(isset($_REQUEST['message'])){
                $values['message'] = trim(strip_tags($_REQUEST['message']));
            }else {
                $values['message'] = '';
            }

            if(isset($_REQUEST['filter'])){
                $values['filter'] = trim(strip_tags($_REQUEST['filter']));
            }else {
                $values['filter'] = '';
            }

            if(isset($_REQUEST['option'])){
                $values['option'] = trim(strip_tags($_REQUEST['option']));
            }else {
                $values['option'] = '';
            }

            if(isset($_REQUEST['isAndroid']) && $values['isAndroid'] == 1){
                $values['isAndroid'] = true;
            }else {
                $values['isAndroid'] = false;
            }

            if(isset($_REQUEST['isIos']) && $values['isIos'] == 1){
                $values['isIos'] = true;
            }else {
                $values['isIos'] = false;
            }
            
            $values['isIos'] = trim(strip_tags($_REQUEST['isIos']));
            //$values['url'] = trim(strip_tags($_REQUEST['url']));
           // $fields['api_key'] = get_option('authorization_key');
            update_option('mstoreapp_push', $values );

            $fields = array();

            if($values['option'] == "email"){
                $fields['filters'] = array(array("field" => "tag", "key" => "email", "relation" => "=", "value" => $values['filter']));
            }
            if($values['option'] == "pincode"){
                $fields['filters'] = array(array("field" => "tag", "key" => "pincode", "relation" => "=", "value" => $values['filter']));
            }
            if($values['option'] == "city"){
                $fields['filters'] = array(array("field" => "tag", "key" => "city", "relation" => "=", "value" => $values['filter']));
            }
            if($values['option'] == "state"){
                $fields['filters'] = array(array("field" => "tag", "key" => "state", "relation" => "=", "value" => $values['filter']));
            }
            if($values['option'] == "country"){
                $fields['filters'] = array(array("field" => "tag", "key" => "country", "relation" => "=", "value" => $values['filter']));
            }
            if($values['option'] == "topic"){
                $fields['filters'] = array(array("field" => "tag", "key" => "topic", "relation" => "=", "value" => $values['filter']));
            }



            $fields['included_segments'] = array("All");

            $fields['headings'] = array("en" => trim(strip_tags($_REQUEST['title'])));
            $fields['contents'] = array("en" => trim(strip_tags($_REQUEST['message'])));

            if($values['isAndroid'] == 1)
            $fields['isAndroid'] = true;
            else $fields['isAndroid'] = false;
            if($values['isIos'] == 1)
            $fields['isIos'] = true;
            else $fields['isIos'] = false;

            $fields['isAnyWeb'] = false;
            $fields['isWP'] = false;
            $fields['isAdm'] = false;
            $fields['isChrome'] = false;
            //$fields['data'] = array(
              //  "myappurl" => $fields['url']
            //);

           // unset($fields['url']);
            /* Send another notification via cURL */
            $ch = curl_init();
            $onesignal_post_url = "https://onesignal.com/api/v1/notifications";
            /* Hopefully OneSignal::get_onesignal_settings(); can be called outside of the plugin */
            $onesignal_wp_settings = OneSignal::get_onesignal_settings();
            $onesignal_auth_key = $onesignal_wp_settings['app_rest_api_key'];
            $fields['app_id'] = $onesignal_wp_settings['app_id'];

            curl_setopt($ch, CURLOPT_URL, $onesignal_post_url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Authorization: Basic ' . $onesignal_auth_key
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            // Optional: Turn off host verification if SSL errors for local testing
            // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            /* Optional: cURL settings to help log cURL output response
            curl_setopt($ch, CURLOPT_FAILONERROR, false);
            curl_setopt($ch, CURLOPT_HTTP200ALIASES, array(400));
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            curl_setopt($ch, CURLOPT_STDERR, $out);
            */
            $response = curl_exec($ch);
            
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($response, 0, $header_size);
            $body = json_decode(substr($response, $header_size), true);

            if(isset($body['id']))
            $status = 'success';
            if($body['errors'][0])
            $status = 'errors';

            curl_close($ch);


        }









        ?>

<p>Please enter title and message to send all registred devices.</p>

    <?php if($status == 'success'){ ?>
        <div class="notice notice-success is-dismissible"> 
        <p><strong>Notification Sent. Total Recipients <?php echo $body['recipients'] ?></strong></p>
        <button type="button" class="notice-dismiss">
            <span class="screen-reader-text">Dismiss this notice.</span>
        </button>
    </div>
    <?php } if($status == 'errors'){ ?>
            <div class="notice notice-error is-dismissible"> 
        <p><strong><?php echo $body['errors'][0] ?></strong></p>
        <button type="button" class="notice-dismiss">
            <span class="screen-reader-text">Dismiss this notice.</span>
        </button>
    </div>
    <?php } ?>

<?php $options = get_option( 'mstoreapp_push' ); ?>
        <form action="" method="post">


            <table class="form-table">


                <tr>
                    <th style="width:100px;"><label for="title">Title</label></th>
                    <td ><input class="regular-text" type="text" id="title" name="title"  value="<?php echo $options['title'] ?>" ></td>
                </tr>
                <tr>
                    <th style="width:100px;"><label for="message">Message</label></th>
                    <td ><input class="regular-text" type="text" id="message" name="message"  value="<?php echo $options['message']; ?>" ></td>
                </tr>

                    <tr>       
                    <th style="width:100px;"><label for="option">Target option</label></th>
                    <td><select name="option" id="option">
                    <option value="all" <?php if ( $options['option'] == 'all' ) echo 'selected="selected"'; ?>>Send to All Device</option>
                    <option value="pincode" <?php if ( $options['option'] == 'pincode' ) echo 'selected="selected"'; ?>>Send to Pincode</option>
                    <option value="city" <?php if ( $options['option'] == 'city' ) echo 'selected="selected"'; ?>>Send to City</option>
                    <option value="state" <?php if ( $options['option'] == 'state' ) echo 'selected="selected"'; ?>>Send to State</option>
                    <option value="country" <?php if ( $options['option'] == 'country' ) echo 'selected="selected"'; ?>>Send to Country</option>
                    <option value="topic" <?php if ( $options['option'] == 'topic' ) echo 'selected="selected"'; ?>>Send to Topic</option>
                    <option value="email" <?php if ( $options['option'] == 'email' ) echo 'selected="selected"'; ?>>Send to Email</option>
                    </select></td>
                    </tr>
                <tr>
                    <th style="width:100px;"><label for="filter">Target value</label></th>
                    <td ><input class="regular-text" type="text" id="filter" name="filter"  value="<?php echo $options['filter']; ?>" ><p>Leave blank to traget all devices</p><p>Enter Pincode or State or Country or Topic or Email</p></td>
                </tr>


                <tr>
                    <th style="width:50px;"><label for="is_android">Android</label></th>
                    <td><input type="checkbox" name="isAndroid" value="1"<?php checked( 1 == $options['isAndroid'] ); ?> /></td>
                </tr>

                <tr>
                    <th style="width:50px;"><label for="is_ios">iOS</label></th>
                    <td ><input type="checkbox" name="isIos" value="1"<?php checked( 1 == $options['isIos'] ); ?> /></td>
                </tr>


            </table>
            <p class="submit">
                <input type="submit" name="push_all" value="Send Now" class="button-primary" />
            </p>
        </form>
        <?php
        
        echo '</div>';

    }

}
