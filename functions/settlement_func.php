<?php

add_action( 'usces_action_memberinfo_page_header', 'usces_action_settlement_memberinfo_page_header' );
add_filter( 'usces_filter_template_redirect', 'usces_filter_settlement_template_redirect' );
add_filter( 'usces_filter_delivery_secure_form_howpay', 'usces_filter_update_settlement_form_howpay' );
add_filter( 'usces_filter_available_payment_method', 'usces_filter_settlement_available_payment_method' );

function usces_filter_update_settlement_form_howpay( $html ) {
	if( isset($_GET['page'] ) and 'member_update_settlement' == $_GET['page'] ) {
		$html = '';
	}
	return $html;
}

function usces_filter_settlement_available_payment_method( $payments ) {
	global $usces;

	if( $usces->is_member_page($_SERVER['REQUEST_URI']) ) {
		$payment_method = array();
		foreach( (array)$payments as $id => $payment ) {
			if( 'acting_zeus_card' == $payment['settlement'] ) {
				$payment_method[$id] = $payments[$id];
				break;
			}
		}
		if( !empty($payment_method) ) $payments = $payment_method;
	}
	return $payments;
}

function usces_action_settlement_memberinfo_page_header() {
	global $usces;

	if( defined('WCEX_MOBILE') ) {
		global $wcmb;
		if( DOCOMO === $wcmb['device_div'] || SOFTBANK === $wcmb['device_div'] || KDDI === $wcmb['device_div'] ) return;
	}

	$html = '';
	$member = $usces->get_member();
	$pcid = $usces->get_member_meta_value( 'zeus_pcid', $member['ID'] );
	$partofcard = $usces->get_member_meta_value( 'zeus_partofcard', $member['ID'] );
	if( !empty($pcid) && !empty($partofcard) ) {
		$update_settlement_url = add_query_arg( array( 'page' => 'member_update_settlement', 're-enter' => 1 ), USCES_MEMBER_URL );
		$html .= '
		<div class="gotoedit">
		<a href="'.$update_settlement_url.'">'.__("Change the credit card is here >>", 'usces').'</a>
		</div>';
	}
	echo $html;
}

function usces_filter_settlement_template_redirect() {
	global $usces;

	if( $usces->is_member_page($_SERVER['REQUEST_URI']) ) {
		if( $usces->options['membersystem_state'] != 'activate' ) return;

		if( $usces->is_member_logged_in() and ( isset($_REQUEST['page']) and 'member_update_settlement' == $_REQUEST['page'] ) ) {
			$usces->page = 'member_update_settlement';
			usces_member_update_settlement_form();
			exit;
		}
	}
	return;
}

