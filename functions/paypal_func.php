<?php
$usces_payment = false;
$payments = usces_get_system_option( 'usces_payment_method', 'sort' );
foreach( (array)$payments as $id => $payment ) {
	if( 'acting_paypal_ec' == $payment['settlement'] ) { $usces_payment = true; break; }
}
if( $usces_payment ) {
	add_action( 'init', 'usces_paypal_add_stylesheet' );
	add_action( 'usces_after_main', 'usces_paypal_add_script' );
	add_action( 'usces_front_ajax', 'usces_paypal_front_ajax' );
	add_filter( 'usces_filter_uscesL10n', 'usces_paypal_filter_uscesL10n' );
	add_filter( 'usces_filter_paypal_ec_cancelurl', 'usces_paypal_filter_paypal_ec_cancelurl', 10, 2 );
	add_action( 'usces_action_cart_page_footer', 'usces_paypal_action_cart_page_footer' );
	//add_filter( 'usces_filter_cart_page_footer', 'usces_paypal_filter_cart_page_footer' );
	add_filter( 'usces_filter_cartContent', 'usces_paypal_filter_cart_page_footer' );
	add_action( 'usces_action_customerinfo', 'usces_paypal_action_customerinfo' );
}

function usces_paypal_add_stylesheet() {
	global $usces;
	if( $usces->is_cart_page($_SERVER['REQUEST_URI']) ) {
		$jquery_ui_style_url = USCES_FRONT_PLUGIN_URL.'/css/jquery/jquery-ui-1.10.3.custom.min.css';
		$jquery_usces_paypal_style_url = USCES_FRONT_PLUGIN_URL.'/css/usces_paypal_style.css';
		wp_register_style( 'jquery-ui-style', $jquery_ui_style_url );
		wp_register_style( 'usces_paypal_style', $jquery_usces_paypal_style_url );
		wp_enqueue_style( 'jquery-ui-style' );
		wp_enqueue_style( 'usces_paypal_style' );
	}
}

function usces_paypal_add_script() {
	global $usces;
	if( $usces->is_cart_page($_SERVER['REQUEST_URI']) ) {
		wp_enqueue_script( 'jquery-ui-dialog' );
	}
}

function usces_paypal_filter_uscesL10n() {
	global $usces;
	if( $usces->is_cart_page($_SERVER['REQUEST_URI']) ) {
		echo "'frontAjaxUrl': '".USCES_SSL_URL."',\n";
	}
}

function usces_paypal_action_cart_page_footer() {
	$footer = usces_paypal_cart_page_footer();
	echo $footer;
}

function usces_paypal_filter_cart_page_footer( $html ) {
	global $usces;
	$footer = ( 'cart' == $usces->page ) ? usces_paypal_cart_page_footer( false ) : '';
	return $html.$footer;
}

