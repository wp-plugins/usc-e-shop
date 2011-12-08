<?php
global $usces_settings;
$status = $this->action_status;
$message = $this->action_message;
$this->action_status = 'none';
$this->action_message = '';

$divide_item = $this->options['divide_item'];
$itemimg_anchor_rel = $this->options['itemimg_anchor_rel'];
$fukugo_category_orderby = $this->options['fukugo_category_orderby'];
$fukugo_category_order = $this->options['fukugo_category_order'];
//20110331ysk start
//$usces_pref = empty($this->options['province']) ? array() : $this->options['province'];
$settlement_path = $this->options['settlement_path'];
//$province = '';
//for($i=1; $i<count($usces_pref); $i++){
//	$province .= $usces_pref[$i] . "\n";
//}
//$province = trim($province);
//20110331ysk end
$use_ssl = $this->options['use_ssl'];
$ssl_url = $this->options['ssl_url'];
$ssl_url_admin = $this->options['ssl_url_admin'];
$inquiry_id = $this->options['inquiry_id'];
$orderby_itemsku = isset($this->options['system']['orderby_itemsku']) ? $this->options['system']['orderby_itemsku'] : 0;
$orderby_itemopt = isset($this->options['system']['orderby_itemopt']) ? $this->options['system']['orderby_itemopt'] : 0;
$system_front_lang =  ( isset($this->options['system']['front_lang']) && !empty($this->options['system']['front_lang']) ) ? $this->options['system']['front_lang'] : usces_get_local_language();
$system_currency =  ( isset($this->options['system']['currency']) && !empty($this->options['system']['currency']) ) ? $this->options['system']['currency'] : usces_get_base_country();
$system_addressform =  ( isset($this->options['system']['addressform']) && !empty($this->options['system']['addressform']) ) ? $this->options['system']['addressform'] : usces_get_local_addressform();
$system_target_markets =  ( isset($this->options['system']['target_market']) && !empty($this->options['system']['target_market']) ) ? $this->options['system']['target_market'] : usces_get_local_target_market();
$no_cart_css = isset($this->options['system']['no_cart_css']) ? $this->options['system']['no_cart_css'] : 0;
$dec_orderID_flag = isset($this->options['system']['dec_orderID_flag']) ? $this->options['system']['dec_orderID_flag'] : 0;
$dec_orderID_prefix = isset($this->options['system']['dec_orderID_prefix']) ? $this->options['system']['dec_orderID_prefix'] : '';
$dec_orderID_digit = isset($this->options['system']['dec_orderID_digit']) ? $this->options['system']['dec_orderID_digit'] : '';
$subimage_rule = isset($this->options['system']['subimage_rule']) ? $this->options['system']['subimage_rule'] : 0;
?>
<script type="text/javascript">
jQuery(function($){
<?php if($status == 'success'){ ?>
			$("#anibox").animate({ backgroundColor: "#ECFFFF" }, 2000);
<?php }else if($status == 'caution'){ ?>
			$("#anibox").animate({ backgroundColor: "#FFF5CE" }, 2000);
<?php }else if($status == 'error'){ ?>
			$("#anibox").animate({ backgroundColor: "#FFE6E6" }, 2000);
<?php } ?>

	$("#aAdditionalURLs").click(function () {
		$("#AdditionalURLs").toggle();
	});

//20110331ysk start
	var pre_target = '';

	operation = {
		set_target_market: function() {
			var target = [];
			var target_text = [];
			$("#target_market option:selected").each(function () {
				target.push($(this).val());
				target_text.push($(this).text());
			});
			if(target.length == 0) {
				alert('<?php _e('Please select one of the country.', 'usces'); ?>');
				return -1;
			}
			var sel = $('select_target_market_province').val();
			var name_select = '<select name="select_target_market_province" id="select_target_market_province" onchange="operation.onchange_target_market_province(this.selectedIndex);">'+"\n";
			var target_args = '';
			var c = '';
			for(var i=0; i<target.length; i++){
				name_select += '<option value="'+target[i]+'">'+target_text[i]+'</option>'+"\n";
				target_args += c+target[i];
				c = ',';
			}
			name_select += "</select>\n";
			$("#target_market_province").html(name_select);
			$("#target_market_loading").html('<img src="<?php echo USCES_PLUGIN_URL; ?>/images/loading-publish.gif" />');
			var s = operation.settings;
			s.data = "action=target_market_ajax&target="+target_args;
			s.success = function(data, dataType) {
				$('#province_ajax').empty();
				var province = data.split('#usces#');
				for(var i=0; i<province.length; i++) {
					if(province[i].length > 0) {
						var state = province[i].split(',');
						$('#province_ajax').append('<input type="hidden" name="province_'+state[0]+'" id="province_'+state[0]+'" value="'+state[1]+'">');
					}
				}
				$('#select_target_market_province').triggerHandler('change', 0);
				$("#target_market_loading").html('');
			};
			$.ajax( s );
			return false;
		},

		onchange_target_market_province: function(index) {
			if(pre_target != '') $('#province_'+pre_target).val($("#province").val());
			var target = $("#select_target_market_province option:selected").val();
			$("#province").text('');
			$("#province").text($('#province_'+target).val());
			pre_target = target;
		},

		settings: {
			url: uscesL10n.requestFile,
			type: 'POST',
			cache: false,
			success: function(data, dataType) {
				$("#target_market_loading").html('');
			}, 
			error: function(msg) {
				$("#target_market_loading").html('');
			}
		}
	};

	$('form').submit(function() {
		$('#province_'+pre_target).val($("#province").val());

		var error = 0;
		var tabs = 0;

		if( !checkAlp( $("#dec_orderID_prefix").val() ) ) {
			error++;
			$("#dec_orderID_prefix").css({'background-color': '#FFA'}).click(function() {
				$(this).css({'background-color': '#FFF'});
			});
		}
		if( !checkNum( $("#dec_orderID_digit").val() ) ) {
			error++;
			$("#dec_orderID_digit").css({'background-color': '#FFA'}).click(function() {
				$(this).css({'background-color': '#FFF'});
			});
		}

		var target = [];
		$("#target_market option:selected").each(function() {
			target.push($(this).val());
		});
		if( target.length == 0 ) {
			error++;
			tabs = 1;
			$("#target_market").css({'background-color': '#FFA'}).click(function() {
				$(this).css({'background-color': '#FFF'});
			});

		} else {
			var province = 'OK';
			for(var i=0; i<target.length; i++) {
				if(target[i] != 'JP' && target[i] != 'US') {
					if( "" == $("#province_"+target[i]).val() ) province = 'NG';
				}
			}
			if( 'OK' != province ) {
				error++;
				tabs = 1;
				$("#province").css({'background-color': '#FFA'}).click(function() {
					$(this).css({'background-color': '#FFF'});
				});
			}
		}

		if( 0 < error ) {
			$("#aniboxStatus").removeClass("none");
			$("#aniboxStatus").addClass("error");
			$("#info_image").attr("src", "<?php echo USCES_PLUGIN_URL; ?>/images/list_message_error.gif");
			$("#info_massage").html("データに不備があります");
			$("#anibox").animate({ backgroundColor: "#FFE6E6" }, 2000);
			$('#uscestabs_system').tabs("select", tabs);
			return false;
		} else {
			return true;
		}
	});
//20110331ysk end
});