function usces_member_update_settlement_form() {
	global $usces;

	$member = $usces->get_member();
	$acting_opts = $usces->options['acting_settings']['zeus'];
	$script = '';
	$message = '';

	if( isset($_POST['update']) ) {
		$interface = parse_url( $acting_opts['card_url'] );
		$rand = sprintf( '%010d', mt_rand(1, 9999999999) );

		$vars = 'send=mall';
		$vars .= '&clientip='.$acting_opts['clientip'];
		$cardnumber = ( empty($_POST['cnum1']) ) ? $usces->get_member_meta_value( 'zeus_pcid', $member['ID'] ) : $_POST['cnum1'];
		$vars .= '&cardnumber='.$cardnumber;
		if( 1 == $usces->options['acting_settings']['zeus']['security'] ) {
			$vars .= '&seccode='.$_POST['securecode'];
		}
		$vars .= '&expyy='.substr( $_POST['expyy'], 2 );
		$vars .= '&expmm='.$_POST['expmm'];
		$vars .= '&telno='.str_replace( '-', '', $member['tel'] );
		$vars .= '&email='.$member['mailaddress1'];
		$vars .= '&sendid='.$member['ID'];
		if( !empty($_POST['username_card']) ) {
			$vars .= '&username='.$_POST['username_card'];
		}
		$vars .= '&money=0';
		$vars .= '&sendpoint='.$rand;
		$vars .= '&printord=';
		$vars .= '&return_value=yes';
		//if( isset($_POST['howpay']) && WCUtils::is_zero($_POST['howpay']) ) {
		//	$vars .= '&div='.$_POST['div'];
		//}

		$header = "POST ".$interface['path']." HTTP/1.1\r\n";
		$header .= "Host: ".$_SERVER['HTTP_HOST']."\r\n";
		$header .= "User-Agent: PHP Script\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: ".strlen( $vars )."\r\n";
		$header .= "Connection: close\r\n\r\n";
		$header .= $vars;
		$fp = fsockopen( 'ssl://'.$interface['host'], 443, $errno, $errstr, 30 );

		$err_code = '';
		$settltment_errmsg = '';

		if( $fp ) {
			$page = '';
			fwrite( $fp, $header );
			while( !feof( $fp ) ) {
				$scr = fgets( $fp, 1024 );
				$page .= $scr;
			}
			fclose( $fp );

			if( false !== strpos( $page, 'Success_order') ) {
				usces_log( 'zeus card : Settlement update', 'acting_transaction.log' );
				//$usces->error_message = __( 'The update was completed.', 'usces' );
				$usces->error_message = '';
				$message = __( 'The update was completed.', 'usces' );
				if( !empty($_POST['cnum1']) ) {
					$partofcard = substr( $_POST['cnum1'], -4 );
					$usces->set_member_meta_value( 'zeus_partofcard', $partofcard );
				}
				//$limitofcard = $_POST['expmm'].'/'.substr( $_POST['expyy'], -2 );
				$usces->set_member_meta_value( 'zeus_limitofcard', $_POST['expyy'].'/'.$_POST['expmm'] );
				usces_send_update_settlement_mail();
			} else {
				$err_code = usces_get_err_code( $page );
				usces_log( 'zeus card : Certification Error : '.$err_code, 'acting_transaction.log' );
				$usces->error_message = __( 'failure in update', 'usces' );
			}
		} else {
			usces_log( 'zeus card : Socket Error', 'acting_transaction.log' );
			$usces->error_message = __( 'failure in update', 'usces' );
		}
	}

	$partofcard = $usces->get_member_meta_value( 'zeus_partofcard', $member['ID'] );
	//if( 4 == strlen($partofcard) ) $_POST['cnum1'] = '************'.$partofcard;
	$limitofcard = $usces->get_member_meta_value( 'zeus_limitofcard', $member['ID'] );
	list( $expyy, $expmm ) = explode( '/', $limitofcard );
	if( is_numeric($expyy) ) $_POST['expyy'] = $expyy;
	if( is_numeric($expmm) ) $_POST['expmm'] = $expmm;

	$update_settlement_url = add_query_arg( array( 'page' => 'member_update_settlement', 'settlement' => 1, 're-enter' => 1 ), USCES_MEMBER_URL );
/*
	$script .= "
	<script type=\"text/javascript\">
		jQuery( function($) {
			$(\"input[name='howpay']\").change(function() {
				if( '' != $(\"select[name='cbrand'] option:selected\").val() ){
					$(\"#div_zeus\").css({\"display\": \"\"});
				}
				if( '1' == $(\"input[name='howpay']:checked\").val() ){
					$(\"#cbrand_zeus\").css({\"display\": \"none\"});
					$(\"#div_zeus\").css({\"display\": \"none\"});
				}else{
					$(\"#cbrand_zeus\").css({\"display\": \"\"});
				}
			});

			$(\"select[name='cbrand']\").change(function() {
				$(\"#div_zeus\").css({\"display\": \"\"});
				if( '1' == $(\"select[name='cbrand'] option:selected\").val() ){
					$(\"#brand1\").css({\"display\": \"\"});
					$(\"#brand2\").css({\"display\": \"none\"});
					$(\"#brand3\").css({\"display\": \"none\"});
				}else if( '2' == $(\"select[name='cbrand'] option:selected\").val() ){
					$(\"#brand1\").css({\"display\": \"none\"});
					$(\"#brand2\").css({\"display\": \"\"});
					$(\"#brand3\").css({\"display\": \"none\"});
				}else if( '3' == $(\"select[name='cbrand'] option:selected\").val() ){
					$(\"#brand1\").css({\"display\": \"none\"});
					$(\"#brand2\").css({\"display\": \"none\"});
					$(\"#brand3\").css({\"display\": \"\"});
				}else{
					$(\"#brand1\").css({\"display\": \"none\"});
					$(\"#brand2\").css({\"display\": \"none\"});
					$(\"#brand3\").css({\"display\": \"none\"});
				}
			});

			if( '1' == $(\"input[name='howpay']:checked\").val() ){
				$(\"#cbrand_zeus\").css({\"display\": \"none\"});
				$(\"#div_zeus\").css({\"display\": \"none\"});
			}else{
				$(\"#cbrand_zeus\").css({\"display\": \"\"});
				$(\"#div_zeus\").css({\"display\": \"\"});
			}

			if( '1' == $(\"select[name='cbrand'] option:selected\").val() ){
				$(\"#brand1\").css({\"display\": \"\"});
				$(\"#brand2\").css({\"display\": \"none\"});
				$(\"#brand3\").css({\"display\": \"none\"});
			}else if( '2' == $(\"select[name='cbrand'] option:selected\").val() ){
				$(\"#brand1\").css({\"display\": \"none\"});
				$(\"#brand2\").css({\"display\": \"\"});
				$(\"#brand3\").css({\"display\": \"none\"});
			}else if( '3' == $(\"select[name='cbrand'] option:selected\").val() ){
				$(\"#brand1\").css({\"display\": \"none\"});
				$(\"#brand2\").css({\"display\": \"none\"});
				$(\"#brand3\").css({\"display\": \"\"});
			}else{
				$(\"#brand1\").css({\"display\": \"none\"});
				$(\"#brand2\").css({\"display\": \"none\"});
				$(\"#brand3\").css({\"display\": \"none\"});
			}
		});
	</script>";
*/
	if( '' != $message ) {
	$script .= "
	<script type=\"text/javascript\">
		jQuery.event.add( window, 'load', function() {
			alert('".$message."');
		});
	</script>";
	}

	ob_start();
	get_header();
?>
<?php if( '' != $script ) echo $script; ?>
<div id="content" class="two-column">
<div class="catbox">

<?php if( have_posts() ) : usces_remove_filter(); ?>

<div class="post" id="wc_<?php usces_page_name(); ?>">

<h1 class="member_page_title"><?php _e('Credit card update', 'usces'); ?></h1>
<div class="entry">

<div id="memberpages">

<div class="whitebox">
	<div id="memberinfo">

	<div class="header_explanation">
	<?php do_action( 'usces_action_member_update_settlement_page_header' ); ?>
	</div>
	<p>※有効期限のみ変更する場合は、カード番号は空白にして更新してください。</p>

	<h3><?php _e('Credit card information', 'usces'); ?></h3>

	<div class="error_message"><?php usces_error_message(); ?></div>
	<form action="<?php echo $update_settlement_url; ?>" method="post" onKeyDown="if(event.keyCode == 13) {return false;}">
	<?php usces_delivery_secure_form(); ?>
	<div class="send">
	<input name="update" type="submit" value="<?php _e('update', 'usces'); ?>" />
	<input name="back" type="button" value="<?php _e('Back to the member page.', 'usces'); ?>" onclick="location.href='<?php echo USCES_MEMBER_URL; ?>'" />
	<input name="top" type="button" value="<?php _e('Back to the top page.', 'usces'); ?>" onclick="location.href='<?php echo home_url(); ?>'" />
	</div>
	<?php do_action( 'usces_action_member_update_settlement_page_inform' ); ?>
	</form>

	<div class="footer_explanation">
	<?php do_action( 'usces_action_member_update_settlement_page_footer' ); ?>
	</div>
	</div><!-- end of memberinfo -->
</div><!-- end of whitebox -->
</div><!-- end of memberpages -->

</div><!-- end of entry -->
</div><!-- end of post -->
<?php else: ?>
<p><?php _e('Sorry, no posts matched your criteria.'); ?></p>
<?php endif; ?>
</div><!-- end of catbox -->
</div><!-- end of content -->
<?php
	get_sidebar( 'cartmember' );

	get_footer();
	$r = ob_get_contents();
	ob_end_clean();

	echo $r;
}