function usces_paypal_cart_page_footer( $include = true ) {
	global $usces;
	$html = '';

	$member = $usces->get_member();
	if( !usces_paypal_set_session( $member['ID'] ) ) return $html;
	if( false === $usces->cart->num_row() ) return $html;
	if( defined('WCEX_AUTO_DELIVERY') and wcad_have_regular_order() ) return $html;

	$usces_entries = $usces->cart->get_entry();
	$usces->set_cart_fees( $member, $usces_entries );

	$usces_entries = $usces->cart->get_entry();
	$total_price = $usces_entries['order']['total_items_price'] + $usces_entries['order']['discount'] + $usces_entries['order']['shipping_charge'] + $usces_entries['order']['cod_fee'];
	$item_price = $usces_entries['order']['total_items_price'] + $usces_entries['order']['discount'];

	if( $include ) {
		include( USCES_PLUGIN_DIR."/includes/delivery_info_script.php" );
	} else {
		ob_start();
		include( USCES_PLUGIN_DIR."/includes/delivery_info_script.php" );
		$html .= ob_get_clean();
	}

	$html .= '
	<script type="text/javascript">
	jQuery(function($) {
		paypalfunc = {
			settings: {
				url: uscesL10n.frontAjaxUrl + "/",
				type: "POST",
				cache: false,
				error: function( res, dataType ) {
					alert( "Ajax error" );
				}
			},
			deliveryMethodSelect: function() {
				var s = this.settings;
				s.data = "usces_ajax_action=paypal_delivery_method&selected=" + $("#delivery_method_select option:selected").val() + "&delivery_date=" + $("#delivery_date_select").val() + "&delivery_time=" + $("#delivery_time_select").val();
				s.success = function( res, dataType ) {
					var r = res.split( "#usces#" );
					if( r[0] == "error" ) {
						$("#paypal_error_message_delivery_method").html( r[1] );
					} else {
						$("#paypal_error_message_delivery_method").empty();
						$("#paypal_confirm").empty();
						$("#paypal_purchase").empty();
						$("#paypal_confirm").html( r[1] );
						$("#paypal_purchase").html( r[2] );
						$("#delivery_date_select").bind("change", function(){ this.deliveryDateSelect(); });
						$("#delivery_time_select").bind("change", function(){ this.deliveryTimeSelect(); });
					}
				};
				$.ajax( s );
				return false;
			},
			deliveryDateSelect: function() {
				var s = this.settings;
				s.data = "usces_ajax_action=paypal_delivery_date_select&selected=" + $("#delivery_date_select option:selected").val();
				s.success = function( res, dataType ) {
				};
				$.ajax( s );
				return false;
			},
			deliveryTimeSelect: function() {
				var s = this.settings;
				s.data = "usces_ajax_action=paypal_delivery_time_select&selected=" + $("#delivery_time_select option:selected").val();
				s.success = function( res, dataType ) {
				};
				$.ajax( s );
				return false;
			},
			usePoint: function() {
				var s = this.settings;
				s.data = "usces_ajax_action=paypal_use_point&usepoint=" + $("#set_usedpoint").val() + "&total_price='.$total_price.'&item_price='.$item_price.'";
				s.success = function( res, dataType ) {
					var r = res.split( "#usces#" );
					if( r[0] == "error" ) {
						$("#paypal_error_message_use_point").html( r[1] );
					} else {
						$("#paypal_error_message_use_point").empty();
						$("#paypal_confirm").empty();
						$("#paypal_purchase").empty();
						$("#paypal_confirm").html( r[1] );
						$("#paypal_purchase").html( r[2] );
						$("#delivery_date_select").bind("change", function(){ this.deliveryDateSelect(); });
						$("#delivery_time_select").bind("change", function(){ this.deliveryTimeSelect(); });
					}
				};
				$.ajax( s );
				return false;
			}
		};
		$("#paypal_dialog").dialog({
			bgiframe: true,
			autoOpen: false,
			height: "auto",
			width: 400,
			resizable: true,
			modal: true,
			position: ["top",100],
			open: function( event, ui ) {
				$(".ui-dialog-titlebar", ui.panel).hide();
			}
		});
		$("#paypal_button").click( function() {
			$("#paypal_dialog").dialog( "open" );
		});
		$("#paypal_close").click( function() {
			$("#paypal_dialog").dialog( "close" );
		});
		if( $("#delivery_method_select option").length > 1 ) {
			$("#delivery_method_select").bind("change", function(){ paypalfunc.deliveryMethodSelect(); });
		}
		$("#delivery_date_select").bind("change", function(){ paypalfunc.deliveryDateSelect(); });
		$("#delivery_time_select").bind("change", function(){ paypalfunc.deliveryTimeSelect(); });
		if( $("#paypal_use_point") != undefined ) {
			$("#paypal_use_point").bind("click", function(){ paypalfunc.usePoint(); });
		}
	});
	</script>
	<div class="send"><input type="image" src="https://www.paypal.com/'.( USCES_JP ? 'ja_JP/JP' : 'en_US' ).'/i/btn/btn_xpressCheckout.gif" border="0" class="paypal_button" id="paypal_button" alt="PayPal" /></div>
	<div id="paypal_dialog">
		<div id="paypal_confirm">'.usces_paypal_confirm_form().'</div>
		<div id="paypal_shipping">'.usces_paypal_shipping_form().'</div>
		<div id="paypal_point">'.usces_paypal_point_form( $member['point'] ).'</div>
		<div id="paypal_purchase">'.usces_paypal_purchase_form().'</div>
		<div class="send"><input name="paypal_close" type="button" id="paypal_close" class="back_to_delivery_button" value="'.__('Cancel', 'usces').'" /></div>
	</div>';

	return $html;
}