function toggleVisibility(id) {
   var e = document.getElementById(id);
   if(e.style.display == 'block')
	  e.style.display = 'none';
   else
	  e.style.display = 'block';
}
//20110331ysk start
jQuery(document).ready(function($) {
	operation.set_target_market();

	var $tabs = $('#uscestabs_system').tabs({
		cookie: {
			// store cookie for a day, without, it would be a session cookie
			expires: 1
		}
	});
});
//20110331ysk end
</script>
<div class="wrap">
<div class="usces_admin">
<h2>Welcart Shop <?php _e('System Setting','usces'); ?></h2>
<div id="aniboxStatus" class="<?php echo $status; ?>">
	<div id="anibox" class="clearfix">
		<img id="info_image" src="<?php echo USCES_PLUGIN_URL; ?>/images/list_message_<?php echo $status; ?>.gif" />
		<div class="mes" id="info_massage"><?php echo $message; ?></div>
	</div>
</div>
<form action="" method="post" name="option_form" id="option_form">
<input name="usces_option_update" type="submit" class="button" value="<?php _e('change decision','usces'); ?>" />
<div id="poststuff" class="metabox-holder">

<!--20110331ysk start-->
<div class="uscestabs" id="uscestabs_system">
	<ul>
		<li><a href="#system_page_setting_1"><?php _e('System Setting','usces'); ?></a></li>
		<li><a href="#system_page_setting_2"><?php _e('Language Currency Country','usces'); ?></a></li>
	</ul>
