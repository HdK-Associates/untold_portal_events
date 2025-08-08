<?php

class ExternalEvent extends SpektrixEvent
{
    private $post_id;
    private $tables;
    private $Spektrix;
    private $data;
    private $id;
    public $fields;
    public $type = 'event';
    public $instances;
    public $active_event = true;

    public function __construct(int $post_id)
    {
        $this->post_id = $post_id;
        $this->id = $post_id;
        $this->get_data();
    }

    public function get_data()
    {
        $this->all_day = get_field('all_day_event', $this->post_id);
        $this->get_instances();
    }

    public function get_dates(): array
    {
        $start = get_field('start_date', $this->post_id);
        $end = get_field('end_date', $this->post_id);
        $nextInstance = get_post_meta($this->post_id, 'next_instance_date', true);
        if (!$nextInstance) {
            $nextInstance = $start;
        }
        if (!$end) {
            $end = $start;
        }
        if ($start == $end) {
            $date_range = date('j M Y', strtotime($start));
        } else {
            $start_no_time = date('Ymd', strtotime($start));
            $end_no_time = date('Ymd', strtotime($end));
            if ($start_no_time == $end_no_time) {
                $date_range = date('j M Y', strtotime($start));
            } else {
                if ($nextInstance == $end) {
                    $date_range = date('j M Y', strtotime($nextInstance));
                } else {
                    $instance_no_time = date('Ymd', strtotime($nextInstance));
                    if ($instance_no_time == $end_no_time) {
                        $date_range = date('j M Y', strtotime($nextInstance));
                    } else {
                        $date_range = $this->process_date_range($nextInstance, $end);
                    }
                }
            }
        }
        return
            array(
                'date_range' => $date_range,
                'date_start' => $start,
                'date_end' => $end,
            );
    }

    public function get_instances()
    {
        $this->instances = get_field('dates_and_times', $this->post_id);
        return $this->instances;
    }

    public function get_instances_dates()
    {
        if (!$this->instances) {
            $this->instances = get_field('dates_and_times', $this->post_id) ?: [];
        }
        if ($this->all_day) {
            $now = date('Y-m-d H:i:s', strtotime('today 00:00:00'));
        } else {
            $now = date('Y-m-d H:i:s');
        }
        $instance_dates = array_filter($this->instances, function ($n) use ($now) {
            $start = $n['start_time'];
            return ($start >= $now);
        });
        $instance_dates = array_map(function ($n) {
            $data = array(
                'gates_open' => $n['gate_opens'],
                'start' => $n['start_time'],
                'end' => isset($n['end_time']) ? $n['end_time'] : $n['start_time'],
                'info' => isset($n['additional_info']) ? $n['additional_info'] : false,
            );
            return $data;
        }, $instance_dates);

        return array_values($instance_dates);
    }


    public function process_date_range($open, $close)
    {
        $day = "j";
        $day_month = "j M";
        $day_month_year = "j M Y";
        $event_startMonth = date("m", strtotime($open));
        $event_endMonth = date("m", strtotime($close));
        $event_startYear = date("Y", strtotime($open));
        $event_endYear = date("Y", strtotime($close));
        if ($event_startMonth == $event_endMonth && $event_startYear == $event_endYear) {
            return date($day, strtotime($open)) . " - " . date($day_month_year, strtotime($close));
        } elseif (($event_startYear == $event_endYear)) {
            return date($day_month, strtotime($open)) . " - " . date($day_month_year, strtotime($close));
        } else {
            return date($day_month_year, strtotime($open)) . " - " . date($day_month_year, strtotime($close));
        }
    }

    public function get_price_range()
    {
        $price = get_field('ticket_price', $this->post_id);
        if ($price == 0) {
            $price = 'Free';
        } else {
            $price = 'From R' . $price;
        }
        return $price;
    }



    public function is_spektrix()
    {
        return false;
    }
}