function usces_paypal_set_session( $member_id, $uscesid = NULL ) {
	global $usces, $wpdb;

	$member_table = $wpdb->prefix."usces_member";
	$query = $wpdb->prepare( "SELECT * FROM $member_table WHERE ID = %d", $member_id );
	$member = $wpdb->get_row( $query, ARRAY_A );
	if( empty($member) ) return false;

	$_SESSION['usces_member']['ID'] = $member['ID'];
	$_SESSION['usces_member']['mailaddress1'] = $member['mem_email'];
	$_SESSION['usces_member']['mailaddress2'] = $member['mem_email'];
	$_SESSION['usces_member']['point'] = $member['mem_point'];
	$_SESSION['usces_member']['name1'] = $member['mem_name1'];
	$_SESSION['usces_member']['name2'] = $member['mem_name2'];
	$_SESSION['usces_member']['name3'] = $member['mem_name3'];
	$_SESSION['usces_member']['name4'] = $member['mem_name4'];
	$_SESSION['usces_member']['zipcode'] = $member['mem_zip'];
	$_SESSION['usces_member']['pref'] = $member['mem_pref'];
	$_SESSION['usces_member']['address1'] = $member['mem_address1'];
	$_SESSION['usces_member']['address2'] = $member['mem_address2'];
	$_SESSION['usces_member']['address3'] = $member['mem_address3'];
	$_SESSION['usces_member']['tel'] = $member['mem_tel'];
	$_SESSION['usces_member']['fax'] = $member['mem_fax'];
	$_SESSION['usces_member']['delivery_flag'] = $member['mem_delivery_flag'];
	$_SESSION['usces_member']['delivery'] = ( !empty($member['mem_delivery']) ) ? unserialize($member['mem_delivery']) : '';
	$_SESSION['usces_member']['registered'] = $member['mem_registered'];
	$_SESSION['usces_member']['nicename'] = $member['mem_nicename'];
	$_SESSION['usces_member']['country'] = $usces->get_member_meta_value( 'customer_country', $member['ID'] );
	$_SESSION['usces_member']['status'] = $member['mem_status'];
	$usces->set_session_custom_member( $member['ID'] );

	foreach( $_SESSION['usces_member'] as $key => $value ) {
		if( 'custom_member' == $key ) {
			foreach( $_SESSION['usces_member']['custom_member'] as $mbkey => $mbvalue ) {
				//if( empty($_SESSION['usces_entry']['custom_customer'][$mbkey]) ) {
					if( is_array($mbvalue) ) {
						foreach( $mbvalue as $k => $v ) {
							$_SESSION['usces_entry']['custom_customer'][$mbkey][$v] = $v;
						}
					} else {
						$_SESSION['usces_entry']['custom_customer'][$mbkey] = $mbvalue;
					}
				//}
			}
		} else {
			if( 'country' == $key and empty( $value ) ) {
				$_SESSION['usces_entry']['customer'][$key] = usces_get_base_country();
			} else {
				$_SESSION['usces_entry']['customer'][$key] = trim( $value );
			}
		}
	}
	foreach( $_SESSION['usces_entry']['customer'] as $key => $value ) {
		if( 'country' == $key and empty( $value ) ) {
			$_SESSION['usces_entry']['delivery'][$key] = usces_get_base_country();
		} else {
			$_SESSION['usces_entry']['delivery'][$key] = trim( $value );
		}
	}

	return true;
}

