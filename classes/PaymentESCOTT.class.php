<?php
/*
e-SCOTT Smart Settlement module
Version: 1.0.0
Author: Collne Inc.

*/

class ESCOTT_SETTLEMENT
{
	private $error_mes, $pay_method;
	
	public function __construct(){
	
		$this->pay_method = array(
			'acting_escott_card',
			'acting_escott_conv'
		);
		
		if( is_admin() ){
		
			add_action( 'usces_action_settlement_tab_title', array( $this, 'tab_title') );
			add_action( 'usces_action_settlement_tab_body', array( $this, 'tab_body') );
			add_action( 'usces_action_admin_settlement_update', array( $this, 'data_update') );
			add_filter( 'usces_filter_settle_info_field_keys', array( $this, 'settle_info_field_keys') );
			add_filter( 'usces_filter_settle_info_field_value', array( $this, 'settle_info_field_value'), 10, 3 );
			
		}else{
		
			add_filter( 'usces_filter_delivery_secure_form_loop', array( $this, 'delivery_secure_form'), 10, 2 );
			add_filter( 'usces_filter_payments_str', array( $this, 'payments_str'), 10, 2 );
			add_filter( 'usces_filter_payments_arr', array( $this, 'payments_arr'), 10, 2 );
			add_filter( 'usces_filter_delivery_check', array( $this, 'delivery_check') );
			add_filter( 'usces_filter_confirm_inform', array( $this, 'confirm_inform'), 10, 5 );
			add_filter( 'usces_purchase_check', array( $this, 'purchase'), 5 );
			add_filter( 'usces_filter_check_acting_return_results', array( $this, 'acting_return') );
			add_filter( 'usces_filter_check_acting_return_duplicate', array( $this, 'check_acting_return_duplicate'), 10, 2 );
			add_action( 'usces_action_reg_orderdata', array( $this, 'reg_order_metadata') );
			add_filter( 'usces_filter_get_error_settlement', array( $this, 'error_page_mesage') );
			add_action( 'usces_post_reg_orderdata', array( $this, 'order_status_change'), 10, 2 );
			add_action( 'init', array( $this, 'conv_result_notification') );
		}
	}
	
	/**********************************************
	* usces_filter_delivery_check
	* カード情報入力チェック
	* @param  $mes
	* @return str $mes
	***********************************************/
	public function delivery_check( $mes ){
		global $usces;
		
		$payments = $usces->getPayments($_POST['offer']['payment_name']);
		if( 'acting_escott_card' == $payments['settlement'] ){
			$total_items_price = $usces->get_total_price();
			if( 1 > $total_items_price )
				$mes .= sprintf(__('A total products amount of money surpasses the upper limit(%s) that I can purchase in C.O.D.', 'usces'), usces_crform($this->options['cod_limit_amount'], true, false, 'return')) . "<br />";

			if( ( isset($_POST['acting']) && 'escott' == $_POST['acting'] ) && 
				( isset($_POST['cnum1']) && empty($_POST['cnum1']) ) || 
				( isset($_POST['securecode']) && empty($_POST['securecode']) ) || 
				( isset($_POST['expyy']) && empty($_POST['expyy']) ) || 
				( isset($_POST['expmm']) && empty($_POST['expmm']) ) || 
				( isset($_POST['username_card']) && empty($_POST['username_card']) )
			){
				$mes .= __('カード情報を正しくご入力ください。', 'usces') . "<br />";
			}

		}
		return $mes;
	}
	
	/**********************************************
	* init
	* コンビニ及びペイジー入金通知処理
	* @param  -
	* @return -
	***********************************************/
	public function conv_result_notification(){
		global $wpdb, $usces;

		if( isset($_POST['TransactionId']) && isset($_POST['MerchantFree2']) && 'acting_escott_conv' == $_POST['MerchantFree2'] && isset($_POST['RecvNum']) && isset($_POST['NyukinDate']) ){
			
			global $wpdb;
			$order_meta_table_name = $wpdb->prefix . "usces_order_meta";
			$query = $wpdb->prepare("SELECT order_id FROM $order_meta_table_name WHERE meta_key = %s", 
									$_POST['MerchantFree1']);
			$order_id = $wpdb->get_var($query);

			//オーダーステータス変更
			usces_change_order_receipt( $order_id, 'receipted' );
			//ポイント付与
			usces_action_acting_getpoint( $order_id );
	
			$acting_flag = $_POST['MerchantFree2'];
			$value = $usces->get_order_meta_value( $acting_flag, $order_id );
			$results = unserialize($value);
			$meta_value = serialize(array_merge( $results, $_POST ));
			$usces->set_order_meta_value( $acting_flag, $meta_value, $order_id );
			usces_log('e-SCOTT Conv return : '.print_r($_POST, true), 'acting_transaction.log');
			header("HTTP/1.0 200 OK");
			die();
			
		}
	}
 
	/**********************************************
	* usces_post_reg_orderdata
	* 初期入金状況を「未入金」に指定
	* @param  $order_id, $results
	* @return -
	***********************************************/
	public function order_status_change( $order_id, $results ){
		if( isset($results['acting']) && 'escott_conv' == $results['acting'] ){
			usces_change_order_receipt( $order_id, 'noreceipt' );
		}
	}
 
	/**********************************************
	* usces_filter_get_error_settlement
	* カード決済用エラーメッセージ
	* @param  $keys
	* @return str $keys
	***********************************************/
	public function error_page_mesage( $res ){
		if( !isset($_REQUEST['MerchantFree2']) || 'acting_escott_card' != $_REQUEST['MerchantFree2'] )
			return $res;
			
		switch($_REQUEST['ResponseCd']){
		case 'C13':
			$res .= '<div class="error_page_mesage">
			<p>カードの有効期限が切れています。もう一度ご入力される場合は、下記の「カード番号再入力」をクリックしてください。</p>
			<p><a href="' . add_query_arg(array('backDelivery'=>'escott_card_C13'), USCES_CART_URL) . '">「カード番号再入力」</a></p>
			</div>';
			break;
		case 'G55':
			$res .= '<div class="error_page_mesage">
			<p>ご利用限度額を超えています。</p>
			</div>';
			break;
		case 'G44':
		case 'G45':
		case 'G65':
			$res .= '<div class="error_page_mesage">
			<p>カード番号が違っています。もう一度ご入力される場合は、下記の「カード番号再入力」をクリックしてください。</p>
			<p><a href="' . add_query_arg(array('backDelivery'=>'escott_card_G65'), USCES_CART_URL) . '">「カード番号再入力」</a></p>
			</div>';
			break;
		default:
			$res .= '<div class="error_page_mesage">
			<p>エラーコード：' . $_REQUEST['ResponseCd'] . '</p>
			<p>エラー内容：' . $this->response_message($_REQUEST['ResponseCd']) . '</p>
			
			<p><a href="' . add_query_arg(array('backDelivery'=>'escott_card'), USCES_CART_URL) . '">「カード番号再入力」</a></p>
			</div>';
		}

		return $res;
	}