<div id="system_page_setting_1">
<!--20110331ysk end-->
<div class="postbox">
<h3 class="hndle"><span><?php _e('System Setting','usces'); ?></span></h3>
<div class="inside">
<table class="form_table">
	<tr height="35">
	    <th class="system_th"><a style="cursor:pointer;" onclick="toggleVisibility('ex_divide_item');"><?php _e('Display Modes','usces'); ?></a></th>
		<?php $checked = $divide_item == 1 ? ' checked="checked"' : ''; ?>
		<td width="10"><input name="divide_item" type="checkbox" id="divide_item" value="<?php echo esc_attr($divide_item); ?>"<?php echo $checked; ?> /></td>
		<td width="300"><label for="divide_item"><?php _e('Not display an article in blog', 'usces'); ?></label></td>
	    <td><div id="ex_divide_item" class="explanation"><?php _e('In the case of the loop indication that plural contributions are displayed in a shop, you can be decided display or non-display the item.', 'usces'); ?></div></td>
	</tr>
</table>
<hr />
<table class="form_table">
	<tr height="35">
	    <th class="system_th"><a style="cursor:pointer;" onclick="toggleVisibility('ex_itemimg_anchor_rel');"><?php _e('rel attribute', 'usces'); ?></a></th>
		<td width="30">rel="</td>
		<td width="100"><input name="itemimg_anchor_rel" id="itemimg_anchor_rel" type="text" value="<?php echo esc_attr($itemimg_anchor_rel); ?>" /></td>
		<td width="10">"</td>
	    <td><div id="ex_itemimg_anchor_rel" class="explanation"><?php _e('In item details page, you can appoint a rel attribute for anchor tag to display an image, sach as Lightbox plugin.', 'usces'); ?></div></td>
	</tr>
</table>
<hr />
<table class="form_table">
	<tr height="35">
	    <th class="system_th"><a style="cursor:pointer;" onclick="toggleVisibility('ex_fcat_orderby');"><?php _e('compound category sort item', 'usces'); ?></a></th>
		<td width="10"><select name="fukugo_category_orderby" id="fukugo_category_orderby">
		    <option value="ID"<?php if($fukugo_category_orderby == 'ID') echo ' selected="selected"'; ?>><?php _e('category ID', 'usces'); ?></option>
		    <option value="name"<?php if($fukugo_category_orderby == 'name') echo ' selected="selected"'; ?>><?php _e('category name', 'usces'); ?></option>
		</select></td>
	    <td><div id="ex_fcat_orderby" class="explanation"><?php _e('In a category to display in a compound category search page, you can choose an object to sort.', 'usces'); ?></div></td>
	</tr>
	<tr height="35">
	    <th class="system_th"><a style="cursor:pointer;" onclick="toggleVisibility('ex_fcat_order');"><?php _e('compound category sort order', 'usces'); ?></a></th>
		<td width="10"><select name="fukugo_category_order" id="fukugo_category_order">
		    <option value="ASC"<?php if($fukugo_category_order == 'ASC') echo ' selected="selected"'; ?>><?php _e('Ascending', 'usces'); ?></option>
		    <option value="DESC"<?php if($fukugo_category_order == 'DESC') echo ' selected="selected"'; ?>><?php _e('Descendin', 'usces'); ?></option>
		</select></td>
	    <td><div id="ex_fcat_order" class="explanation"><?php _e('In a category to display in a compound category search page, you can choose sort order.', 'usces'); ?></div></td>
	</tr>
