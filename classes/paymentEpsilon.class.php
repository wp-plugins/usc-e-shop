<?php
/*
Epsilon Settlement module
Version: 1.0.0
Author: Collne Inc.

*/
class EPSILON_SETTLEMENT
{
	private $error_mes, $pay_method;

	public function __construct() {

		$this->pay_method = array(
			'acting_epsilon_card',
			'acting_epsilon_conv'
		);

		$this->noreceipt_status();
		add_action( 'usces_after_cart_instant', array( $this, 'acting_transaction' ) );

		if( is_admin() ) {
			add_action( 'usces_action_settlement_tab_title', array( $this, 'tab_title' ) );
			add_action( 'usces_action_settlement_tab_body', array( $this, 'tab_body' ) );
			add_action( 'usces_action_settlement_script', array( $this, 'settlement_script' ) );
			add_action( 'usces_action_admin_settlement_update', array( $this, 'data_update' ) );
			add_action( 'usces_filter_settle_info_field_keys', array( $this, 'settlement_info_field_keys' ) );
			add_action( 'usces_action_revival_order_data', array( $this, 'revival_order_data' ), 10, 3 );

		} else {
			add_filter( 'usces_filter_confirm_inform', array( $this, 'confirm_inform' ), 10, 5 );
			add_action( 'usces_action_acting_processing', array( $this, 'acting_processing' ), 10, 2 );
			add_filter( 'usces_filter_completion_settlement_message', array( $this, 'completion_settlement_message' ), 10, 2 );
		}
	}

	/**********************************************
	* usces_filter_noreceipt_status
	* @param  $actings
	* @return array $actings
	***********************************************/
	public function noreceipt_status() {
		$noreceipt_status = get_option( 'usces_noreceipt_status' );
		$noreceipt_status[] = 'acting_epsilon_conv';
		update_option( 'usces_noreceipt_status', $noreceipt_status );
	}

	/**********************************************
	* usces_filter_completion_settlement_message
	* @param  $html, $usces_entries
	* @return str $html
	***********************************************/
	public function completion_settlement_message( $html, $usces_entries ) {
		if( isset($_REQUEST['acting']) && ( 'epsilon' == $_REQUEST['acting'] ) ) {
			$payments = usces_get_payments_by_name($usces_entries['order']['payment_name']);
			if( $payments['settlement'] == 'acting_epsilon_conv' ) {
				$html .= '<div id="status_table"><h5>イプシロン・コンビニ決済</h5>
					<p>「お支払いのご案内」は、'.esc_html($usces_entries['customer']['mailaddress1']).'　宛にメールさせていただいております。</p>
					</div>'."\n";
			}
		}
		return $html;
	}

	/**********************************************
	* Order purchase form
	* @param  $html, $payments, $acting_flg, $rand, $purchase_disabled
	* @return form str
	***********************************************/
	public function confirm_inform( $html, $payments, $acting_flg, $rand, $purchase_disabled ) {
		if( in_array($acting_flg, $this->pay_method) ) {
			$html = '<form id="purchase_form" action="'.USCES_CART_URL.'" method="post" onKeyDown="if(event.keyCode == 13){return false;}">
				<div class="send"><input name="backDelivery" type="submit" id="back_button" value="'.__('Back', 'usces').'"'.apply_filters('usces_filter_confirm_prebutton', NULL).' />
				<input name="purchase" type="submit" id="purchase_button" class="checkout_button" value="'.__('Checkout', 'usces').'"'.$purchase_disabled.' /></div>
				<input type="hidden" name="rand" value="'.$rand.'">
				<input type="hidden" name="_nonce" value="'.wp_create_nonce( $acting_flg ).'">'."\n";
		}
		return $html;
	}