	/**********************************************
	* usces_filter_settle_info_field_keys
	* 受注編集画面に表示する決済情報のキー
	* @param  $keys
	* @return array $keys
	***********************************************/
	public function settle_info_field_keys( $keys ){
		$array = array_merge( $keys, array('MerchantFree1','ResponseCd', 'PayType','CardNo','CardExp','KessaiNumber', 'NyukinDate', 'CvsCd') );
		return $array;
	}
	
	/**********************************************
	* usces_filter_settle_info_field_value
	* 受注編集画面に表示する決済情報の値整形
	* @param  $value, $key, $acting
	* @return str $value
	***********************************************/
	public function settle_info_field_value( $value, $key, $acting ){
		if( 'escott_card' != $acting && 'escott_conv' != $acting )
			return $value;

		switch($key){
			case 'acting':
				switch($value){
					case 'escott_card':
						$value = 'e-SCOTT カード決済';
						break;
					case 'escott_conv':
						$value = 'e-SCOTT オンライン収納';
						break;
				
				}
				break;
			case 'CvsCd':
				switch($value){
					case 'LSN':
						$value = 'ローソン';
						break;
					case 'FAM':
						$value = 'ファミリーマート';
						break;
					case 'SAK':
						$value = 'サンクス';
						break;
					case 'CCK':
						$value = 'サークルK';
						break;
					case 'ATM':
						$value = 'Pay-easy（ATM）';
						break;
					case 'ONL':
						$value = 'Pay-easy（オンライン）';
						break;
					case 'LNK':
						$value = 'Pay-easy（情報リンク）';
						break;
					case 'SEV':
						$value = 'セブンイレブン';
						break;
					case 'MNS':
						$value = 'ミニストップ';
						break;
					case 'DAY':
						$value = 'デイリーヤマザキ';
						break;
					case 'EBK':
						$value = '楽天銀行';
						break;
					case 'JNB':
						$value = 'ジャパンネット銀行';
						break;
					case 'EDY':
						$value = 'Edy';
						break;
					case 'SUI':
						$value = 'Suica';
						break;
					case 'FFF':
						$value = 'スリーエフ';
						break;
					case 'JIB':
						$value = 'じぶん銀行';
						break;
					case 'SNB':
						$value = '住信SBIネット銀行';
						break;
					case 'SCM':
						$value = 'セイコーマート';
						break;
				}
				break;

		}
		return $value;
	}
	
	/**********************************************
	* usces_action_reg_orderdata
	* オーダーメタ保存
	* @param  $args = array(
				'cart'=>$cart, 'entry'=>$entry, 'order_id'=>$order_id, 
				'member_id'=>$member['ID'], 'payments'=>$set, 'charging_type'=>$charging_type, 
				'results'=>$results
				);
	* @return -
	***********************************************/
	public function reg_order_metadata( $args ){
		global $usces;
		extract($args);

		$acting_flag = ( 'acting' == $payments['settlement'] ) ? $payments['module'] : $payments['settlement'];
		if( 'acting_escott_card' != $acting_flag && 'acting_escott_conv' != $acting_flag )
			return;
		
		$meta_value = serialize($results);
		$usces->set_order_meta_value( $acting_flag, $meta_value, $order_id );
		if( 'acting_escott_conv' == $acting_flag ){
			$usces->set_order_meta_value( $results['MerchantFree1'], $acting_flag, $order_id );
		}
	}
	
	/**********************************************
	* usces_filter_check_acting_return_duplicate
	* 重複オーダー禁止処理
	* @param  $tid, $results
	* @return str RandId
	***********************************************/
	public function check_acting_return_duplicate( $tid, $results ){
		if( !isset($results['acting']) || ('escott_card' != $results['acting'] && 'escott_conv' != $results['acting']) )
			return $tid;
		else	
			return $results['MerchantFree1'];
	}

	/**********************************************
	* usces_filter_payments_str
	* 支払方法JavaScript用決済名追加
	* @param  $payments_str $payment
	* @return str $payments_str
	***********************************************/
	public function payments_str( $payments_str, $payment ){
		global $usces;
		
		switch( $payment['settlement'] ){
		case 'acting_escott_card':
			$paymod_base = 'escott';
			if( 'on' == $usces->options['acting_settings'][$paymod_base]['card_activate'] 
				&& 'on' == $usces->options['acting_settings'][$paymod_base]['activate'] ){
				$payments_str .= "'" . $payment['name'] . "': '" . $paymod_base . "', ";
			}
			break;
		}
		return $payments_str;
	}
	
	/**********************************************
	* usces_filter_payments_arr
	* 支払方法JavaScript用決済追加
	* @param  $payments_arr $payment
	* @return str $payments_arr
	***********************************************/
	public function payments_arr( $payments_arr, $payment ){
		global $usces;
		
		switch( $payment['settlement'] ){
		case 'acting_escott_card':
			$paymod_base = 'escott';
			if( 'on' == $usces->options['acting_settings'][$paymod_base]['card_activate'] 
				&& 'on' == $usces->options['acting_settings'][$paymod_base]['activate'] ){
				$payments_arr[] = $paymod_base;
			}
			break;
		}
		return $payments_arr;
	}
	
