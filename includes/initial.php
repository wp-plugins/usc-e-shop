<?php
$zaiko_status = array(
					'0' => __('In Stock', 'usces'),
					'1' => __('A Few Stock', 'usces'),
					'2' => __('Sold Out', 'usces'),
					'3' => __('Out Of Stock', 'usces'),
					'4' => __('Out of print', 'usces')
					);
$management_status = array(
					'estimate' => __('An estimate', 'usces'),
					'adminorder' => __('Management of Note', 'usces'),
					'noreceipt' => __('unpaid', 'usces'),
					'receipted' => __('payment confirmed', 'usces'),
					'duringorder' => __('temporaly out of stock', 'usces'),
					'cancel' => __('Cancel', 'usces'),
					'completion' => __('It has sent it out.', 'usces'),
					'pending' => __('Pending', 'usces'),
//					'continuation' => __('Continuation', 'usces'),
//					'termination' => __('Termination', 'usces'),
					);

$customer_status = array(
					'0' => __('notmal member', 'usces'),
					'1' => __('good member', 'usces'),
					'2' => __('VIP member', 'usces'),
					'99' => __('bad member', 'usces')
					);

$payment_structure = array(
					'acting' => __('The representation supplier settlement', 'usces'),
					'transferAdvance' => __('Transfer (prepayment)', 'usces'),
					'transferDeferred' => __('Transfer (postpay)', 'usces'),
					'COD' => __('COD', 'usces'),
					'installment' => __('Payment in installments', 'usces')
					);

$display_mode = array(
					'Usualsale' => __('Normal business', 'usces'),
					'Promotionsale' => __('During the campaign', 'usces'),
					'Maintenancemode' => __('Under Maintenance', 'usces')
					);

$shipping_rule = array(
					'0' => __('-- Select --', 'usces'),
					'1' => __('immediately', 'usces'),
					'2' => __('1-2 days', 'usces'),
					'3' => __('2-3days', 'usces'),
					'4' => __('3-5days', 'usces'),
					'5' => __('4-6days', 'usces'),
					'6' => __('about 1 week later', 'usces'),
					'7' => __('about 2 weeks later', 'usces'),
					'8' => __('about 3 weeks later', 'usces'),
					'9' => __('after we get new items', 'usces')
					);
					
$shipping_indication = array(0, 0, 2, 3, 5, 6, 7, 14, 21, 0);

//20100914ysk start
//$item_option_select = array(__('Single-select','usces'), __('Multi-select','usces'), __('Text','usces'));
$item_option_select = array(
					'0' => __('Single-select', 'usces'),
					'1' => __('Multi-select', 'usces'),
					'2' => __('Text', 'usces'),
					'5' => __('Text-area', 'usces')
					);
//20100914ysk end

//20100818ysk start
//20100809ysk start
$custom_order_select = array(
					'0' => __('Single-select', 'usces'),
					'2' => __('Text', 'usces'),
					'3' => __('Radio-button', 'usces'),
					'4' => __('Check-box', 'usces')
					);
//20100809ysk end
$custom_customer_select = array(
					'0' => __('Single-select', 'usces'),
					'2' => __('Text', 'usces'),
					'3' => __('Radio-button', 'usces'),
					'4' => __('Check-box', 'usces')
					);
$custom_delivery_select = array(
					'0' => __('Single-select', 'usces'),
					'2' => __('Text', 'usces'),
					'3' => __('Radio-button', 'usces'),
					'4' => __('Check-box', 'usces')
					);
$custom_member_select = array(
					'0' => __('Single-select', 'usces'),
					'2' => __('Text', 'usces'),
					'3' => __('Radio-button', 'usces'),
					'4' => __('Check-box', 'usces')
					);
$custom_field_position_select = array(
					'name_pre' => __('Previous the name', 'usces'),
					'name_after' => __('After the name', 'usces'),
					'fax_after' => __('After the fax', 'usces')
					);
					
