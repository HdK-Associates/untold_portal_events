<h2>Spektrix Settings</h2>
<?php 
$settings_message=get_transient('hdk_settings_form_message');
$settings_error=get_transient('hdk_settings_form_error');
if ($settings_message||$settings_error){
    ?><p class="<?php echo $settings_message?'message':'error'; ?>"><?php echo $settings_message?$settings_message:$settings_error; ?></p><?php
}?>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    <?php $hdk_settings_nonce = wp_create_nonce( 'hdk_settings_nonce' ); ?>
    <input type="hidden" name="action" value="hdk_settings_form">
    <input type="hidden" name="hdk_settings_nonce" value="<?php echo $hdk_settings_nonce ?>" />
    <table class="form-table">
        <tr valign="top">
            <th scope="row">Spektrix Client Code:</th>
            <td><input type="text" name="hdk-client-code" rows="4" cols="50" value="<?php echo esc_html(get_option('hdk-client-code')); ?>"></td>
        </tr>
        <tr valign="top">
            <th scope="row">Spektrix Stylesheet Name:</th>
            <td><input type="text" name="hdk-stylesheet" value="<?php echo esc_html(get_option('hdk-stylesheet')); ?>"/></td>
        </tr>
        <tr valign="top">
            <th scope="row">Ticket Subdomain (without the scheme or slashes .eg tickets.wearehdk.com):</th>
            <td><input type="text" name="hdk-subdomain" value="<?php echo esc_html(get_option('hdk-subdomain')); ?>"/></td>
        </tr>
        <tr valign="top">
            <?php $cron_options = array(
                'daily'=>'Daily (default)',
                'hourly'=>'Hourly',
                'fourhours'=>'Every Four Hours',
                'thricedaily'=>'Every Eight Hours',
                'twicedaily'=>'Twice Daily',
                'weekly'=>'Weekly'
            ); ?>
            <th scope="row">Cron frequency:</th>
            <td><select name="hdk-cron">
                <?php foreach($cron_options as $key=>$value){
                    $select=get_option('hdk-cron')==$key ?>
                    <option value="<?php echo $key; ?>" <?php echo $select?'selected':''; ?>><?php echo $value; ?></option>
                <?php } ?>
            </select></td>
        </tr>
        <tr valign="top">
            <?php $retrieve_options = array(
                '+ 90 days'=>'Three Months (default)',
                '+ 30 days'=>'One Month',
                '+ 180 days'=>'Six Months',
                '+ 365 days'=>'One Year',
                ''=>'All',
            ); ?>
            <th scope="row">Months of event data to retrieve:</th>
            <td><select name="hdk-retrieve">
                <?php foreach($retrieve_options as $key=>$value){
                    $select=get_option('hdk-retrieve')==$key ?>
                    <option value="<?php echo $key; ?>" <?php echo $select?'selected':''; ?>><?php echo $value; ?></option>
                <?php } ?>
            </select></td>
        </tr>
    </table>
    <?php submit_button('Save Settings'); ?>
</form>