	/**********************************************
	* usces_filter_delivery_secure_form_loop
	* 支払方法ページ用入力フォーム
	* @param  $nouse $payment
	* @return str $html
	***********************************************/
	public function delivery_secure_form( $nouse, $payment ){
		global $usces;
		
		$paymod_id = 'escott';
		$acting_opts = $usces->options['acting_settings'][$paymod_id];
		$html = '';
		switch( $payment['settlement'] ){
		case 'acting_escott_card':

			if( 'on' != $acting_opts['card_activate'] 
				|| 'on' != $acting_opts['activate'] )
				continue;

			$cnum1 = isset( $_POST['cnum1'] ) ? esc_html($_POST['cnum1']) : '';
			$securecode = isset( $_POST['securecode'] ) ? esc_html($_POST['securecode']) : '';
			$expyy = isset( $_POST['expyy'] ) ? esc_html($_POST['expyy']) : '';
			$expmm = isset( $_POST['expmm'] ) ? esc_html($_POST['expmm']) : '';
			$username = isset( $_POST['username_card'] ) ? esc_html($_POST['username_card']) : '';
			$paytype = isset( $_POST['paytype'] ) ? esc_html($_POST['paytype']) : '1';

			$html .= '<input type="hidden" name="acting" value="escott">'."\n";
			$html .= '<table class="customer_form" id="' . $paymod_id . '">'."\n";

			if( $usces->is_member_logged_in() ){
				$member = $usces->get_member();
				$KaiinPass = $this->get_quick_pass( $member['ID'] );
				$KaiinId = $this->get_quick_kaiin_id( $member['ID'] );
			}
			$res['ResponseCd'] = '';
			if( 'on' == $acting_opts['quickpay'] && !empty($KaiinPass) ){
			
				$param_list = array(); 
				$params = array();
				$TransactionDate = date('Ymd', current_time('timestamp'));
				// 共通部 
				$param_list['MerchantId'] = urlencode( $acting_opts['merchant_id'] ); 
				$param_list['MerchantPass'] = urlencode( $acting_opts['merchant_pass'] ); 
				$param_list['TransactionDate'] = urlencode( $TransactionDate ); 
				$param_list['MerchantFree3'] = urlencode( "wc1collne" ); 
				$params['send_url'] = $acting_opts['send_url_member'];
				$params['param_list'] = array_merge($param_list, 
					array(
						'OperateId' => '4MemRefM',
						'KaiinPass' => $KaiinPass,
						'KaiinId' => $KaiinId
					)
				);//escott会員照会
				$res = $this->connection( $params );
			}
			if( 'OK' == $res['ResponseCd'] && !( isset($_REQUEST['backDelivery']) && 'escott_card' == substr($_REQUEST['backDelivery'], 0, 11)) ){
				$expyy = substr(date('Y', current_time('timestamp')), 0, 2) . substr($res['CardExp'], 0, 2);
				$expmm = substr($res['CardExp'], 2, 2);
				$html .= '<input name="cnum1" type="hidden" value="8888888888888888" />
				<input name="expyy" type="hidden" value="' . $expyy . '" />
				<input name="expmm" type="hidden" value="' . $expmm . '" />
				<input name="username_card" type="hidden" value="QUICKPAY" />';
				$html .= '<tr>
				<th scope="row">'.__('ご登録のカード番号下4桁', 'usces').'</th>
				<td colspan="2"><p>' . substr($res['CardNo'], -4) . '　（<a href="' . add_query_arg( array('backDelivery'=>'escott_card'), USCES_CART_URL ) . '">カード情報の変更はこちら</a>）</p></td>
				</tr>';
				$html .= '<th scope="row">'.__('セキュリティコード', 'usces').'</th>
				<td colspan="2"><input name="securecode" type="text" size="6" value="' . esc_attr($securecode) . '" />(半角数字のみ)</td>
				</tr>';

			}else{
			
				$label = __('カード番号', 'usces');
				$html .= '<tr>
				<th scope="row">'.$label.'<input name="acting" type="hidden" value="escott" /></th>
				<td colspan="2"><input name="cnum1" type="text" size="16" value="' . esc_attr($cnum1) . '" />(半角数字のみ)</td>
				</tr>';
				$html .= '<tr>
				<th scope="row">'.__('セキュリティコード', 'usces').'</th>
				<td colspan="2"><input name="securecode" type="text" size="6" value="' . esc_attr($securecode) . '" />(半角数字のみ)</td>
				</tr>';
				$html .= '<tr>
				<th scope="row">'.__('カード有効期限', 'usces').'</th>
				<td colspan="2">
				<select name="expmm">
					<option value=""' . (empty($expmm) ? ' selected="selected"' : '') . '>----</option>
					<option value="01"' . (('01' === $expmm) ? ' selected="selected"' : '') . '> 1</option>
					<option value="02"' . (('02' === $expmm) ? ' selected="selected"' : '') . '> 2</option>
					<option value="03"' . (('03' === $expmm) ? ' selected="selected"' : '') . '> 3</option>
					<option value="04"' . (('04' === $expmm) ? ' selected="selected"' : '') . '> 4</option>
					<option value="05"' . (('05' === $expmm) ? ' selected="selected"' : '') . '> 5</option>
					<option value="06"' . (('06' === $expmm) ? ' selected="selected"' : '') . '> 6</option>
					<option value="07"' . (('07' === $expmm) ? ' selected="selected"' : '') . '> 7</option>
					<option value="08"' . (('08' === $expmm) ? ' selected="selected"' : '') . '> 8</option>
					<option value="09"' . (('09' === $expmm) ? ' selected="selected"' : '') . '> 9</option>
					<option value="10"' . (('10' === $expmm) ? ' selected="selected"' : '') . '>10</option>
					<option value="11"' . (('11' === $expmm) ? ' selected="selected"' : '') . '>11</option>
					<option value="12"' . (('12' === $expmm) ? ' selected="selected"' : '') . '>12</option>
				</select>月&nbsp;<select name="expyy">
					<option value=""' . (empty($expyy) ? ' selected="selected"' : '') . '>------</option>
				';
				for($i=0; $i<10; $i++){
					$year = date('Y') - 1 + $i;
					$html .= '
					<option value="' . $year . '"' . (($year == $expyy) ? ' selected="selected"' : '') . '>' . $year . '</option>';
				}
				$html .= '
				</select>年</td>
				</tr>
				<tr>
				<th scope="row">'.__('カード名義', 'usces').'</th>
				<td colspan="2"><input name="username_card" id="username_card_escott" type="text" size="30" value="' . esc_attr($username) . '" />(半角英字)</td>
				</tr>';
			}

			$html_paytype = '';
			$html_paytype .= '
			<tr>
			<th scope="row">'.__('支払方法', 'usces').'</th>
			<td colspan="2">
			<select name="offer[paytype]">
				<option value="01"' . (('01' == $paytype) ? ' selected="selected"' : '') . '>1回払い</option>
				<option value="02"' . (('02' == $paytype) ? ' selected="selected"' : '') . '>2回払い</option>
				<option value="03"' . (('03' == $paytype) ? ' selected="selected"' : '') . '>3回払い</option>
				<option value="04"' . (('04' == $paytype) ? ' selected="selected"' : '') . '>4回払い</option>
				<option value="05"' . (('05' == $paytype) ? ' selected="selected"' : '') . '>5回払い</option>
				<option value="06"' . (('06' == $paytype) ? ' selected="selected"' : '') . '>6回払い</option>
				<option value="07"' . (('07' == $paytype) ? ' selected="selected"' : '') . '>7回払い</option>
				<option value="08"' . (('08' == $paytype) ? ' selected="selected"' : '') . '>8回払い</option>
				<option value="09"' . (('09' == $paytype) ? ' selected="selected"' : '') . '>9回払い</option>
				<option value="10"' . (('10' == $paytype) ? ' selected="selected"' : '') . '>10回払い</option>
				<option value="11"' . (('11' == $paytype) ? ' selected="selected"' : '') . '>11回払い</option>
				<option value="12"' . (('12' == $paytype) ? ' selected="selected"' : '') . '>12回払い</option>
				<option value="15"' . (('15' == $paytype) ? ' selected="selected"' : '') . '>15回払い</option>
				<option value="16"' . (('16' == $paytype) ? ' selected="selected"' : '') . '>16回払い</option>
				<option value="18"' . (('18' == $paytype) ? ' selected="selected"' : '') . '>18回払い</option>
				<option value="20"' . (('20' == $paytype) ? ' selected="selected"' : '') . '>20回払い</option>
				<option value="24"' . (('24' == $paytype) ? ' selected="selected"' : '') . '>24回払い</option>
				<option value="30"' . (('30' == $paytype) ? ' selected="selected"' : '') . '>30回払い</option>
				<option value="36"' . (('36' == $paytype) ? ' selected="selected"' : '') . '>36回払い</option>
				<option value="54"' . (('54' == $paytype) ? ' selected="selected"' : '') . '>54回払い</option>
				<option value="72"' . (('72' == $paytype) ? ' selected="selected"' : '') . '>72回払い</option>
				<option value="84"' . (('84' == $paytype) ? ' selected="selected"' : '') . '>84回払い</option>
				<option value="80"' . (('80' == $paytype) ? ' selected="selected"' : '') . '>ボーナス一括払い</option>
				<option value="88"' . (('88' == $paytype) ? ' selected="selected"' : '') . '>リボルビング払い</option>
			</select>
			</td>
			</tr>
			';

			$html .= apply_filters( 'usces_filter_escott_secure_form_paytype', $html_paytype );
			$html .= '
			</table>';
			break;
		}
		return $html;
	}