function usces_paypal_confirm_form() {
	global $usces;

	$usces_entries = $usces->cart->get_entry();
	$html = '
			<table>
			<tr>
				<th>'.__('total items', 'usces').'</th>
				<td>'.usces_crform( $usces_entries['order']['total_items_price'], true, false, 'return', true ).'</td>
			</tr>';
	if( usces_is_member_system_point() && !empty($usces_entries['order']['usedpoint']) ) {
		$html .= '
			<tr>
				<th>'.__('Used points', 'usces').'</th>
				<td><span class="confirm_usedpoint">'.usces_crform( $usces_entries['order']['usedpoint'], false, false, 'return', true ).'</span></td>
			</tr>';
	}
	if( !empty($usces_entries['order']['discount']) ) {
		$html .= '
			<tr>
				<th>'.apply_filters( 'usces_confirm_discount_label', __('Campaign disnount', 'usces') ).'</th>
				<td>'.usces_crform( $usces_entries['order']['discount'], true, false, 'return', true ).'</td>
			</tr>';
	}
	$html .= '
			<tr>
				<th>'.__('Shipping', 'usces').'</th>
				<td>'.usces_crform( $usces_entries['order']['shipping_charge'], true, false, 'return', true ).'</td>
			</tr>';
	if( !empty($usces_entries['order']['cod_fee']) ) {
		$html .= '
			<tr>
				<th>'.apply_filters( 'usces_filter_cod_label', __('COD fee', 'usces') ).'</th>
				<td>'.usces_crform( $usces_entries['order']['cod_fee'], true, false, 'return', true ).'</td>
			</tr>';
	}
	if( !empty($usces_entries['order']['tax']) ) {
		$html .= '
			<tr>
				<th>'.__('consumption tax', 'usces').'</th>
				<td>'.usces_crform( $usces_entries['order']['tax'], true, false, 'return', true ).'</td>
			</tr>';
	}
	$html .= '
			<tr>
				<th>'.__('Total Amount', 'usces').'</th>
				<td>'.usces_crform( $usces_entries['order']['total_full_price'], true, false, 'return', true ).'</td>
			</tr>
			</table>';
	return $html;
}

function usces_paypal_shipping_form() {
	global $usces;

	$html = '';
	$cart = $usces->cart->get_cart();
	if( 'shipped' == $usces->getItemDivision( $cart[0]['post_id'] ) ) {
		$usces_entries = $usces->cart->get_entry();
		$html = '
			<div class="error_message" id="paypal_error_message_delivery_method"></div>
			<table>
			<tr>
				<th>'.__('shipping option', 'usces').'</th>
				<td>'.usces_the_delivery_method( $usces_entries['order']['delivery_method'], 'return' ).'</td>
			</tr>
			<tr>
				<th>'.__('Delivery date', 'usces').'</th>
				<td>'.usces_the_delivery_date( $usces_entries['order']['delivery_date'], 'return' ).'</td>
			</tr>
			<tr>
				<th>'.__('Delivery Time', 'usces').'</th>
				<td>'.usces_the_delivery_time( $usces_entries['order']['delivery_time'], 'return' ).'</td>
			</tr>
			</table>';
	}
	return $html;
}

function usces_paypal_point_form( $point ) {
	global $usces;

	$html = '';
	if( usces_is_member_system_point() ) {
		$usces_entries = $usces->cart->get_entry();
		$usedpoint = ( 0 < $usces_entries['order']['usedpoint'] ) ? $usces_entries['order']['usedpoint'] : '';
		$html = '
			<div class="error_message" id="paypal_error_message_use_point"></div>
			<table>
			<tr>
				<th>'.__('The current point', 'usces').'</th>
				<td><span class="point">'.$point.'</span>pt</td>
			</tr>
			<tr>
				<th>'.__('Points you are using here', 'usces').'</th>
				<td><input name="offer[usedpoint]" class="used_point" id="set_usedpoint" type="text" value="'.$usedpoint.'" />pt</td>
			</tr>
			<tr>
				<td colspan="2"><input name="use_point" type="button" class="use_point_button" id="paypal_use_point" value="'.__('Use the points', 'usces').'" /></td>
			</tr>
			</table>';
	}
	return $html;
}

