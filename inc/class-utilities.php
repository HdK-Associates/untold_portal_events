<?php
class HdKSpUtilities{

    public static function SpektrixConvertTimeStamps($Timestamp) {
    
        $date = DateTime::createFromFormat('Y-m-d\TH:i:s', $Timestamp);
        $DatesArr = [];
        $DatesArr[] = strtoupper($date->format('l jS F'));
        $DatesArr[] = strtoupper($date->format('g:i A'));
        return $DatesArr;
    }	
    
    public static function get_client_codes(){
       $_settings = new HdKSpSettings();
       $settings = $_settings->getSettings();
       return [
        'client_code'=>$settings['client_code'],
        'subdomain'=>$settings['subdomain'],
        'stylesheet'=>$settings['stylesheet']
       ];
    }
    
    public static function get_spektrix_settings(){
        $_settings = new HdKSpSettings();
       $settings = $_settings->getSettings();
       return $settings;
       
    }

    /*The success message here is specific to MK. Will need to add this link to the actual options menu*/
    public static function get_merchandise_component($client_code, $item_id){
        $success = get_option('hdk-merchandise-success');
        $error = get_option('hdk-merchandise-error');
        $output = '<spektrix-merchandise client-name="'.$client_code['client_code'].'" custom-domain="'.$client_code['subdomain'].'" merchandise-item-id="'.$item_id.'" merchandise-quantity=1>';
        $output.= '<div class="quantity"><button data-decrement-quantity> - </button><span data-display-quantity></span><button data-increment-quantity> + </button></div>';
        $output .= '<button class="buy" data-submit-merchandise>Buy now</button><div data-success-container style="display: none;">'.$success.'<a href="/basket" class="chevron sub_head">Go to checkout</a></div>
        <div data-fail-container style="display: none;">'.$error.'</div></spektrix-merchandise>';
        return $output;
    }

    public static function get_donate_component($client_code, $fund_id){
        $amounts = explode(',',get_option('hdk-donate-amounts'));
        $success = get_option('hdk-donate-success');
        $error = get_option('hdk-donate-error');
        $output = '<spektrix-donate client-name="'.$client_code['client_code'].'" custom-domain="'.$client_code['subdomain'].'" fund-id="'.$fund_id.'">';
        $output.= '<div class="donate--amount"><span>Donate £</span><span data-display-donation-amount></span></div>';
        $output.= '<div class="donate--buttons">';
        foreach($amounts as $amount){
            $output.='<button data-donate-amount="'.$amount.'">£'.trim($amount).'</button>';
        }
        $output.= '</div><div class="donate--input">';
        $output.='<p>Or choose a different amount:</p><input type="number" data-custom-donation-input></input></div>';
        $output .= '<button data-submit-donation>Donate</button><div data-success-container style="display: none;">'.$success.'</div>
        <div data-fail-container style="display: none;">'.$error.'</div></spektrix-donate>';
        return $output;
    }

    public static function get_members_component($client_code, $memberships){
        $success = get_option('hdk-members-success');
        $error = get_option('hdk-members-error');
   
        $output='';
        foreach($memberships as $membership){
            $output.= '<spektrix-memberships client-name="'.$client_code['client_code'].'" custom-domain="'.$client_code['subdomain'].'" membership-id="'.$membership['id'].'">';
            $output.= '<h3>'.$membership['name'].'</h3><p>'.$membership['htmlDescription'].'</p>';
            $output.='<h4>£'.$membership['price'].'</h4>';
            $output .= '<button data-submit-membership>Join</button>
            <label for="autorenew">
                <input type="checkbox" name="autorenew" data-set-autorenew>Automatically renew?
            </label>
           <div data-success-container style="display: none;">'.$success.'</div>
            <div data-fail-container style="display: none;">'.$error.'</div></spektrix-memberships>';
        }
       
        return $output;
    }