//20100818ysk end
//20110331ysk start
/*
$province_ja = array(__('-- Select --', 'usces'),"北海道","青森県","岩手県","宮城県","秋田県","山形県","福島県","茨城県",
				"栃木県","群馬県","埼玉県","千葉県","東京都","神奈川県","新潟県","富山県","石川県",
				"福井県","山梨県","長野県","岐阜県","静岡県","愛知県","三重県","滋賀県","京都府",
				"大阪府","兵庫県","奈良県","和歌山県","鳥取県","島根県","岡山県","広島県","山口県",
				"徳島県","香川県","愛媛県","高知県","福岡県","佐賀県","長崎県","熊本県","大分県",
				"宮崎県","鹿児島県","沖縄県");
$province_en = array(__('-- Select --', 'usces'),"Alabama","Alaska","Arizona","Arkansas","California","Colorado",
				"Connecticut","Delaware","District of Columbia","Florida","Georgia","Hawaii",
				"Idaho","Illinois","Indiana","Iowa","Kansas","Kentucky","Louisiana","Maine",
				"Maryland","Massachusetts","Michigan","Minnesota","Mississippi","Missouri",
				"Montana","Nebraska","Nevada","New Hampshire","New Jersey","New Mexico","New York",
				"North Carolina","North Dakota","Ohio","Oklahoma","Oregon","Pennsylvania",
				"Rhode Island","South Carolina","South Dakota","Tennessee","Texas","Utah","Vermont",
				"Virginia","Washington","West Virginia","Wisconsin","Wyoming");
$usces_pref = ( 'ja' == get_locale() ) ? $province_ja : $province_en;
*/
//20110331ysk end

$usces_secure_link = array('zeus');


update_option('usces_management_status',$management_status);
update_option('usces_zaiko_status',$zaiko_status);
update_option('usces_customer_status',$customer_status);
if( !get_option('usces_payment_structure') )
	update_option('usces_payment_structure',$payment_structure);
update_option('usces_display_mode',$display_mode);
//20110331ysk start
//update_option('usces_pref',$usces_pref);
//20110331ysk end
update_option('usces_shipping_rule',$shipping_rule);
update_option('usces_item_option_select',$item_option_select);
//20100809ysk start
update_option('usces_custom_order_select', $custom_order_select);
//20100809ysk end
//20100818ysk start
update_option('usces_custom_customer_select', $custom_customer_select);
update_option('usces_custom_delivery_select', $custom_delivery_select);
update_option('usces_custom_member_select', $custom_member_select);
update_option('usces_custom_field_position_select', $custom_field_position_select);
//20100818ysk end

update_option('usces_currency_symbol',__('$', 'usces'));
update_option('usces_secure_link', $usces_secure_link);
if(!get_option('usces_wcid'))
	update_option('usces_wcid', md5(uniqid(rand(),1)));

update_option('usces_secure_link', $usces_secure_link);
update_option('usces_secure_link', $usces_secure_link);

$usces_op = get_option('usces');
if( !is_array($usces_op) || empty($usces_op) ){
	$usces_op = array();
}
$uop_init['company_name'] = isset($usces_op['company_name']) ? $usces_op['company_name'] : '';
$uop_init['zip_code'] = isset($usces_op['zip_code']) ? $usces_op['zip_code'] : '';
$uop_init['address1'] = isset($usces_op['address1']) ? $usces_op['address1'] : '';
$uop_init['address2'] = isset($usces_op['address2']) ? $usces_op['address2'] : '';
$uop_init['tel_number'] = isset($usces_op['tel_number']) ? $usces_op['tel_number'] : '';
$uop_init['fax_number'] = isset($usces_op['fax_number']) ? $usces_op['fax_number'] : '';
$uop_init['inquiry_mail'] = isset($usces_op['inquiry_mail']) ? $usces_op['inquiry_mail'] : '';

$usces_op['mail_default']['title']['thankyou'] = __('Confirmation of order details', 'usces');
$usces_op['mail_default']['title']['order'] = __('An order report', 'usces');
$usces_op['mail_default']['title']['inquiry'] = __('Your question is sent', 'usces');
$usces_op['mail_default']['title']['returninq'] = __('About your question', 'usces');
$usces_op['mail_default']['title']['membercomp'] = __('Comfirmation of your registration for membership', 'usces');
$usces_op['mail_default']['title']['completionmail'] = __('Information for shipping of your ordered items', 'usces');
$usces_op['mail_default']['title']['ordermail'] = __('Confirmation of order details', 'usces');
$usces_op['mail_default']['title']['changemail'] = __('Confirmation of change for your order details', 'usces');
$usces_op['mail_default']['title']['receiptmail'] = __('Confirmation mail for your transfer', 'usces');
$usces_op['mail_default']['title']['mitumorimail'] = __('Estimate', 'usces');
$usces_op['mail_default']['title']['cancelmail'] = __('Confirmatin of your cancellation', 'usces');
$usces_op['mail_default']['title']['othermail'] = "[]";

