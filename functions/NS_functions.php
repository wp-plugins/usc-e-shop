<?php

class NS_SetPage
{
	var $action;
	var $product;
	var $body_caption;
	var $posts_per_page;
	
	function __construct(){
		$this->set_action();
	}

	function set_action(){
		if( isset($_POST['head_list']) )
			$this->action = 'head_list';
		elseif( isset($_POST['head_detail']) )
			$this->action = 'head_detail';
		elseif( isset($_POST['shuft_list']) )
			$this->action = 'shuft_list';
		elseif( isset($_POST['shuft_detail']) )
			$this->action = 'shuft_detail';
		elseif( isset($_POST['grip_list']) )
			$this->action = 'grip_list';
		elseif( isset($_POST['grip_detail']) )
			$this->action = 'grip_detail';
//		elseif( isset($_POST['select_item']) )
//			$this->action = 'select_item';
//		elseif( isset($_POST['enter_item']) )
//			$this->action = 'enter_item';
		elseif( isset($_POST['decide']) )
			$this->action = 'decide';
		else
			$this->action = 'default';
	}
	
	function set_data(){
		$this->get_session();

		switch( $this->action ){
			case 'head_list':
				$this->body_caption = 'ヘッド一覧';

				break;
			case 'head_detail':
				$this->body_caption = 'ヘッド詳細';
				//$this->product['head']['post_id'] = isset($_POST['selected_post_id']) ? (int)$_POST['selected_post_id'] : NULL;
				if(isset($_POST['selected_post_id'])) {
					if($_POST['selected_post_id'] != $this->product['head']['post_id']) {
						$this->product['head']['post_id'] = (int)$_POST['selected_post_id'];
						$this->product['head']['sku'] = NULL;
						$this->product['head']['price'] = NULL;
						$this->product['head']['options'] = NULL;
						$this->init_session( 'shuft' );
						$this->init_session( 'grip' );
					}
				}

				break;
			case 'shuft_list':
				$this->body_caption = 'シャフト一覧';
				if( isset($_POST['head_post_id']) ) {
					$this->product['head']['post_id'] = (int)$_POST['head_post_id'];
					$this->product['head']['sku'] = $_POST['head_sku'];
					$this->product['head']['price'] = (int)$_POST['head_price'];
					if( -1 == $_POST['head_post_id'] ){
						$this->product['head']['options'] = $_POST['mochiopt'];
					}else{
						$this->product['head']['options'] = $_POST['head_options'];
					}
				}

				break;
			case 'shuft_detail':
				$this->body_caption = 'シャフト詳細';
				//$this->product['shuft']['post_id'] = isset($_POST['selected_post_id']) ? (int)$_POST['selected_post_id'] : NULL;
				if(isset($_POST['selected_post_id'])) {
					if($_POST['selected_post_id'] != $this->product['shuft']['post_id']) {
						$this->product['shuft']['post_id'] = (int)$_POST['selected_post_id'];
						$this->product['shuft']['sku'] = NULL;
						$this->product['shuft']['price'] = NULL;
						$this->product['shuft']['options'] = NULL;
						$this->init_session( 'grip' );
					}
				}

				break;
			case 'grip_list':
				$this->body_caption = 'グリップ一覧';
				if( isset($_POST['shuft_post_id']) ) {
					$this->product['shuft']['post_id'] = (int)$_POST['shuft_post_id'];
					$this->product['shuft']['sku'] = $_POST['shuft_sku'];
					$this->product['shuft']['price'] = (int)$_POST['shuft_price'];
					if( -2 == $_POST['shuft_post_id'] ){
						$this->product['shuft']['options'] = $_POST['mochiopt'];
					}else{
						$this->product['shuft']['options'] = $_POST['shuft_options'];
					}
				}

				break;
			case 'grip_detail':
				$this->body_caption = 'グリップ詳細';
				//$this->product['grip']['post_id'] = isset($_POST['selected_post_id']) ? (int)$_POST['selected_post_id'] : NULL;
				if(isset($_POST['selected_post_id'])) {
					if($_POST['selected_post_id'] != $this->product['grip']['post_id']) {
						$this->product['grip']['post_id'] = (int)$_POST['selected_post_id'];
						$this->product['grip']['sku'] = NULL;
						$this->product['grip']['price'] = NULL;
						$this->product['grip']['options'] = NULL;
					}
				}

				break;
//			case 'select_item':
//				$this->body_caption = '商品詳細';
//			
//				break;
//			case 'enter_item':
//			
//				break;
			case 'decide':
				$this->body_caption = 'ヘッド一覧';
				if( isset($_POST['grip_post_id']) ) {
					$this->product['grip']['post_id'] = (int)$_POST['grip_post_id'];
					$this->product['grip']['sku'] = $_POST['grip_sku'];
					$this->product['grip']['price'] = (int)$_POST['grip_price'];
					if( -3 == $_POST['grip_post_id'] ){
						$this->product['grip']['options'] = $_POST['mochiopt'];
					}else{
						$this->product['grip']['options'] = $_POST['grip_options'];
					}
				}

				break;
			default:
				$this->body_caption = 'ヘッド一覧';
		}
		$this->set_session();
	}

	function init_session( $type ){
//		foreach($ses as $val){
//			if( is_array($val) ){
//				$this->init_session( $val );
//			}else{
//				$val = NULL;
//			}
//		}
		$this->product[$type]['post_id'] = NULL;
		$this->product[$type]['sku'] = NULL;
		$this->product[$type]['price'] = NULL;
		$this->product[$type]['options'] = NULL;
	}

	function get_session(){
		
		if( !isset($_SESSION['nsset']['product']['head']['post_id']) )
			$_SESSION['nsset']['product']['head']['post_id'] = NULL;
		if( !isset($_SESSION['nsset']['product']['head']['sku']) )
			$_SESSION['nsset']['product']['head']['sku'] = NULL;
		if( !isset($_SESSION['nsset']['product']['head']['price']) )
			$_SESSION['nsset']['product']['head']['price'] = NULL;
		if( !isset($_SESSION['nsset']['product']['head']['options']) )
			$_SESSION['nsset']['product']['head']['options'] = NULL;
		if( !isset($_SESSION['nsset']['product']['shuft']['post_id']) )
			$_SESSION['nsset']['product']['shuft']['post_id'] = NULL;
		if( !isset($_SESSION['nsset']['product']['shuft']['sku']) )
			$_SESSION['nsset']['product']['shuft']['sku'] = NULL;
		if( !isset($_SESSION['nsset']['product']['shuft']['price']) )
			$_SESSION['nsset']['product']['shuft']['price'] = NULL;
		if( !isset($_SESSION['nsset']['product']['shuft']['options']) )
			$_SESSION['nsset']['product']['shuft']['options'] = NULL;
		if( !isset($_SESSION['nsset']['product']['grip']['post_id']) )
			$_SESSION['nsset']['product']['grip']['post_id'] = NULL;
		if( !isset($_SESSION['nsset']['product']['grip']['sku']) )
			$_SESSION['nsset']['product']['grip']['sku'] = NULL;
		if( !isset($_SESSION['nsset']['product']['grip']['price']) )
			$_SESSION['nsset']['product']['grip']['price'] = NULL;
		if( !isset($_SESSION['nsset']['product']['grip']['options']) )
			$_SESSION['nsset']['product']['grip']['options'] = NULL;

		$this->product = $_SESSION['nsset']['product'];
	}

	function set_session(){

		$_SESSION['nsset']['product'] = $this->product;

	}

	function set_list_per_page( $per_page ){
		$this->posts_per_page = $per_page;
	}