    public static function get_login_status_component($client_code){
        $output = '<spektrix-login-status client-name="'.$client_code['client_code'].'" custom-domain="'.$client_code['subdomain'].'" >';
        $output.= '<span data-logged-in-container style="display: none;">
                        <a href="/account">
                            Hi, <span data-logged-in-status-customer-first-name></span>!
                        </a>
                    </span>';
        $output.= '<span data-logged-out-container>
                        <a href="/account">
                            <span class="screen-reader-text">Login</span>
                            <svg width="20" height="38" viewBox="0 0 20 38" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M0 26C0 22.134 3.13401 19 7 19H12.5758C16.4417 19 19.5758 22.134 19.5758 26V38H0V26Z" fill="#1D4E8F"/>
                                <ellipse cx="9.78766" cy="8.34848" rx="8.06061" ry="8.34848" fill="#1D4E8F"/>
                            </svg>
                        </a>
                    </span></spektrix-login-status>';
       
        return $output;
    }
    public static function get_basket_summary_component($client_code){
        $output = '<spektrix-basket-summary client-name="'.$client_code['client_code'].'" custom-domain="'.$client_code['subdomain'].'" >';
        $output.= '<a href="/basket">
                        <span class="screen-reader-text">Basket</span>
                        <svg width="91" height="98" viewBox="0 0 91 98" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M34.0878 56.596V84.444C34.0878 87.1816 31.8694 89.4 29.1318 89.4C26.3942 89.4 24.1758 87.1816 24.1758 84.444V56.596C24.1758 53.8584 26.3942 51.64 29.1318 51.64C30.5006 51.64 31.7278 52.1828 32.6246 53.0796C33.545 54 34.0878 55.2272 34.0878 56.596ZM50.3246 53.0796C49.4278 52.1828 48.2006 51.64 46.8318 51.64C44.0942 51.64 41.8758 53.8584 41.8758 56.596V84.444C41.8758 87.1816 44.0942 89.4 46.8318 89.4C49.5694 89.4 51.7878 87.1816 51.7878 84.444V56.596C51.7878 55.2272 51.245 54 50.3246 53.0796ZM68.0246 53.3156C67.1278 52.4188 65.9006 51.876 64.5318 51.876C61.7942 51.876 59.5758 54.0944 59.5758 56.832V84.68C59.5758 87.4176 61.7942 89.636 64.5318 89.636C67.2694 89.636 69.4878 87.4176 69.4878 84.68V56.832C69.4878 55.4632 68.945 54.236 68.0246 53.3156ZM34.0878 56.596C34.0878 55.2272 33.545 54 32.6246 53.0796C31.7278 52.1828 30.5006 51.64 29.1318 51.64C26.3942 51.64 24.1758 53.8584 24.1758 56.596V84.444C24.1758 87.1816 26.3942 89.4 29.1318 89.4C31.8694 89.4 34.0878 87.1816 34.0878 84.444V56.596ZM51.7878 56.596C51.7878 55.2272 51.245 54 50.3246 53.0796C49.4278 52.1828 48.2006 51.64 46.8318 51.64C44.0942 51.64 41.8758 53.8584 41.8758 56.596V84.444C41.8758 87.1816 44.0942 89.4 46.8318 89.4C49.5694 89.4 51.7878 87.1816 51.7878 84.444V56.596ZM69.4878 56.832C69.4878 55.4632 68.945 54.236 68.0246 53.3156C67.1278 52.4188 65.9006 51.876 64.5318 51.876C61.7942 51.876 59.5758 54.0944 59.5758 56.832V84.68C59.5758 87.4176 61.7942 89.636 64.5318 89.636C67.2694 89.636 69.4878 87.4176 69.4878 84.68V56.832ZM90.0198 39.958V44.7016C90.0198 46.0704 88.9106 47.1796 87.5418 47.1796H84.1198V77.0808C84.1198 88.1964 75.1046 97.2116 63.989 97.2116H27.0786C15.963 97.2116 6.94784 88.1964 6.94784 77.0808V47.156H2.81784C1.44904 47.156 0.339844 46.0468 0.339844 44.678V39.9344C0.339844 38.5656 1.44904 37.4564 2.81784 37.4564H12.6826L33.1438 2.05638C33.9698 0.64038 35.7634 0.168379 37.1794 0.970779C38.1234 1.51358 38.6426 2.50478 38.6426 3.51958C38.6426 4.01518 38.5246 4.53438 38.2414 4.98278L19.503 37.48H73.7358L54.9974 5.00638C54.7378 4.53438 54.5962 4.03878 54.5962 3.54318C54.5962 2.52838 55.1154 1.53718 56.083 0.99438C57.499 0.16838 59.2926 0.66398 60.1186 2.07998L80.5562 37.48H87.5418C88.9106 37.48 90.0198 38.5892 90.0198 39.958ZM34.0878 56.596C34.0878 55.2272 33.545 54 32.6246 53.0796C31.7278 52.1828 30.5006 51.64 29.1318 51.64C26.3942 51.64 24.1758 53.8584 24.1758 56.596V84.444C24.1758 87.1816 26.3942 89.4 29.1318 89.4C31.8694 89.4 34.0878 87.1816 34.0878 84.444V56.596ZM51.7878 56.596V84.444C51.7878 87.1816 49.5694 89.4 46.8318 89.4C44.0942 89.4 41.8758 87.1816 41.8758 84.444V56.596C41.8758 53.8584 44.0942 51.64 46.8318 51.64C48.2006 51.64 49.4278 52.1828 50.3246 53.0796C51.245 54 51.7878 55.2272 51.7878 56.596ZM69.4878 56.832V84.68C69.4878 87.4176 67.2694 89.636 64.5318 89.636C61.7942 89.636 59.5758 87.4176 59.5758 84.68V56.832C59.5758 54.0944 61.7942 51.876 64.5318 51.876C65.9006 51.876 67.1278 52.4188 68.0246 53.3156C68.945 54.236 69.4878 55.4632 69.4878 56.832Z" fill="#1D4E8F"/>
                        </svg>
                        <span data-basket-item-count></span>
                    </a>
                    </spektrix-basket-summary>';
       
        return $output;
    }