$usces_op['mail_default']['header']['thankyou'] = sprintf(__('Thank you for choosing %s.', 'usces'), get_option('blogname')) . "\r\n";
$usces_op['mail_default']['header']['thankyou'] .= __("We have received your order. Please check following information.", 'usces') . "\r\n\r\n";
$usces_op['mail_default']['header']['thankyou'] .= __("We will inform you by e-mail when we are ready to ship ordered items to you.", 'usces') . "\r\n\r\n";
$usces_op['mail_default']['header']['order'] = sprintf(__('There is new order by %s.', 'usces'), get_option('blogname')) . "\r\n";
$usces_op['mail_default']['header']['inquiry'] = sprintf(__('Thank you for choosing %s.', 'usces'), get_option('blogname')) . "\r\n";
$usces_op['mail_default']['header']['inquiry'] .= __("We have received following e-mail.", 'usces') . "\r\n\r\n";
$usces_op['mail_default']['header']['inquiry'] .= __("We will contact you by e-mail soon.", 'usces') . "\r\n\r\n";
$usces_op['mail_default']['header']['returninq'] = "";
$usces_op['mail_default']['header']['membercomp'] = sprintf(__('Than you for registrating as %s membership.', 'usces'), get_option('blogname')) . "\r\n\r\n";
$usces_op['mail_default']['header']['membercomp'] .= __("You can chek your purchase status at section 'membership information'.", 'usces') . "\r\n\r\n";
$usces_op['mail_default']['header']['completionmail'] = __("Your ordered items have been sent today.", 'usces') . "\r\n\r\n";
$usces_op['mail_default']['header']['completionmail'] .= __("It will be delivered by company xxx in couple of days.", 'usces') . "\r\n\r\n";
$usces_op['mail_default']['header']['completionmail'] .= __("Please contact us in case you have any problems with receiving your items.", 'usces') . "\r\n\r\n";
$usces_op['mail_default']['header']['ordermail'] = sprintf(__('Thank you for choosing %s.', 'usces'), get_option('blogname')) . "\r\n";
$usces_op['mail_default']['header']['ordermail'] .= __("We have received your order. Please check following information.", 'usces') . "\r\n\r\n";
$usces_op['mail_default']['header']['ordermail'] .= __("We will inform you by e-mail when we are ready to ship ordered items to you.", 'usces') . "\r\n\r\n";
$usces_op['mail_default']['header']['changemail'] = sprintf(__('Thank you for choosing %s.', 'usces'), get_option('blogname')) . "\r\n";
$usces_op['mail_default']['header']['changemail'] .= __("You have changed your order as following.", 'usces') . "\r\n\r\n";
$usces_op['mail_default']['header']['changemail'] .= __("We will inform you by e-mail when we are ready to send your items.", 'usces') . "\r\n\r\n";
$usces_op['mail_default']['header']['receiptmail'] =  sprintf(__('Thank you for choosing %s.', 'usces'), get_option('blogname')) . "\r\n";
$usces_op['mail_default']['header']['receiptmail'] .= __("Your transfer have been made successfully.", 'usces') . "\r\n\r\n";
$usces_op['mail_default']['header']['receiptmail'] .= __("We will inform you by e-mail when we are ready to send your items.", 'usces') . "\r\n\r\n";
$usces_op['mail_default']['header']['mitumorimail'] = sprintf(__('Thank you for choosing %s.', 'usces'), get_option('blogname')) . "\r\n";
$usces_op['mail_default']['header']['mitumorimail'] .= __("We will send you following estimate for your items.", 'usces') . "\r\n\r\n";
$usces_op['mail_default']['header']['mitumorimail'] .= __("This estimate is valid for one week.", 'usces') . "\r\n\r\n";
$usces_op['mail_default']['header']['cancelmail'] = sprintf(__('Thank you for choosing %s.', 'usces'), get_option('blogname')) . "\r\n";
$usces_op['mail_default']['header']['cancelmail'] .= __("We have received your cancellationfor your order.", 'usces') . "\r\n\r\n";
$usces_op['mail_default']['header']['othermail'] = sprintf(__('Thank you for choosing %s.', 'usces'), get_option('blogname')) . "\r\n\r\n";

