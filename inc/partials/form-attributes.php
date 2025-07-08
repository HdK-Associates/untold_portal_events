<?php 
$attribute_message=get_transient('hdk_attribute_form_message');
$attribute_error=get_transient('hdk_attribute_form_error');
if ($attribute_message||$attribute_error){
    ?><p class="<?php echo $attribute_message?'message':'error'; ?>"><?php echo $attribute_message?$attribute_message:$attribute_error; ?></p><?php
}?>
<h2>Spektrix Attributes</h2>
<p>Attributes should match the Name field of the Spektrix Attribute exactly. See <a href="https://system.spektrix.com/<?php echo $this->client_code; ?>/client/settings-interface/attribute-templates">here</a>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    <?php $hdk_attribute_nonce = wp_create_nonce( 'hdk_attribute_nonce' ); ?>
    <input type="hidden" name="action" value="hdk_attribute_form">
    <input type="hidden" name="hdk_attribute_nonce" value="<?php echo $hdk_attribute_nonce ?>" />
    <h3>Hierarchical attributes</h3>
    <p>Attributes added here will be created as WP taxonomies.</p>
    <table class="form-table">
        <tr valign="top">
            <th scope="row">Add a comma separated list of hierarchical attributes for Events:</th>
            <td><textarea name="hdk-attributes"/><?php echo esc_html(get_option('hdk-attributes')); ?></textarea></td>
        </tr>
        <?php if(0==get_option('hdk-initiate-plugin')||1==get_option('hdk-merch')){ ?>
            <tr valign="top">
                <th scope="row">Add a comma separated list of hierarchical attributes for Merchandise:</th>
                <td><textarea name="hdk-attributes-merch"/><?php echo esc_html(get_option('hdk-attributes-merch')); ?></textarea></td>
            </tr>
        <?php } ?>
    </table>
    <h3>Secondary attributes</h3>
    <p>Attributes added here will be created as Terms in a Taxonomy called Event Features (or Merch Features). These should be boolean attributes in Spektrix</p>
    <table class="form-table">
        <tr valign="top">
            <th scope="row">Add a comma separated list of secondary attributes for Events:</th>
            <td><textarea name="hdk-secondary-attributes"/><?php echo esc_html(get_option('hdk-secondary-attributes')); ?></textarea></td>
        </tr>
        <?php if(0==get_option('hdk-initiate-plugin')||1==get_option('hdk-merch')){ ?>
            <tr valign="top">
                <th scope="row">Add a comma separated list of secondary attributes for Merchandise:</th>
                <td><textarea name="hdk-secondary-attributes-merch"/><?php echo esc_html(get_option('hdk-secondary-attributes-merch')); ?></textarea></td>
            </tr>
        <?php } ?>
    </table>
    <h3>Boolean attributes</h3>
    <p>Attributes added here will be created as WP post meta</p>
    <table class="form-table">
        <tr valign="top">
            <th scope="row">Add a comma separated list of boolean attributes for Events:</th>
            <td><textarea name="hdk-bool-attributes"/><?php echo esc_html(get_option('hdk-bool-attributes')); ?></textarea></td>
        </tr>
        <?php if(0==get_option('hdk-initiate-plugin')||1==get_option('hdk-merch')){ ?>
            <tr valign="top">
                <th scope="row">Add a comma separated list of boolean attributes for Merchandise:</th>
                <td><textarea name="hdk-bool-attributes-merch"/><?php echo esc_html(get_option('hdk-bool-attributes-merch')); ?></textarea></td>
            </tr>
        <?php } ?>
    </table>
    <?php submit_button('Save Attributes'); ?>
</form>