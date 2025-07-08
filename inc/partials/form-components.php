<?php 
$args = array(
    'post_type'=>'page',
    'numberposts' => -1,
    'order'=>'ASC',
);
$all_pages = get_posts($args);
$all_pages_mapped = array_map(function($n) { return array('page_id'=>$n->ID,'page_name'=>$n->post_title); }, $all_pages);
wp_add_inline_script( 'spektrix', 'const pageList = ' . json_encode($all_pages_mapped), 'before' ); 
$component_message=get_transient('hdk_base_form_message');
$component_error=get_transient('hdk_base_form_error');
if ($component_message||$component_error){
    ?><p class="<?php echo $component_message?'message':'error'; ?>"><?php echo $component_message?$component_message:$component_error; ?></p><?php
}?>
<h2>Spektrix Components</h2>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="components" onkeydown="return event.key != 'Enter';">
    <?php $hdk_component_nonce = wp_create_nonce( 'hdk_component_nonce' ); ?>
    <input type="hidden" name="action" value="hdk_component_form">
    <input type="hidden" name="hdk_component_nonce" value="<?php echo $hdk_component_nonce ?>" />
    <table class="form-table">
        <tr valign="top">
            <th scope="row">Pages to load iframe scripts:</th>
            <td>
            <?php $iframe_value = get_option('hdk-iframe-pages'); 
                
                $iframe_values = json_decode(stripslashes($iframe_value),true); ?>
            <p><input type = "hidden" name="hdk-iframe-pages" id="hdk-iframe-pages" value="<?php echo $iframe_value; ?>"></p>
            <div id="button_container">

                <?php if($iframe_values && count($iframe_values)>0) {   
                    foreach($iframe_values as $id){
                    ?><button data-page_id="<?php echo $id; ?>"><?php echo get_the_title($id); ?></button><?php
                    }
                } ?>
            </div>
            <p><input type="text" id="page_select"></p>
            <p>Start typing to choose a Page.</p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">Web Components to load:</th>
            <?php $webcomponents_value = get_option('hdk-webcomponents');
            $webcomponents_values = json_decode(stripslashes($webcomponents_value)); ?>
            <td><p><input type = "hidden" name="hdk-webcomponents" id="hdk-webcomponents" value = "<?php echo $webcomponents_value; ?>"></p>
            <fieldset id="hdk-webcomponents-boxes">
                <legend>Select components</legend>
                <?php $checkboxes=array(
                    array('spektrix-donate','Donate Component'),
                    array('spektrix-memberships','Membership Component'),
                    array('spektrix-login-status','Login Status Component'),
                    array('spektrix-basket-summary','Basket Summary Component'),
                    array('spektrix-merchandise','Merchandise Component'),
                ); 
                foreach($checkboxes as $checkbox){
                    $checked = false;
                    if(is_array($webcomponents_values) && in_array($checkbox[0],$webcomponents_values)){
                        $checked = true;
                    }
                    ?><input type="checkbox" id="<?php echo $checkbox[0]; ?>" name="<?php echo $checkbox[0]; ?>" value="<?php echo $checkbox[0]; ?>" <?php echo $checked?'checked':''; ?>><label for="<?php echo $checkbox[0]; ?>"><?php echo $checkbox[1]; ?></label>
                <?php } ?>
            </fieldset>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">Donation Amounts (comma separated list, without currency symbols):</th>
            <td><input type="text" name="hdk-donate-amounts" value="<?php echo esc_html(get_option('hdk-donate-amounts')); ?>"/></td>
        </tr>

        <tr valign="top">
            <th scope="row">Donation Success Message:</th>
            <td><textarea name="hdk-donate-success" ><?php echo esc_html(get_option('hdk-donate-success')); ?></textarea></td>
        </tr>
        
        <tr valign="top">
            <th scope="row">Donation Error Message:</th>
            <td><textarea name="hdk-donate-error" ><?php echo esc_html(get_option('hdk-donate-error')); ?></textarea></td>
        </tr>

        <tr valign="top">
            <th scope="row">Members Success Message:</th>
            <td><textarea name="hdk-members-success" ><?php echo esc_html(get_option('hdk-members-success')); ?></textarea></td>
        </tr>
        
        <tr valign="top">
            <th scope="row">Members Error Message:</th>
            <td><textarea name="hdk-members-error" ><?php echo esc_html(get_option('hdk-members-error')); ?></textarea></td>
        </tr>

        <tr valign="top">
            <th scope="row">Merchandise Success Message:</th>
            <td><textarea name="hdk-merchandise-success" ><?php echo get_option('hdk-merchandise-success'); ?></textarea></td>
        </tr>
        
        <tr valign="top">
            <th scope="row">Merchandises Error Message:</th>
            <td><textarea name="hdk-merchandise-error" ><?php echo esc_html(get_option('hdk-merchandise-error')); ?></textarea></td>
        </tr>
    </table>
    <?php submit_button('Save Messages'); ?>
</form>