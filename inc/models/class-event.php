<?php
class HdKEvent
{
    public static function build($id,$sp_id='')
    {
        $is_spektrix = get_field('spektrix_ticketing', $id);
        $manual_override = get_field('manual_override', $id);
        if ($is_spektrix && !$manual_override) {
            $event = new SpektrixEvent($id,$sp_id);
            if (!$event->is_active_event()) {   
                $event = new ExternalEvent($id);
            }
        } else {
            $event = new ExternalEvent($id);
        }

        return $event;
    }
}