function usces_paypal_purchase_form() {
	global $usces, $payments;

	$usces_entries = $usces->cart->get_entry();
	$acting_opts = $usces->options['acting_settings']['paypal'];
	$currency_code = $usces->get_currency_code();

	$payment_paypal = array();
	foreach( (array)$payments as $id => $payment ) {
		if( 'acting_paypal_ec' == $payment['settlement'] ) {
			$usces->cart->set_order_entry( array( 'payment_name' => $payment['name'] ) );
			$payment_paypal = $payment;
			break;
		}
	}
	$acting_flag = 'acting_paypal_ec';
	$rand = usces_rand();
	//$purchase_disabled = ( '' != $usces->error_message ) ? ' disabled="true"' : '';
	$purchase_disabled = '';

	$cart = $usces->cart->get_cart();
	$html = '
			<form id="purchase_form" action="'.USCES_CART_URL.'" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
				<input type="hidden" name="SOLUTIONTYPE" value="Sole">
				<input type="hidden" name="LANDINGPAGE" value="Billing">
				<input type="hidden" name="EMAIL" value="'.esc_attr( $usces_entries['customer']['mailaddress1'] ).'">
				<input type="hidden" name="PAYMENTREQUEST_0_CURRENCYCODE" value="'.$currency_code.'">';
			if( 'shipped' == $usces->getItemDivision( $cart[0]['post_id'] ) ) {
				$name = apply_filters( 'usces_filter_paypalec_shiptoname', esc_attr( $usces_entries['delivery']['name2'].' '.$usces_entries['delivery']['name1'] ) );
				$address2 = apply_filters( 'usces_filter_paypalec_shiptostreet', esc_attr( $usces_entries['delivery']['address2'] ) );
				$address3 = apply_filters( 'usces_filter_paypalec_shiptostreet2', esc_attr( $usces_entries['delivery']['address3'] ) );
				$address1 = apply_filters( 'usces_filter_paypalec_shiptocity', esc_attr( $usces_entries['delivery']['address1'] ) );
				$pref = apply_filters( 'usces_filter_paypalec_shiptostate', esc_attr( $usces_entries['delivery']['pref'] ) );
				$country = ( !empty($usces_entries['delivery']['country']) ) ? $usces_entries['delivery']['country'] : usces_get_base_country();
				$country_code = apply_filters( 'usces_filter_paypalec_shiptocountrycode', $country );
				$zip = apply_filters( 'usces_filter_paypalec_shiptozip', $usces_entries['delivery']['zipcode'] );
				$tel = apply_filters( 'usces_filter_paypalec_shiptophonenum', ltrim( str_replace( '-', '', $usces_entries['delivery']['tel'] ), '0' ) );
				$html .= '
				<input type="hidden" name="PAYMENTREQUEST_0_SHIPTONAME" value="'.$name.'">
				<input type="hidden" name="PAYMENTREQUEST_0_SHIPTOSTREET" value="'.$address2.'">
				<input type="hidden" name="PAYMENTREQUEST_0_SHIPTOSTREET2" value="'.$address3.'">
				<input type="hidden" name="PAYMENTREQUEST_0_SHIPTOCITY" value="'.$address1.'">
				<input type="hidden" name="PAYMENTREQUEST_0_SHIPTOSTATE" value="'.$pref.'">
				<input type="hidden" name="PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE" value="'.$country_code.'">
				<input type="hidden" name="PAYMENTREQUEST_0_SHIPTOZIP" value="'.$zip.'">
				<input type="hidden" name="PAYMENTREQUEST_0_SHIPTOPHONENUM" value="'.$tel.'">';
			}
			if( 'shipped' != $usces->getItemDivision( $cart[0]['post_id'] ) ) {
				$html .= '<input type="hidden" name="NOSHIPPING" value="1">';
			}
			$charging_type = $usces->getItemChargingType( $cart[0]['post_id'], $cart );
			if( 'continue' != $charging_type ) {
				//通常購入
				$item_total_price = 0;
				$i = 0;
				foreach( $cart as $cart_row ) {
					$html .= '
						<input type="hidden" name="L_PAYMENTREQUEST_0_NAME'.$i.'" value="'.esc_attr( $usces->getItemName($cart_row['post_id']) ).'">
						<input type="hidden" name="L_PAYMENTREQUEST_0_AMT'.$i.'" value="'.usces_crform( $cart_row['price'], false, false, 'return', false ) .'">
						<input type="hidden" name="L_PAYMENTREQUEST_0_NUMBER'.$i.'" value="'.esc_attr( $usces->getItemCode($cart_row['post_id']) ).'">
						<input type="hidden" name="L_PAYMENTREQUEST_0_QTY'.$i.'" value="'.esc_attr( $cart_row['quantity'] ).'">';
					$item_total_price += ( $cart_row['price'] * $cart_row['quantity'] );
					$i++;
				}
				if( !empty($usces_entries['order']['discount']) ) {
					$html .= '
						<input type="hidden" name="L_PAYMENTREQUEST_0_NAME'.$i.'" value="'.esc_attr( __('Campaign disnount', 'usces') ).'">
						<input type="hidden" name="L_PAYMENTREQUEST_0_AMT'.$i.'" value="'.usces_crform( $usces_entries['order']['discount'], false, false, 'return', false ).'">';
					$item_total_price += $usces_entries['order']['discount'];
					$i++;
				}
				if( !empty($usces_entries['order']['usedpoint']) ) {
					$html .= '
						<input type="hidden" name="L_PAYMENTREQUEST_0_NAME'.$i.'" value="'.esc_attr(__('Used points', 'usces')).'">
						<input type="hidden" name="L_PAYMENTREQUEST_0_AMT'.$i.'" value="'.usces_crform( $usces_entries['order']['usedpoint']*(-1), false, false, 'return', false ).'">';
					$item_total_price -= $usces_entries['order']['usedpoint'];
					$i++;
				}
				$html .= '
					<input type="hidden" name="PAYMENTREQUEST_0_ITEMAMT" value="'.usces_crform( $item_total_price, false, false, 'return', false ).'">
					<input type="hidden" name="PAYMENTREQUEST_0_SHIPPINGAMT" value="'.usces_crform( $usces_entries['order']['shipping_charge'], false, false, 'return', false ).'">
					<input type="hidden" name="PAYMENTREQUEST_0_AMT" value="'.usces_crform( $usces_entries['order']['total_full_price'], false, false, 'return', false ).'">
					';
				if( !empty($usces_entries['order']['cod_fee']) ) $html .= '<input type="hidden" name="PAYMENTREQUEST_0_HANDLINGAMT" value="'.usces_crform( $usces_entries['order']['cod_fee'], false, false, 'return', false ).'">';
				if( !empty($usces_entries['order']['tax']) ) $html .= '<input type="hidden" name="PAYMENTREQUEST_0_TAXAMT" value="'.usces_crform( $usces_entries['order']['tax'], false, false, 'return', false ).'">';
			} else {
				//定期支払い
				$desc = usces_make_agreement_description( $cart, $usces_entries['order']['total_full_price'] );
				$html .= '<input type="hidden" name="L_BILLINGTYPE0" value="RecurringPayments">
					<input type="hidden" name="L_BILLINGAGREEMENTDESCRIPTION0" value="'.esc_attr($desc).'">
					<input type="hidden" name="AMT" value="0">';
			}
			if( !empty($acting_opts['logoimg']) ) $html .= '<input type="hidden" name="LOGOIMG" value="'.esc_attr( $acting_opts['logoimg'] ).'">';
			if( !empty($acting_opts['cartbordercolor']) ) $html .= '<input type="hidden" name="CARTBORDERCOLOR" value="'.esc_attr( $acting_opts['cartbordercolor'] ).'">';
			$html .= '<input type="hidden" name="purchase" value="acting_paypal_ec">';
			$html .= '<input type="hidden" name="paypal_from_cart" value="1">';
			$html .= '<div class="send"><input type="image" src="https://www.paypal.com/'.( USCES_JP ? 'ja_JP/JP' : 'en_US' ).'/i/btn/btn_xpressCheckout.gif" border="0" name="submit" value="submit" alt="PayPal"'.apply_filters( 'usces_filter_confirm_nextbutton', NULL ).$purchase_disabled.' /></div>';
			$html = apply_filters( 'usces_filter_confirm_inform', $html, $payment_paypal, $acting_flag, $rand, $purchase_disabled );
			$html .= '</form>';
	return $html;
}