	/**********************************************
	* Acting processing
	* @param  $acting_flg, $query
	* @return form str
	***********************************************/
	public function acting_processing( $acting_flg, $query ) {
		if( !in_array($acting_flg, $this->pay_method) )
			wp_redirect(USCES_CART_URL);

		if( !wp_verify_nonce( $_REQUEST['_nonce'], $acting_flg ) )
			wp_redirect(USCES_CART_URL);

		global $usces;
		$usces_entries = $usces->cart->get_entry();
		$cart = $usces->cart->get_cart();
		if( !$usces_entries || !$cart )
			wp_redirect(USCES_CART_URL);

		$delim = apply_filters( 'usces_filter_delim', $usces->delim );
		$acting_opts = $usces->options['acting_settings']['epsilon'];
		$rand = $_REQUEST['rand'];
		//$usces->save_order_acting_data($rand);
		usces_save_order_acting_data( $rand );
		$user_name = mb_strimwidth( $usces_entries['customer']['name1'].$usces_entries['customer']['name2'], 0, 64 );
		$item_code = mb_convert_kana( $usces->getItemCode($cart[0]['post_id']), 'a' );
		$item_name = $usces->getItemName($cart[0]['post_id']);
		if( 1 < count($cart) ) $item_name .= ' '.__('Others', 'usces');
		if( 32 < mb_strlen($item_name) ) $item_name = mb_strimwidth( $item_name, 0, 28 ).'...';

		switch( $acting_flg ) {
		case 'acting_epsilon_card'://クレジットカード決済
			if( 'on' == $acting_opts['multi_currency'] ) {
				$st_code = '10000-0000-00000-00001-00000-00000-00000';
				$currency_id = $usces->get_currency_code();
				$user_id = '-';
				$process_code = '1';
			} else {
				$st_code = '10000-0000-00000-00000-00000-00000-00000';
				$currency_id = '';
				if( 'on' == $acting_opts['process_code'] ) {
					$member = $usces->get_member();
					$user_id = ( !empty($member['ID']) ) ? $member['ID'] : '-';
					$process_code = ( $user_id == '-' ) ? '1' : '2';
				} else {
					$user_id = '-';
					$process_code = '1';
				}
			}
			break;
		case 'acting_epsilon_conv'://コンビニ決済
			$st_code = '00100-0000-00000-00000-00000-00000-00000';
			$user_id = '-';
			$currency_id = '';
			$process_code = '1';
			break;
		}

		$send_data = array(
			'version' => '2',
			'contract_code' => $acting_opts['contract_code'],
			'user_id' => $user_id,
			'user_name' => $user_name,
			'user_mail_add' => $usces_entries['customer']['mailaddress1'],
			'item_code' => $item_code,
			'item_name' => $item_name,
			'order_number' => $rand,
			'st_code' => $st_code,
			'mission_code' => '1',
			'item_price' => $usces_entries['order']['total_full_price'],
			'process_code' => $process_code,
			'memo1' => '',
			'memo2' => 'wc1collne',
			'xml' => '1',
			'character_code' => 'UTF8',
			'currency_id' => $currency_id
		);
		if( $acting_flg == 'acting_epsilon_conv' ) {
			$send_data['user_tel'] = str_replace( '-', '', mb_convert_kana($usces_entries['customer']['tel'], 'a', 'UTF-8') );
			$send_data['user_name_kana'] = $usces_entries['customer']['name3'].$usces_entries['customer']['name4'];
		}
usces_log("send_data=".print_r($send_data,true),"epsilon.log");
		$vars = http_build_query( $send_data );
		$host = parse_url( USCES_CART_URL );
		$interface = parse_url( $acting_opts['send_url'] );

		$request  = "POST ".$acting_opts['send_url']." HTTP/1.1\r\n";
		$request .= "Host: ".$host['host']."\r\n";
		$request .= "User-Agent: PHP Script\r\n";
		$request .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$request .= "Content-Length: ".strlen( $vars )."\r\n";
		$request .= "Connection: close\r\n\r\n";
		$request .= $vars;
		$fp = fsockopen( 'ssl://'.$interface['host'], 443 );

		if( $fp ) {
			fwrite( $fp, $request );
			while( !feof($fp) ) {
				$scr = fgets($fp, 1024);
				preg_match_all( "/<result\s(.*)\s\/>/", $scr, $match, PREG_SET_ORDER );
				if( !empty($match[0][1]) ) {
					list( $key, $value ) = explode( '=', $match[0][1] );
					$datas[$key] = urldecode(trim($value, '"'));
				}
			}
			fclose($fp);
			if( (int)$datas['result'] === 1 ) {
				header("location: ".$datas['redirect']);
			} else {
				usces_log('Epsilon : Certification Error'.print_r($datas,true), 'acting_transaction.log');
				$err_code = ( isset($datas['err_code']) ) ? urlencode($datas['err_code']) : '';
				$err_detail = ( isset($datas['err_detail']) ) ? urlencode($datas['err_detail']) : '';
				header("location: ".USCES_CART_URL.$delim."acting=epsilon&acting_return=0&err_code=".$err_code."&err_detail=".$err_detail);
			}
		} else {
			usces_log('Epsilon : Socket Error', 'acting_transaction.log');
			header("location: ".USCES_CART_URL.$delim."acting=epsilon&acting_return=0");
		}
		exit;
	}