	function get_list_query(){
		$cat_item = array();
		switch( $this->action ){
			case 'shuft_list':
				if ( in_category( 'setdriverhead', (int)$this->product['head']['post_id'] ) || ( -1 == $this->product['head']['post_id'] && 'setdriverhead' == $this->product['head']['options']['genre']) ) {
					$cat_item[] = (int)usces_get_cat_id( 'setdrivershuft' );
					$cat_item[] = (int)usces_get_cat_id( 'setdriverfairwayshuft' );
				} elseif ( in_category( 'setfairwayhead', (int)$this->product['head']['post_id'] ) || ( -1 == $this->product['head']['post_id'] && 'setfairwayhead' == $this->product['head']['options']['genre']) ) {
					$cat_item[] = (int)usces_get_cat_id( 'setfairwayshuft' );
					$cat_item[] = (int)usces_get_cat_id( 'setdriverfairwayshuft' );
				} elseif ( in_category( 'setironhead', (int)$this->product['head']['post_id'] ) || ( -1 == $this->product['head']['post_id'] && 'setironhead' == $this->product['head']['options']['genre']) ) {
					$cat_item[] = (int)usces_get_cat_id( 'setironshuft' );
				} elseif ( in_category( 'setutilityhead', (int)$this->product['head']['post_id'] ) || ( -1 == $this->product['head']['post_id'] && 'setutilityhead' == $this->product['head']['options']['genre']) ) {
					$cat_item[] = (int)usces_get_cat_id( 'setutilityshuft' );
				} elseif ( in_category( 'setwedgehead', (int)$this->product['head']['post_id'] ) || ( -1 == $this->product['head']['post_id'] && 'setwedgehead' == $this->product['head']['options']['genre']) ) {
					$cat_item[] = (int)usces_get_cat_id( 'setwedgeshuft' );
				} elseif ( in_category( 'setputterhead', (int)$this->product['head']['post_id'] ) || ( -1 == $this->product['head']['post_id'] && 'setputterhead' == $this->product['head']['options']['genre']) ) {
					$cat_item[] = (int)usces_get_cat_id( 'setputtershuft' );
				}
				
				break;
			case 'grip_list':
				if ( in_category( 'setdrivershuft', (int)$this->product['shuft']['post_id'] ) 
					|| in_category( 'setfairwayshuft', (int)$this->product['shuft']['post_id'] ) 
					|| in_category( 'setdriverfairwayshuft', (int)$this->product['shuft']['post_id'] ) 
					|| in_category( 'setutilityshuft', (int)$this->product['shuft']['post_id'] ) 
					|| in_category( 'setwedgeshuft', (int)$this->product['shuft']['post_id'] ) 
					|| ( -2 == $this->product['shuft']['post_id'] && in_array($this->product['shuft']['options']['genre'], array('setdrivershuft', 'setfairwayshuft', 'setdriverfairwayshuft', 'setutilityshuft', 'setwedgeshuft', )))
					){
					$cat_item[] = (int)usces_get_cat_id( 'setwoodirongrip' );
				} elseif ( in_category( 'setputtershuft', (int)$this->product['shuft']['post_id'] ) || ( -2 == $this->product['shuft']['post_id'] && 'setputtershuft' == $this->product['shuft']['options']['genre']) ){
					$cat_item[] = (int)usces_get_cat_id( 'setputtergrip' );
				}

				break;
			case 'decide':
			case 'head_list':
			default:
				$cat_item[] = (int)usces_get_cat_id( 'sethead' );
		}
		if( empty($cat_item) ){
			$category__in = array(99999);
		}else{
			$category__in = $cat_item;
		}
		$page = get_query_var( 'page' );
		$paged = empty($page) ? 1 : $page;
		$offset = $this->posts_per_page * ($paged - 1);
		
		$query = array(
			'category__in'		=> $category__in,
			'posts_per_page'	=> $this->posts_per_page, 
			'paged'				=> $paged, 
			'offset'			=> $offset, 
			'post_status'		=> 'publish'
		);
		return $query;
	}
	
	function get_top_info_class( $type ){
		$res = !empty($this->product[$type]['post_id']) ? 'gray' : 'white';
		return $res;
	}
	
	function get_top_name_class( $type ){
		$focus = $this->get_top_focus();
		switch( $type ){
			case 'head':
				$res = 'focused';
				break;
			case 'shuft':
				$res = ( 'shuft' == $focus || 'grip' == $focus || 'amount' == $focus ) ? 'focused' : '';
				break;
			case 'grip':
				$res = ( 'grip' == $focus || 'amount' == $focus ) ? 'focused' : '';
				break;
		}
		return $res;
	}
	
	function get_top_thumb( $type ){
		$res = !empty($this->product[$type]['post_id']) ? usces_get_itemImage( $this->product[$type]['post_id'], 0, 60, 60) : '';
		return $res;
	}

	function get_top_itemname( $type ){
		global $usces;
		$res = !empty($this->product[$type]['post_id']) ? $usces->getItemName($this->product[$type]['post_id']) : '選択してください';
		return $res;
	}

	function get_top_itemprice( $type ){
		if( empty($this->product[$type]['post_id']) || empty($this->product[$type]['sku']) )
			return;

		global $usces;
		$res = usces_get_item_price($this->product[$type]['post_id'], $this->product[$type]['sku']);
		return $usces->get_currency($res, true, false, true);
	}

	function get_top_itemcprice( $type ){
		if( empty($this->product[$type]['post_id']) || empty($this->product[$type]['sku']) )
			return;

		global $usces;
		$res = usces_get_item_cprice($this->product[$type]['post_id'], $this->product[$type]['sku']);
		return $usces->get_currency($res, true, false, true);
	}

	function get_top_change_button( $type ){
		$focus = $this->get_top_focus();
		switch( $type ){
			case 'head':
				$res = '<input name="head_list" type="submit" class="change_button" value="　" />';
				break;
			case 'shuft':
				//if( 'shuft' == $focus || 'grip' == $focus || 'amount' == $focus ){
				if( ('shuft' == $focus || 'grip' == $focus || 'amount' == $focus) and !empty($this->product['head']['sku']) ){
					$res = '<input name="shuft_list" type="submit" class="change_button" value="　" />';
				}else{
					$res = '<input name="shuft_list" type="button" class="change_button_dis" value="　" disabled="disabled" />';
				}
				break;
			case 'grip':
				//if( 'grip' == $focus || 'amount' == $focus ){
				if( ('grip' == $focus || 'amount' == $focus) and !empty($this->product['head']['sku']) and !empty($this->product['shuft']['sku']) ){
					$res = '<input name="grip_list" type="submit" class="change_button" value="　" />';
				}else{
					$res = '<input name="grip_list" type="button" class="change_button_dis" value="　" disabled="disabled" />';
				}
				break;
		}
		return $res;
	}

	function get_top_detail_button( $type ){
		$focus = $this->get_top_focus();
		switch( $type ){
			case 'head':
				//$res = '<input name="head_detail" type="submit" class="detail_button" value="　" />';
				if( !empty($this->product['head']['sku']) ){
					$res = '<input name="head_detail" type="submit" class="detail_button" value="　" />';
				}else{
					$res = '<input name="head_detail" type="button" class="detail_button_dis" value="　" disabled="disabled" />';
				}
				break;
			case 'shuft':
				//if( 'shuft' == $focus || 'grip' == $focus || 'amount' == $focus ){
				if( ('shuft' == $focus || 'grip' == $focus || 'amount' == $focus) and !empty($this->product['shuft']['sku']) ){
					$res = '<input name="shuft_detail" type="submit" class="detail_button" value="　" />';
				}else{
					$res = '<input name="shuft_detail" type="button" class="detail_button_dis" value="　" disabled="disabled" />';
				}
				break;
			case 'grip':
				//if( 'grip' == $focus || 'amount' == $focus ){
				if( ('grip' == $focus || 'amount' == $focus) and !empty($this->product['grip']['sku']) ) {
					$res = '<input name="grip_detail" type="submit" class="detail_button" value="　" />';
				}else{
					$res = '<input name="grip_detail" type="button" class="detail_button_dis" value="　" disabled="disabled" />';
				}
				break;
		}
		return $res;
	}

	function get_select_button_name() {
		switch( $this->action ) {
		case 'shuft_list':
			$res = 'shuft_detail';
			break;
		case 'grip_list':
			$res = 'grip_detail';
			break;
		case 'head_list':
		case 'default':
			$res = 'head_detail';
		}
		return $res;
	}

	function get_product_amount(){
		
	}

