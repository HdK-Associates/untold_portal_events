
jQuery(document).ready(function($) {
    if($.isFunction($.fn.datetimepicker)){
        $(".event_date").datetimepicker(
            { 
                controlType : 'select',
                oneLine: true,
                timeFormat: 'h:mm tt',
                dateFormat: 'MM d, yy',
            }
        );
    }
});