	/**********************************************
	* usces_filter_check_acting_return_results
	* 決済完了ページ制御
	* @param  $results
	* @return array $results
	***********************************************/
	public function acting_return( $results ){
		if( !in_array( $results['acting'], $this->pay_method) )
			return $results;

		$results['reg_order'] = false;
		
		if( !isset( $_REQUEST['nonce'] ) || !wp_verify_nonce($_REQUEST['nonce'], 'escott_'.$_REQUEST['TransactionId']) ) {
			$results[0] = 0;
		}
		
		usces_log('e-SCOTT Payment results : '.print_r($results, true), 'acting_transaction.log');
		return $results;
	}

	/**********************************************
	* e-SCOTT 会員パスワード取得
	* @param  $params
	* @return $response
	***********************************************/
	public function get_quick_pass( $member_id ){
		global $usces;
		
		if( empty($member_id) )
			return false;
			
		$escott_member_passwd = $usces->get_member_meta_value( 'escott_member_passwd', $member_id );
		return $escott_member_passwd;
		
	}
	
	/**********************************************
	* e-SCOTT 会員ID取得
	* @param  $params
	* @return $response
	***********************************************/
	public function get_quick_kaiin_id( $member_id ){
		global $usces;
		
		if( empty($member_id) )
			return false;
			
		$escott_member_passwd = $usces->get_member_meta_value( 'escott_member_id', $member_id );
		return $escott_member_passwd;
		
	}
	
	/**********************************************
	* e-SCOTT 会員パスワード生成
	* @param  $params
	* @return $response
	***********************************************/
	public function make_kaiin_pass(){
		$passwd = mt_rand( 100000000000, 999999999999 );
		return $passwd;
		
	}
	
	/**********************************************
	* e-SCOTT 会員ID生成
	* @param  $params
	* @return $response
	***********************************************/
	public function make_kaiin_id(){
		$id = mt_rand( 100000000000, 999999999999 );
		return 'i'.$id;
		
	}
	
	/**********************************************
	* e-SCOTT 会員情報登録・更新
	* @param  $params
	* @return $response
	***********************************************/
	public function escott_member_process( $param_list = array() ){
		global $usces;

		$member = $usces->get_member();
		$acting_opts = $usces->options['acting_settings']['escott'];
		$params['send_url'] = $acting_opts['send_url_member'];
		$KaiinPass = $this->get_quick_pass( $member['ID'] );//member_ID
		$KaiinId = $this->get_quick_kaiin_id( $member['ID'] );//member_ID
		if( empty( $KaiinPass ) ){
			$KaiinPass = $this->make_kaiin_pass();
			$KaiinId = $this->make_kaiin_id();
			$params['param_list'] = array_merge($param_list, 
				array(
					'OperateId' => '4MemAdd',
					'KaiinPass' => $KaiinPass,
					'KaiinId' => $KaiinId,
					'CardNo' => trim($_POST['cardnumber']),
					'CardExp' => substr($_POST['expyy'],2) . $_POST['expmm']
				)
			);//escott新規会員登録
			$res = $this->connection( $params );
			if( 'OK' == $res['ResponseCd'] ){
				$usces->set_member_meta_value( 'escott_member_passwd', $KaiinPass, $member['ID'] );
				$usces->set_member_meta_value( 'escott_member_id', $KaiinId, $member['ID'] );
				$res['KaiinPass'] = $KaiinPass;
				$res['KaiinId'] = $KaiinId;
			}
		}else{
			if( isset($_POST['cardnumber']) && '8888888888888888' != $_POST['cardnumber'] ){
				$params['param_list'] = array_merge($param_list, 
					array(
						'OperateId' => '4MemChg',
						'KaiinPass' => $KaiinPass,
						'KaiinId' => $KaiinId,
						'CardNo' => trim($_POST['cardnumber']),
						'CardExp' => substr($_POST['expyy'],2) . $_POST['expmm']
					)
				);//escott会員更新
				$res = $this->connection( $params );
				if( 'OK' == $res['ResponseCd'] ){
					$res['KaiinPass'] = $KaiinPass;
					$res['KaiinId'] = $KaiinId;
				}else{
					$params['param_list']['OperateId'] = '4MemInval';
					$params['param_list']['KaiinPass'] = $KaiinPass;
					$params['param_list']['KaiinId'] = $KaiinId;
					$ires = $this->connection( $params );
					if( 'OK' == $ires['ResponseCd'] ){
						$params['param_list']['OperateId'] = '4MemDel';
						$dres = $this->connection( $params );
						if( 'OK' == $dres['ResponseCd'] ){
							$usces->del_member_meta( 'escott_member_passwd', $member['ID'] );
							$usces->del_member_meta( 'escott_member_id', $member['ID'] );
						}
					}
				}
			}else{
				$res['ResponseCd'] = 'OK';
				$res['KaiinPass'] = $KaiinPass;
				$res['KaiinId'] = $KaiinId;
			}
		}
		return $res;
	}
	
	/**********************************************
	* ソケット通信接続
	* @param  $params
	* @return array $response_data
	***********************************************/
	public function connection( $params ){

		$gc = new SLNConnection(); 
		$gc->set_connection_url( $params['send_url'] ); 
		$gc->set_connection_timeout( 60 ); 
		$response_list = $gc->send_request( $params['param_list'] ); 

		if( !empty($response_list) ){
		
			$resdata = explode("\r\n\r\n", $response_list);
			parse_str($resdata[1], $response_data );

		}else{
			$response_data['ResponseCd'] = 'error';
		}
		return $response_data;
	}
	