</table>
<hr />
<table class="form_table">
	<tr height="35">
	    <th class="system_th"><a style="cursor:pointer;" onclick="toggleVisibility('ex_settlement_path');"><?php _e('settlement module path', 'usces'); ?></a></th>
		<td><input name="settlement_path" type="text" id="settlement_path" value="<?php echo esc_attr($settlement_path); ?>" size="60" /></td>
	    <td><div id="ex_settlement_path" class="explanation"><?php _e('This is Field appointing the setting path of the settlement module. The initial value is a place same as a sample, but it is deleted at the time of automatic upgrading. Therefore you must arrange a module outside a plugin folder.', 'usces'); ?></div></td>
	</tr>
</table>
<hr />
<table class="form_table">
	<tr height="35">
	    <th class="system_th"><a style="cursor:pointer;" onclick="toggleVisibility('ex_use_ssl');"><?php _e('Use SSL','usces'); ?></a></th>
		<?php $checked = $use_ssl == 1 ? ' checked="checked"' : ''; ?>
		<td width="10"><input name="use_ssl" type="checkbox" id="use_ssl" value="<?php echo esc_attr($use_ssl); ?>"<?php echo $checked; ?> /></td>
		<td width="300">&nbsp;</td>
	    <td><div id="ex_use_ssl" class="explanation"><?php _e('Please decide whether you use SSL', 'usces'); ?></div></td>
	</tr>
</table>
<table class="form_table">
	<tr height="35">
	    <th class="system_th"><a style="cursor:pointer;" onclick="toggleVisibility('ex_ssl_url_admin');"><?php _e('WordPress address (SSL)', 'usces'); ?></a></th>
		<td><input name="ssl_url_admin" type="text" id="ssl_url_admin" value="<?php echo esc_attr($ssl_url_admin); ?>" size="60" /></td>
	    <td><div id="ex_ssl_url_admin" class="explanation"><?php _e('https://*WordPress address*<br />You can use common use SSL.', 'usces'); ?></div></td>
	</tr>
</table>
<table class="form_table">
	<tr height="35">
	    <th class="system_th"><a style="cursor:pointer;" onclick="toggleVisibility('ex_ssl_url');"><?php _e('Blog address (SSL)', 'usces'); ?></a></th>
		<td><input name="ssl_url" type="text" id="ssl_url" value="<?php echo esc_attr($ssl_url); ?>" size="60" /></td>
	    <td><div id="ex_ssl_url" class="explanation"><?php _e('https://*Blog address*<br />You can use common use SSL.', 'usces'); ?></div></td>
	</tr>
</table>
<hr />
<table class="form_table">
	<tr height="35">
	    <th class="system_th"><a style="cursor:pointer;" onclick="toggleVisibility('ex_inquiry_id');"><?php _e('The page_id of the inquiry-form', 'usces'); ?></a></th>
		<td><input name="inquiry_id" type="text" id="inquiry_id" value="<?php echo esc_attr($inquiry_id); ?>" size="7" /></td>
	    <td><div id="ex_inquiry_id" class="explanation"><?php _e('When you want to use the inquiry-form through SSL, please input the page_id.<br />When you use a permanent link, you have need to set the permanent link of this page in usces-inquiry.', 'usces'); ?></div></td>
	</tr>
</table>
<hr />
<table class="form_table">
	<tr height="35">
	    <th class="system_th"><a style="cursor:pointer;" onclick="toggleVisibility('ex_no_cart_css');"><?php _e('To disable usces_cart.css','usces'); ?></a></th>
		<?php $checked = $no_cart_css == 1 ? ' checked="checked"' : ''; ?>
		<td width="10"><input name="no_cart_css" type="checkbox" id="no_cart_css" value="<?php echo esc_attr($no_cart_css); ?>"<?php echo $checked; ?> /></td>
		<td width="300">&nbsp;</td>
	    <td><div id="ex_no_cart_css" class="explanation"><?php _e('When checked, Welcart will not output the usces_cart.css. If you want to make own usces_cart.css file, please copy and paste it in your theme folder currently in use.', 'usces'); ?></div></td>
	</tr>
