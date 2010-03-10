<?php
/* order data claa */

class orderDataObject
{

	var $customer;
	var $deliveri;
	var $cart;
	var $condition;
	var $order;
	var $reserve;

	function orderDataObject($order_id) {
		global $wpdb;
	
		$tableName = $wpdb->prefix . "usces_order";
		$query = $wpdb->prepare("SELECT * FROM $tableName WHERE ID = %d", $order_id);
		$data = $wpdb->get_row( $query, ARRAY_A );
		
		$this->customer['mem_id'] = $data['mem_id'];
		$this->customer['email'] = $data['order_email'];
		$this->customer['name1'] = $data['order_name1'];
		$this->customer['name2'] = $data['order_name2'];
		$this->customer['name3'] = $data['order_name3'];
		$this->customer['name4'] = $data['order_name4'];
		$this->customer['zip'] = $data['order_zip'];
		$this->customer['pref'] = $data['order_pref'];
		$this->customer['address1'] = $data['order_address1'];
		$this->customer['address2'] = $data['order_address2'];
		$this->customer['address3'] = $data['order_address3'];
		$this->customer['tel'] = $data['order_tel'];
		$this->customer['fax'] = $data['order_fax'];

		$this->deliveri = (array)unserialize($data['order_delivery']);

		$this->cart = (array)unserialize($data['order_cart']);

		$this->condition = (array)unserialize($data['order_condition']);

		$this->order['ID'] = $order_id;
		$this->order['note'] = $data['order_note'];
		$this->order['delivery_method'] = $data['order_delivery_method'];
		$this->order['payment_name'] = $data['order_payment_name'];
		$this->order['item_total_price'] = $data['order_item_total_price'];
		$this->order['getpoint'] = $data['order_getpoint'];
		$this->order['usedpoint'] = $data['order_usedpoint'];
		$this->order['discount'] = $data['order_discount'];
		$this->order['shipping_charge'] = $data['order_shipping_charge'];
		$this->order['cod_fee'] = $data['order_cod_fee'];
		$this->order['tax'] = $data['order_tax'];
		$this->order['date'] = $data['order_date'];
		$this->order['modified'] = $data['order_modified'];
		$this->order['status'] = $data['order_status'];
		$this->order['check'] = $data['order_check'];
		$this->order['total_full_price'] = $data['order_item_total_price'] - $data['order_usedpoint'] + $data['order_discount'] + $data['order_shipping_charge'] + $data['order_cod_fee'] + $data['order_tax'];

		$this->reserve = array();
	}
}
?>