	/**********************************************
	* usces_purchase_check
	* 決済処理
	* @param  no use
	* @return allways false
	***********************************************/
	public function purchase( $nouse ){
		global $usces;
		
		$usces_entries = $usces->cart->get_entry();
		$cart = $usces->cart->get_cart();

		if( !$usces_entries || !$cart )
			wp_redirect(USCES_CART_URL);
			
		$payments = usces_get_payments_by_name($usces_entries['order']['payment_name']);
		$acting_flag = ( 'acting' == $payments['settlement'] ) ? $payments['module'] : $payments['settlement'];
		if( 'acting_escott_card' != $acting_flag && 'acting_escott_conv' != $acting_flag )
			return true;

		if( !wp_verify_nonce( $_REQUEST['_nonce'], $acting_flag ) )
			wp_redirect(USCES_CART_URL);
		

		$TransactionDate = date('Ymd', current_time('timestamp'));
		$rand = $_REQUEST['rand'];
		$member = $usces->get_member();

		$charging_type = $usces->getItemChargingType($cart[0]['post_id']);
		$frequency = $usces->getItemFrequency($cart[0]['post_id']);
		$chargingday = $usces->getItemChargingDay($cart[0]['post_id']);
		
		$item_id = $cart[0]['post_id'];
		$item_name = mb_convert_kana($usces->getItemName($cart[0]['post_id']), 'AK');
		if(1 < count($cart)){
			if(15 < mb_strlen($item_name.'　その他')){
				$item_name = mb_substr($item_name, 0, 10).'　その他';
			}else{
				$item_name .= '　その他';
			}
		}else{
			if(15 < mb_strlen($item_name)){
				$item_name = mb_substr($item_name, 0, 10).'・・・';
			}
		}
		
		$acting_opts = $usces->options['acting_settings']['escott'];
		$send_url = $acting_opts['send_url'];

		$param_list = array(); 
		$response_list = array(); 
		// 共通部 
		$param_list['MerchantId'] = urlencode( $acting_opts['merchant_id'] ); 
		$param_list['MerchantPass'] = urlencode( $acting_opts['merchant_pass'] ); 
		$param_list['TransactionDate'] = urlencode( $TransactionDate ); 
		$param_list['MerchantFree1'] = urlencode( $rand ); 
		$param_list['MerchantFree2'] = urlencode( $acting_flag ); 
		$param_list['MerchantFree3'] = urlencode( "wc1collne" ); 
		if( !empty($acting_opts['tenant_id']) )
			$param_list['TenantId'] = urlencode( $acting_opts['tenant_id'] ); 
		$param_list['Amount'] = urlencode( $usces_entries['order']['total_full_price'] ); 

		// 処理部 
		switch( $acting_flag ) {
		
		case 'acting_escott_card':
			$acting = "escott_card";

			if( !empty($member['ID']) && 'on' == $acting_opts['quickpay'] ) {
				
				$mem_response_data = $this->escott_member_process( $param_list );
				if( 'OK' == $mem_response_data['ResponseCd'] ){
					$param_list['KaiinPass'] = urlencode( $mem_response_data['KaiinPass'] );
					$param_list['KaiinId'] = urlencode( $mem_response_data['KaiinId'] );
				}
			}else{
				$param_list['CardNo'] = urlencode( trim($_POST['cardnumber']) ); 
				$param_list['CardExp'] = urlencode( substr($_POST['expyy'],2) . $_POST['expmm'] ); 
			}

			$param_list['PayType'] = urlencode( $usces_entries['order']['paytype'] ); 
			$param_list['SecCd'] = urlencode( trim($_POST['securecode']) ); 
			$param_list['OperateId'] = urlencode( $acting_opts['operateid'] );
	
			$params['send_url'] = $acting_opts['send_url'];
			$params['param_list'] = $param_list;
			$response_data = $this->connection( $params );
			
			$response_data['acting'] = $acting;
			$response_data['PayType'] = $usces_entries['order']['paytype'];
			$response_data['CardNo'] = substr($_POST['cardnumber'], -4);
			$response_data['CardExp'] = substr($_POST['expyy'],2) . '/' . $_POST['expmm'];
			if( 'OK' == $response_data['ResponseCd'] ){
			
				$res = $usces->order_processing($response_data);
				if( 'ordercompletion' == $res ){
					if( isset($response_data['MerchantFree1']) ){
						usces_ordered_acting_data($response_data['MerchantFree1']);
					}
					$response_data['acting_return'] = 1;
					$response_data['result'] = 1;
					$response_data['nonce'] = wp_create_nonce('escott_'.$response_data['TransactionId']);
					wp_redirect( add_query_arg( $response_data, USCES_CART_URL) );
				}else{
					$response_data['acting_return'] = 0;
					$response_data['result'] = 0;
					wp_redirect( add_query_arg( $response_data, USCES_CART_URL) );
				}
			
			}else{
				$response_data['acting_return'] = 0;
				$response_data['result'] = 0;
				wp_redirect( add_query_arg( $response_data, USCES_CART_URL) );
			
			}

			break;
			
		case 'acting_escott_conv':
			$acting = "escott_conv";

			$param_list['OperateId'] = '2Add';
			$param_list['PayLimit'] = urlencode( date( 'Ymd', current_time('timestamp')+(86400*$acting_opts['conv_limit']) ).'2359' ); 
			$param_list['NameKanji'] = urlencode( $usces_entries['customer']['name1'].$usces_entries['customer']['name2'] ); 
			$param_list['NameKana'] = !empty($usces_entries['customer']['name2']) ? urlencode( $usces_entries['customer']['name2'].$usces_entries['customer']['name3'] ) : $param_list['NameKanji']; 
			$param_list['TelNo'] = urlencode( $usces_entries['customer']['tel'] ); 
			$param_list['ShouhinName'] = urlencode( $item_name );

			$params['send_url'] = $acting_opts['send_url_conv'];
			$params['param_list'] = $param_list;
			$response_data = $this->connection( $params );
			$response_data['acting'] = $acting;
			
			if( 'OK' == $response_data['ResponseCd'] ){
			
				$res = $usces->order_processing($response_data);
				if( 'ordercompletion' == $res ){
					if( isset($response_data['MerchantFree1']) ){
						usces_ordered_acting_data($response_data['MerchantFree1']);
					}
					$usces->cart->crear_cart();
					$FreeArea = trim($response_data['FreeArea']);
					$url = add_query_arg( array('code'=>$FreeArea, 'rkbn'=>2), $acting_opts['redirect_url_conv']);
					header('location: ' . $url);
					exit;
				}else{
					$response_data['acting_return'] = 0;
					$response_data['result'] = 0;
					wp_redirect( add_query_arg( $response_data, USCES_CART_URL) );
				}
				
			}else{
			
				$response_data['acting_return'] = 0;
				$response_data['result'] = 0;
				wp_redirect( add_query_arg( $response_data, USCES_CART_URL) );
			}
			break;
			
		}
		
		exit;
		return false;
	}
	
