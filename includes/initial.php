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

//$shipping_charge_structure = array(
//					'1' => '通常料金',
//					'2' => '特別料金1',
//					'3' => '特別料金2'
//					);


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

$usces_secure_link = array('zeus');


update_option('usces_management_status',$management_status);
update_option('usces_zaiko_status',$zaiko_status);
update_option('usces_customer_status',$customer_status);
if( !get_option('usces_payment_structure') )
	update_option('usces_payment_structure',$payment_structure);
update_option('usces_display_mode',$display_mode);
update_option('usces_pref',$usces_pref);
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
//update_option('shipping_charge_structure',$shipping_charge_structure);



$usces_op = get_option('usces');

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

$usces_op['mail_default']['footer']['thankyou'] = "=============================================\r\n" . get_option('blogname') . "\r\n" . $usces_op['company_name'] . "\r\n" . __('zip code', 'usces') . " " . $usces_op['zip_code'] . "\r\n" . $usces_op['address1'] . "\r\n" . $usces_op['address2'] . "\r\n" . "TEL " . $usces_op['tel_number'] . "\r\n" . "FAX " . $usces_op['fax_number'] . "\r\n" . __('contact', 'usces') . " " . $usces_op['inquiry_mail'] . "\r\n" . get_option('home') . "\r\n" . "=============================================\r\n";
$usces_op['mail_default']['footer']['order'] = "=============================================\r\n" . get_option('blogname') . "\r\n" . $usces_op['company_name'] . "\r\n" . __('zip code', 'usces') . " " . $usces_op['zip_code'] . "\r\n" . $usces_op['address1'] . "\r\n" . $usces_op['address2'] . "\r\n" . "TEL " . $usces_op['tel_number'] . "\r\n" . "FAX " . $usces_op['fax_number'] . "\r\n" . __('contact', 'usces') . " " . $usces_op['inquiry_mail'] . "\r\n" . get_option('home') . "\r\n" . "=============================================\r\n";
$usces_op['mail_default']['footer']['inquiry'] = "=============================================\r\n" . get_option('blogname') . "\r\n" . $usces_op['company_name'] . "\r\n" . __('zip code', 'usces') . " " . $usces_op['zip_code'] . "\r\n" . $usces_op['address1'] . "\r\n" . $usces_op['address2'] . "\r\n" . "TEL " . $usces_op['tel_number'] . "\r\n" . "FAX " . $usces_op['fax_number'] . "\r\n" . __('contact', 'usces') . " " . $usces_op['inquiry_mail'] . "\r\n" . get_option('home') . "\r\n" . "=============================================\r\n";
$usces_op['mail_default']['footer']['returninq'] = "=============================================\r\n" . get_option('blogname') . "\r\n" . $usces_op['company_name'] . "\r\n" . __('zip code', 'usces') . " " . $usces_op['zip_code'] . "\r\n" . $usces_op['address1'] . "\r\n" . $usces_op['address2'] . "\r\n" . "TEL " . $usces_op['tel_number'] . "\r\n" . "FAX " . $usces_op['fax_number'] . "\r\n" . __('contact', 'usces') . " " . $usces_op['inquiry_mail'] . "\r\n" . get_option('home') . "\r\n" . "=============================================\r\n";
$usces_op['mail_default']['footer']['membercomp'] = "=============================================\r\n" . get_option('blogname') . "\r\n" . $usces_op['company_name'] . "\r\n" . __('zip code', 'usces') . " " . $usces_op['zip_code'] . "\r\n" . $usces_op['address1'] . "\r\n" . $usces_op['address2'] . "\r\n" . "TEL " . $usces_op['tel_number'] . "\r\n" . "FAX " . $usces_op['fax_number'] . "\r\n" . __('contact', 'usces') . " " . $usces_op['inquiry_mail'] . "\r\n" . get_option('home') . "\r\n" . "=============================================\r\n";
$usces_op['mail_default']['footer']['completionmail'] = "=============================================\r\n" . get_option('blogname') . "\r\n" . $usces_op['company_name'] . "\r\n" . __('zip code', 'usces') . " " . $usces_op['zip_code'] . "\r\n" . $usces_op['address1'] . "\r\n" . $usces_op['address2'] . "\r\n" . "TEL " . $usces_op['tel_number'] . "\r\n" . "FAX " . $usces_op['fax_number'] . "\r\n" . __('contact', 'usces') . " " . $usces_op['inquiry_mail'] . "\r\n" . get_option('home') . "\r\n" . "=============================================\r\n";
$usces_op['mail_default']['footer']['ordermail'] = "=============================================\r\n" . get_option('blogname') . "\r\n" . $usces_op['company_name'] . "\r\n" . __('zip code', 'usces') . " " . $usces_op['zip_code'] . "\r\n" . $usces_op['address1'] . "\r\n" . $usces_op['address2'] . "\r\n" . "TEL " . $usces_op['tel_number'] . "\r\n" . "FAX " . $usces_op['fax_number'] . "\r\n" . __('contact', 'usces') . " " . $usces_op['inquiry_mail'] . "\r\n" . get_option('home') . "\r\n" . "=============================================\r\n";
$usces_op['mail_default']['footer']['changemail'] = "=============================================\r\n" . get_option('blogname') . "\r\n" . $usces_op['company_name'] . "\r\n" . __('zip code', 'usces') . " " . $usces_op['zip_code'] . "\r\n" . $usces_op['address1'] . "\r\n" . $usces_op['address2'] . "\r\n" . "TEL " . $usces_op['tel_number'] . "\r\n" . "FAX " . $usces_op['fax_number'] . "\r\n" . __('contact', 'usces') . " " . $usces_op['inquiry_mail'] . "\r\n" . get_option('home') . "\r\n" . "=============================================\r\n";
$usces_op['mail_default']['footer']['receiptmail'] = "=============================================\r\n" . get_option('blogname') . "\r\n" . $usces_op['company_name'] . "\r\n" . __('zip code', 'usces') . " " . $usces_op['zip_code'] . "\r\n" . $usces_op['address1'] . "\r\n" . $usces_op['address2'] . "\r\n" . "TEL " . $usces_op['tel_number'] . "\r\n" . "FAX " . $usces_op['fax_number'] . "\r\n" . __('contact', 'usces') . " " . $usces_op['inquiry_mail'] . "\r\n" . get_option('home') . "\r\n" . "=============================================\r\n";
$usces_op['mail_default']['footer']['mitumorimail'] = "=============================================\r\n" . get_option('blogname') . "\r\n" . $usces_op['company_name'] . "\r\n" . __('zip code', 'usces') . " " . $usces_op['zip_code'] . "\r\n" . $usces_op['address1'] . "\r\n" . $usces_op['address2'] . "\r\n" . "TEL " . $usces_op['tel_number'] . "\r\n" . "FAX " . $usces_op['fax_number'] . "\r\n" . __('contact', 'usces') . " " . $usces_op['inquiry_mail'] . "\r\n" . get_option('home') . "\r\n" . "=============================================\r\n";
$usces_op['mail_default']['footer']['cancelmail'] = "=============================================\r\n" . get_option('blogname') . "\r\n" . $usces_op['company_name'] . "\r\n" . __('zip code', 'usces') . " " . $usces_op['zip_code'] . "\r\n" . $usces_op['address1'] . "\r\n" . $usces_op['address2'] . "\r\n" . "TEL " . $usces_op['tel_number'] . "\r\n" . "FAX " . $usces_op['fax_number'] . "\r\n" . __('contact', 'usces') . " " . $usces_op['inquiry_mail'] . "\r\n" . get_option('home') . "\r\n" . "=============================================\r\n";
$usces_op['mail_default']['footer']['othermail'] = "=============================================\r\n" . get_option('blogname') . "\r\n" . $usces_op['company_name'] . "\r\n" . __('zip code', 'usces') . " " . $usces_op['zip_code'] . "\r\n" . $usces_op['address1'] . "\r\n" . $usces_op['address2'] . "\r\n" . "TEL " . $usces_op['tel_number'] . "\r\n" . "FAX " . $usces_op['fax_number'] . "\r\n" . __('contact', 'usces') . " " . $usces_op['inquiry_mail'] . "\r\n" . get_option('home') . "\r\n" . "=============================================\r\n";


update_option('usces', $usces_op);


?>