	function get_top_amount_mes(){
		global $usces;
		$focus = $this->get_top_focus();
		//$res = ( 'amount' == $focus ) ? 'kouchin' : '※パーツ構成が確定していません。';
		if( 'amount' == $focus and !empty($this->product['head']['sku']) and !empty($this->product['shuft']['sku']) and !empty($this->product['grip']['sku']) ){
			$post_id = $usces->get_postIDbyCode( NS_ITEM_SET );
			$sku = (in_category( 'straightbore', $this->product['head']['post_id'] )) ? 'straightbore' : 'normal';
			$set_price = usces_get_item_price($post_id, $sku);
			$res = usces_crform($this->product['head']['price'] + $this->product['shuft']['price'] + $this->product['grip']['price'] + $set_price, true, false, 'return');
		} else {
			$res = '※パーツ構成が確定していません。';
		}
		return $res;
	}
	
	function get_top_amount_button(){
		global $usces;
		$focus = $this->get_top_focus();
		//if( 'amount' == $focus ){
		if( 'amount' == $focus and !empty($this->product['head']['sku']) and !empty($this->product['shuft']['sku']) and !empty($this->product['grip']['sku']) ){
			$post_id = $usces->get_postIDbyCode( NS_ITEM_SET );
			//$skus = $usces->get_skus($post_id);
			$sku = (in_category( 'straightbore', $this->product['head']['post_id'] )) ? 'straightbore' : 'normal';
			$set_price = usces_get_item_price($post_id, $sku);
			$price = $this->product['head']['price'] + $this->product['shuft']['price'] + $this->product['grip']['price'] + $set_price;
			$res  = '<form action="'.USCES_CART_URL.'" method="post">';
			$res .= '<input name="decide" type="submit" class="decide_button" value="　" />';
			$res .= '<input name="inCart['.$post_id.']['.$sku.']" type="hidden" value="　" />';
			$res .= '<input name="skuPrice['.$post_id.']['.$sku.']" type="hidden" value="'.$price.'" />';
			$res .= '<input name="itemOption['.$post_id.']['.$sku.'][set_head]" type="hidden" value="'.$this->product['head']['post_id'].'" />';
			$res .= '<input name="itemOption['.$post_id.']['.$sku.'][set_head_sku]" type="hidden" value="'.$this->product['head']['sku'].'" />';
			$res .= '<input name="itemOption['.$post_id.']['.$sku.'][set_shuft]" type="hidden" value="'.$this->product['shuft']['post_id'].'" />';
			$res .= '<input name="itemOption['.$post_id.']['.$sku.'][set_shuft_sku]" type="hidden" value="'.$this->product['shuft']['sku'].'" />';
			$res .= '<input name="itemOption['.$post_id.']['.$sku.'][set_grip]" type="hidden" value="'.$this->product['grip']['post_id'].'" />';
			$res .= '<input name="itemOption['.$post_id.']['.$sku.'][set_grip_sku]" type="hidden" value="'.$this->product['grip']['sku'].'" />';
			foreach((array)$this->product['head']['options'] as $key => $value) {
				$res .= '<input name="advance['.$post_id.']['.$sku.'][set_head_options]['.$key.']" type="hidden" value="'.$value.'" />';
			}
			foreach((array)$this->product['shuft']['options'] as $key => $value) {
				$res .= '<input name="advance['.$post_id.']['.$sku.'][set_shuft_options]['.$key.']" type="hidden" value="'.$value.'" />';
			}
			foreach((array)$this->product['grip']['options'] as $key => $value) {
				$res .= '<input name="advance['.$post_id.']['.$sku.'][set_grip_options]['.$key.']" type="hidden" value="'.$value.'" />';
			}
			$res .= '</form>';
		}else{
			$res = '<input name="decide" type="button" class="decide_button_dis" value="　" disabled="disabled" />';
		}
		return $res;
	}

	function get_top_focus(){
		if( empty($this->product['head']['post_id']) && empty($this->product['shuft']['post_id']) && empty($this->product['grip']['post_id'])){
			$res = 'head';
		}elseif( !empty($this->product['head']['post_id']) && empty($this->product['shuft']['post_id'])){
			$res = 'shuft';
		}elseif( !empty($this->product['shuft']['post_id']) && empty($this->product['grip']['post_id'])){
			$res = 'grip';
		}else{
			$res = 'amount';
		}
		return $res;
	}

	function body_caption(){
		return $this->body_caption;
	}