	/**********************************************
	* usces_filter_confirm_inform
	* 内容確認ページ Purchase Button
	* @param  $html, $payments, $acting_flag, $rand, $purchase_disabled
	* @return form str
	***********************************************/
	public function confirm_inform( $html, $payments, $acting_flag, $rand, $purchase_disabled ){
		global $usces;
		if( 'acting_escott_card' != $acting_flag && 'acting_escott_conv' != $acting_flag )
			return $html;

		$usces_entries = $usces->cart->get_entry();
		$acting_opts = $usces->options['acting_settings']['escott'];
		$usces->save_order_acting_data($rand);
		$html = '<form id="purchase_form" action="' . USCES_CART_URL . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">';
		$mem_id = '';
		$quick_pass = '';
		if( $usces->is_member_logged_in() ){
			$member = $usces->get_member();
			$mem_id = $member['ID'];
			$quick_pass = $this->get_quick_pass( $mem_id );
		}
		$html .= '<input type="hidden" name="cardnumber" value="' . esc_attr($_POST['cnum1']) . '">';
		$securecode = isset($_POST['securecode']) ? $_POST['securecode'] : '';
		$html .= '<input type="hidden" name="securecode" value="' . esc_attr($securecode) . '">';
		$html .= '<input type="hidden" name="expyy" value="' . esc_attr($_POST['expyy']) . '">
			<input type="hidden" name="expmm" value="' . esc_attr($_POST['expmm']) . '">';
		$html .= '<input type="hidden" name="telno" value="' . esc_attr(str_replace('-', '', $usces_entries['customer']['tel'])) . '">
			<input type="hidden" name="email" value="' . esc_attr($usces_entries['customer']['mailaddress1']) . '">
			<input type="hidden" name="sendid" value="' . $mem_id . '">
			<input type="hidden" name="username" value="' . esc_attr($_POST['username_card']) . '">
			<input type="hidden" name="money" value="' . usces_crform($usces_entries['order']['total_full_price'], false, false, 'return', false) . '">
			<input type="hidden" name="sendpoint" value="' . $rand . '">
			<input type="hidden" name="printord" value="yes">';
		$html .= '<input type="hidden" name="paytype" value="' . $usces_entries['order']['paytype'] . '">';
		$html .= '
			<input type="hidden" name="rand" value="' . $rand . '">
			<input type="hidden" name="cnum1" value="' . esc_attr($_POST['cnum1']) . '">
			<div class="send"><input name="backDelivery" type="submit" id="back_button" class="back_to_delivery_button" value="'.__('Back', 'usces').'"' . apply_filters('usces_filter_confirm_prebutton', NULL) . ' />
			<input name="purchase" type="submit" id="purchase_button" class="checkout_button" value="'.__('Checkout', 'usces').'"' . apply_filters('usces_filter_confirm_nextbutton', NULL) . $purchase_disabled . ' /></div>
			<input type="hidden" name="username_card" value="' . esc_attr($_POST['username_card']) . '">
			<input type="hidden" name="_nonce" value="'.wp_create_nonce( $acting_flag ).'">' . "\n";

		return $html;

	}

	/**********************************************
	* usces_action_admin_settlement_update
	* 決済オプション登録・更新
	* @param  -
	* @return -
	***********************************************/
	public function data_update(){
		global $usces;
		
		if( 'escott' != $_POST['acting'])
			return;
	
		$this->error_mes = '';
		$options = get_option('usces');

		unset( $options['acting_settings']['escott'] );
		$options['acting_settings']['escott']['merchant_id'] = isset($_POST['merchant_id']) ? $_POST['merchant_id'] : '';
		$options['acting_settings']['escott']['merchant_pass'] = isset($_POST['merchant_pass']) ? $_POST['merchant_pass'] : '';
		$options['acting_settings']['escott']['tenant_id'] = isset($_POST['tenant_id']) ? $_POST['tenant_id'] : '';
		$options['acting_settings']['escott']['ope'] = isset($_POST['ope']) ? $_POST['ope'] : '';
		$options['acting_settings']['escott']['card_activate'] = isset($_POST['card_activate']) ? $_POST['card_activate'] : '';
		$options['acting_settings']['escott']['operateid'] = isset($_POST['operateid']) ? $_POST['operateid'] : '1Auth';
		$options['acting_settings']['escott']['quickpay'] = isset($_POST['quickpay']) ? $_POST['quickpay'] : '';
		$options['acting_settings']['escott']['paytype'] = isset($_POST['paytype']) ? $_POST['paytype'] : '';
//		$options['acting_settings']['escott']['continuation'] = isset($_POST['continuation']) ? $_POST['continuation'] : '';
		$options['acting_settings']['escott']['conv_activate'] = isset($_POST['conv_activate']) ? $_POST['conv_activate'] : '';
		$options['acting_settings']['escott']['conv_limit'] = !empty($_POST['conv_limit']) ? $_POST['conv_limit'] : '7';
		$options['acting_settings']['escott']['payeasy_activate'] = isset($_POST['payeasy_activate']) ? $_POST['payeasy_activate'] : '';


		if( WCUtils::is_blank($_POST['merchant_id']) )
			$this->error_mes .= '※マーチャントIDを入力して下さい<br />';
		if( WCUtils::is_blank($_POST['merchant_pass']) )
			$this->error_mes .= '※マーチャントパスワードを入力して下さい<br />';
		if( WCUtils::is_blank($_POST['tenant_id']) )
			$this->error_mes .= '※店舗コードを入力して下さい<br />';

		if( WCUtils::is_blank($this->error_mes) ){
			$usces->action_status = 'success';
			$usces->action_message = __('options are updated','usces');
			$options['acting_settings']['escott']['activate'] = 'on';
			if( isset($_POST['ope']) && 'public' == $_POST['ope'] ) {
				$options['acting_settings']['escott']['send_url'] = 'https://www.e-scott.jp/online/aut/OAUT002.do';
				$options['acting_settings']['escott']['send_url_member'] = 'https://www.e-scott.jp/online/crp/OCRP005.do';
				$options['acting_settings']['escott']['send_url_conv'] = 'https://www.e-scott.jp/online/cnv/OCNV005.do';
				$options['acting_settings']['escott']['redirect_url_conv'] = 'https://link.kessai.info/JLP/JLPcon';
			}else{
				$options['acting_settings']['escott']['send_url'] = 'https://www.test.e-scott.jp/online/aut/OAUT002.do';
				$options['acting_settings']['escott']['send_url_member'] = 'https://www.test.e-scott.jp/online/crp/OCRP005.do';
				$options['acting_settings']['escott']['send_url_conv'] = 'https://www.test.e-scott.jp/online/cnv/OCNV005.do';
				$options['acting_settings']['escott']['redirect_url_conv'] = 'https://link.kessai.info/JLPCT/JLPcon';
			}
			if( 'on' == $options['acting_settings']['escott']['card_activate'] ){
				$usces->payment_structure['acting_escott_card'] = 'カード決済（e-SCOTT Smart）';
			}else{
				unset($usces->payment_structure['acting_escott_card']);
			}
			if( 'on' == $options['acting_settings']['escott']['conv_activate'] ){
				$usces->payment_structure['acting_escott_conv'] = 'オンライン収納代行（e-SCOTT Smart）';
			}else{
				unset($usces->payment_structure['acting_escott_conv']);
			}
		}else{
			$usces->action_status = 'error';
			$usces->action_message = __('Data have deficiency.','usces');
			$options['acting_settings']['escott']['activate'] = 'off';
			unset($usces->payment_structure['acting_escott_card']);
			unset($usces->payment_structure['acting_escott_conv']);
		}
		ksort($usces->payment_structure);
		update_option('usces', $options);
		update_option('usces_payment_structure', $usces->payment_structure);
	}