</table>
<hr />
<table class="form_table">
	<tr height="35">
	    <th class="system_th"><a style="cursor:pointer;" onclick="toggleVisibility('ex_dec_orderID_flag');"><?php _e('Rules of order-ID numbering', 'usces'); ?></a></th>
	    <td width="10"><input name="dec_orderID_flag" id="dec_orderID_flag0" type="radio" value="0"<?php if($dec_orderID_flag === 0) echo 'checked="checked"'; ?> /></td><td width="100"><label for="dec_orderID_flag0"><?php _e('Sequential number', 'usces'); ?></label></td>
	    <td width="10"><input name="dec_orderID_flag" id="dec_orderID_flag1" type="radio" value="1"<?php if($dec_orderID_flag === 1) echo 'checked="checked"'; ?> /></td><td width="100"><label for="dec_orderID_flag1"><?php _e('Random string', 'usces'); ?></label></td>
		<td><div id="ex_dec_orderID_flag" class="explanation"><?php _e("The initial value is a sequential number starting from 1000.", 'usces'); ?></div></td>
	</tr>
</table>
<table class="form_table">
	<tr height="35">
	    <th class="system_th"><a style="cursor:pointer;" onclick="toggleVisibility('ex_dec_orderID_prefix');"><?php _e('Prefix of order-ID', 'usces'); ?></a></th>
		<td><input name="dec_orderID_prefix" type="text" id="dec_orderID_prefix" value="<?php echo esc_attr($dec_orderID_prefix); ?>" size="7" /></td>
	    <td><div id="ex_dec_orderID_prefix" class="explanation"><?php _e('If you do not need it, leave it blank.', 'usces'); ?></div></td>
	</tr>
</table>
<table class="form_table">
	<tr height="35">
	    <th class="system_th"><a style="cursor:pointer;" onclick="toggleVisibility('ex_dec_orderID_digit');"><?php _e('Digits of order-ID', 'usces'); ?></a></th>
		<td><input name="dec_orderID_digit" type="text" id="dec_orderID_digit" value="<?php echo esc_attr($dec_orderID_digit); ?>" size="7" /></td>
	    <td><div id="ex_dec_orderID_digit" class="explanation"><?php _e('This value must be at least six digits. The prefix is ​​not included.', 'usces'); ?></div></td>
	</tr>
</table>
<hr />
<table class="form_table">
	<tr height="30">
	    <th class="system_th" rowspan="2"><a style="cursor:pointer;" onclick="toggleVisibility('ex_subimage_rule');"><?php _e('Product sub-image rule', 'usces'); ?></a></th>
	    <td width="10"><input name="subimage_rule" id="subimage_rule0" type="radio" value="0"<?php if($subimage_rule === 0) echo 'checked="checked"'; ?> /></td><td width="400"><label for="subimage_rule0"><?php _e('not apply the new rule<br />(Truncation Product Code)', 'usces'); ?></label></td>
	    <td rowspan="2"><div id="ex_subimage_rule" class="explanation"><?php _e('If the sub-images are not applied correctly, please apply the new rules.<br />And insert Tow hyphens between the Product Code and serial number.', 'usces'); ?></div></td>
	</tr>
	<tr height="30">
	    <td width="10"><input name="subimage_rule" id="subimage_rule1" type="radio" value="1"<?php if($subimage_rule === 1) echo 'checked="checked"'; ?> /></td><td width="400"><label for="subimage_rule1"><?php _e('apply the new rule<br />(Tow hyphens between the Product Code and serial number)', 'usces'); ?></label></td>
	</tr>
</table>
<hr />
</div>
<!--20110331ysk start-->
</div><!--postbox-->
</div><!--system_page_setting_1-->
<div id="system_page_setting_2">
<div class="postbox">
<h3 class="hndle"><span><?php _e('Language Currency Country','usces'); ?></span></h3>
<div class="inside">
<table class="form_table">
	<tr height="50">
	    <th class="system_th"><a style="cursor:pointer;" onclick="toggleVisibility('ex_front_lang');"><?php _e('The language of the front-end', 'usces'); ?></a></th>
		<td width="10"><select name="front_lang" id="front_lang">
		<?php foreach( $usces_settings['language'] as $Lkey => $Lvalue ){ ?>
		    <option value="<?php echo $Lkey; ?>"<?php echo ($system_front_lang == $Lkey ? ' selected="selected"' : ''); ?>><?php echo $Lvalue; ?></option>
		<?php } ?>
		</select></td>
	    <td><div id="ex_front_lang" class="explanation"><?php _e('You can select the Front-end language. The Back-end language follows setting config.php.', 'usces'); ?></div></td>
	</tr>