	function view_item_detail(){
		$post_id = '';
		$type = '';
		$next_action = '';

		switch( $this->action ) {
		case 'head_detail':
			$post_id = $this->product['head']['post_id'];
			$type = 'head';
			$next_action = 'shuft_list';
			break;
		case 'shuft_detail':
			$post_id = $this->product['shuft']['post_id'];
			$type = 'shuft';
			$next_action = 'grip_list';
			break;
		case 'grip_detail':
			$post_id = $this->product['grip']['post_id'];
			$type = 'grip';
			$next_action = 'decide';
			break;
		default:
		}
		if($post_id == '') return;

		global $usces;
		$post = new NS_Post();
		$post->set_id( $post_id );

		get_currentuserinfo();

		NS_the_item($post);
		usces_have_skus();

		$ioptkeys = $usces->get_itemOptionKey( $post->ID );
		$mes_opts_str = "";
		$key_opts_str = "";
		$opt_means = "";
		$opt_esse = "";
		if($ioptkeys){
			foreach($ioptkeys as $key => $value){
				$optValues = NS_get_itemOptions( $value, $post->ID );
				if($optValues) {
					if($optValues['means'] < 2){
						$mes_opts_str .= "'" . sprintf(__("Chose the %s", 'usces'), $value) . "',";
					}else{
						$mes_opts_str .= "'" . sprintf(__("Input the %s", 'usces'), $value) . "',";
					}
					$key_opts_str .= "'{$value}',";
					$opt_means .= "'{$optValues['means']}',";
					$opt_esse .= "'{$optValues['essential']}',";
				}
			}
			$mes_opts_str = rtrim($mes_opts_str, ',');
			$key_opts_str = rtrim($key_opts_str, ',');
			$opt_means = rtrim($opt_means, ',');
			$opt_esse = rtrim($opt_esse, ',');
		}
?>
		<script type='text/javascript'>
		var opt_esse = new Array( <?php echo $opt_esse; ?> );
		var opt_means = new Array( <?php echo $opt_means; ?> );
		var mes_opts = new Array( <?php echo $mes_opts_str; ?> );
		var key_opts = new Array( <?php echo $key_opts_str; ?> );

		(function($) {
		uscesCart = {
			intoCart : function (post_id, sku) {
				var mes = '';
				$(':input[name^="opt"]').each(function(i, obj) {
					if($(this).val() == '') {
						name = $(this).attr("name").substring(3);
						mes += name+'を選択してください。'+"\n";
					}
				});
				for(i=0; i<key_opts.length; i++){
					var skuob = document.getElementById("iopt"+key_opts[i]);
					if( opt_esse[i] == '1' ){
						if( opt_means[i] < 2 && skuob.value == '#NONE#' ){
							mes += mes_opts[i]+"\n";
						}else if( opt_means[i] >= 2 && skuob.value == '' ){
							mes += mes_opts[i]+"\n";
						}
					}
				}
				if( mes != '' ){
					alert( mes );
					return false;
				}
				$(':input[name^="iopt"]').each(function(i, obj) {
					name = $(this).attr("name").substring(4);
					$('#sku_option_button').append('<input name="<?php echo $type; ?>_options['+name+']" type="hidden" value="'+$(this).val()+'">');
				});
			},
			
			changeSkuSelect : function(index) {
				var id = '#opt'+index;
				var value = $(id).val();
				if(value == '') return;
				var key = $(id).attr("name");
				$('#sku_option_message').html('<img src="<?php echo USCES_PLUGIN_URL; ?>/images/loading.gif" /> 検索中').addClass('sku_option_status_message').removeClass('sku_option_error_message');

				var skukey = '';
				var skuoption = '';
				var sp = '';
				if(1 < index) {
					$(':input[name^="opt"]').each(function(i, obj) {
						if(i < (index-1)) {
							skukey += sp + $(this).attr("name").substring(3);
							skuoption += sp + $(this).val();
							sp = '#usces#';
						}
					});
				}

				var nextskukey = '';
				var skucnt = $('#skucnt').val();
				if(index < skucnt) {
					nextskukey = $('#opt'+(index+1)).attr("name").substring(3);
				}

				var s = this.settings;
				s.url = "<?php bloginfo('home'); ?>/index.php";
				s.data = "usces_ajax_action=change_sku_option_ajax&post_id=<?php echo $post->ID; ?>&key="+key.substring(3)+"&value="+value+"&index="+index+"&skukey="+skukey+"&skuoption="+skuoption+"&nextskukey="+nextskukey+"&set=1&type=<?php echo $type; ?>&nextaction=<?php echo $next_action; ?>";
				s.success = function(data, dataType) {
					d = data.split('#usces#');
					var sku = (d[0].match(/#ns#/i)) ? d[0].split("#ns#") : d[0];
					var nextval = (d[1].match(/#ns#/i)) ? d[1].split("#ns#") : new Array(d[1]);
					var optkey = (d[2].match(/#ns#/i)) ? d[2].split("#ns#") : new Array(d[2]);
					var optval = (d[3].match(/#ns#/i)) ? d[3].split("#ns#") : new Array(d[3]);
					var skuprice = d[4];
					var zaikonum = d[5];
					var html = d[6];
					var msg = d[7];
					if(nextskukey != '') {
						$(':input[name="opt'+nextskukey+'"]').attr('disabled', false);
						$(':input[name="opt'+nextskukey+'"]').html('');
						var nextopt = '<option value="">選択してください</option>';
						for(i = 0; i < nextval.length; i++) {
							nextopt += '<option value="'+nextval[i]+'">'+nextval[i]+'</option>';
						}
						$(':input[name="opt'+nextskukey+'"]').html(nextopt);
						$(':input[name="opt'+nextskukey+'"]').val(nextval);
						$(':input[name="opt'+nextskukey+'"]').attr('selectedIndex', 0);
					}
					if(0 < optkey.length && optkey[0] != '') {
						for(i = 0; i < optkey.length; i++) {
							if(index < $(':input[name="opt'+optkey[i]+'"]').attr('id').substring(3)) {
								$(':input[name="opt'+optkey[i]+'"]').attr('disabled', false);
								$(':input[name="opt'+optkey[i]+'"]').html('');
								if($(':input[name="opt'+optkey[i]+'"]').attr("class") == 'sku_option_select_field') {
									var opt = '<option value="">選択してください</option><option value="'+optval[i]+'">'+optval[i]+'</option>';
									$(':input[name="opt'+optkey[i]+'"]').html(opt);
								}
							}
							$(':input[name="opt'+optkey[i]+'"]').val(optval[i]);
						}
						$('#sku').val(sku);
						$('#sku_option_price').text(skuprice);
					} else {
						$(':input[name^="opt"]').each(function(i, obj) {
							if(index < i) {
								if($(this).attr("class") == 'sku_option_select_field') {
									$(this).html('<option value="">選択してください</option>');
									$(this).attr('disabled', true);
								}
								$(this).val('');
							}
						});
						$('#sku_option_price').text('');
					}
					$('#sku_option_button').html(html);
					if(msg){
						$('#sku_option_message').html(msg).addClass('sku_option_error_message').removeClass('sku_option_status_message');
					}else if($('#sku_option_price').text()){
						$('#sku_option_message').html('品番 ： ' + sku).removeClass('sku_option_error_message');
					}else{
						$('#sku_option_message').html('').removeClass('sku_option_error_message');
					}
					
				};
				$.ajax( s );
				return false;
			},
			
			settings: {
				type: 'POST',
				cache: false,
				success: function(data, dataType){
					//$("tbody#item-opt-list").html( data );
				}, 
				error: function(msg){
					//$("#ajax-response").html(msg);
				}
			},
			
			isNum : function (num) {
				if (num.match(/[^0-9]/g)) {
					return false;
				}
				return true;
			}

		};
		})(jQuery);
		
		jQuery(document).ready(function($) {
			if(0 < $('#skucnt').val()) {
				$(':input[name^="opt"]').each(function(i, obj) {
					if($(this).attr('class') == 'sku_option_select_field') {
						$(this).attr('selectedIndex', 0);
						$('#opt'+(i+1)).change(function() {
							uscesCart.changeSkuSelect(i+1);
						});
						if(0 < i) $(this).attr('disabled', true);
					} else {
						$(this).val('');
					}
				});
			}
		});
		</script>
		<div id="itempage" class="border_arround clearfix">
			<div class="item_header clearfix">
				<div class="item_info alignleft">
					<div class="item_maker"><?php NS_the_item_maker(); ?></div>
					<h2 class="item_name"><?php usces_the_itemName('', $post); ?></h2>
				</div>
				<div class="item_addition alignright">
					<div class="sale_tag"><?php NS_the_salse_tag(); ?></div>
					<div class="itemstar"><?php NS_the_item_star($post); ?></div>
				</div>
			</div>
			<div class="item_exp_1 clear">
				<?php the_content(); ?>
			</div>
			<div class="itemdetail_left">
				<div class="itemimg border_arround">
					<a href="<?php usces_the_itemImageURL(0, '', $post); ?>" <?php echo apply_filters('usces_itemimg_anchor_rel', NULL); ?>><?php usces_the_itemImage(0, 300, 300, $post); ?></a>
				</div>
			<?php $imageid = NS_get_itemSubImageNums($post); ?>
			<?php if($imageid): $count = 1;?>
				<div class="itemsubimg">
				<ul class="clearfix">
				<?php foreach ( $imageid as $id ) : ?>
					<li class="subimg_<?php echo $id; ?>"><a href="<?php usces_the_itemImageURL($id, $post); ?>" <?php echo apply_filters('usces_itemimg_anchor_rel', NULL); ?>><?php usces_the_itemImage($id, 55, 55, $post); ?></a></li>
					<?php if ($count >= 5) break;?>
					<?php $count += 1; ?>
				<?php endforeach; ?>
				</ul>
				</div><!-- end of itemsubimg -->
			<?php endif; ?>

				<div class="item_country textright">
					<?php NS_the_item_country($post); NS_have_sku_option($post); ?>
				</div>
				<div class="item_exp_2">
					<?php NS_the_item_explanation(2, $post); ?>
				</div>
			</div>
			<div class="itemdetail_right">
				<div class="tag_field">
				<?php NS_the_fantastic4($post); ?>
				</div>
				<div class="item_field clear">
					<div class="field_name">商品コード</div>
					<div class="field_code">：<?php NS_the_itemCode($post); ?></div>

					<div class="field_name">商品名</div>
					<div class="field_itemname">：<?php usces_the_itemName('', $post); ?></div>
				<?php if( usces_the_itemCprice('return') > 0 ) : ?>
					<div class="field_name">通常価格</div>
					<div class="field_cprice">：<?php usces_crform( usces_the_firstCprice('return', $post), true, false ); ?></div>
				<?php endif; ?>
					<div class="field_name">販売価格</div>
					<div class="field_cprice price">：<?php NS_the_item_pricesCr($post); ?></div>
			</div>
			<div class="item_exp_3"><?php NS_the_item_explanation(3, $post); ?></div>
			<form action="#capture" method="post">
				<div class="skuform">
				<?php if (usces_is_options()) : ?>
					<div class="item_option">
						<table id="option_list">
					<?php while (usces_have_options()) : ?>
						<tr>
							<th><?php usces_the_itemOptName(); ?></th>
							<td><?php NS_the_itemOption(usces_getItemOptName(), '', $post); ?></td>
						</tr>
					<?php endwhile; ?>
						</table>
					</div>
				<?php endif; ?>
				<?php if( $item_custom = usces_get_item_custom( $post->ID, 'list', 'return' ) ) : ?>
					<div class="field"><?php echo $item_custom; ?></div>
				<?php endif; ?>
					<div class="send_info">発送日目安：<?php NS_the_shipment_aim($post); ?></div>
					<div id="sku_option_field" class="sku_option_box"><?php NS_sku_option_field($post); ?></div>
					<div id="sku_option_price"></div>
					<div id="sku_option_message"></div>
					<div id="sku_option_button"></div>
					<div class="error_message"><?php usces_singleitem_error_message($post->ID, usces_the_itemSku('return')); ?></div>
				</div><!-- end of skuform -->
				<?php echo apply_filters('single_item_single_sku_after_field', NULL); ?>
				<?php do_action('usces_action_single_item_inform'); ?>
			</form>
		</div>
		</div><!-- end of itemspage -->
		<div class="item_detail">
			<h3 class="titlebar">この商品の解説</h2>
			<div class="item_exp_4"><?php NS_the_item_explanation(4, $post); ?></div>
			<?php NS_the_sku_list($post); ?>
		</div>
<?php
	}

	function is_mochikomihin( $type ) {
		return (!empty($this->product[$type]['post_id']) and ($this->product[$type]['post_id'] < 0));
	}

	function get_top_mochikomihin( $type ) {
		$res = '';
		$idObj = get_category_by_slug($this->product[$type]['options']['genre']);
		$genre = $idObj->cat_name;

		switch( $type ){
		case 'head':
			$bore = ($this->product[$type]['options']['bore'] == "straight") ? 'ストレート・ボア' : 'ノーマル・ボア';
			$res  = '<div>お持込ヘッド</div>';
			$res .= '<div>種類:'.$genre.'</div>';
			$res .= '<div>タイプ:'.$bore.'</div>';
			$res .= '<div>メーカー:'.$this->product['head']['options']['maker'].'</div>';
			break;

		case 'shuft':
			$res  = '<div>お持込シャフト</div>';
			$res .= '<div>種類:'.$genre.'</div>';
			break;

		case 'grip':
			$res  = '<div>お持込グリップ</div>';
			$res .= '<div>種類:'.$genre.'</div>';
			break;
		}

		return $res;
	}
}

class NS_Post {
	var $ID;

	function __construct() {
	}

	function set_id( $id ) {
		$this->ID = $id;
	}
}

function NS_mochikomihin( $action ){
?>
			<script type='text/javascript'>
			(function($) {
			uscesCart = {
				intoCart : function () {
					var mes = '';
					$(':input[name^="mochiopt"]').each(function(i, obj) {
						if($(this).val() == '') {
							label = $("label[for='"+$(this).attr("id")+"']").text();
							if($(this).attr("class") == "mochi_select") {
								mes += label+'を選択してください。'+"\n";
							} else if($(this).attr("class") == "mochi_text") {
								mes += label+'を入力してください。'+"\n";
							}
						}
					});
					if( mes != '' ){
						alert( mes );
						return false;
					}
				}
			};
			})(jQuery);
			</script>
<?php
	switch( $action ) {
		case 'default':
		case 'decide':
		case 'head_list':
?>
			<div class="thumbnail_box">
				<form action="#capture" method="post">
				<div class="item_name">お持込ヘッド</div>
				<div class="item_etc">
				<label for="mochi_genre" class="mochi_label">種類</label><select name="mochiopt[genre]" id="mochi_genre" class="mochi_select">
					<option value="">選択してください</option>
<?php
				$idObj = get_category_by_slug('sethead'); 
				$parent_id = $idObj->term_id;
				$cats = get_categories( array('child_of'=>$idObj->term_id, 'hide_empty'=>0) );
				foreach( $cats as $cat ){
					echo '<option value="' . esc_attr__($cat->category_nicename) . '" >' . esc_html__($cat->cat_name) . '</option>';
				}
?>
				</select>
				<label for="mochi_bore" class="mochi_label">タイプ</label><select name="mochiopt[bore]" id="mochi_bore" class="mochi_select">
					<option value="">選択してください</option>
					<option value="normal">ノーマル・ボア</option>
					<option value="straight">ストレート・ボア</option>
				</select>
				<label for="mochi_maker" class="mochi_label">メーカー</label><input name="mochiopt[maker]" type="text" id="mochi_maker" class="mochi_text"/>
				</div>
				<div class="select_button_box">
					<input name="shuft_list" type="submit" class="select_item_button" value="　" onclick="return uscesCart.intoCart()" />
					<input name="head_post_id" type="hidden" value="-1" />
					<input name="head_sku" type="hidden" value="-1" />
					<input name="head_price" type="hidden" value="0" />
				</div>
				</form>
			</div>
<?php
			break;
			
		case 'shuft_list':
?>
			<div class="thumbnail_box">
				<form action="#capture" method="post">
				<div class="item_name">お持込シャフト</div>
				<div class="item_etc">
				<label for="mochi_genre" class="mochi_label">種類</label><select name="mochiopt[genre]" id="mochi_genre" class="mochi_select">
					<option value="">選択してください</option>
<?php
				$idObj = get_category_by_slug('setshuft'); 
				$parent_id = $idObj->term_id;
				$cats = get_categories( array('child_of'=>$idObj->term_id, 'hide_empty'=>0) );
				foreach( $cats as $cat ){
					echo '<option value="' . esc_attr__($cat->category_nicename) . '" >' . esc_html__($cat->cat_name) . '</option>';
				}
?>
				</select>
				</div>
				<div class="select_button_box">
					<input name="grip_list" type="submit" class="select_item_button" value="　" />
					<input name="shuft_post_id" type="hidden" value="-2" />
					<input name="shuft_sku" type="hidden" value="-2" />
					<input name="shuft_price" type="hidden" value="0" />
				</div>
				</form>
			</div>
<?php
			break;
			
		case 'grip_list':
?>
<!--			<div class="thumbnail_box">
				<form action="#capture" method="post">
					<div class="item_name">お持込グリップ</div>
				<div class="item_etc">
				<label for="mochi_genre" class="mochi_label">種類</label><select name="mochiopt[genre]" id="mochi_genre" class="mochi_select">
					<option value="">選択してください</option>
<?php
				$idObj = get_category_by_slug('setgrip'); 
				$parent_id = $idObj->term_id;
				$cats = get_categories( array('child_of'=>$idObj->term_id, 'hide_empty'=>0) );
				foreach( $cats as $cat ){
					echo '<option value="' . esc_attr__($cat->category_nicename) . '" >' . esc_html__($cat->cat_name) . '</option>';
				}
?>
				</select>
				</div>
				<div class="select_button_box">
					<input name="decide" type="submit" class="select_item_button" value="　" />
					<input name="grip_post_id" type="hidden" value="-3" />
					<input name="grip_sku" type="hidden" value="-3" />
					<input name="grip_price" type="hidden" value="0" />
				</div>
				</form>
			</div>-->
<?php
			break;
			
		default:
			return;
	}
}
?>



<?php
/**********************************************************/
// Net Stage フィルター
add_filter('usces_filter_management_status', 'NS_filter_management_status');
function NS_filter_management_status($status){
	$status['work'] = '作業中';
	return $status;
}

//function NS_the_itemOption( $name, $label = '#default#', $out = '' ) {
function NS_the_itemOption( $name, $label = '#default#', $post = '', $out = '' ) {
	//global $post, $usces;
	global $usces;
	if($post == '') global $post;
	$post_id = $post->ID;
	$session_value = isset( $_SESSION['usces_singleitem']['itemOption'][$post_id][$usces->itemsku['key']][$name] ) ? $_SESSION['usces_singleitem']['itemOption'][$post_id][$usces->itemsku['key']][$name] : NULL;
	
	if($label == '#default#')
		$label = $name;
	$key = '_iopt_' . $name;
	$value = get_post_custom_values($key, $post_id);
	if(!$value) return false;
	$values = maybe_unserialize($value[0]);
	$means = (int)$values['means'];
	$essential = (int)$values['essential'];

	$html = '';
	$name = esc_attr($name);
	$label = esc_attr($label);
	switch($means) {
	case 0://Single-select
	case 1://Multi-select
		$selects = explode("\n", $values['value'][0]);
		$multiple = ($means === 0) ? '' : ' multiple';
		$html .= "\n<label for='iopt{$name}' class='iopt_label'>{$label}</label>\n";
		$html .= "\n<select name='iopt{$name}' id='iopt{$name}' class='iopt_select'{$multiple} onKeyDown=\"if (event.keyCode == 13) {return false;}\">\n";
		if($essential == 1){
			if(  '#NONE#' == $session_value || NULL == $session_value ) 
				$selected = ' selected="selected"';
			else
				$selected = '';
			$html .= "\t<option value='#NONE#'{$selected}>" . __('Choose','usces') . "</option>\n";
		}
		$i=0;
		foreach($selects as $v) {
			if( ($i == 0 && $essential == 0 && NULL == $session_value) || esc_attr($v) == $session_value ) 
				$selected = ' selected="selected"';
			else
				$selected = '';
			$html .= "\t<option value='" . esc_attr($v) . "'{$selected}>" . esc_html($v) . "</option>\n";
			$i++;
		}
		$html .= "</select>\n";
		break;
	case 2://Text
		$html .= "\n<input name='iopt{$name}' type='text' id='iopt{$name}' class='iopt_text' onKeyDown=\"if (event.keyCode == 13) {return false;}\" value=\"" . esc_attr($session_value) . "\" />\n";
		break;
	case 5://Text-area
		$html .= "\n<textarea name='iopt{$name}' id='iopt{$name}' class='iopt_textarea' />" . esc_attr($session_value) . "</textarea>\n";
		break;
	}
	
	if( $out == 'return' ){
		return $html;
	}else{
		echo $html;
	}
}

//function NS_the_itemQuant( $out = '' ) {
function NS_the_itemQuant( $post = '', $out = '' ) {
	//global $usces, $post;
	global $usces;
	if($post == '') global $post;
	$post_id = $post->ID;
	$value = isset( $_SESSION['usces_singleitem']['quant'][$post_id][$usces->itemsku['key']] ) ? $_SESSION['usces_singleitem']['quant'][$post_id][$usces->itemsku['key']] : 1;
	$html = "<input name=\"qnt\" type=\"text\" id=\"qnt\" class=\"skuquantity\" value=\"" . $value . "\" onKeyDown=\"if (event.keyCode == 13) {return false;}\" />";
		
	if( $out == 'return' ){
		return $html;
	}else{
		echo $html;
	}
}

//function NS_the_itemSkuButton($value, $type=0, $out = '') {
function NS_the_itemSkuButton($value, $type=0, $post = '', $out = '') {
	//global $usces, $post;
	global $usces;
	if($post == '') global $post;
	$post_id = $post->ID;

	if($type == 1)
		$type = 'button';
	else
		$type = 'image';

	if( $usces->use_js ){
		$html .= "<input name=\"inCart\" type=\"{$type}\" src=\"" . get_stylesheet_directory_uri() . "/images/item/btn_addcart2.png\" alt=\"カートに入れる\" id=\"inCart\" class=\"skubutton\" value=\"{$value}\" disabled />";
	}else{
		$html .= "<a name=\"cart_button\"></a><input name=\"inCart\" type=\"{$type}\" id=\"inCart\" class=\"skubutton\" value=\"{$value}\" disabled />";
		$html .= "<input name=\"usces_referer\" type=\"hidden\" value=\"" . $_SERVER['REQUEST_URI'] . "\" />\n";
	}

	if( $out == 'return' ){
		return $html;
	}else{
		echo $html;
	}
}

function NS_get_itemOptions( $key, $post_id ) {
	$metakey = '_iopt_' . $key;
	$values = get_post_custom_values( $metakey, $post_id );
	if(empty($values)) return NULL;

	$val = ( is_serialized( $values[0] )) ? unserialize( $values[0] ) : $values[0];
	if( $val['sku'] != 1 ) return $val; else return NULL;
}

add_action('usces_front_ajax', 'change_sku_option_ajax');
function change_sku_option_ajax() {
	global $wpdb, $usces;
	$post_id = $_POST['post_id'];
	$key = $_POST['key'];
	$value = $_POST['value'];
	$index = $_POST['index'];
	$skukey = isset($_POST['skukey']) ? explode("#usces#", trim($_POST['skukey'])) : array();//SKUオプション(KEY)
	$skuoption = isset($_POST['skuoption']) ? explode("#usces#", trim($_POST['skuoption'])) : array();//SKUオプション(VALUE)
	$skucnt = count($skukey);
	$nextskukey = $_POST['nextskukey'];
	$sku = array();
	$nextskuvalue = array();
	$optkey = array();
	$optvalue = array();
	$skuprice = '';
	$zaikonum = 0;
	$html = '';
	$msg = '';
	$set = isset($_POST['set']) ? $_POST['set'] : '';
	$type = isset($_POST['type']) ? $_POST['type'] : '';
	$nextaction = isset($_POST['nextaction']) ? $_POST['nextaction'] : '';

	$orderby = $usces->options['system']['orderby_itemsku'] ? 'meta_id' : 'meta_key';
	$res = $wpdb->get_results( $wpdb->prepare("SELECT meta_key, meta_value, meta_id, post_id
			FROM $wpdb->postmeta WHERE post_id = %d AND meta_key LIKE '%s' 
			ORDER BY {$orderby}", $post_id, '_isku_%'), ARRAY_A );
	foreach( $res as $row ) {
		if( is_serialized( $row['meta_value'] )) $row['meta_value'] = maybe_unserialize( $row['meta_value'] );
		$chk = 0;
		for($i = 0; $i < $skucnt; $i++) {
			if($row['meta_value']['option'][$skukey[$i]] != $skuoption[$i]) {
				$chk = 1;
				break;
			}
		}
		if($chk == 0 and $row['meta_value']['option'][$key] == $value) {
			$sku[] = esc_attr(substr($row['meta_key'],6));
			if($nextskukey != '') $nextskuvalue[] = esc_attr($row['meta_value']['option'][$nextskukey]);
		}
	}

	$sku = array_unique($sku);
	$nextskuvalue = array_unique($nextskuvalue);

	if(0 == count($sku)) {
		$msg = esc_attr('ご選択戴きました商品は未だ登録されていません。');
	} else {
		if(count($sku) == 1 and !empty($sku[0])) {
			foreach( $res as $row ) {
				if($row['meta_key'] == '_isku_'.$sku[0]) {
					if( is_serialized( $row['meta_value'] )) $row['meta_value'] = maybe_unserialize( $row['meta_value'] );
					foreach( $row['meta_value']['option'] as $k => $v ) {
						$optkey[] = esc_attr($k);
						$optvalue[] = esc_attr($v);
					}
					$skuprice = esc_attr(usces_crform($row['meta_value']['price'], true, false, 'return'));
					$zaiko_num = trim($row['meta_value']['zaikonum']);
					$status_num = $row['meta_value']['zaiko'];
					if( false !== $zaiko_num 
						&& ( 0 < (int)$zaiko_num || '' == $zaiko_num ) 
						&& 2 > $status_num 
					){
						if($set == 1) {
							$html  = "<input name=\"".$nextaction."\" type=\"submit\" class=\"select_item_button\" value=\"　\" onclick=\"return uscesCart.intoCart('".$post_id."','".$sku[0]."')\" />\n";
							$html .= "<input name=\"".$type."_post_id\" type=\"hidden\" value=\"".$post_id."\" />\n";
							$html .= "<input name=\"".$type."_sku\" type=\"hidden\" value=\"".$sku[0]."\" />\n";
							$html .= "<input name=\"".$type."_price\" type=\"hidden\" value=\"".$row['meta_value']['price']."\" />\n";
						} else {
							$html  = "<input name=\"zaikonum[".$post_id."][".$sku[0]."]\" type=\"hidden\" id=\"zaikonum[".$post_id."][".$sku[0]."]\" value=\"".$row['meta_value']['zaikonum']."\" />\n";
							$html .= "<input name=\"zaiko[".$post_id."][".$sku[0]."]\" type=\"hidden\" id=\"zaiko[".$post_id."][".$sku[0]."]\" value=\"".$row['meta_value']['zaiko']."\" />\n";
							$html .= "<input name=\"skuPrice[".$post_id."][".$sku[0]."]\" type=\"hidden\" id=\"skuPrice[".$post_id."][".$sku[0]."]\" value=\"".$row['meta_value']['price']."\" />\n";
							if( $usces->use_js ){
								$html .= "<input name=\"inCart[".$post_id."][".$sku[0]."]\" type=\"image\" src=\"" . get_stylesheet_directory_uri() . "/images/item/btn_addcart.png\" alt=\"カートに入れる\" id=\"inCart[".$post_id."][".$sku[0]."]\" class=\"skubutton\" value=\"カートに入れる\" onclick=\"return uscesCart.intoCart('".$post_id."','".$sku[0]."')\" />";
							}else{
								$html .= "<a name=\"cart_button\"></a><input name=\"inCart[".$post_id."][".$sku[0]."]\" type=\"image\" id=\"inCart[".$post_id."][".$sku[0]."]\" class=\"skubutton\" value=\"カートに入れる\" />";
								$html .= "<input name=\"usces_referer\" type=\"hidden\" value=\"" . $_SERVER['REQUEST_URI'] . "\" />\n";
							}
						}
					} else {
						$msg = esc_attr('大変申し訳ございません。ご選択いただきました商品は、只今在庫切れとなっております。');
					}
					break;
				}
			}
		}
	}
	if($html == '' and $set != 1) $html = NS_the_itemSkuButton(__('Add to Shopping Cart', 'usces'), 0, '', 'return');
	
	die(implode("#ns#", $sku)."#usces#".implode("#ns#", $nextskuvalue)."#usces#".implode("#ns#", $optkey)."#usces#".implode("#ns#", $optvalue)."#usces#".$skuprice."#usces#".$zaikonum."#usces#".$html."#usces#".$msg);
}


//function usces_all_change_order_reciept(&$obj){
//	global $wpdb;
//
//	$tableName = $wpdb->prefix . "usces_order";
//	$ids = $_POST['listcheck'];
//	$status = true;
//	foreach ( (array)$ids as $id ):
//		$query = $wpdb->prepare("SELECT order_status FROM $tableName WHERE ID = %d", $id);
//		$statusstr = $wpdb->get_var( $query );
//		if(strpos($statusstr, 'noreceipt') === false && strpos($statusstr, 'receipted') === false) continue;
//		if($_REQUEST['change']['word']['order_reciept'] == 'receipted') {
//			if(strpos($statusstr, 'noreceipt') !== false)
//				$statusstr = str_replace('noreceipt', 'receipted', $statusstr);
//		}elseif($_REQUEST['change']['word']['order_reciept'] == 'noreceipt') {
//			if(strpos($statusstr, 'receipted') !== false)
//				$statusstr = str_replace('receipted', 'noreceipt', $statusstr);
//		}
//		$query = $wpdb->prepare("UPDATE $tableName SET order_status = %s WHERE ID = %d", $statusstr, $id);
//		$res = $wpdb->query( $query );
//		if( $res === false ) {
//			$status = false;
//		}
//	endforeach;
//	if ( true === $status ) {
//		$obj->set_action_status('success', __('I completed collective operation.','usces'));
//	} elseif ( false === $status ) {
//		$obj->set_action_status('error', __('ERROR: I was not able to complete collective operation','usces'));
//	} else {
//		$obj->set_action_status('none', '');
//	}
//}

/**********************************************************
 * Explanation	: B2用CSV出力
 * UpDate		: 2011.06.27
 * exit			: csv
 **********************************************************/
if( 'dlB2list' == $_REQUEST['order_action'] 
	&& 'usces_orderlist' == $_REQUEST['page'] ){
		NS_download_B2_list();
}
//if( 'dlB2list' == $_REQUEST['allchange[column]'] ){
//		NS_download_B2_list();
//}
function NS_download_B2_list(){
	require_once( USCES_PLUGIN_DIR . "/classes/orderList.class.php" );
	global $wpdb, $usces, $usces_settings;
	
	$usces_option = get_option('usces');

	//==========================================================================

	if( isset($_REQUEST['list_id']) && !empty($_REQUEST['list_id']) ) {
		$ids = trim($_REQUEST['list_id'], ',');
		if( empty($ids) ) return false;
	}else{
		return false;
	}

	$tableName = $wpdb->prefix . "usces_order";
	$query = $wpdb->prepare("SELECT * FROM $tableName WHERE ID IN (%s)", $ids);
	$rows = $wpdb->get_results( $query, ARRAY_A  );

	$line = '';
	//==========================================================================
	//header
	
/* 01 */	$line .= '"お客様管理番号(注文番号)",';
/* 02 */	$line .= '"送り状種類",';
/* 03 */	$line .= '"空欄",';
/* 04 */	$line .= '"空欄",';
/* 05 */	$line .= '"出荷予定日",';
/* 06 */	$line .= '"お届け予定日(空欄)",';
/* 07 */	$line .= '"配達時間帯(空欄)",';
/* 08 */	$line .= '"お届け先コード",';
/* 09 */	$line .= '"お届け先電話番号",';
/* 10 */	$line .= '"お届け先電話番号枝",';
/* 11 */	$line .= '"お届け先郵便番号",';
/* 12 */	$line .= '"お届け先住所",';
/* 13 */	$line .= '"お届け先建物名",';
/* 14 */	$line .= '"お届け先会社・部門１",';
/* 15 */	$line .= '"お届け先会社・部門２",';
/* 16 */	$line .= '"お届け先名",';
/* 17 */	$line .= '"お届け先名略称カナ",';
/* 18 */	$line .= '"空欄",';
/* 19 */	$line .= '"ご依頼主コード",';
/* 20 */	$line .= '"ご依頼主電話番号",';
/* 21 */	$line .= '"ご依頼主電話番号枝",';
/* 22 */	$line .= '"ご依頼主郵便番号",';
/* 23 */	$line .= '"ご依頼主住所",';
/* 24 */	$line .= '"ご依頼主建物名",';
/* 25 */	$line .= '"ご依頼主名",';
/* 26 */	$line .= '"ご依頼主名略称カナ",';
/* 27 */	$line .= '"品名コード１",';
/* 28 */	$line .= '"品名１",';
/* 29 */	$line .= '"品名コード２",';
/* 30 */	$line .= '"品名２",';
/* 31 */	$line .= '"荷扱い１",';
/* 32 */	$line .= '"荷扱い２",';
/* 33 */	$line .= '"記事",';
/* 34 */	$line .= '"コレクト代金引換額",';
/* 35 */	$line .= '"コレクト内消費税",';
/* 36 */	$line .= '"営業所止置き",';
/* 37 */	$line .= '"営業所コード",';
/* 38 */	$line .= '"発行枚数",';
/* 39 */	$line .= '"個数口枠の印字",';
/* 40 */	$line .= '"ご請求先顧客コード",';
/* 41 */	$line .= '"ご請求先分類コード",';
/* 42 */	$line .= '"運賃管理番号"';
			$line .= "\n";
	
	
	
	//==========================================================================
	//body
	
	foreach((array)$rows as $data) {
		$deli = unserialize($data['order_delivery']);
		$cart = unserialize($data['order_cart']);
		$row_num = count($cart);

		$first_code = get_post_meta($cart[0]['post_id'], '_itemCode', true);
		$first_name = get_post_meta($cart[0]['post_id'], '_itemName', true);
		if( 2 === $row_num ){
			$second_code = get_post_meta($cart[1]['post_id'], '_itemCode', true);
			$second_name = get_post_meta($cart[1]['post_id'], '_itemName', true);
		}elseif( 2 < $row_num ){
			$second_code = get_post_meta($cart[1]['post_id'], '_itemCode', true);
			$second_name = get_post_meta($cart[1]['post_id'], '_itemName', true) . '　その他';
		}else{
			$second_code = "";
			$second_name = "";
		}
		
		
/* 01 */	$line .= '"' . $data['ID'] . '",';
/* 02 */	$line .= '"0",';
/* 03 */	$line .= ',';
/* 04 */	$line .= ',';
/* 05 */	$line .= '"' . date('Ymd', current_time('timestamp')) . '",';
/* 06 */	$line .= ',';
/* 07 */	$line .= ',';
/* 08 */	$line .= ',';
/* 09 */	$line .= '"' . str_replace('-', '', $deli['order_tel']) . '",';
/* 10 */	$line .= ',';
/* 11 */	$line .= '"' . str_replace('-', '', $deli['zipcode']) . '",';
/* 12 */	$line .= '"' . str_replace('"', '""', $deli['pref'] . $deli['address1'] . $deli['address2']) . '",';
/* 13 */	$line .= '"' . str_replace('"', '""', $deli['address3']) . '",';
/* 14 */	$line .= ',';
/* 15 */	$line .= ',';
/* 16 */	$line .= '"' . str_replace('"', '""', $deli['name1'] . $deli['name2']) . '",';
/* 17 */	$line .= ',';
/* 18 */	$line .= ',';
/* 19 */	$line .= ',';
/* 20 */	$line .= '"' . str_replace('-', '', $usces_option['tel_number']) . '",';
/* 21 */	$line .= ',';
/* 22 */	$line .= '"' . str_replace('-', '', $usces_option['zip_code']) . '",';
/* 23 */	$line .= '"' . str_replace('"', '""', $usces_option['address1']) . '",';
/* 24 */	$line .= '"' . str_replace('"', '""', $usces_option['address2']) . '",';
/* 25 */	$line .= '"' . str_replace('"', '""', $usces_option['company_name']) . '",';
/* 26 */	$line .= ',';
/* 27 */	$line .= '"' . str_replace('"', '""', $first_code) . '",';
/* 28 */	$line .= '"' . str_replace('"', '""', $first_name) . '",';
/* 29 */	$line .= '"' . str_replace('"', '""', $second_code) . '",';
/* 30 */	$line .= '"' . str_replace('"', '""', $second_name) . '",';
/* 31 */	$line .= ',';
/* 32 */	$line .= ',';
/* 33 */	$line .= ',';
/* 34 */	$line .= ',';
/* 35 */	$line .= ',';
/* 36 */	$line .= ',';
/* 37 */	$line .= ',';
/* 38 */	$line .= '"1",';
/* 39 */	$line .= '"1",';
/* 40 */	$line .= '"' . str_replace('"', '""', $usces_option['b2_bill_code']) . '",';
/* 41 */	$line .= '"' . str_replace('"', '""', $usces_option['b2_div_code']) . '",';
/* 42 */	$line .= '"' . str_replace('"', '""', $usces_option['b2_admin_id']) . '"';
			$line .= "\n";
		
	}	
		
	//==========================================================================

	header("Content-Type: application/octet-stream");
	header("Content-Disposition: attachment; filename=b2_list.csv");
	mb_http_output('pass');
	print(mb_convert_encoding($line, "SJIS-win", "UTF-8"));
	exit();

}
function NS_the_shipment_aim( $post = '', $out = '' ) {
	if($post == '') global $post;
	$post_id = $post->ID;

	$str = get_post_custom_values('_itemShipping', $post_id);
	$no = (int)$str[0];
	if( 0 === $no ) return;
	
	$rules = get_option('usces_shipping_rule');
	
	if( $out == 'return' ){
		return $rules[$no];
	}else{
		echo esc_html($rules[$no]);
	}
}

function NS_the_itemCode( $post = '', $out = '' ) {
	if($post == '') global $post;
	$post_id = $post->ID;

	$str = get_post_custom_values('_itemCode', $post_id);
	
	if( $out == 'return' ){
		return $str[0];
	}else{
		echo esc_html($str[0]);
	}
}

function NS_the_item( $post = '' ) {
	global $usces;
	if($post == '') global $post;
	$usces->itemskus = array();
	$usces->itemopts = array();
	$post_id = $post->ID;
	
	$skuorderby = $usces->options['system']['orderby_itemsku'] ? 'meta_id' : 'meta_key';
	$skufields = $usces->get_post_custom($post_id, $skuorderby);
	$optorderby = $usces->options['system']['orderby_itemopt'] ? 'meta_id' : 'meta_key';
	$optfields = $usces->get_post_custom($post_id, $optorderby);
	foreach((array)$skufields as $key => $value){
		if( preg_match('/^_isku_/', $key, $match) ){
			$key = substr($key, 6);
			$values = maybe_unserialize($value[0]);
			$usces->itemskus[$key] = $values;
		}
	}
	foreach((array)$optfields as $key => $value){
		if( preg_match('/^_iopt_/', $key, $match) ){
			$key = substr($key, 6);
			$values = maybe_unserialize($value[0]);
			//NS Customize
			if( !isset( $values['sku'] ) || 1 != $values['sku'] )
				$usces->itemopts[$key] = $values;
		}
	}
	//var_dump($fields);
	//natcasesort($usces->itemskus);
	//ksort($usces->itemskus, SORT_STRING);
	//ksort($usces->itemopts, SORT_STRING);
	return;
}

function NS_get_itemSubImageNums( $post = '' ) {
	global $usces;
	if($post == '') global $post;
	$post_id = $post->ID;
	$res = array();
	
	$code =  get_post_custom_values('_itemCode', $post_id);
	if(!$code) return false;
	$name = get_post_custom_values('_itemName', $post_id);
	$pictids = $usces->get_pictids($code[0]);
	for($i=1; $i<count($pictids); $i++){
		$res[] = $i;
	}
	return  $res;
}

function NS_get_cart( $cart ) {
	global $usces;
	$rows = array();
	$set_post_id = $usces->get_postIDbyCode( NS_ITEM_SET );

	$i = 0;
	foreach($cart as $key => $row) {
		if($row['post_id'] == $set_post_id) {
			$serial = $row['serial'];
			$quantity = $row['quantity'];
			$advance = $usces->cart->wc_unserialize($row['advance']);
			$options = $advance[$row['post_id']][$row['sku']];

			$post_id = $row['options']['set_head'];
			if($post_id < 0) {
				$advance['mochi_head']['mochi_head_sku'] = $options['set_head_options'];
			} else {
				$sku = $row['options']['set_head_sku'];
				$r = array();
				$r['serial'] = $serial;
				$r['post_id'] = $post_id;
				$r['sku'] = $sku;
				$r['options'] = (!empty($options['set_head_options'])) ? $options['set_head_options'] : array();
				$r['price'] = usces_get_item_price($post_id, $sku);
				$r['quantity'] = $quantity;
				$r['advance'] = array();
				$rows[$i] = $r;
				$i++;
			}

			$post_id = $row['options']['set_shuft'];
			if($post_id < 0) {
				$advance['mochi_shuft']['mochi_shuft_sku'] = $options['set_shuft_options'];
			} else {
				$sku = $row['options']['set_shuft_sku'];
				$r = array();
				$r['serial'] = $serial;
				$r['post_id'] = $post_id;
				$r['sku'] = $sku;
				$r['options'] = (!empty($options['set_shuft_options'])) ? $options['set_shuft_options'] : array();
				$r['price'] = usces_get_item_price($post_id, $sku);
				$r['quantity'] = $quantity;
				$r['advance'] = array();
				$rows[$i] = $r;
				$i++;
			}

			$post_id = $row['options']['set_grip'];
			if($post_id < 0) {
				$advance['mochi_grip']['mochi_grip_sku'] = $options['set_grip_options'];
			} else {
				$sku = $row['options']['set_grip_sku'];
				$r = array();
				$r['serial'] = $serial;
				$r['post_id'] = $post_id;
				$r['sku'] = $sku;
				$r['options'] = (!empty($options['set_grip_options'])) ? $options['set_grip_options'] : array();
				$r['price'] = usces_get_item_price($post_id, $sku);
				$r['quantity'] = $quantity;
				$r['advance'] = array();
				$rows[$i] = $r;
				$i++;
			}

			$row['price'] = usces_get_item_price($row['post_id'], $row['sku']);
			$row['advance'] = $usces->cart->wc_serialize($advance);
			$rows[$i] = $row;
			$i++;

		} else {
			$rows[$i] = $row;
			$i++;
		}
	}

	return $rows;
}

function usces_get_itemImage( $post_id, $number = 0, $width = 60, $height = 60 ) {
	global $usces;

	$code =  get_post_custom_values('_itemCode', $post_id);
	if(!$code) return false;
	
	$name = get_post_custom_values('_itemName', $post_id);
	
	$pictids = $usces->get_pictids($code[0]);
	$html = wp_get_attachment_image( $pictids[$number], array($width, $height), false );

	return $html;
}

function usces_get_item_price($post_id, $sku){
	global $usces;
	$field = get_post_meta($post_id, '_isku_'.$sku, true);
	return $field['price'];
}

function usces_get_item_cprice($post_id, $sku){
	global $usces;
	$field = get_post_meta($post_id, '_isku_'.$sku, true);
	return $field['cprice'];
}