	/**********************************************
	* usces_action_settlement_tab_title
	* クレジット決済設定画面タブ追加
	* @param  -
	* @return -
	* @echo   str
	***********************************************/
	public function tab_title(){
		echo '<li><a href="#uscestabs_escott">e-SCOTT Smart</a></li>';
	}

	/**********************************************
	* usces_action_settlement_tab_body
	* クレジット決済設定画面フォーム追加
	* @param  -
	* @return -
	* @echo   str
	***********************************************/
	public function tab_body(){
		global $usces;
		$opts = $usces->options['acting_settings'];
?>
	<div id="uscestabs_escott">
	<div class="settlement_service"><span class="service_title">e-SCOTT Smart　ソニーペイメント</span></div>

	<?php if( isset($_POST['acting']) && 'escott' == $_POST['acting'] ){ ?>
		<?php if( '' != $this->error_mes ){ ?>
		<div class="error_message"><?php echo $this->error_mes; ?></div>
		<?php }else if( isset($opts['escott']['activate']) && 'on' == $opts['escott']['activate'] ){ ?>
		<div class="message">十分にテストを行ってから運用してください。</div>
		<?php } ?>
	<?php } ?>
	<form action="" method="post" name="escott_form" id="escott_form">
		<table class="settle_table">
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_merchant_id_escott');">マーチャントID</a></th>
				<td colspan="6"><input name="merchant_id" type="text" id="merchant_id_escott" value="<?php echo esc_html(isset($opts['escott']['merchant_id']) ? $opts['escott']['merchant_id'] : ''); ?>" size="20"  /></td>
				<td><div id="ex_merchant_id_escott" class="explanation"><?php _e('契約時にスマートリンクネットワークから発行されるマーチャントID（半角数字）', 'usces'); ?></div></td>
			</tr>
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_merchant_pass_escott');">マーチャントパスワード</a></th>
				<td colspan="6"><input name="merchant_pass" type="password" id="merchant_pass_escott" value="<?php echo esc_html(isset($opts['escott']['merchant_pass']) ? $opts['escott']['merchant_pass'] : ''); ?>" size="20"  /></td>
				<td><div id="ex_merchant_pass_escott" class="explanation"><?php _e('契約時にスマートリンクネットワークから発行されるサービスパスワード（半角数字）', 'usces'); ?></div></td>
			</tr>
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_tenant_id_escott');">店舗コード</a></th>
				<td colspan="6"><input name="tenant_id" type="text" id="tenant_id_escott" value="<?php echo esc_html(isset($opts['escott']['tenant_id']) ? $opts['escott']['tenant_id'] : ''); ?>" size="20"  /></td>
				<td><div id="ex_tenant_id_escott" class="explanation"><?php _e('契約時にスマートリンクネットワークから発行される店舗コード', 'usces'); ?></div></td>
			</tr>
			<tr>
				<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_ope_escott');"><?php _e('Operation Environment', 'usces'); ?></a></th>
				<td><input name="ope" type="radio" id="ope_escott_2" value="test"<?php if( isset($opts['escott']['ope']) && $opts['escott']['ope'] == 'test' ) echo ' checked="checked"'; ?> /></td><td><label for="ope_escott_2">テスト環境</label></td>
				<td><input name="ope" type="radio" id="ope_escott_3" value="public"<?php if( isset($opts['escott']['ope']) && $opts['escott']['ope'] == 'public' ) echo ' checked="checked"'; ?> /></td><td><label for="ope_escott_3">本番環境</label></td>
				<td><div id="ex_ope_escott" class="explanation"><?php _e('動作環境を切り替えます。', 'usces'); ?></div></td>
			</tr>
		</table>
		<table class="settle_table">
			<tr>
				<th>クレジットカード決済</th>
				<td><input name="card_activate" type="radio" id="card_activate_escott_1" value="on"<?php if( isset($opts['escott']['card_activate']) && $opts['escott']['card_activate'] == 'on' ) echo ' checked="checked"'; ?> /></td><td><label for="card_activate_escott_1">利用する</label></td>
				<td><input name="card_activate" type="radio" id="card_activate_escott_2" value="off"<?php if( isset($opts['escott']['card_activate']) && $opts['escott']['card_activate'] == 'off' ) echo ' checked="checked"'; ?> /></td><td><label for="card_activate_escott_2">利用しない</label></td>
				<td></td>
			</tr>
			<tr>
				<th>処理区分</th>
				<td><input name="operateid" type="radio" id="operateid_escott_1" value="1Auth"<?php if( isset($opts['escott']['operateid']) && $opts['escott']['operateid'] == '1Auth' ) echo ' checked="checked"'; ?> /></td><td><label for="operateid_escott_1">与信</label></td>
				<td><input name="operateid" type="radio" id="operateid_escott_2" value="1Gathering"<?php if( isset($opts['escott']['operateid']) && $opts['escott']['operateid'] == '1Gathering' ) echo ' checked="checked"'; ?> /></td><td><label for="operateid_escott_2">与信売上計上</label></td>
				<td></td>
			</tr>
			<tr>
				<th>クイック決済</th>
				<td><input name="quickpay" type="radio" id="quickpay_escott_1" value="on"<?php if( isset($opts['escott']['quickpay']) && $opts['escott']['quickpay'] == 'on' ) echo ' checked="checked"'; ?> /></td><td><label for="quickpay_escott_1">利用する</label></td>
				<td><input name="quickpay" type="radio" id="quickpay_escott_2" value="off"<?php if( isset($opts['escott']['quickpay']) && $opts['escott']['quickpay'] == 'off' ) echo ' checked="checked"'; ?> /></td><td><label for="quickpay_escott_2">利用しない</label></td>
				<td></td>
			</tr>
		</table>
		<table class="settle_table">
			<tr>
				<th>オンライン収納代行</th>
				<td><input name="conv_activate" type="radio" id="conv_activate_escott_1" value="on"<?php if( isset($opts['escott']['conv_activate']) && $opts['escott']['conv_activate'] == 'on' ) echo ' checked="checked"'; ?> /></td><td><label for="conv_activate_escott_1">利用する</label></td>
				<td><input name="conv_activate" type="radio" id="conv_activate_escott_2" value="off"<?php if( isset($opts['escott']['conv_activate']) && $opts['escott']['conv_activate'] == 'off' ) echo ' checked="checked"'; ?> /></td><td><label for="conv_activate_escott_2">利用しない</label></td>
				<td></td>
			</tr>
			<tr>
				<th>支払制限日数</th>
				<td colspan="3"><input name="conv_limit" type="test" id="conv_limit" value="<?php esc_html_e(isset($opts['escott']['conv_limit']) ? $opts['escott']['conv_limit'] : '7'); ?>" />日</td>
				<td></td>
			</tr>
		</table>
		<input name="send_url_test" type="hidden" value="https://www.test.e-scott.jp/online/aut/OAUT002.do" />
		<input name="acting" type="hidden" value="escott" />
		<input name="usces_option_update" type="submit" class="button" value="e-SCOTT Smartの設定を更新する" />
		<?php wp_nonce_field( 'admin_settlement', 'wc_nonce' ); ?>
	</form>
	<div class="settle_exp">
		<p><strong>e-SCOTT Smart　ソニーペイメント</strong></p>
		<a href="http://www.welcart.com/wc-settlement/escott_guide/" target="_blank">e-SCOTT Smartの詳細はこちら 》</a>
		<p>　</p>
		<p>この決済は「埋め込み型」の決済システムです。</p>
		<p>「埋め込み型」とは、決済会社のページへは遷移せず、Welcart のページのみで完結する決済システムです。<br />
デザインの統一されたスタイリッシュな決済が可能です。但し、カード番号を扱いますので専用SSLが必須となります。</p>
		<p>カード番号はe-SCOTT Smart のシステムに送信されるだけで、Welcart に記録は残しません。</p>
		<!--<p>「簡易継続課金」を利用するには「DL Seller」拡張プラグインのインストールが必要です。</p>-->
		<p>尚、本番環境では、正規SSL証明書のみでのSSL通信となりますのでご注意ください。</p>
	</div>
	</div><!--uscestabs_escott-->
<?php
	}