$usces_op['mail_default']['footer']['thankyou'] = "=============================================\r\n" . get_option('blogname') . "\r\n" . $uop_init['company_name'] . "\r\n" . __('zip code', 'usces') . " " . $uop_init['zip_code'] . "\r\n" . $uop_init['address1'] . "\r\n" . $uop_init['address2'] . "\r\n" . "TEL " . $uop_init['tel_number'] . "\r\n" . "FAX " . $uop_init['fax_number'] . "\r\n" . __('contact', 'usces') . " " . $uop_init['inquiry_mail'] . "\r\n" . get_option('home') . "\r\n" . "=============================================\r\n";
$usces_op['mail_default']['footer']['order'] = "=============================================\r\n" . get_option('blogname') . "\r\n" . $uop_init['company_name'] . "\r\n" . __('zip code', 'usces') . " " . $uop_init['zip_code'] . "\r\n" . $uop_init['address1'] . "\r\n" . $uop_init['address2'] . "\r\n" . "TEL " . $uop_init['tel_number'] . "\r\n" . "FAX " . $uop_init['fax_number'] . "\r\n" . __('contact', 'usces') . " " . $uop_init['inquiry_mail'] . "\r\n" . get_option('home') . "\r\n" . "=============================================\r\n";
$usces_op['mail_default']['footer']['inquiry'] = "=============================================\r\n" . get_option('blogname') . "\r\n" . $uop_init['company_name'] . "\r\n" . __('zip code', 'usces') . " " . $uop_init['zip_code'] . "\r\n" . $uop_init['address1'] . "\r\n" . $uop_init['address2'] . "\r\n" . "TEL " . $uop_init['tel_number'] . "\r\n" . "FAX " . $uop_init['fax_number'] . "\r\n" . __('contact', 'usces') . " " . $uop_init['inquiry_mail'] . "\r\n" . get_option('home') . "\r\n" . "=============================================\r\n";
$usces_op['mail_default']['footer']['returninq'] = "=============================================\r\n" . get_option('blogname') . "\r\n" . $uop_init['company_name'] . "\r\n" . __('zip code', 'usces') . " " . $uop_init['zip_code'] . "\r\n" . $uop_init['address1'] . "\r\n" . $uop_init['address2'] . "\r\n" . "TEL " . $uop_init['tel_number'] . "\r\n" . "FAX " . $uop_init['fax_number'] . "\r\n" . __('contact', 'usces') . " " . $uop_init['inquiry_mail'] . "\r\n" . get_option('home') . "\r\n" . "=============================================\r\n";
$usces_op['mail_default']['footer']['membercomp'] = "=============================================\r\n" . get_option('blogname') . "\r\n" . $uop_init['company_name'] . "\r\n" . __('zip code', 'usces') . " " . $uop_init['zip_code'] . "\r\n" . $uop_init['address1'] . "\r\n" . $uop_init['address2'] . "\r\n" . "TEL " . $uop_init['tel_number'] . "\r\n" . "FAX " . $uop_init['fax_number'] . "\r\n" . __('contact', 'usces') . " " . $uop_init['inquiry_mail'] . "\r\n" . get_option('home') . "\r\n" . "=============================================\r\n";
$usces_op['mail_default']['footer']['completionmail'] = "=============================================\r\n" . get_option('blogname') . "\r\n" . $uop_init['company_name'] . "\r\n" . __('zip code', 'usces') . " " . $uop_init['zip_code'] . "\r\n" . $uop_init['address1'] . "\r\n" . $uop_init['address2'] . "\r\n" . "TEL " . $uop_init['tel_number'] . "\r\n" . "FAX " . $uop_init['fax_number'] . "\r\n" . __('contact', 'usces') . " " . $uop_init['inquiry_mail'] . "\r\n" . get_option('home') . "\r\n" . "=============================================\r\n";
$usces_op['mail_default']['footer']['ordermail'] = "=============================================\r\n" . get_option('blogname') . "\r\n" . $uop_init['company_name'] . "\r\n" . __('zip code', 'usces') . " " . $uop_init['zip_code'] . "\r\n" . $uop_init['address1'] . "\r\n" . $uop_init['address2'] . "\r\n" . "TEL " . $uop_init['tel_number'] . "\r\n" . "FAX " . $uop_init['fax_number'] . "\r\n" . __('contact', 'usces') . " " . $uop_init['inquiry_mail'] . "\r\n" . get_option('home') . "\r\n" . "=============================================\r\n";
$usces_op['mail_default']['footer']['changemail'] = "=============================================\r\n" . get_option('blogname') . "\r\n" . $uop_init['company_name'] . "\r\n" . __('zip code', 'usces') . " " . $uop_init['zip_code'] . "\r\n" . $uop_init['address1'] . "\r\n" . $uop_init['address2'] . "\r\n" . "TEL " . $uop_init['tel_number'] . "\r\n" . "FAX " . $uop_init['fax_number'] . "\r\n" . __('contact', 'usces') . " " . $uop_init['inquiry_mail'] . "\r\n" . get_option('home') . "\r\n" . "=============================================\r\n";
$usces_op['mail_default']['footer']['receiptmail'] = "=============================================\r\n" . get_option('blogname') . "\r\n" . $uop_init['company_name'] . "\r\n" . __('zip code', 'usces') . " " . $uop_init['zip_code'] . "\r\n" . $uop_init['address1'] . "\r\n" . $uop_init['address2'] . "\r\n" . "TEL " . $uop_init['tel_number'] . "\r\n" . "FAX " . $uop_init['fax_number'] . "\r\n" . __('contact', 'usces') . " " . $uop_init['inquiry_mail'] . "\r\n" . get_option('home') . "\r\n" . "=============================================\r\n";
$usces_op['mail_default']['footer']['mitumorimail'] = "=============================================\r\n" . get_option('blogname') . "\r\n" . $uop_init['company_name'] . "\r\n" . __('zip code', 'usces') . " " . $uop_init['zip_code'] . "\r\n" . $uop_init['address1'] . "\r\n" . $uop_init['address2'] . "\r\n" . "TEL " . $uop_init['tel_number'] . "\r\n" . "FAX " . $uop_init['fax_number'] . "\r\n" . __('contact', 'usces') . " " . $uop_init['inquiry_mail'] . "\r\n" . get_option('home') . "\r\n" . "=============================================\r\n";
$usces_op['mail_default']['footer']['cancelmail'] = "=============================================\r\n" . get_option('blogname') . "\r\n" . $uop_init['company_name'] . "\r\n" . __('zip code', 'usces') . " " . $uop_init['zip_code'] . "\r\n" . $uop_init['address1'] . "\r\n" . $uop_init['address2'] . "\r\n" . "TEL " . $uop_init['tel_number'] . "\r\n" . "FAX " . $uop_init['fax_number'] . "\r\n" . __('contact', 'usces') . " " . $uop_init['inquiry_mail'] . "\r\n" . get_option('home') . "\r\n" . "=============================================\r\n";
$usces_op['mail_default']['footer']['othermail'] = "=============================================\r\n" . get_option('blogname') . "\r\n" . $uop_init['company_name'] . "\r\n" . __('zip code', 'usces') . " " . $uop_init['zip_code'] . "\r\n" . $uop_init['address1'] . "\r\n" . $uop_init['address2'] . "\r\n" . "TEL " . $uop_init['tel_number'] . "\r\n" . "FAX " . $uop_init['fax_number'] . "\r\n" . __('contact', 'usces') . " " . $uop_init['inquiry_mail'] . "\r\n" . get_option('home') . "\r\n" . "=============================================\r\n";