    public static function get_basket_summary_event_component($client_code){
        $output = '<div class="spektrix-basket-component">
                        <spektrix-basket-summary client-name="'.$client_code['client_code'].'" custom-domain="'.SPEKTRIX_ENDPOINT_NO_SCHEMA.'" >';
        $output.= '
                            <h3>BASKET SUMMARY</h3>
                                <span data-basket-item-count></span> item(s)
                                <br />
                                Discount:
                                <span data-basket-summary-currency></span>
                                <span data-basket-summary-discount-total></span>
                                <div class="basket-summary-total">
                                    Total:
                                    <span data-basket-summary-currency></span>
                                    <span data-basket-summary-basket-total></span>
                                </div>
                        </spektrix-basket-summary>
                    </div>';
       
        return $output;
    }

    public static function getChooseSeatsIframe($short_id,$params=false){
        $add_params = '';
        if($params&&is_array($params)){
            foreach($params as $param=>$value){
                $add_params.='&'.$param.'='.$value;
            }
        }
        return self::generateIframe('ChooseSeats',true,'EventInstanceId='.$short_id.'&Optimise=mobile'.$add_params,false,true);
    }

    public static function getBasketIframe(){
        return self::generateIframe('Basket2');
    }
    
    public static function getCheckoutIframe(){
        return self::generateIframe('Checkout',false,false,true);
    }
    
    public static function getAccountIframe(){
        return self::generateIframe('MyAccount',true,false,true);
    }

    public static function getCookiesIframe(){
        return self::generateIframe('Cookies',false,false,false);
    }

    public static function getGiftVouchersIframe(){
        if($_GET['MembershipId']){
            $parameters = 'MembershipId='.$_GET['membership_id'];
        }
        else{
            $parameters = false;
        }
        return self::generateIframe('GiftVouchers',true,$parameters,false);
    }

    public static function getNewsletterFormWithTags($tags){

        $output='<form id="newsletter" action="/wp-json/spektrix/v1/submitnewsletter" method="POST">
			
			<label for="FirstName" >First Name:</label>
			<input name="FirstName" id="FirstName" type="text" required>

			<label for="LastName">Last Name:</label>
			<input name="LastName" id="LastName" type="text" required>

			<input placeholder="Email address" name="Email" id="Email" type="email" required>';
        if($tags){
            foreach($tags as $tag){
                $output.='<details class="expandable collapse"><summary>
                <span class="summary-title">'.$tag['name'].'</span>
                <div class="arrow_up"></div></summary>';
                if($tag['description']){
                    $output.='<p class="small">'.$tag['description'].'</p>';
                }
                $output.='<fieldset>';
                foreach($tag['tags'] as $tag_option){
                    $output.='<input type="checkbox" name="tags" id="'.$tag_option['id'].'" value="'.$tag_option['id'].'">
                    <label for="'.$tag_option['id'].'">'.$tag_option['name'].'</label>';
                }
                $output.='</fieldset><div class="arrow_down"></div></details>';
            }
        }
		$output.='<input id="newsletter_submit" type="submit" name="submit" value="Subscribe">
		</form>';
        return $output;
    }

