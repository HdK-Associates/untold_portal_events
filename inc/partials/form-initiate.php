<h2>Spektrix Base Settings</h2>
<?php 
$base_message=get_transient('hdk_base_form_message');
$base_error=get_transient('hdk_base_form_error');
if ($base_message||$base_error){
    ?><p class="<?php echo $base_message?'message':'error'; ?>"><?php echo $base_message?$base_message:$base_error; ?></p><?php
}?>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="base">
    <?php $hdk_base_nonce = wp_create_nonce( 'hdk_base_nonce' ); ?>
    <input type="hidden" name="action" value="hdk_base_form">
    <input type="hidden" name="hdk_base_nonce" value="<?php echo $hdk_base_nonce ?>" />
    <table class="form-table">
        <tr valign="top">
            <th scope="row">Enable Merchandise:</th>
            <td><input type="checkbox" name="hdk-merch" id="hdk-merch"  <?php echo get_option('hdk-merch')==1?'checked':''; ?>></td>
        </tr>
        <tr valign="top">
            <th scope="row">Enable Memberships:</th>
            <td><input type="checkbox" name="hdk-members" <?php echo get_option('hdk-members')==1?'checked':''; ?>/></td>
        </tr>
        <tr valign="top">
            <th scope="row">Enable Funds:</th>
            <td><input type="checkbox" name="hdk-funds" <?php echo get_option('hdk-funds')==1?'checked':''; ?>/></td>
        </tr>
        <tr valign="top">
            <th scope="row">Enable Newsletter Tags:</th>
            <td><input type="checkbox" name="hdk-tags" <?php echo get_option('hdk-tags')==1?'checked':''; ?>/></td>
        </tr>
        <?php $post_types = get_post_types(
                array(
                    'public'   => true,
                    '_builtin' => false
                )
            );
            ?>
        <tr valign="top">
            <th scope="row">Select Custom Post Type to use for Events:</th>
            <td>
                <select name="hdk-event-cpt" id="hdk-event-cpt" value="<?php echo esc_html(get_option('hdk-event-cpt')); ?>">
                <option value="default">Default</option>
                <?php if($post_types){
                    foreach($post_types as $post_type){ ?>
                        <option value="<?php echo $post_type; ?>"><?php echo $post_type; ?></option>
                    <?php } 
                }?>
                </select>
                <p>Selecting 'Default' will create a new custom post_type called Events</p>
            </td>
        </tr>
        <tr valign="top" id="merch-select" style="display:none;">
            <th scope="row">Select Custom Post Type to use for Merchandise:</th>
            <td>
                <select name="hdk-merch-cpt" id="hdk-merch-cpt" value="<?php echo esc_html(get_option('hdk-merch-cpt')); ?>">
                <option value="default">Default</option>
                <?php if($post_types){
                    foreach($post_types as $post_type){ ?>
                        <option value="<?php echo $post_type; ?>"><?php echo $post_type; ?></option>
                    <?php } 
                }?>
                </select>
                <p>Selecting 'Default' will create a new custom post_type called Merchandise if Merchandise is enabled</p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">Create Spektrix Tables:</th>
            <td><input type="checkbox" name="hdk-activate" value="<?php echo esc_html(get_option('hdk-activate')); ?>"><p>This will initiate Spektrix on this site. Any changes after this will require deactivating the plugin and deleting the tables in the DB</td>
        </tr>
    </table>
<?php submit_button('Save Base Settings'); ?>
</form>