$usces_op['usces_shipping_indication'] = $shipping_indication;

update_option('usces', $usces_op);

/************************************************************************/
/* usces_settings */
$usces_settings['language'] = array();
//20111117ysk 0000308
$usces_settings['currency'] = array(
					'AR' => array('ARS', 2, '.', ',', '$'),
					'AU' => array('AUD', 2, '.', ',', '$'),
					'AT' => array('EUR', 2, '.', ',', '&#x20AC;'),
					'BE' => array('EUR', 2, '.', ',', '&#x20AC;'),
					'BR' => array('BRL', 2, '.', ',', '$'),
					'CA' => array('CAD', 2, '.', ',', '$'),
					'CL' => array('CLP', 2, '.', ',', '$'),
					'CN' => array('CNY', 2, '.', ',', '&yen;'),
					'CR' => array('CRC', 2, '.', ',', '₡'),
					'CZ' => array('CZK', 2, '.', ',', 'Kč'),
					'DK' => array('DKK', 2, '.', ',', 'kr'),
					'DO' => array('DOP', 2, '.', ',', 'RD$'),
					'FI' => array('EUR', 2, '.', ',', '&#x20AC;'),
					'FR' => array('EUR', 2, '.', ',', '&#x20AC;'),
					'DE' => array('EUR', 2, '.', ',', '&#x20AC;'),
					'GR' => array('EUR', 2, '.', ',', '&#x20AC;'),
					'GT' => array('GTQ', 2, '.', ',', ''),
					'HK' => array('HKD', 2, '.', ',', '$'),
					'HU' => array('HUF', 2, '.', ',', ''),
					'IN' => array('INR', 2, '.', ',', '&#x20A8;'),
					'ID' => array('IDR', 2, '.', ',', 'Rp'),
					'IE' => array('EUR', 2, '.', ',', '&#x20AC;'),
					'IL' => array('ILS', 2, '.', ',', '&#x20AA;'),
					'IT' => array('EUR', 2, '.', ',', '&#x20AC;'),
					'JP' => array('JPY', 0, '.', ',', '&yen;'),
					'MO' => array('MOP', 2, '.', ',', '$'),
					'MY' => array('MYR', 2, '.', ',', 'RM'),
					'MX' => array('MXN', 2, '.', ',', '$'),
					'NL' => array('EUR', 2, '.', ',', '&#x20AC;'),
					'NZ' => array('NZD', 2, '.', ',', '$'),
					'NO' => array('NOK', 2, '.', ',', ''),
					'PA' => array('PAB', 2, '.', ',', ''),
					'PH' => array('PHP', 2, '.', ',', 'P'),
					'PL' => array('PLN', 2, '.', ',', ''),
					'PT' => array('EUR', 2, '.', ',', '&#x20AC;'),
					'PR' => array('USD', 2, '.', ',', '$'),
					'RO' => array('ROL', 2, '.', ',', 'L'),
					'RU' => array('RUR', 2, '.', ',', ''),
					'SG' => array('SGD', 2, '.', ',', '$'),
					'KR' => array('KRW', 0, '.', ',', '&#x20A9;'),
					'ES' => array('EUR', 2, '.', ',', '&#x20AC;'),
					'SW' => array('SEK', 2, '.', ',', ''),
					'CH' => array('CHF', 2, '.', ',', 'Fr.'),
					'TW' => array('NT$', 0, '.', ',', '元'),
					'TH' => array('THB', 2, '.', ',', '฿'),
					'TR' => array('TRL', 2, '.', ',', '₤'),
					'GB' => array('GBP', 2, '.', ',', '£'),
					'US' => array('USD', 2, '.', ',', '$'),
					'VN' => array('VND', 2, '.', ',', '₫'),
					'OO' => array('USD', 2, '.', ',', '$'),
					);