	/**********************************************
	* Acting transaction
	* @param  -
	* @return -
	***********************************************/
	function acting_transaction() {
		global $wpdb;
		if( isset($_POST['trans_code']) && isset($_POST['user_id']) && isset($_POST['order_number']) ) {
			foreach( $_POST as $key => $value ){
				$data[$key] = mb_convert_encoding( $value, 'UTF-8', 'SJIS' );
			}
usces_log("acting_transaction=".print_r($data,true),"epsilon.log");
			if( $data['paid'] == '1' ) {
				$table_name = $wpdb->prefix."usces_order";
				$table_meta_name = $wpdb->prefix."usces_order_meta";
				$mquery = $wpdb->prepare( "SELECT order_id FROM $table_meta_name WHERE meta_key = %s AND meta_value = %s", 'settlement_id', $data['order_number'] );
				$order_id = $wpdb->get_var( $mquery );
				if( $order_id == NULL ) {
					usces_log( 'Epsilon conv error1 : '.print_r($data, true), 'acting_transaction.log' );
					exit("0 999 ERROR1");
				}

				$mquery = $wpdb->prepare( "UPDATE $table_name SET order_status = 
					CASE 
						WHEN LOCATE( 'noreceipt', order_status ) > 0 THEN REPLACE( order_status, 'noreceipt', 'receipted' ) 
						ELSE CONCAT( 'receipted,', order_status ) 
					END 
					WHERE ID = %d", $order_id
				);
				$res = $wpdb->query( $mquery );
				if( $res === false ) {
					usces_log( 'Epsilon conv error2 : '.print_r($data, true), 'acting_transaction.log' );
					exit("0 999 ERROR2");
				}

				$datastr = serialize( $data );
				$mquery = $wpdb->prepare( "UPDATE $table_meta_name SET meta_value = %s WHERE meta_key = %s AND order_id = %d", $datastr, 'settlement_id', $order_id );
				$res = $wpdb->query( $mquery );
				if( $res === false ) {
					usces_log( 'Epsilon conv error3 : '.print_r($data, true), 'acting_transaction.log' );
					exit("0 999 ERROR3");
				}

				usces_action_acting_getpoint( $order_id );

				usces_log( 'Epsilon conv transaction : '.$data['settlement_id'], 'acting_transaction.log' );
				exit("1");
			}
		}
	}