</table>
<table class="form_table">
	<tr height="50">
	    <th class="system_th"><a style="cursor:pointer;" onclick="toggleVisibility('ex_currency');"><?php _e('Currencies', 'usces'); ?></a></th>
		<td width="10"><select name="currency" id="currency">
		<?php foreach( $usces_settings['country'] as $Ckey => $Cvalue ){ ?>
		    <option value="<?php echo $Ckey; ?>"<?php echo ($system_currency == $Ckey ? ' selected="selected"' : ''); ?>><?php echo $Cvalue; ?></option>
		<?php } ?>
		    <option value="manual"<?php echo ($system_currency == 'manual' ? ' selected="selected"' : ''); ?>><?php _e('Manual', 'usces'); ?></option>
		</select></td>
	    <td><div id="ex_currency" class="explanation"><?php _e('Displays the currency symbol for each country, the amount separator, the decimal digits. This is a common item in both front end and back end.', 'usces'); ?></div></td>
	</tr>
</table>
<table class="form_table">
	<tr height="50">
	    <th class="system_th"><a style="cursor:pointer;" onclick="toggleVisibility('ex_addressform');"><?php _e('The name and address form', 'usces'); ?></a></th>
		<td width="10"><select name="addressform" id="addressform">
		<?php foreach( $usces_settings['country'] as $Ckey => $Cvalue ){ ?>
		    <option value="<?php echo $Ckey; ?>"<?php echo ($system_addressform == $Ckey ? ' selected="selected"' : ''); ?>><?php echo $Cvalue; ?></option>
		<?php } ?>
		</select></td>
	    <td><div id="ex_addressform" class="explanation"><?php _e('For entry form style, choose your country.', 'usces'); ?></div></td>
	</tr>
</table>
<table class="form_table">
	<tr height="50">
	    <th class="system_th">
			<a style="cursor:pointer;" onclick="toggleVisibility('ex_target_market');"><?php _e('Target Market', 'usces'); ?></a>
			<div><input name="set_target_market" id="set_target_market" type="button" value="<?php _e('Choose', 'usces'); ?>" onclick="operation.set_target_market();" /></div>
		</th>
		<td width="20"><select name="target_market[]" size="10" multiple="multiple" class="multipleselect" id="target_market">
		    <!--<option value="all"<?php echo ($system_target_markets == 'all' ? ' selected="selected"' : ''); ?>><?php _e('All countries', 'usces'); ?></option>-->
		<?php foreach( $usces_settings['country'] as $Ckey => $Cvalue ){ ?>
		    <option value="<?php echo $Ckey; ?>"<?php echo (in_array($Ckey, $system_target_markets) ? ' selected="selected"' : ''); ?>><?php echo $Cvalue; ?></option>
		<?php } ?>
		</select></td>
	    <td><div id="ex_target_market" class="explanation"><?php _e('Select the number of possible areas within the country. Allows multiple selections.', 'usces'); ?></div></td>
	</tr>
</table>
<table class="form_table">
	<tr height="50">
		<th class="system_th">
			<a style="cursor:pointer;" onclick="toggleVisibility('ex_province');"><?php _e('Province', 'usces'); ?></a>
			<div><span id="target_market_loading"></span><span id="target_market_province"></span></div>
		</th>
		<td width="150"><textarea name="province" id="province" cols="30" rows="10"></textarea><div id="province_ajax"></div></td>
	    <td><div id="ex_province" class="explanation"><?php _e('The district where sale is possible', 'usces'); ?>(<?php _e('Province', 'usces'); ?>) <?php _e('One line one by one.', 'usces'); ?></div></td>
	</tr>
</table>
</div>
</div><!--postbox-->
</div><!--system_page_setting_2-->
</div><!--uscestabs_system-->
<!--20110331ysk end-->


</div><!--poststuff-->



<input name="usces_option_update" type="submit" class="button" value="<?php _e('change decision','usces'); ?>" />
<input type="hidden" id="post_ID" name="post_ID" value="<?php echo USCES_CART_NUMBER ?>" />
</form>
</div><!--usces_admin-->
</div><!--wrap-->