$usces_settings['nameform'] = array(
					'AR' => 1,
					'AU' => 1,
					'AT' => 1,
					'BE' => 1,
					'BR' => 1,
					'CA' => 1,
					'CL' => 1,
					'CN' => 0,
					'CR' => 1,
					'CZ' => 1,
					'DK' => 1,
					'DO' => 1,
					'FI' => 1,
					'FR' => 1,
					'DE' => 1,
					'GR' => 1,
					'GT' => 1,
					'HK' => 1,
					'HU' => 1,
					'IN' => 1,
					'ID' => 1,
					'IE' => 1,
					'IL' => 1,
					'IT' => 1,
					'JP' => 0,
					'MO' => 1,
					'MY' => 1,
					'MX' => 1,
					'NL' => 1,
					'NZ' => 1,
					'NO' => 1,
					'PA' => 1,
					'PH' => 1,
					'PL' => 1,
					'PT' => 1,
					'PR' => 1,
					'RO' => 1,
					'RU' => 1,
					'SG' => 1,
					'KR' => 1,
					'ES' => 1,
					'SW' => 1,
					'CH' => 1,
					'TW' => 0,
					'TH' => 1,
					'TR' => 1,
					'GB' => 1,
					'US' => 1,
					'VN' => 1,
					'OO' => 1,
					);
$usces_settings['addressform'] = array(
					'AR' => 'US',
					'AU' => 'US',
					'AT' => 'US',
					'BE' => 'US',
					'BR' => 'US',
					'CA' => 'US',
					'CL' => 'US',
					'CN' => 'CN',
					'CR' => 'US',
					'CZ' => 'US',
					'DK' => 'US',
					'DO' => 'US',
					'FI' => 'US',
					'FR' => 'US',
					'DE' => 'US',
					'GR' => 'US',
					'GT' => 'US',
					'HK' => 'US',
					'HU' => 'US',
					'IN' => 'US',
					'ID' => 'US',
					'IE' => 'US',
					'IL' => 'US',
					'IT' => 'US',
					'JP' => 'JP',
					'MO' => 'US',
					'MY' => 'US',
					'MX' => 'US',
					'NL' => 'US',
					'NZ' => 'US',
					'NO' => 'US',
					'PA' => 'US',
					'PH' => 'US',
					'PL' => 'US',
					'PT' => 'US',
					'PR' => 'US',
					'RO' => 'US',
					'RU' => 'US',
					'SG' => 'US',
					'KR' => 'US',
					'ES' => 'US',
					'SW' => 'US',
					'CH' => 'US',
					'TW' => 'JP',
					'TH' => 'US',
					'TR' => 'US',
					'GB' => 'US',
					'US' => 'US',
					'VN' => 'US',
					'OO' => 'US',
					);