function usces_send_update_settlement_mail() {
	global $usces;

	$member = $usces->get_member();
	$mail_data = $usces->options['mail_data'];

	$subject = apply_filters( 'usces_filter_send_update_settlement_mail_subject', __('Confirmation of credit card update', 'usces'), $member );
	$mail_header = __('Your credit card information has been updated.','usces')."\r\n\r\n";
	$mail_footer = $mail_data['footer']['thankyou'];
	$name = usces_localized_name( $member['name1'], $member['name2'], 'return' );

	$message = '--------------------------------'."\r\n";
	$message .= __('Member ID', 'usces').' : '.$member['ID']."\r\n";
	$message .= __('Name', 'usces').' : '.sprintf( __('Mr/Mrs %s', 'usces'), $name )."\r\n";
	$message .= __('e-mail adress', 'usces').' : '.$member['mailaddress1']."\r\n";
	$message .= '--------------------------------'."\r\n\r\n";
	$message .= __('If you have not requested this email, sorry to trouble you, but please contact us.', 'usces')."\r\n\r\n";
	$message = apply_filters( 'usces_filter_send_update_settlement_mail_message', $message, $member );
	$message = apply_filters( 'usces_filter_send_update_settlement_mail_message_head', $mail_header, $member ).$message.apply_filters( 'usces_filter_send_update_settlement_mail_message_foot', $mail_footer, $member )."\n";

	$send_para = array(
		'to_name' => sprintf( __('Mr/Mrs %s', 'usces'), $name ),
		'to_address' => $member['mailaddress1'],
		'from_name' => get_option('blogname'),
		'from_address' => $usces->options['sender_mail'],
		'return_path' => $usces->options['sender_mail'],
		'subject' => $subject,
		'message' => $message
	);
	usces_send_mail( $send_para );

	$admin_para = array(
		'to_name' => 'Shop Admin',
		'to_address' => $usces->options['order_mail'],
		'from_name' => 'Welcart Auto BCC',
		'from_address' => $usces->options['sender_mail'],
		'return_path' => $usces->options['sender_mail'],
		'subject' => $subject,
		'message' => $message
	);
	usces_send_mail( $admin_para );
}

?>