    public static function getNewsletterFormWithTagsByPage($tags){
        global $post;
        $post_slug = $post->post_name;
        
        $tag_list =[];
        if($tags[0]['tags']){
            foreach($tags[0]['tags'] as $tag){
                $tag_list[$tag['name']]=$tag['id'];
            } 
        }
        $signuptags = [$tag_list['Exhibitions'],$tag_list['Events']];
        $subdomain = explode('.',$_SERVER['HTTP_HOST']);
        if($subdomain[0]=='shop'){
            $signuptags[]=$tag_list['Shop'];
        }
        if($post_slug=='cafe-bowes'){
            $signuptags[]=$tag_list['Café Bowes'];
        }
        if($post_slug=='families'){
            $signuptags[]=$tag_list['Family Activities'];
        }
        $output='<div class="sign-up_message hide"></div><form id="newsletter" action="/wp-json/spektrix/v1/submitnewsletter" method="POST" data-return="'.get_site_url().'/thank-you-for-signing-up/">
			<label for="firstName" >First Name:</label>
			<input name="FirstName" id="FirstName" type="text" placeholder="First name" required>

			<label for="lastName">Last Name:</label>
			<input name="LastName" id="LastName" type="text" placeholder="Last name" required>

			<input placeholder="Email address" name="Email" id="Email" type="email" required>
            ';
        if($signuptags){
            $output.='<input type="hidden" name="tags" id="tags" value="'.implode(',',$signuptags).'">';
        }
		$output.='<input id="newsletter_submit" type="submit" name="submit" value="Subscribe">
		</form>';
        return $output;
    }

    public static function getNewsletterForm(){
        $client_codes = self::get_client_codes();
        $output='<form id="newsletter" action="https://'.$client_codes['subdomain'].'/'.$client_codes['client_code'].'/website/secure/signup.aspx" method="POST">
			<input
                type="hidden"
                name="ReturnUrl"
                value="'.get_site_url().'/thank-you-for-signing-up/"
            />
			<label for="FirstName" >First Name:</label>
			<input name="FirstName" id="FirstName" type="text" placeholder="First name" required>

			<label for="LastName">Last Name:</label>
			<input name="LastName" id="LastName" type="text" placeholder="Last name" required>

			<input placeholder="Email address" name="Email" id="Email" type="email" required>';
		$output.='<input id="newsletter_submit" type="submit" name="submit" value="Subscribe">
		</form>';
        return $output;
    }

    public static function getTaggedSignUpForm($tags){ 
        
        ?>
        <form data-action="<?php echo get_site_url().'/wp-json/spektrix/v1/form_tags'; ?>" class="form-inline" id="ap_form_tags" data-redirect="<?php echo get_site_url().'/thank-you-for-signing-up/'; ?>">
            <input type="hidden" id="Tags" name="Tags" value="<?php echo implode(',',$tags); ?>">
            <?php wp_nonce_field( 'ap_form_tags', 'form_tags_nonce' ); ?>
            <div class="form-group name-group">
                <label for="FirstName">Name</label>
                <input name="FirstName" id="FirstName" type="text" placeholder="Enter your first name" required/>
            </div>
            <div class="form-group name-group lastname-group">
                <label for="LastName">Surname</label>
                <input name="LastName" id="LastName" type="text" placeholder="Enter your surname" required/>
            </div>
            <div class="form-group email-group">
                <label for="Email">Email Address</label>
                <input name="Email" id="Email" type="text" placeholder="Enter your email address" required/>
            </div>
            <div class="form-group control-group">
                <button type="submit" class="ap_btn_fill_icon ap_btn_pink btn_subscribe">Sign up</button>
            </div>
        </form>
        <p class="signup_notice">By signing up you are agreeing to our <a href="/privacy-policy/">Privacy&nbsp;Policy</a></p>
        <div id="ap_form_tags_response"></div>
    <?php }

