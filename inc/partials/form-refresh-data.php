<?php 
/*Gets all Events for the refresh single event form*/
$args = array(
    'post_type'=>'event',
    'numberposts' => -1,
    'order'=>'ASC',
    'post_status'=>['publish','draft','private']
);
$all_events = get_posts($args);
$all_events_mapped = array_map(function($n) { return array('event_id'=>$n->ID,'event_name'=>$n->post_title.' ('.date('M Y',strtotime($n->post_date)).')'); }, $all_events);
wp_add_inline_script( 'spektrix', 'const eventsList = ' . json_encode($all_events_mapped), 'before' );
if ($message||$error){
    ?><p class="<?php echo $message?'message':'error'; ?>"><?php echo $message?$message:$error; ?></p><?php
}?>

<h2>Refresh All Spektrix Data Now</h2>
    <button id="refresh_now">Refresh Spektrix Data</button>
    <p>This will make sure all Spektrix events, instances, prices are up to date. It may take a minute. Please be patient and don't refresh the page.</p>
<h2>Refresh Single Event Spektrix Data</h2>
    <p><label>Event<input name="single_event" id="single_event"></label></p>
    <button id="refresh_single_event">Refresh Single Spektrix Event Data</button>
    <p>Start typing to choose an Event. This will refresh the instances of this Event. It may take a minute. Please be patient and don't refresh the page.</p>
<h2>Import Spektrix Event</h2>
    <p><label>Enter a Spektrix ID<input name="import_event" id="import_event"></label></p>
    <button id="import_single_event">Import with Spektrix ID</button>
    <p>Enter a Spektrix ID. Should be a number between 4 and 6 digits long. This should only be used for events that aren't showing up in the system yet</p>
<div id="track-container">
    <div id="spinner"></div>
    <div id="track"><div id="track-inner"></div></div>
</div>
<p id="event_message"></p>
<div id="event_modal" style="display:none;"><div class="event_modal_inner"><div id="event_modal_content"></div><button id="event_modal_accept" disabled>Yes. Add this data to the database</button><button id="event_modal_reject" disabled>No. Something is wrong. I'll review my posts and Spektrix data</button></div></div>
<h2>Refresh Wordpress Data</h2>
<?php
$update_message=get_transient('hdk_update_form_message');
$update_error=get_transient('hdk_update_form_error');
if ($update_message||$update_error){
    ?><p class="<?php echo $update_message?'message':'error'; ?>"><?php echo $update_message?$update_message:$update_error; ?></p><?php
}?>
<form method='post' action='<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>'>
    <?php $hdk_refresh_nonce = wp_create_nonce( 'hdk_refresh_nonce' ); ?>
    <input type="hidden" name="action" value="hdk_refresh_form">
    <input type="hidden" name="hdk_refresh_nonce" value="<?php echo $hdk_refresh_nonce ?>" />
    <input type="submit" name="refresh_wp" value="Refresh Wordpress Data">
    <p>This will create Events and Merchandise items from Spektrix data. This may take a minute. Please be patient and don't refresh the page. New events and merch will be created as drafts. Any items that are not on the Spektrix system will be tagged with 'Not Active'.</p>
</form>
