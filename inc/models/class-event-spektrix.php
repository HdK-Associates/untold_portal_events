<?php

class SpektrixEvent
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
    public $requires_time_table = null;
    public $postMeta;
    public $all_day;
    public $post_meta;
    public $manual_instances;
    public $short_id;
    public $populate;
    public $seat_gap;
    private $client_code;
    private $subdomain;
    public function __construct(int $post_id=0,$id='')
    {
        $this->post_id = $post_id;
        $this->Spektrix = new HdKSpektrix;
        $this->tables = $this->Spektrix->tables;
        $this->id = $id;
        $this->client_code 		= $_ENV['SPEKTRIX_ACCOUNT_ID'];
        $this->subdomain		= $_ENV['SPEKTRIX_ENDPOINT'];
        $this->get_data();
    }
    public function get_data()
    {
        if(!$this->id){
            $this->id = $this->Spektrix->get_event_id($this->post_id);
        }
        if (!$this->id) {
            $this->id = get_post_meta($this->post_id, 'ap-event-spektrix-id', true);
            //Checking if there is data for this event
            if (!$this->id || !$this->Spektrix->get_event_data($this->id)) {
                $this->active_event = false;
            } else {
                //If there is data, update the relations table
                $this->Spektrix->update_event_relations($this->post_id, $this->id);
            }
        }
        if (!$this->id) {
            $this->active_event = false;
        }
        $this->data = $this->Spektrix->get_event_data($this->id);
        $this->all_day = get_field('all_day_event', $this->post_id);
        $this->get_instances();
    }

    public function get_dates(): array
    {
        $startDate = strtotime($this->data['firstInstanceDateTime']);
        $endDate = strtotime($this->data['lastInstanceDateTime']);
        $nextInstance = get_post_meta($this->post_id, 'next_instance_date', true);
        if (!$nextInstance) {
            $nextInstance = $startDate;
        } else {
            $nextInstance = strtotime($nextInstance);
        }
        $date_range = '';
        if (date('n', $nextInstance) == date('n', $endDate) && (date('Y', $nextInstance) == date('Y', $endDate))) {
            if (date('j', $nextInstance) != date('j', $endDate)) {
                $date_range = date('j', $nextInstance) . ' - ' . date('j M Y', $endDate);
            } else {
                $date_range = date('j M Y', $nextInstance);
            }
        } elseif (date('Y', $nextInstance) == date('Y', $endDate)) {
            $date_range = date('j M', $nextInstance) . ' - ' . date('j M Y', $endDate);
        } else {
            $date_range = date('j M Y', $nextInstance) . ' - ' . date('j M Y', $endDate);
        }
        return
            array(
                'date_range' => $date_range,
                'date_start' => $this->data['firstInstanceDateTime'],
                'date_end' => $this->data['lastInstanceDateTime'],
            );
    }

    public function get_instances()
    {
        $this->instances = $this->Spektrix->get_event_instances($this->id);
        return $this->instances;
    }

    public function get_post_meta()
    {
        if (!$this->post_meta) {
            $this->post_meta = get_post_meta($this->post_id);
        }
        return $this->post_meta;
    }

    public function get_instances_ids()
    {
        if (!$this->instances) {
            $this->instances = $this->Spektrix->get_event_instances($this->id);
        }
        $instances_ids = array_map(function ($n) {
            return $n['id'];
        }, $this->instances);
        return $instances_ids;
    }

    public function is_active_event()
    {
        return $this->active_event;
    }

    public function get_instances_dates()
    {
        if (!$this->instances) {
            $this->instances = $this->Spektrix->get_event_instances($this->id) ?: [];
        }
        if ($this->all_day) {
            $now = date('Y-m-d\TH:i:s', strtotime('today 00:00:00'));
        } else {
            $now = date('Y-m-d\TH:i:s');
        }
        $instance_dates_filtered = array_filter($this->instances, function ($n) use ($now) {
            //return $now<=$n['stopSellingAtWeb'] && $now >= $n['startSellingAtWeb'];
            return $now <= $n['stopSellingAtWeb'];
        });
        $instance_dates = array_map(function ($n) {
            $shortID = $this->get_short_id($n['id']);
            return array(
                'start' => $n['start'],
                'end' => isset($n['end']) ?: $n['start'],
                'id' => $shortID,
                'full_id' => $n['id'],
                'available' => $n['available']
            );
        }, $instance_dates_filtered);

        return array_values($instance_dates);
    }

    public function get_instances_dates_manually()
    {
        if (!$this->manual_instances) {
            $this->manual_instances = get_field('dates_and_times', $this->post_id);
        }
        if ($this->all_day) {
            $now = date('Y-m-d H:i:s', strtotime('today 00:00:00'));
        } else {
            $now = date('Y-m-d H:i:s');
        }
        $instance_dates = array_filter($this->manual_instances, function ($n) use ($now) {
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
    /*To do: add date format into options, add override*/

    public function get_instances_dates_aggregated()
    {
        $dates = $this->get_instances_dates();
        $month_year = array(
            'month' => array(),
        );
        foreach ($dates as $date) {
            $time = strtotime($date['start']);
            $thismonth = date('Y-m', $time);
            if (!in_array($thismonth, $month_year['month'])) {
                $month_year['month'][] = $thismonth;
            }
        }
        return $month_year;
    }

    public function get_event_dates_times()
    {
        if (!$this->instances) {
            $this->instances = $this->Spektrix->get_event_instances($this->id);
        }
        if (count($this->instances) > 100) {
            $html = '<h3>Dates and Times</h3>
            <div class="inside">
                This event has more than 100 instances. Please refer directly to Spektrix to see date and time info.
            </div>';
            return $html;
        }
        $post_meta = $this->get_post_meta();

        $html = '
        <h3>Dates and Times</h3>
            <div class="inside">
                <table class="widefat striped" id="event_date_time">
                    <thead>
                        <tr>
                            <th>Instance ID</th>
                            <th>Start Time</th>
                            <th>Available</th>
                            <th>Pricing</th>
                        </tr>
                    </thead>
                    <tbody>';
        $_ticketType = '';
        foreach ($this->instances as $i => $instance) {
            $ticket_types = $this->Spektrix->get_instance_ticket_types($instance['id']) ?: [];
            $ticket_types = array_map(function ($n) {
                return '<li>' . $n['ticketType_name'] . ' (£' . $n['amount'] . ')</li>';
            }, $ticket_types);
            $ticket_types = implode('', $ticket_types);
            if ($i == 0) {
                $_ticketType = $ticket_types;
            }
            if (!$ticket_types) {
                $ticket_types = $_ticketType;
            }
            $html .=

                '<tr>
                                <td>' . $instance['id'] . '</td>
                                <td>' . date('j F Y g:i a', strtotime($instance['start'])) . '</td>
                                <td>' . ($instance['available'] > 0 ? $instance['available'] : 'Sold Out') . '</td>
                                <td><details><summary>Ticket Types</summary><ul>' . $ticket_types . '</ul></details></td>
                           </tr>';
        }
        $html .=
            '</tbody>
                </table>
            </div>';
        return $html;
    }


    /*This is working for block booking unreserved seating, with only one ticket type*/
    public function get_instance_ticket_types($instanceID)
    {
        $ticket_types = array();
        $pricelists = $this->Spektrix->get_instance_ticket_types($instanceID);
        if (!$pricelists) {
            $pricelists = $this->Spektrix->populate_price_data($instanceID, $this->id);
        }
        $key = array_search($instanceID, array_column($this->instances, 'id'));
        $instance = $this->instances[$key];
        foreach ($pricelists as $pricelist) {
            $name = isset($pricelist['ticketType_name']) ? $pricelist['ticketType_name'] : $pricelist['name'];
            $ticket_types[$name] = array(
                'instance' => $instance['id'],
                'seatingPlan' => $instance['planId'],
                'type' => $pricelist['ticketType_id'],
                'price' => $pricelist['amount'],
            );
        }
        return $ticket_types;
    }

    public function get_price_range()
    {
        $prices = $this->Spektrix->get_event_price_range($this->id);
        if (0 == $prices['MinPrice']) {
            $prices['MinPrice'] = 'Free';
        } else {
            $prices['MinPrice'] = '£' . $prices['MinPrice'];
        }
        if (0 == $prices['MaxPrice']) {
            $prices['MaxPrice'] = 'Free';
        } else {
            $prices['MaxPrice'] = '£' . $prices['MaxPrice'];
        }
        if ($prices['MinPrice'] == $prices['MaxPrice']) {
            $prices_str = $prices['MinPrice'];
        } else {
            $prices_str = 'Tickets from: ' . $prices['MinPrice'] . ' - ' . $prices['MaxPrice'];
        }
        return $prices_str;
    }

    public function get_add_tickets_to_basket_url()
    {
        $client_codes = HdkSpUtilities::get_client_codes();
        return 'https://' . $client_codes['subdomain'] . '/' . $client_codes['client_code'] . '/api/v3/basket/tickets';
    }

    public function is_sold_out()
    {
        $available = $this->data['totalAvailable'];
        return $available <= 0;
    }

    public function get_realtime_availability($instance)
    {
        $settings = HdkSpUtilities::get_client_codes();
        $api = new HdkSpAPI($settings);
        return $api->SpektrixGetAPIEventDataStatus($instance)['available'];
    }

    public function get_basket()
    {
        return $this->Spektrix->get_basket_summary_event_component();
    }

    public function get_choose_tickets($facilitated_booking)
    {
        $instanceID = $this->get_best_available_instance_id();
        if (!$instanceID) {
            $onSaleDate = $this->get_earliest_onsale_date();
            if ($onSaleDate) {
                if (strtotime($onSaleDate['start']) > time()) {
                    return '<div class="ChooseSeats loaded"><h1 id="tickets">Tickets</h1><div class="message"><h4>Tickets are not yet on sale for this event</h4><p>Tickets will be available from ' . wp_date('jS F Y', strtotime($onSaleDate['start'])) . '</p></div></div>';
                } else {
                    $instanceID = $onSaleDate['id'];
                }
            } else {
                return '<div class="ChooseSeats loaded"><h1 id="tickets">Tickets</h1><div class="message"><h4>There are no available instances for this event</h4></div></div>';
            }
        }
        $shortID = $this->get_short_id($instanceID);
        $params = [];
        if ($facilitated_booking) {
            $params['ChooseAttendee'] = 'true';
        }
        return HdkSpUtilities::getChooseSeatsIframe($shortID, $params);
    }

    public function get_choose_tickets_with_seating_area_selector($facilitated_booking)
    {
        $instanceID = $this->get_best_available_instance_id();
        if (!$instanceID) {
            $onSaleDate = $this->get_earliest_onsale_date();
            if ($onSaleDate) {
                if (strtotime($onSaleDate['start']) > time()) {
                    return '<div class="ChooseSeats"><h1 id="tickets">Tickets</h1><div class="message"><h4>Tickets are not yet on sale for this event</h4><p>Tickets will be available from ' . wp_date('jS F Y', strtotime($onSaleDate['start'])) . '</p></div></div>';
                } else {
                    $instanceID = $onSaleDate['id'];
                }
            } else {
                return '<div class="ChooseSeats"><h1 id="tickets">Tickets</h1><div class="message"><h4>There are no available instances for this event</h4></div></div>';
            }
        }
        $seatingPlans = $this->Spektrix->get_seating_plan($instanceID);
        if (!$seatingPlans) {
            return $this->get_choose_tickets($facilitated_booking);
        }
        $html = '<div class="ChooseSeats seating-area-selector-wrapper"><h1 id="tickets">' . $seatingPlans['name'] . '</h1><div class="seating_area_selector"><h3>Choose your seating area</h3><div class="flex" id="seating_area_selector">';
        foreach ($seatingPlans['areas'] as $area) {
            $html .= '<button class="btn btn-pink seat_selector_button" data-id="' . $this->get_short_id($area['id']) . '">' . $area['name'] . '</button>';
        }
        $html .= '</div></div></div>';
        $params = [];
        if ($facilitated_booking) {
            $params['ChooseAttendee'] = 'true';
        }
        $params['SeatingAreaId'] = '__seatingPlanId__';
        $shortID = $this->get_short_id($instanceID);
        $html .= '<div class="iframe-wrapper d-none load_manually">' . HdkSpUtilities::getChooseSeatsIframe($shortID, $params) . '</div>';
        return $html;
    }

    public function get_login_check($not_logged_in, $restrict_by_tags, $no_access_message, $facilitated_booking)
    {
        $checker = $this->get_login_checker($not_logged_in, $restrict_by_tags, $no_access_message);
        $instanceID = $this->get_best_available_instance_id();
        $shortID = $this->get_short_id($instanceID);
        $params = [];
        if ($facilitated_booking) {
            $params['ChooseAttendee'] = 'true';
        }
        $output = $checker['open'];
        $output .= HdkSpUtilities::getChooseSeatsIframe($shortID, $params);
        $output .= $checker['close'];
        return $output;
    }

    public function get_seat_selector_login_check($not_logged_in, $restrict_by_tags, $no_access_message, $facilitated_booking)
    {
        $checker = $this->get_login_checker($not_logged_in, $restrict_by_tags, $no_access_message);
        $output = $checker['open'];
        $output .= $this->get_choose_tickets_with_seating_area_selector($facilitated_booking);
        $output .= $checker['close'];
    }

    public function get_login_checker($not_logged_in, $restrict_by_tags, $no_access_message)
    {
        global $post;
        $script  = 'const SPEKTRIXBASEURLCUSTOMER = "' . $this->subdomain . '/' . $this->client_code . '/api/v3/customer";
        const SPEKTRIXTAGNAME = ' . json_encode($restrict_by_tags) . ';
        const SPEKTRIXNOACCESSMESSAGE = "' . $no_access_message . '";';
        wp_add_inline_script('ap-customer', $script, 'before');
        $url = urlencode(get_permalink($post) . '#tickets');
        $has_priority_booking = get_field('priority_booking', $this->post_id);
        $times_str = '';
        if ($has_priority_booking) {
            $priority_booking_days = get_field('priority_booking_days', $this->post_id);
            $first_instance = $this->instances[0];
            /* $booking_open = strtotime($first_instance['startSellingAtWeb'])-($priority_booking_days*24*60*60);
            $booking_open = date('H:i \o\n j M Y',$booking_open); */
            $times_str = '<p>Online booking for this event opens at ' . date('H:i \o\n j M Y', strtotime($first_instance['startSellingAtWeb'] . ' -' . $priority_booking_days . 'days')) . '</p>';
        }
        $output['open'] = '<div id="login_check"><div class="message"><h4>' . $not_logged_in . '</h4>' . $times_str . '<a href="/login?returnUrl=' . $url . '" class="vc_general vc_btn3 ap_btn_fill_icon vc_btn3-color-vc_btn_ap ap_btn_blue">Login</a></div>';
        $output['close'] = '</div>';
        return $output;
    }

    public function get_api_booking_flow_with_login_check($not_logged_in, $restrict_by_tags, $no_access_message, $cache, $seat_spacing, $supps, $facilitated_booking)
    {
        $checker = $this->get_login_checker($not_logged_in, $restrict_by_tags, $no_access_message);
        $output = $checker['open'];
        $output .= $this->get_api_booking_flow($cache, $seat_spacing, $supps, $facilitated_booking);
        $output .= $checker['close'];
        return $output;
    }

    public function get_supplementary_events_booking_flow_with_login_check($not_logged_in, $restrict_by_tags, $no_access_message, $cache, $seat_spacing, $supps_by_tag, $facilitated_booking)
    {
        $checker = $this->get_login_checker($not_logged_in, $restrict_by_tags, $no_access_message);
        $output = $checker['open'];
        $output .= $this->get_supplementary_events_booking_flow($cache, $seat_spacing, $supps_by_tag, $facilitated_booking);
        $output .= $checker['close'];
        return $output;
    }

    public function get_api_booking_flow($cache, $seat_spacing, $page=1)
    {
        if (!$this->instances) {
            $this->instances = $this->Spektrix->get_event_instances($this->id);
        }
        $this->populate = new HdKSpPopulate();
        $post_meta = $this->get_post_meta();
        if ($cache || $this->instances[0]['available'] === null) {
            $cache_time = intval($cache ?: 1) * 60;
            if (false === ($instances = get_transient('caching_' . $this->id))) {
                $single_event = $this->populate->UpdateSingleEvent($this->post_id, $this->id);
                $this->populate->UpdateTablesEventInstance($single_event[1], 0, true);
                set_transient('caching_' . $this->id, 1, $cache_time);
            }
        }
        $seat_gap = false;
        if ($seat_spacing) {
            $first_instance = $this->instances[0];
            if (isset($first_instance['attribute_AutoDistancingSeatGap'])) {
                $seat_gap = $first_instance['attribute_AutoDistancingSeatGap'];
            }
        };
        $ticketType=$this->get_instance_ticket_types($this->instances[0]['id']);
        $instances = [];
        $now = wp_date('Y-m-d\TH:i:s');
        foreach ($this->instances as $instance) {
            if ($instance['start']>$now && $instance['isOnSale'] != "0" && ($instance['cancelled'] == "0" || !$instance['cancelled']) ) {
                $available = true;
                if ($seat_gap && isset($instance['available']) && $instance['available'] < $seat_gap) {
                    $available = false;
                }
                $start = strtotime($instance['start']);
                $end = isset($post_meta['end_' . $instance['id']]) ? strtotime($post_meta['end_' . $instance['id']][0]) : $start;
                $shortID = $this->get_short_id($instance['id']);
                $instances[] = [
                    'start' => date(DateTimeInterface::ATOM, $start),
                    'end' => date(DateTimeInterface::ATOM, $end),
                    'formattedTime' => date('H:i', $start),
                    'id' => $instance['id'],
                    'shortid' => $shortID,
                    'available' => $available,
                ];
            }
        }
        $script = 'const INSTANCES = ' . json_encode($instances) . '; const SPEKTRIXBASEURL = "' . $this->Spektrix->settings['subdomain'] . '/' . $this->Spektrix->settings['client_code'] . '/api/v3/instances"; const SPEKTRIXBASKETURL = "' .  $this->Spektrix->settings['subdomain'] . '/' . $this->Spektrix->settings['client_code'] . '/api/v3/basket/tickets";';
        $script .= ' const SEATGAP = "' . $seat_gap . '";';
        $script.=' const TICKETTYPES = '.json_encode($ticketType).';';
        $script .= ' const CURRENT_PAGE = ' . intval($page) . ';';


        wp_add_inline_script('spektrix-calendar', $script, 'before');

        $output = '<div id="buy_tickets_api_component" class="api_component">';
        $output .= '<div id="select_tickets" class="ChooseSeats api_component_to_basket step_1"><h4 id="tickets">Select a date</h4><div class="grid grid_50"><div id="color-calendar"></div><div id="time_response"></div></div><div id="ticket_response"></div></div>';
        $output .= '<div class="basket_iframe step_2">' . (HdKSpUtilities::generateIframe('Basket2',true,false,false,true) ). '</div>';
        $output .= '<div class="checkout_iframe step_3">' . (HdKSpUtilities::generateIframe('Checkout',false,false,true,true) ). '</div>';
        $output .= '</div>';

        return $output;
    }

    public function get_supplementary_events_booking_flow($cache, $seat_spacing, $supps_by_tag, $facilitated_booking)
    {
        global $post;
        if (!$this->instances) {
            $this->instances = $this->Spektrix->get_event_instances($this->id);
        }
        $this->populate = new HdKSpPopulate();
        $post_meta = $this->get_post_meta();
        if ($cache || $this->instances[0]['available'] === null) {
            $cache_time = intval($cache ?: 1) * 60;
            if (false === ($instances = get_transient('caching_' . $this->id))) {
                $single_event = $this->populate->UpdateSingleEvent($this->post_id, $this->id);
                $this->populate->UpdateTablesEventInstance($single_event[1], 0, true);
                set_transient('caching_' . $this->id, 1, $cache_time);
            }
        }
        //if(false===($supp_events_data = get_transient('caching_supps_'.$this->id))){
        $supp_posts = get_posts([
            'post_type' => 'event',
            'posts_per_page' => -1,
            'post_status' => ['publish', 'draft'],
            'post__not_in' => [$post->ID],
            'tax_query' => [
                [
                    'taxonomy' => 'supp-event',
                    'field' => 'term_id',
                    'terms' => $supps_by_tag,
                    'operator' => 'IN'
                ]
            ],
            'meta_query' => [
                [
                    'key' => 'end_date',
                    'value' => wp_date('Y-m-d H:i:s'),
                    'compare' => '>='
                ]
            ]
        ]);
        $supp_events_data = array_map(
            function ($n) {
                $post_id = $n->ID;
                $order = get_post_meta($post_id, 'supp_order');
                $event_id = $this->Spektrix->get_event_id($post_id);
                $data = $this->Spektrix->get_event_data($event_id);
                return ['html' => $data['description'], 'post_id' => $post_id, 'title' => get_the_title($n->ID), 'spektrix_id' => $event_id, 'order' => intval($data['attribute_WebsiteSupplementaryItemOrder'])];
            },
            $supp_posts
        );
        usort($supp_events_data, function ($a, $b) {
            if (!$a['order']) {
                return -1;
            }
            if (!$b['order']) {
                return 1;
            }
            if ($a['order'] == $b['order']) {
                return 0;
            }
            return ($a['order'] < $b['order']) ? -1 : 1;
        });
        $seat_gap = false;
        if ($seat_spacing) {
            $first_instance = $this->instances[0];
            if (isset($first_instance['attribute_AutoDistancingSeatGap'])) {
                $seat_gap = $first_instance['attribute_AutoDistancingSeatGap'];
            }
        };
        foreach ($supp_events_data as &$data) {
            $event_instances = $this->Spektrix->get_event_instances($data['spektrix_id']);
            $data['instances'] = [];
            $ticketTypeRaw = $this->Spektrix->get_instance_ticket_types($event_instances[0]['id']);
            $ticketType = [];
            $seat_gap = false;
            if ($seat_spacing) {
                $first_instance = $event_instances[0];
                if (isset($first_instance['attribute_AutoDistancingSeatGap'])) {
                    $seat_gap = $first_instance['attribute_AutoDistancingSeatGap'];
                }
            };
            foreach ($ticketTypeRaw as $type) {
                $name = isset($type['ticketType_name']) ? $type['ticketType_name'] : $type['name'];
                $ticketType[$name] = [
                    'instance' => $event_instances[0]['id'],
                    'seatingPlan' => $event_instances[0]['planId'],
                    'type' => $type['ticketType_id'],
                    'price' => $type['amount'],
                ];
            }
            foreach ($event_instances as $instance) {
                if ($instance['isOnSale'] != "0" && $instance['cancelled'] == "0" || !$instance['cancelled']) {
                    $available = true;
                    if ($seat_gap && isset($instance['available']) && $instance['available'] < $seat_gap) {
                        $available = false;
                    }

                    $start = strtotime($instance['start']);
                    $end = isset($post_meta['end_' . $instance['id']]) ? strtotime($post_meta['end_' . $instance['id']][0]) : $start;
                    $shortID = $this->get_short_id($instance['id']);
                    $data['instances'][] = [
                        'start' => date(DateTimeInterface::ATOM, $start),
                        'end' => date(DateTimeInterface::ATOM, $end),
                        'formattedTime' => date('H:i', $start),
                        'id' => $instance['id'],
                        'shortid' => $shortID,
                        'available' => $available,
                        'ticketTypes' => $ticketType,
                    ];
                }
            }
        }
        // set_transient('caching_supps_'.$this->id,$supp_events_data,$cache_time);    
        // }
        $seat_gap = false;
        if ($seat_spacing) {
            $first_instance = $this->instances[0];
            if (isset($first_instance['attribute_AutoDistancingSeatGap'])) {
                $seat_gap = $first_instance['attribute_AutoDistancingSeatGap'];
            }
        };
        //$ticketType=$this->get_instance_ticket_types($this->instances[0]['id']);
        $instances = [];
        foreach ($this->instances as $instance) {
            if ($instance['isOnSale'] != "0" && $instance['cancelled'] == "0" || !$instance['cancelled']) {
                $available = true;
                if ($seat_gap && isset($instance['available']) && $instance['available'] < $seat_gap) {
                    $available = false;
                }
                $start = strtotime($instance['start']);
                $end = isset($post_meta['end_' . $instance['id']]) ? strtotime($post_meta['end_' . $instance['id']][0]) : $start;
                $shortID = $this->get_short_id($instance['id']);
                $_ticketType = $this->get_instance_ticket_types($instance['id']);
                $instances[] = [
                    'start' => date(DateTimeInterface::ATOM, $start),
                    'end' => date(DateTimeInterface::ATOM, $end),
                    'formattedTime' => date('H:i', $start),
                    'id' => $instance['id'],
                    'shortid' => $shortID,
                    'available' => $available,
                    'ticketTypes' => $_ticketType,
                ];
            }
        }
        $script = 'const INSTANCES = ' . json_encode($instances) . ';
            const SUPP_INSTANCES = ' . json_encode($supp_events_data) . '; 
            const SPEKTRIXBASEURL = "' . $this->subdomain . '/' . $this->client_code . '/api/v3/instances"; 
            const SPEKTRIXBASKETURL = "' . $this->subdomain . '/' . $this->client_code . '/api/v3/basket/tickets";
            const SPEKTRIXCLEARBASKETURL = "' . $this->subdomain . '/' . $this->client_code . '/api/v3/basket/clear";';
        $script .= ' const SUPP_EVENT = true;';
        $script .= ' const SEATGAP = "' . $seat_gap . '";';
        // $script.=' const TICKETTYPES = '.json_encode($ticketType).';';


        wp_add_inline_script('spektrix-calendar', $script, 'before');

        $output = '<div id="buy_tickets_api_component" class="vc_row wpb_row vc_inner vc_row-fluid api_component"><div id="select_tickets" class="ChooseSeats supp_api_component_to_basket"><h1 id="tickets">Select your dates</h1><div id="color-calendar"></div><div id="response"></div></div>';
        $output .= '<div id="supp_frame" class="SupplementaryEventsPage">';
        foreach ($supp_events_data as $event) {
            $description = $event['html'] ?: '<h3>' . $event['title'] . '</h3>';
            $output .= '<div id="supp_' . $event['post_id'] . '" class="supp d-none">'
                . $description .
                '</div>';
        }
        $output .= '<div id = "attributes" class="d-none"></div>';
        $output .= '<button id="clear" class="d-none ap_btn_blue">Start Over</button>';
        $output .= '</div>';


        $output .= '</div>';
        return $output;
    }

    public function get_best_available_instance_id()
    {
        if (!$this->instances) {
            try {
                $this->instances = $this->Spektrix->get_event_instances($this->id);
            } catch (Exception $e) {
                return false;
            }
        }
        $instances = $this->instances;
        $fallback = null;

        foreach ($instances as $instance) {
            $cancelled = $instance['cancelled'] && $instance['cancelled'] !== '0';
            $onSale    = $instance['isOnSale']   && $instance['isOnSale']   !== '0';
            $stopSelling = $instance['stopSellingAtWeb'] && $instance['stopSellingAtWeb'] !== '0' ? strtotime($instance['stopSellingAtWeb']) : false;
            $now = time();
            if ($instance['id'] && $onSale && !$cancelled && $stopSelling && $now < $stopSelling) {
                return $instance['id'];
            } else {
                $priority = $this->check_if_instance_has_priority_booking($instance);
                if ($priority) {
                    return $instance['id'];
                }
            }
        }
        if ($instances) {
            return $instances[0]['id'];
        }
        return false;
    }

    public function get_earliest_onsale_date()
    {
        if (!$this->instances) {
            try {
                $this->instances = $this->Spektrix->get_event_instances($this->id);
            } catch (Exception $e) {
                return false;
            }
        }
        $instances = $this->instances;
        $fallback = null;

        foreach ($instances as $instance) {
            $cancelled = $instance['cancelled'] && $instance['cancelled'] !== '0';

            if ($instance['startSellingAtWeb'] && !$cancelled) {
                return ['start' => $instance['startSellingAtWeb'], 'id' => $instance['id']];
            }
        }

        return false;
    }

    public function check_if_instance_has_priority_booking($instance)
    {
        $has_priority_booking = get_field('priority_booking', $this->post_id);
        if (!$has_priority_booking) {
            return false;
        }
        $priority_days = intval(get_field('priority_booking_days', $this->post_id));
        $now = time();
        $instance_start_selling = strtotime($instance['startSellingAtWeb']);
        $seconds_difference = $instance_start_selling - $now;
        $days_difference = floor($seconds_difference / (60 * 60 * 24));
        if ($priority_days && $days_difference <= $priority_days) {
            return true;
        }
        return false;
    }

    public function get_short_id($instanceID)
    {
        $id = preg_split("/[a-zA-Z]/", $instanceID, 2);
        return $id[0];
    }

    public function get_booking_url(){
        return get_site_url().'/book-tickets/?event_id='.$this->post_id;
    }

    public function is_spektrix()
    {
        return true;
    }
}