	/**********************************************
	* Settlement setting data update
	* @param  -
	* @return -
	***********************************************/
	public function data_update() {
		global $usces;

		if( 'epsilon' != $_POST['acting'] )
			return;

		$this->error_mes = '';
		$options = get_option('usces');

		unset( $options['acting_settings']['epsilon'] );
		$options['acting_settings']['epsilon']['contract_code'] = ( isset($_POST['contract_code']) ) ? $_POST['contract_code'] : '';
		$options['acting_settings']['epsilon']['ope'] = ( isset($_POST['ope']) ) ? $_POST['ope'] : '';
		$options['acting_settings']['epsilon']['card_activate'] = ( isset($_POST['card_activate']) ) ? $_POST['card_activate'] : '';
		$options['acting_settings']['epsilon']['multi_currency'] = ( isset($_POST['multi_currency']) ) ? $_POST['multi_currency'] : '';
		$options['acting_settings']['epsilon']['3dsecure'] = ( isset($_POST['3dsecure']) ) ? $_POST['3dsecure'] : '';
		$options['acting_settings']['epsilon']['process_code'] = ( isset($_POST['process_code']) ) ? $_POST['process_code'] : '';
		$options['acting_settings']['epsilon']['conv_activate'] = ( isset($_POST['conv_activate']) ) ? $_POST['conv_activate'] : '';

		if( '' == $options['acting_settings']['epsilon']['contract_code'] )
			$this->error_mes .= '※契約番号を入力してください<br />';
		if( '' == $options['acting_settings']['epsilon']['ope'] )
			$this->error_mes .= '※稼働環境を選択してください<br />';

		if( WCUtils::is_blank($this->error_mes) ) {
			$usces->action_status = 'success';
			$usces->action_message = __( 'options are updated','usces' );
			$options['acting_settings']['epsilon']['activate'] = 'on';
			if( 'public' == $options['acting_settings']['epsilon']['ope'] ) {
				$options['acting_settings']['epsilon']['send_url'] = 'https://secure.epsilon.jp/cgi-bin/order/receive_order3.cgi';
			} elseif( 'test' == $options['acting_settings']['epsilon']['ope'] ) {
				$options['acting_settings']['epsilon']['send_url'] = 'https://beta.epsilon.jp/cgi-bin/order/receive_order3.cgi';
			}
			if( 'on' == $options['acting_settings']['epsilon']['card_activate'] ) {
				$usces->payment_structure['acting_epsilon_card'] = 'カード決済（イプシロン）';
			} else {
				unset( $usces->payment_structure['acting_epsilon_card'] );
			}
			if( 'on' == $options['acting_settings']['epsilon']['conv_activate'] ) {
				$usces->payment_structure['acting_epsilon_conv'] = 'コンビニ決済（イプシロン）';
			} else {
				unset( $usces->payment_structure['acting_epsilon_conv'] );
			}
		} else {
			$usces->action_status = 'error';
			$usces->action_message = __('Data have deficiency.','usces');
			$options['acting_settings']['epsilon']['activate'] = 'off';
			unset( $usces->payment_structure['acting_epsilon_card'] );
			unset( $usces->payment_structure['acting_epsilon_conv'] );
		}
		ksort( $usces->payment_structure );
		update_option( 'usces', $options );
		update_option( 'usces_payment_structure', $usces->payment_structure );
	}

	/**********************************************
	* Settlement setting page tab title
	* @param  -
	* @return -
	* @echo   str
	***********************************************/
	public function tab_title() {
		echo '<li><a href="#uscestabs_epsilon">イプシロン</a></li>';
	}