$usces_settings['country'] = array(
					'AR' => __('Argentina', 'usces'),
					'AU' => __('Australia', 'usces'),
					'AT' => __('Austria', 'usces'),
					'BE' => __('Belgium', 'usces'),
					'BR' => __('Brazil', 'usces'),
					'CA' => __('Canada', 'usces'),
					'CL' => __('Chile', 'usces'),
					'CN' => __('China', 'usces'),
					'CR' => __('Costa Rica', 'usces'),
					'CZ' => __('Czech Republic', 'usces'),
					'DK' => __('Denmark', 'usces'),
					'DO' => __('Dominican Republic', 'usces'),
					'FI' => __('Finland', 'usces'),
					'FR' => __('France', 'usces'),
					'DE' => __('Germany', 'usces'),
					'GR' => __('Greece', 'usces'),
					'GT' => __('Guatemala', 'usces'),
					'HK' => __('Hong Kong', 'usces'),
					'HU' => __('Hungary', 'usces'),
					'IN' => __('India', 'usces'),
					'ID' => __('Indonesia', 'usces'),
					'IE' => __('Ireland', 'usces'),
					'IL' => __('Israel', 'usces'),
					'IT' => __('Italy', 'usces'),
					'JP' => __('Japan', 'usces'),
					'MO' => __('Macau', 'usces'),
					'MY' => __('Malaysia', 'usces'),
					'MX' => __('Mexico', 'usces'),
					'NL' => __('Netherlands', 'usces'),
					'NZ' => __('New Zealand', 'usces'),
					'NO' => __('Norway', 'usces'),
					'PA' => __('Panama', 'usces'),
					'PH' => __('Philippines', 'usces'),
					'PL' => __('Poland', 'usces'),
					'PT' => __('Portugal', 'usces'),
					'PR' => __('Puerto Rico', 'usces'),
					'RO' => __('Romania', 'usces'),
					'RU' => __('Russia', 'usces'),
					'SG' => __('Singapore', 'usces'),
					'KR' => __('South Korea', 'usces'),
					'ES' => __('Spain', 'usces'),
					'SW' => __('Sweden', 'usces'),
					'CH' => __('Switzerland', 'usces'),
					'TW' => __('Taiwan', 'usces'),
					'TH' => __('Thailand', 'usces'),
					'TR' => __('Turkey', 'usces'),
					'GB' => __('United Kingdom', 'usces'),
					'US' => __('United States', 'usces'),
					'VN' => __('Vietnam', 'usces'),
					'OO' => __('Other', 'usces'),
					);
$usces_settings['country_num'] = array(
					'AR' => '54',
					'AR' => '61',
					'AT' => '43',
					'BE' => '32',
					'BR' => '55',
					'CA' => '1',
					'CL' => '56',
					'CN' => '86',
					'CR' => '506',
					'CZ' => '420',
					'DK' => '45',
					'DO' => '1-809',
					'FI' => '358',
					'FR' => '33',
					'DE' => '49',
					'GR' => '30',
					'GT' => '502',
					'HK' => '852',
					'HU' => '36',
					'IN' => '91',
					'ID' => '62',
					'IE' => '353',
					'IL' => '972',
					'IT' => '39',
					'JP' => '81',
					'MO' => '853',
					'MY' => '60',
					'MX' => '52',
					'NL' => '31',
					'NZ' => '64',
					'NO' => '47',
					'PA' => '507',
					'PH' => '63',
					'PL' => '48',
					'PT' => '351',
					'PR' => '1-787',
					'RO' => '40',
					'RU' => '7',
					'SG' => '65',
					'KR' => '82',
					'ES' => '34',
					'SW' => '46',
					'CH' => '41',
					'TW' => '886',
					'TH' => '66',
					'TR' => '90',
					'GB' => '44',
					'US' => '1',
					'VN' => '84',
					'OO' => '1',
					);