    public static function getTagForm($tags,$user_id){
        $output='<form id="tag_form" class="container-narrow gutenberg" action="'.get_site_url().'/thank-you-for-signing-up/" method="POST">
            <h3>What would you like to hear about? (Please select all that apply)</h3>
			<input
                type="hidden"
                name="user_id"
                value="'.$user_id.'"
            />';
			if($tags){
                foreach($tags as $tag){
                    foreach($tag['tags'] as $tag_option){
                        $output.='<label for="'.$tag_option['id'].'"><input type="checkbox" name="tags[]" id="'.$tag_option['id'].'" value="'.$tag_option['id'].'">
                        <span>'.$tag_option['name'].'</span></label>';
                    }
                }
            };
		$output.='<input id="tag_form_submit" class="btn" type="submit" name="submit" value="Subscribe">
		</form>';
        return $output;
    }
    
    public static function getNewsletterErrors($error){
        switch ($error) {
            case 'NoEmail':
                return 'Error: Please enter an email address';
                break;
            case 'NoLastName':
                return 'Error: Please enter a last name';
                break;
            case 'InvalidEmail':
                return 'Error: Please enter a valid email address';
                break;
            case 'EmailAddressTooLong':
                return 'Error: Your email address is too long';
                break;
            case 'FirstNameTooLong':
                return 'Error: Your first name is too long';
                break;
            case 'LastNameTooLong':
                return 'Error: Your last name is too long';
                break;
            default:
                return 'Error: Something is wrong with the sign up. Please try again later';
          }
    }
    
    /*This assumes a standard set of slugs for the relevant pages. This should ultimately be put into the options form*/
    public static function getIframebySlug($slug){
        switch($slug){
            case 'basket':
                return self::getBasketIframe();
            case 'checkout':
                return self::getCheckoutIframe();
            case 'account':
                return self::getAccountIframe();
            case 'cookies':
               return self::getCookiesIframe();
            case 'gift-vouchers':
                return self::getGiftVouchersIframe();
            case 'gift-membership':
                return self::getGiftVouchersIframe();
        }
    }

    public static function generateIframe($type,$onload=true,$parameters=false,$secure=false,$lazy=false){
        if($parameters){
            $parameters=$parameters.'&';
        }
        if($onload){
            //$onload='onload="setTimeout(function(){ window.scrollTo(0,0);}, 100)"';
            $onload='onload="window.onSpektrixIframeLoad && window.onSpektrixIframeLoad(event)"';
        }
        if($secure){
            $secure='secure/';
        }
        $data_src = '';
        $settings = self::get_client_codes();
        $allow = '';
        if($type=='Checkout'){
            //$src = $settings['subdomain'].'/'.$settings['client_code'].'/website/'.$secure.$type.'/v2?'.$parameters.'stylesheet='.$settings['stylesheet'].'&resize=true';
            $allow = 'allow="payment"';
        }
        if($lazy){
            $src = '';
            $data_src = 'data-src="'.$settings['subdomain'].'/'.$settings['client_code'].'/website/'.$secure.$type.'.aspx?'.$parameters.'stylesheet='.$settings['stylesheet'].'&resize=true"';

        }
        else{
            $src = $settings['subdomain'].'/'.$settings['client_code'].'/website/'.$secure.$type.'.aspx?'.$parameters.'stylesheet='.$settings['stylesheet'].'&resize=true';
        }
        $output = '<iframe
            name="SpektrixIFrame"
            id="SpektrixIFrame"
            frameborder="0"
            src="'.$src.'"
            '.$data_src.'
            '.$allow.'
            style="width: 100%; height: 1000px;"'.$onload.'
        >
        </iframe>';
        return $output;
    }

    public static function loginForm(){
        $client_codes = self::get_client_codes();
        wp_enqueue_script('hdk-login',plugin_dir_url( __DIR__ ). 'js/login.js',[],'1.0',true);
        $output = '<form id="login_form" action="'.$client_codes['subdomain'].'/'.$client_codes['client_code'].'/api/v3/customer/authenticate" method="POST">
            <label for="Email">Email address</label>
            <input name="email" id="email" type="email" required>

            <label for="Password">Password</label>
            <input name="password" id="password" type="password" required>

            <a href="'.get_site_url().'/reset-password/" class="forgot_password">Forgotten your password?</a>
            
            <input id="login_submit" type="submit" name="submit" class="button decorative" value="Log in">
            <div id="login_errors"></div>
        </form>';
        return $output;
        
    }
    
}