	/**********************************************
	* response_message
	* エラーコード対応メッセージ
	* @param  $code
	* @return str $mes
	***********************************************/
	public function response_message( $code ){
		switch( $code ){
			case 'K40':
			case 'K82':
			case 'K83':
			case 'C13':
			case 'C16':
			case 'G65':
			case 'C17':
			case 'G12':
			case 'G44':
			case 'G45':
				$mes = '正しいカード番号を入れて下さい';
				break;
			case 'C15':
				$mes = 'ボーナス金額下限エラー';
				break;
			case 'G54':
				$mes = '1日利用回数または金額オーバです';
				break;
			case 'G55':
				$mes = '1日利用限度額オーバです';
				break;
			case 'G56':
			case 'G60':
			case 'G61':
			case 'G96':
				$mes = '無効カード';
				break;
			case 'G68':
				$mes = '金額エラー';
				break;
			case 'G72':
				$mes = 'ボーナス額エラー';
				break;
			case 'G74':
				$mes = '分割回数エラー';
				break;
			case 'G75':
				$mes = '分割金額エラー';
				break;
			case 'G78':
				$mes = '支払区分エラー';
				break;
			case 'G83':
				$mes = '有効期限エラー';
				break;
			default:
				$mes = '';
		}
		return $mes;
	}
}

/**************************************************************************************/
//クラス定義 : SLNConnection 

class SLNConnection 
{ 
	//  プロパティ定義 
	// 接続先URLアドレス 
	private $connection_url; 

	// 通信タイムアウト 
	private $connection_timeout; 

	// メソッド定義 
	// コンストラクタ 
	// 引数： なし 
	// 戻り値： なし 
	function __construct() 
	{ 
		// プロパティ初期化 
		$this->connection_url = ""; 
		$this->connection_timeout = 600; 
	} 

	// 接続先URLアドレスの設定 
	// 引数： 接続先URLアドレス 
	// 戻り値： なし 
	function set_connection_url( $connection_url = "" ) 
	{ 
		$this->connection_url = $connection_url; 
	} 

	// 接続先URLアドレスの取得 
	// 引数： なし 
	// 戻り値： 接続先URLアドレス 
	function get_connection_url() 
	{ 
		return $this->connection_url; 
	} 

	// 通信タイムアウト時間（s）の設定 
	// 引数： 通信タイムアウト時間（s） 
	// 戻り値： なし 
	function set_connection_timeout( $connection_timeout = 0 ) 
	{ 
		$this->connection_timeout = $connection_timeout; 
	} 

	// 通信タイムアウト時間（s）の取得 
	// 引数： なし 
	// 戻り値： 通信タイムアウト時間（s） 
	function get_connection_timeout() 
	{ 
		return $this->connection_timeout; 
	} 

	// リクエスト送信クラス 
	// 引数： リクエストパラメータ（要求電文）配列 
	// 戻り値： レスポンスパラメータ（応答電文）配列 
	function send_request( &$param_list = array() ) 
	{ 
		$rValue = array(); 
		// パラメータチェック 
		if( empty($param_list) === false ){ 
			// 送信先情報の準備 
			$url = parse_url( $this->connection_url ); 

			// HTTPデータ生成 
			$http_data = ""; 
			reset( $param_list ); 
			while( list($key,$value) = each($param_list) ){ 
				$http_data .= (($http_data!=="") ? "&" : "") . $key . "=" . $value; 
			} 

			// HTTPヘッダ生成 
			$http_header = "POST " . $url['path'] . " HTTP/1.1" . "\r\n" .  
			"Host: " . $url['host'] . "\r\n" .  
			"User-Agent:SLN_PAYMENT_CLIENT_PG_PHP_VERSION_1_0" . "\r\n" .  
			"Content-Type:application/x-www-form-urlencoded" . "\r\n" .  
			"Content-Length:" . strlen($http_data) . "\r\n" .  
			"Connection: close";

			// POSTデータ生成 
			$http_post = $http_header . "\r\n\r\n" . $http_data; 

			// 送信処理 
			$errno = 0; 
			$errstr = ""; 
			$hm = array(); 

			// ソケット通信接続 
			$fp = fsockopen( 'ssl://'.$url['host'], 443, $errno, $errstr, $this->connection_timeout ); 
			if( $fp !== false ){ 

				// 接続後タイムアウト設定 
				$result = socket_set_timeout( $fp, $this->connection_timeout ); 
				if( $result === true ){ 
					// データ送信 
					fwrite( $fp, $http_post ); 
					// 応答受信 
					$response_data = ""; 
					while( !feof($fp) ){ 
						$response_data .= fgets( $fp, 4096 ); 
					} 

					// ソケット通信情報を取得 
					$hm = stream_get_meta_data( $fp ); 
					// ソケット通信切断 
					$result = fclose( $fp ); 
					if( $result === true ){ 
						if( $hm['timed_out'] !== true ){ 
							// レスポンスデータ生成 
							$rValue = $response_data ; 
						}else{ 
							// エラー： タイムアウト発生 
							throw new SLNConnectionError( "通信中にタイムアウトが発生しました" ); 
						} 
					}else{ 
						// エラー： ソケット通信切断失敗 
						throw new SLNConnectionError( "SLNとの切断に失敗しました" ); 
					} 
				}else{ 
					// エラー： タイムアウト設定失敗 
					throw new SLNConnectionError( "タイムアウト設定に失敗しました" ); 
				} 
			}else{ 
				// エラー： ソケット通信接続失敗 
				throw new SLNConnectionError( "SLNへの接続に失敗しました" ); 
			} 
		}else{ 
			// エラー： パラメータ不整合 
			throw new SLNConnectionError( "リクエストパラメータの指定が正しくありません" ); 
		} 
		return $rValue; 
	} 
} 

// クラス定義: SLNConnectionError 
class SLNConnectionError extends exception 
{ 
	// コンストラクタ 
	function __construct( $_error ){ 
		$this->error = $_error; 
	} 

		// 例外結果取得 
	function get_exception(){ 
		return $this->error; 
	} 
} 

