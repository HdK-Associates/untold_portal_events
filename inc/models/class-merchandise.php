<?php

class SpektrixMerch extends SpektrixItem{
    public $type = 'merchandise';
    public function get_data(){
        $this->id = $this->Spektrix->get_merch_id($this->post_id);
        $this->data = $this->Spektrix->get_merch_data($this->id);
    }

    public function get_price(){
        $price = $this->data['price'];
        return '£'.number_format($price,2,".",",");
    }

    public function is_sold_out(){
        return $this->data['stockLevel']<1;
    }

    public function get_purchase_component(){
        return HdKSpUtilities::get_merchandise_component($this->Spektrix->settings,$this->id);
    }
}