	/**********************************************
	* Settlement setting page tab body
	* @param  -
	* @return -
	* @echo   str
	***********************************************/
	public function tab_body() {
		global $usces;
		$opts = $usces->options['acting_settings'];
?>
	<div id="uscestabs_epsilon">
	<div class="settlement_service"><span class="service_title">イプシロン</span></div>
	<?php if( isset($_POST['acting']) && 'epsilon' == $_POST['acting'] ) : ?>
		<?php if( '' != $this->error_mes ) : ?>
		<div class="error_message"><?php echo $this->error_mes; ?></div>
		<?php elseif( isset($opts['epsilon']['activate']) && 'on' == $opts['epsilon']['activate'] ) : ?>
		<div class="message">十分にテストを行ってから運用してください。</div>
		<?php endif; ?>
	<?php endif; ?>
	<form action="" method="post" name="epsilon_form" id="epsilon_form">
		<table class="settle_table">
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_contract_code_epsilon');">契約番号</a></th>
				<td colspan="6"><input name="contract_code" type="text" id="contract_code_epsilon" value="<?php esc_html_e(isset($opts['epsilon']['contract_code']) ? $opts['epsilon']['contract_code'] : ''); ?>" size="20" maxlength="8" /></td>
				<td><div id="ex_contract_code_epsilon" class="explanation">契約時にイプシロンから発行される契約番号（半角数字8桁）</div></td>
			</tr>
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_ope_epsilon');"><?php _e('Operation Environment', 'usces'); ?></a></th>
				<td><input name="ope" type="radio" id="ope_epsilon_test" value="test"<?php if( isset($opts['epsilon']['ope']) && $opts['epsilon']['ope'] == 'test' ) echo ' checked="checked"'; ?> /></td><td width="100"><label for="ope_epsilon_test">テスト環境</label></td>
				<td><input name="ope" type="radio" id="ope_epsilon_public" value="public"<?php if( isset($opts['epsilon']['ope']) && $opts['epsilon']['ope'] == 'public' ) echo ' checked="checked"'; ?> /></td><td width="100"><label for="ope_epsilon_public">本番環境</label></td>
				<td><div id="ex_ope_epsilon" class="explanation">動作環境を切り替えます</div></td>
			</tr>
		</table>
		<table class="settle_table">
			<tr>
				<th>クレジットカード決済</th>
				<td><input name="card_activate" type="radio" id="card_activate_epsilon_on" value="on"<?php if( isset($opts['epsilon']['card_activate']) && $opts['epsilon']['card_activate'] == 'on' ) echo ' checked="checked"'; ?> /></td><td width="100"><label for="card_activate_epsilon_on">利用する</label></td>
				<td><input name="card_activate" type="radio" id="card_activate_epsilon_off" value="off"<?php if( isset($opts['epsilon']['card_activate']) && $opts['epsilon']['card_activate'] == 'off' ) echo ' checked="checked"'; ?> /></td><td width="100"><label for="card_activate_epsilon_off">利用しない</label></td>
				<td></td>
			</tr>
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_multi_currency_epsilon');">多通貨決済</a></th>
				<td><input name="multi_currency" type="radio" class="multi_currency_epsilon" id="multi_currency_epsilon_on" value="on"<?php if( isset($opts['epsilon']['multi_currency']) && $opts['epsilon']['multi_currency'] == 'on' ) echo ' checked="checked"'; ?> /></td><td width="100"><label for="multi_currency_epsilon_on">利用する</label></td>
				<td><input name="multi_currency" type="radio" class="multi_currency_epsilon" id="multi_currency_epsilon_off" value="off"<?php if( isset($opts['epsilon']['multi_currency']) && $opts['epsilon']['multi_currency'] == 'off' ) echo ' checked="checked"'; ?> /></td><td width="100"><label for="multi_currency_epsilon_off">利用しない</label></td>
				<td><div id="ex_multi_currency_epsilon" class="explanation">イプシロンとの契約時にクレジットカード決済（多通貨）の契約をした場合、「利用する」にしてください。</div></td>
			</tr>
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_3dsecure_epsilon');">3Dセキュア</a></th>
				<td><input name="3dsecure" type="radio" class="3dsecure_epsilon" id="3dsecure_epsilon_on" value="on"<?php if( isset($opts['epsilon']['3dsecure']) && $opts['epsilon']['3dsecure'] == 'on' ) echo ' checked="checked"'; ?> /></td><td width="100"><label for="3dsecure_epsilon_on">利用する</label></td>
				<td><input name="3dsecure" type="radio" class="3dsecure_epsilon" id="3dsecure_epsilon_off" value="off"<?php if( isset($opts['epsilon']['3dsecure']) && $opts['epsilon']['3dsecure'] == 'off' ) echo ' checked="checked"'; ?> /></td><td width="100"><label for="3dsecure_epsilon_off">利用しない</label></td>
				<td><div id="ex_3dsecure_epsilon" class="explanation">イプシロンとの契約時に3Dセキュアの契約をした場合、「利用する」にしてください。<br />「多通貨決済」では必須です。「登録済み課金」は併用できません。</div></td>
			</tr>
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_process_code_epsilon');">登録済み課金</th>
				<td><input name="process_code" type="radio" class="process_code_epsilon" id="process_code_epsilon_on" value="on"<?php if( isset($opts['epsilon']['process_code']) && $opts['epsilon']['process_code'] == 'on' ) echo ' checked="checked"'; ?> /></td><td width="100"><label for="process_code_epsilon_on">利用する</label></td>
				<td><input name="process_code" type="radio" class="process_code_epsilon" id="process_code_epsilon_off" value="off"<?php if( isset($opts['epsilon']['process_code']) && $opts['epsilon']['process_code'] == 'off' ) echo ' checked="checked"'; ?> /></td><td width="100"><label for="process_code_epsilon_off">利用しない</label></td>
				<td><div id="ex_process_code_epsilon" class="explanation">Welcart の会員システムを利用している場合、1度クレジットカード決済を実施すると会員番号で紐付けてクレジットカード番号をイプシロンで保持し、2回目以降のクレジット決済において、クレジットカード番号の入力を不要にします。</div></td>
			</tr>
		</table>
		<table class="settle_table">
			<tr>
				<th>コンビニ決済</th>
				<td><input name="conv_activate" type="radio" id="conv_activate_epsilon_on" value="on"<?php if( isset($opts['epsilon']['conv_activate']) && $opts['epsilon']['conv_activate'] == 'on' ) echo ' checked="checked"'; ?> /></td><td width="100"><label for="conv_activate_epsilon_on">利用する</label></td>
				<td><input name="conv_activate" type="radio" id="conv_activate_epsilon_off" value="off"<?php if( isset($opts['epsilon']['conv_activate']) && $opts['epsilon']['conv_activate'] == 'off' ) echo ' checked="checked"'; ?> /></td><td width="100"><label for="conv_activate_epsilon_off">利用しない</label></td>
				<td></td>
			</tr>
		</table>
		<input name="acting" type="hidden" value="epsilon" />
		<input name="usces_option_update" type="submit" class="button" value="イプシロンの設定を更新する" />
		<?php wp_nonce_field( 'admin_settlement', 'wc_nonce' ); ?>
	</form>
	<div class="settle_exp">
		<!--<p><strong>イプシロン</strong></p>-->
		<!--<a href="http://www.epsilon.jp/" target="_blank">イプシロンの詳細はこちら 》</a>-->
		<!--<p>　</p>-->
		<p>この決済は「外部リンク型」の決済システムです。</p>
		<p>「外部リンク型」とは、決済会社のページへ遷移してカード情報を入力する決済システムです。</p>
	</div>
	</div><!--uscestabs_epsilon-->
<?php
	}

