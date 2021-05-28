<?php 

/**
	 Plugin Name: CST DB - Gravity Form submission
	 Plugin URI: https://github.com/harmancheema93/hsc-agentcubed-gravity-from-integration
	Description: Plugin to submit lead to CST DB from gravity form.
	Author: Harmandeep Singh
	Version: 1.0
	Author URI: https://github.com/harmancheema93
 */

if(!function_exists('hsc_settings_register')){
    function hsc_settings_register() {
        add_option( 'breatheeasyins_com', '');
        add_option( 'internal_attorney', '');
        add_option( 'internal_dui', '');

        register_setting( 'hsc_cstgf_settings', 'breatheeasyins_com' );
        register_setting( 'hsc_cstgf_settings', 'internal_attorney' );
        register_setting( 'hsc_cstgf_settings', 'internal_dui' );
    }
} 
add_action( 'admin_init', 'hsc_settings_register' );


if(!function_exists('hsc_setting_option_page')){
    function hsc_setting_option_page(){
        
        $options   = add_submenu_page( 

            'options-general.php',

            __( 'LeadSource Selector', 'hsc' ),

            'LeadSource Selector',

            'manage_options',

            'hsc_cstgf_settings',

             'hsc_cstgf_setting_action' 

        ); 
    }
}
add_action( 'admin_menu', 'hsc_setting_option_page'  );

function hsc_cstgf_setting_action(){
    ?>
    <div class="wrap" id="hsc-ecommerce-admin">

            <h2>LeadSource Selector</h2>

            <?php if (!empty($_GET['updated'])) : ?>

                <div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">

                    <p><strong><?php _e('Settings saved.') ?></strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>

                </div>

            <?php endif; ?>

            <form action="options.php" method="POST">
                <table class="form-table">

                    <tbody>
                    <tr>
                    <th>breatheeasyins.com</th>
                    <td><textarea name="breatheeasyins_com" rows="20" cols="100"><?php echo get_option('breatheeasyins_com'); ?></textarea>
                    <div>add comma after each URL</div></td>
                    </tr>
                    <tr>
                    <th>Internal Attorney Referral Form</th>
                    <td><textarea name="internal_attorney" rows="20" cols="100"><?php echo get_option('internal_attorney'); ?></textarea><div>add comma after each URL</div></td>
                    </tr>
                    <tr>
                    <th>Internal DUI School referral form</th>
                    <td><textarea name="internal_dui" rows="20" cols="100"><?php echo get_option('internal_dui'); ?></textarea><div>add comma after each URL</div></td>
                    </tr>
                    </tbody>
                </table>
            <?php settings_fields('hsc_cstgf_settings'); ?>
            <?php do_settings_sections('hsc_cstgf_settings'); ?>

            <?php submit_button(__('Save')); ?>
            </form>
            </div>
    <?php
}

if(!function_exists('hsc_gravity_form_submission')){
    function hsc_gravity_form_submission( $entry, $form ){
        foreach ( $form['fields'] as $field ) {
            $inputs = $field->get_entry_inputs();
            if ( is_array( $inputs ) ) {
                foreach ( $inputs as $input ) {
                    $value[$field->description] = rgar( $entry, (string) $input['id'] );
                }
            } else {
                $value[$field->description] = rgar( $entry, (string) $field->id );
            }
        }
        $name = $value['name'];
        $email = $value['email'];
        $zip = $value['zip'];
        $phone = $value['phone'];
        $insurance = $value['insurance'];
        $message = $value['message'];


        $nameA = explode(" ",$name);
        $fname = $nameA[0];
        $lname = $nameA[1];
        if(!$lname) $lname = 'Null';
        if(!$zip) $zip = '92630';
        if( !$insurance ) $insurance = $message;

        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https://" : "http://";  
        $CurPageURL = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];  

        $breatheeasyins_com = trim(get_option('breatheeasyins_com'),' '); 
        $internal_dui = trim(get_option('internal_dui'),' ');
        $internal_attorney = trim(get_option('internal_attorney'),' ');

        $breatheeasyins = array_map('trim', explode(',', $breatheeasyins_com));

        $internalattroney = array_map('trim', explode(',', $internal_attorney));

        $external = array_map('trim', explode(',',$internal_dui));

        
        if(in_array($CurPageURL, $breatheeasyins)){
            $leadsource = 'breatheeasyins.com';
            
        }elseif(in_array($CurPageURL, $internalattroney)){
            $leadsource = 'Internal Attorney Referral Form';
           
        }elseif(in_array($CurPageURL, $external)){
            $leadsource = 'Internal DUI School referral form';
           
        }else{
            $leadsource = 'breatheeasyins.com';
          
        }

        $tokenurl = "https://api.intoxalockdev.com/api/authenticateleads/token";
        $url = 'https://api.intoxalockdev.com/api/partner/sr22/lead/type2/v1/submitlead/693F46A9-0BD7-4D50-AF5A-7E75E0F063A9';

        $data = array(
            "PartnerID" => "693F46A9-0BD7-4D50-AF5A-7E75E0F063A9",
            "PartnerKey" => "gcXcJOY1YcvsJbGXyxbWvkcdIJOwRAT6"
        );
        
       $payload = json_encode($data);
       $ch = curl_init($tokenurl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload))
        );
        
        $token = curl_exec($ch);
        curl_close($ch);

        $info = array(
            'FirstName' => $fname,
            'LastName' => $lname,  
            'PhoneNumber' => $phone,  
            'ZipCode' => $zip,
            'EmailAddress' => $email,
            'SuggestedCallbackDateTime' => '2021-05-17T03:30:13',
            'SourcePage' => 'Lead from BreatheeasyIns',
            'UserID' => '693F46A9-0BD7-4D50-AF5A-7E75E0F063A9',
            'IsSpanish' => '0',
            'Comment' => $insurance,
            'ScheduledAppointmentDate' => '',
            'ScheduledAppointmentTime' => '',
            'ScheduledNotes' => '',
            'ServiceCenterID' => '0',
            'LeadSourceName' => $leadsource,
            'RequestID' => 'testyext101',
        );

        $submission = json_encode($info);
        $ch1 = curl_init($url);
        $tok = str_replace('"', '', $token);
        $auth = 'Authorization: Bearer '.$tok;
        curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch1, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch1, CURLOPT_POST, true);
        curl_setopt($ch1, CURLOPT_POSTFIELDS, $submission);
        curl_setopt($ch1, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            $auth)
        );
        
        $result = curl_exec($ch1);
        curl_close($ch1);

    }
    add_action( 'gform_after_submission', 'hsc_gravity_form_submission', 10, 2 );
}