$usces_settings['lungage2country'] = array(
					'es_AR' => 'AR',
					'en_AU' => 'AU',
					'de_AT' => 'AT',
					'nl_BE' => 'BE',
					'fr_BE' => 'BE',
					'pt_BR' => 'BR',
					'en_CA' => 'CA',
					'fr_CA' => 'CA',
					'es_CL' => 'CL',
					'zh_CN' => 'CN',
					'zh' => 'CN',
					'es_CR' => 'CR',
					'cs_CZ' => 'CZ',
					'cs' => 'CZ',
					'da' => 'DK',
					'da_DK' => 'DK',
					'es_DO' => 'DO',
					'fi_FI' => 'FI',
					'fi' => 'FI',
					'sv_FI' => 'FI',
					'fr' => 'FR',
					'fr_FR' => 'FR',
					'de' => 'DE',
					'de_DE' => 'DE',
					'el' => 'GR',
					'el_GR' => 'GR',
					'es_GT' => 'GT',
					'zh_HK' => 'HK',
					'en_HK' => 'HK',
					'hu_HU' => 'HU',
					'hu' => 'HU',
					'hi' => 'IN',
					'hi_IN' => 'IN',
					'id' => 'ID',
					'id_ID' => 'ID',
					'ga' => 'IE',
					'ga_IE' => 'IE',
					'en_IE' => 'IE',
					'he_IL' => 'IL',
					'ar_IL' => 'IL',
					'it' => 'IT',
					'it_IT' => 'IT',
					'ja' => 'JP',
					'ja_JP' => 'JP',
					'zh_MO' => 'MO',
					'pt_MO' => 'MO',
					'ms' => 'MY',
					'ms_MY' => 'MY',
					'es_MX' => 'MX',
					'nl' => 'NL',
					'nl_NL' => 'NL',
					'en_NZ' => 'NZ',
					'mi_NZ' => 'NZ',
					'mi' => 'NZ',
					'no' => 'NO',
					'no_NO' => 'NO',
					'es_PA' => 'PA',
					'tl' => 'PH',
					'tl_PH' => 'PH',
					'en_PH' => 'PH',
					'pl' => 'PL',
					'pl_PL' => 'PL',
					'pt' => 'PT',
					'pt_PT' => 'PT',
					'es_PR' => 'PR',
					'en_PR' => 'PR',
					'ro' => 'RO',
					'ro_RO' => 'RO',
					'ru' => 'RU',
					'ru_RU' => 'RU',
					'en_SG' => 'SG',
					'ms_SG' => 'SG',
					'zh_SG' => 'SG',
					'ko' => 'KR',
					'ko_KR' => 'KR',
					'es' => 'ES',
					'es_ES' => 'ES',
					'sv' => 'SW',
					'sv_SW' => 'SW',
					'de_CH' => 'CH',
					'fr_CH' => 'CH',
					'it_CH' => 'CH',
					'rm_CH' => 'CH',
					'rm' => 'CH',
					'zh_TW' => 'TW',
					'th' => 'TH',
					'th_TH' => 'TH',
					'tr' => 'TR',
					'tr_TR' => 'TR',
					'en' => 'GB',
					'en_GB' => 'GB',
					'' => 'US',
					'en_US' => 'US',
					'vi' => 'VN',
					'vi_VN' => 'VN',
					'zh_TW' => 'TW'
					);
$usces_states['JP'] = array(__('-- Select --', 'usces'),"北海道","青森県","岩手県","宮城県","秋田県","山形県","福島県","茨城県",
				"栃木県","群馬県","埼玉県","千葉県","東京都","神奈川県","新潟県","富山県","石川県",
				"福井県","山梨県","長野県","岐阜県","静岡県","愛知県","三重県","滋賀県","京都府",
				"大阪府","兵庫県","奈良県","和歌山県","鳥取県","島根県","岡山県","広島県","山口県",
				"徳島県","香川県","愛媛県","高知県","福岡県","佐賀県","長崎県","熊本県","大分県",
				"宮崎県","鹿児島県","沖縄県");
//20120123ysk 0000386
//$usces_states['US'] = array(__('-- Select --', 'usces'),"Alabama","Alaska","Arizona","Arkansas","California","Colorado",
$usces_states['US'] = array('-- Select --',"Alabama","Alaska","Arizona","Arkansas","California","Colorado",
				"Connecticut","Delaware","District of Columbia","Florida","Georgia","Hawaii",
				"Idaho","Illinois","Indiana","Iowa","Kansas","Kentucky","Louisiana","Maine",
				"Maryland","Massachusetts","Michigan","Minnesota","Mississippi","Missouri",
				"Montana","Nebraska","Nevada","New Hampshire","New Jersey","New Mexico","New York",
				"North Carolina","North Dakota","Ohio","Oklahoma","Oregon","Pennsylvania",
				"Rhode Island","South Carolina","South Dakota","Tennessee","Texas","Utah","Vermont",
				"Virginia","Washington","West Virginia","Wisconsin","Wyoming");
if( !get_option('usces_states') )
	update_option('usces_states',$usces_states);


$usces_essential_mark = array(
					'name1' => '<em>' . __('*', 'usces') . '</em>',
					'name2' => '',
					'name3' => '',
					'name4' => '',
					'zipcode' => '<em>' . __('*', 'usces') . '</em>',
					'country' => '<em>' . __('*', 'usces') . '</em>',
					'states' => '<em>' . __('*', 'usces') . '</em>',
					'address1' => '<em>' . __('*', 'usces') . '</em>',
					'address2' => '<em>' . __('*', 'usces') . '</em>',
					'address3' => '',
					'tel' => '<em>' . __('*', 'usces') . '</em>',
					'fax' => ''
					);
					
unset($uop_init);
?>