function usces_paypal_front_ajax() {
	switch( $_POST['usces_ajax_action'] ) {
	case 'paypal_delivery_method':
		usces_paypal_delivery_method( $_POST['selected'], $_POST['delivery_date'], $_POST['delivery_time'] );
		break;
	case 'paypal_use_point':
		usces_paypal_use_point( $_POST['usepoint'], $_POST['total_price'], $_POST['item_price'] );
		break;
	case 'paypal_delivery_date_select':
		usces_paypal_delivery_date_select( $_POST['selected'] );
		break;
	case 'paypal_delivery_time_select':
		usces_paypal_delivery_time_select( $_POST['selected'] );
		break;
	}
}

function usces_paypal_delivery_method( $delivery_method_select, $delivery_date, $delivery_time ) {
	global $usces;

	$usces_entries = $usces->cart->get_entry();
	$usces->cart->set_order_entry( array( 'delivery_method' => $delivery_method_select, 'delivery_date' => $delivery_date, 'delivery_time' => $delivery_time ) );
	$usces_entries['order']['delivery_method'] = $delivery_method_select;
	$usces->set_cart_fees( $member, $usces_entries );
	$res = "ok#usces#".usces_paypal_confirm_form()."#usces#".usces_paypal_purchase_form();
	die( $res );
}