	/**********************************************
	* Settlement setting page script
	* @param  -
	* @return -
	* @echo   -
	***********************************************/
	public function settlement_script() {
?>
	$(document).on( "change", ".multi_currency_epsilon", function() {
		if( "on" == $( this ).val() ) {
			$("#3dsecure_epsilon_on").prop("checked", true);
			$("#process_code_epsilon_off").prop("checked", true);
		}
	});
	$(document).on( "change", ".3dsecure_epsilon", function() {
		if( "on" == $( this ).val() ) {
			$("#process_code_epsilon_off").prop("checked", true);
		} else if( "off" == $( this ).val() ) {
			if( $("#multi_currency_epsilon_on").prop("checked") ) {
				$("#3dsecure_epsilon_on").prop("checked", true);
			}
		}
	});
	$(document).on( "change", ".process_code_epsilon", function() {
		if( "on" == $( this ).val() ) {
			$("#multi_currency_epsilon_off").prop("checked", true);
			$("#3dsecure_epsilon_off").prop("checked", true);
		}
	});
<?php
	}

	/**********************************************
	* Settlement information key
	* @param  -
	* @return -
	* @echo   -
	***********************************************/
	public function settlement_info_field_keys( $keys ) {
		array_push( $keys, 'conveni_name', 'conveni_date' );
		return $keys;
	}

	/**********************************************
	* Revival order data
	* @param  -
	* @return -
	* @echo   -
	***********************************************/
	public function revival_order_data( $order_id, $log_key, $acting ) {
		global $usces;
		if( !in_array($acting, $this->pay_method) ) {
			$usces->set_order_meta_value( 'settlement_id', $log_key, $order_id );
		}
	}
}
