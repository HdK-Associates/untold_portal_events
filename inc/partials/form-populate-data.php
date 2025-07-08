<?php 
/*Gets all Events for the refresh single event form*/
$base_message=get_transient('hdk_base_form_message');
$base_error=get_transient('hdk_base_form_error');
if ($base_message||$base_error){
    ?><p class="<?php echo $base_message?'message':'error'; ?>"><?php echo $base_message?$base_message:$base_error; ?></p><?php
}?>
<h2>Populate Spektrix Data Now</h2>
    <button id="refresh_now" data-init="1" >Populate Spektrix Data</button>
    <p>This will make sure all Spektrix events, instances, prices are up to date. It may take a minute. Please be patient and don't refresh the page.</p>
<div id="track-container">
    <div id="spinner"></div>
    <div id="track"><div id="track-inner"></div></div>
</div>
<p id="event_message"></p>