function usces_paypal_use_point( $usepoint, $total_price, $item_price ) {
	global $usces;

	$res = '';
	$mes = '';
	$member = $usces->get_member();
	$usces_entries = $usces->cart->get_entry();

	if( WCUtils::is_blank( $usepoint ) || !preg_match( "/^[0-9]+$/", $usepoint ) || (int)$usepoint < 0 ) {
		$mes = __('Invalid value. Please enter in the numbers.', 'usces');
	} else {
		if( $usepoint > (int)$member['point'] ) {
			$mes = __('You have exceeded the maximum available.', 'usces')."max".(int)$member['point']."pt";
		} elseif( $usces->options['point_coverage'] && $usepoint > $total_price ) {
			$mes = __('You have exceeded the maximum available.', 'usces')."max".$total_price."pt";
		} elseif( !$usces->options['point_coverage'] && $usepoint > $item_price ) {
			$mes = __('You have exceeded the maximum available.', 'usces')."max".$item_price."pt";
		}
	}
	if( '' != $mes ) {
		$usces->cart->set_order_entry( array( 'usedpoint' => 0 ) );
		$res = "error#usces#".$mes;
	} else {
		$usces->cart->set_order_entry( array( 'usedpoint' => $usepoint ) );
		$usces_entries['order']['usedpoint'] = $usepoint;
		$usces->set_cart_fees( $member, $usces_entries );
		$res = "ok#usces#".usces_paypal_confirm_form()."#usces#".usces_paypal_purchase_form();
	}
	die( $res );
}

function usces_paypal_filter_paypal_ec_cancelurl( $cancelurl, $query ) {
	global $usces;
	$pos = strpos( $query, 'paypal_from_cart' );
	if( false !== $pos ) {
		$cancelurl = urlencode( USCES_CART_URL );
	}
	return $cancelurl;
}

function usces_paypal_delivery_date_select( $selected ) {
	global $usces;
	$usces->cart->set_order_entry( array( 'delivery_date' => $selected ) );
	die( "ok" );
}

function usces_paypal_delivery_time_select( $selected ) {
	global $usces;
	$usces->cart->set_order_entry( array( 'delivery_time' => $selected ) );
	die( "ok" );
}

function usces_paypal_action_customerinfo() {
	global $usces;
	if( $usces->is_member_logged_in() ) {
		$usces->cart->set_order_entry( array( 'payment_name' => '' ) );
	}
}

?>
