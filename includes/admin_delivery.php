<?php
$status = $this->action_status;
$message = $this->action_message;
$this->action_status = 'none';
$this->action_message = '';
$delivery_method = $this->options['delivery_method'];
$shipping_charge = $this->options['shipping_charge'];
//	$prefs = get_option('usces_pref');
//20110317ysk start
	//$prefs = $this->options['province'];
//array_shift($prefs);
//global $usces_states;
$target_market = ( isset($this->options['system']['target_market']) && !empty($this->options['system']['target_market']) ) ? $this->options['system']['target_market'] : usces_get_local_target_market();
foreach((array)$target_market as $tm) {
//20110331ysk start
	//$prefs[$tm] = $usces_states[$tm];
	$prefs[$tm] = get_usces_states($tm);
//20110331ysk end
	array_shift($prefs[$tm]);
}
//20110317ysk end
//20101208ysk start
$delivery_time_limit['hour'] = $this->options['delivery_time_limit']['hour'];
$delivery_time_limit['min'] = $this->options['delivery_time_limit']['min'];
$shortest_delivery_time = $this->options['shortest_delivery_time'];
$delivery_after_days = (empty($this->options['delivery_after_days'])) ? 15 : (int)$this->options['delivery_after_days'];
$delivery_days = $this->options['delivery_days'];
//20101208ysk end
//20110317ysk start
$base_country = $this->options['system']['base_country'];
//20110317ysk end
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
//20110317ysk start
<?php
	$target_market_arr = '';
	foreach((array)$target_market as $tm) $target_market_arr .= "'".$tm."',";
	$target_market_arr = rtrim($target_market_arr, ",");
	if(!array_key_exists($base_country, (array)$target_market)) $base_country = $target_market[0];//20110509ysk
?>
	var target_market = new Array(<?php echo $target_market_arr; ?>);
	var base_country = '<?php echo $base_country; ?>';
//20110317ysk end

	var delivery_method = [];
<?php for($i=0; $i<count((array)$delivery_method); $i++){ $lines = split("\n", $delivery_method[$i]['time']); ?>
	delivery_method[<?php echo $i; ?>] = [];
	delivery_method[<?php echo $i; ?>]['id'] = <?php echo (int)$delivery_method[$i]['id']; ?>;
	delivery_method[<?php echo $i; ?>]['name'] = "<?php echo $delivery_method[$i]['name']; ?>";
	delivery_method[<?php echo $i; ?>]['charge'] = <?php echo (int)$delivery_method[$i]['charge']; ?>;
//20101228ysk start
	delivery_method[<?php echo $i; ?>]['days'] = <?php echo (int)$delivery_method[$i]['days']; ?>;
//20101228ysk end
	sttr = '';
	<?php foreach((array)$lines as $line){ 	if(trim($line) != ''){ ?>
	sttr += "<?php echo trim($line); ?>\n";
	<?php } } ?>
	delivery_method[<?php echo $i; ?>]['time'] = sttr;
//20101119ysk start
	delivery_method[<?php echo $i; ?>]['nocod'] = "<?php echo $delivery_method[$i]['nocod']; ?>";
//20101119ysk end
//20110317ysk start
//20110422ysk start
	delivery_method[<?php echo $i; ?>]['intl'] = "<?php echo (!empty($delivery_method[$i]['intl'])) ? $delivery_method[$i]['intl'] : '0'; ?>";
//20110422ysk end
//20110317ysk end
<?php } ?>

//20110317ysk start
	var pref = [];
<?php //foreach((array)$prefs as $pref){ ?>
	//pref.push('<?php echo $pref; ?>');
<?php //} ?>
<?php foreach((array)$target_market as $tm){ ?>
	pref['<?php echo $tm; ?>'] = [];
<?php foreach((array)$prefs[$tm] as $pref){ ?>
	pref['<?php echo $tm; ?>'].push('<?php echo $pref; ?>');
<?php }} ?>
//20110317ysk end
	var shipping_charge = [];
<?php for($i=0; $i<count((array)$shipping_charge); $i++){ ?>
	shipping_charge[<?php echo $i; ?>] = [];
	shipping_charge[<?php echo $i; ?>]['id'] = <?php echo (int)$shipping_charge[$i]['id']; ?>;
	shipping_charge[<?php echo $i; ?>]['name'] = "<?php echo $shipping_charge[$i]['name']; ?>";
	shipping_charge[<?php echo $i; ?>]['value'] = [];
//20110317ysk start
<?php $country = (empty($shipping_charge[$i]['country'])) ? $base_country : $shipping_charge[$i]['country']; ?>
	shipping_charge[<?php echo $i; ?>]['country'] = "<?php echo $country ?>";
	<?php //foreach((array)$prefs as $pref){ ?>
	<?php foreach((array)$prefs[$country] as $pref){ ?>
//20110317ysk end
	shipping_charge[<?php echo $i; ?>]['value']['<?php echo $pref; ?>'] = <?php echo (int)$shipping_charge[$i]['value'][$pref]; ?>;
<?php }} ?>

//20101208ysk start
	var delivery_days = [];
<?php for($i=0; $i<count((array)$delivery_days); $i++){ ?>
	delivery_days[<?php echo $i; ?>] = [];
	delivery_days[<?php echo $i; ?>]['id'] = <?php echo (int)$delivery_days[$i]['id']; ?>;
	delivery_days[<?php echo $i; ?>]['name'] = "<?php echo $delivery_days[$i]['name']; ?>";
	delivery_days[<?php echo $i; ?>]['value'] = [];
//20110317ysk start
<?php $country = (empty($delivery_days[$i]['country'])) ? $base_country : $delivery_days[$i]['country']; ?>
	delivery_days[<?php echo $i; ?>]['country'] = "<?php echo $country; ?>";
	<?php //foreach((array)$prefs as $pref){ ?>
	<?php foreach((array)$prefs[$country] as $pref){ ?>
//20110317ysk end
	delivery_days[<?php echo $i; ?>]['value']['<?php echo $pref; ?>'] = <?php echo (int)$delivery_days[$i]['value'][$pref]; ?>;
<?php }} ?>
//20101208ysk end

	var selected_method = 0;
	function get_delivery_method_charge(selected){
		var index = 0;
		for(var i=0; i<delivery_method.length; i++){
			if(selected === delivery_method[i]['id']){
				index = i;
			}
		}
		if(undefined === delivery_method[index]){
			return -1;
		}else{
			return delivery_method[index]['charge'];
		}
	}
	
	$("#delivery_method_charge").click(function () {
		if(shipping_charge.length == 0){
			alert('<?php _e('Please set the shipping price', 'usces'); ?>');
		}
	});
	
//20101208ysk start
	function get_delivery_method_days(selected){
		var index = 0;
		for(var i=0; i<delivery_method.length; i++){
			if(selected === delivery_method[i]['id']){
				index = i;
			}
		}
		if(undefined === delivery_method[index]){
			return -1;
		}else{
			return delivery_method[index]['days'];
		}
	}
	
	$("#delivery_method_days").click(function () {
		if(delivery_days.length == 0){
			alert('<?php _e('Please set the delivery days', 'usces'); ?>');
		}
	});
//20101208ysk end
	
	$("#new_delivery_method_action").click(function () {
		if(delivery_method.length === 0) return false;
		$("#delivery_method_name").html('<input name="delivery_method_name" type="text" value="" />');
		$("#delivery_method_name2").html('');
		$("#delivery_method_time").val('');
		$("#delivery_method_button").html('<input name="cancel_delivery_method" id="cancel_delivery_method" type="button" value="<?php _e('Cancel', 'usces'); ?>" onclick="operation.disp_delivery_method(0);" /><input name="add_delivery_method" id="add_delivery_method" type="button" value="<?php _e('Add', 'usces'); ?>" onclick="operation.add_delivery_method();" />');
//20101119ysk start
		$("#delivery_method_nocod").html('<input name="delivery_method_nocod" type="checkbox" value="1" />');
//20101119ysk end
//20110317ysk start
		$("#delivery_method_intl").html('<input name="delivery_method_intl" id="delivery_method_intl_0" type="radio" value="0" checked /><label for="delivery_method_intl_0"><?php _e('国内便', 'uesces'); ?></label>　<input name="delivery_method_intl" id="delivery_method_intl_1" type="radio" value="1" /><label for="delivery_method_intl_1"><?php _e('国際便', 'uesces'); ?></label>');
//20110317ysk end
		$("input[name='delivery_method_name']").focus().select();
		operation.make_delivery_method_charge(-1);
//20101208ysk start
		operation.make_delivery_method_days(-1);
//20101208ysk end
	});
	
	$("#moveup_action").click(function () {
		var id = $("#delivery_method_name_select option:selected").val()-0;
		operation.moveup_delivery_method(id);
		operation.disp_delivery_method(id);
	});
	
	$("#movedown_action").click(function () {
		var id = $("#delivery_method_name_select option:selected").val()-0;
		operation.movedown_delivery_method(id);
		operation.disp_delivery_method(id);
	});
	
	$("#new_shipping_charge_action").click(function () {
		if(shipping_charge.length === 0) return false;
		var valuehtml = '';
//20110317ysk start
		//for(var i=0; i<pref.length; i++){
		//	valuehtml += "<div class='clearfix'><label class='shipping_charge_label'>" + pref[i] + "</label><input type='text' name='shipping_charge_value[" + pref[i] + "]' value='' class='charge_text' /><?php usces_crcode(); ?></div>\n";
		//}
		for(var i=0; i<pref[base_country].length; i++){
			valuehtml += "<div class='clearfix'><label class='shipping_charge_label'>" + pref[base_country][i] + "</label><input type='text' name='shipping_charge_value[" + pref[base_country][i] + "]' value='' class='charge_text' /><?php usces_crcode(); ?></div>\n";
		}
		$("#shipping_charge_country").val(base_country);
//20110317ysk end
		$("#shipping_charge_name").html('<input name="shipping_charge_name" type="text" value="" />');
		$("#shipping_charge_name2").html('');
		$("#shipping_charge_value").html(valuehtml);
		$("#shipping_charge_button").html('<input name="cancel_shipping_charge" id="cancel_shipping_charge" type="button" value="<?php _e('Cancel', 'usces'); ?>" onclick="operation.disp_shipping_charge(0);" /><input name="add_shipping_charge" id="add_shipping_charge" type="button" value="<?php _e('Add', 'usces'); ?>" onclick="operation.add_shipping_charge();" />');
		$("input[name='shipping_charge_name']").focus().select();
	});
	
//20101208ysk start
	$("#new_delivery_days_action").click(function () {
		if(delivery_days.length === 0) return false;
		var valuehtml = '';
//20110317ysk start
		//for(var i=0; i<pref.length; i++){
		//	valuehtml += "<div class='clearfix'><label class='delivery_days_label'>" + pref[i] + "</label><input type='text' name='delivery_days_value[" + pref[i] + "]' value='' class='days_text' /><?php _e('day', 'usces'); ?></div>\n";
		//}
		for(var i=0; i<pref[base_country].length; i++){
			valuehtml += "<div class='clearfix'><label class='delivery_days_label'>" + pref[base_country][i] + "</label><input type='text' name='delivery_days_value[" + pref[base_country][i] + "]' value='' class='days_text' /><?php _e('day', 'usces'); ?></div>\n";
		}
		$("#delivery_days_country").val(base_country);
//20110317ysk end
		$("#delivery_days_name").html('<input name="delivery_days_name" type="text" value="" />');
		$("#delivery_days_name2").html('');
		$("#delivery_days_value").html(valuehtml);
		$("#delivery_days_button").html('<input name="cancel_delivery_days" id="cancel_delivery_days" type="button" value="<?php _e('Cancel', 'usces'); ?>" onclick="operation.disp_delivery_days(0);" /><input name="add_delivery_days" id="add_delivery_days" type="button" value="<?php _e('Add', 'usces'); ?>" onclick="operation.add_delivery_days();" />');
		$("input[name='delivery_days_name']").focus().select();
	});
//20101208ysk end
	
	operation = {
		disp_delivery_method :function (id){
			selected_method = id;
			var index = false;
			for(var i=0; i<delivery_method.length; i++){
				if(id === delivery_method[i]['id']){
					index = i;
				}
			}
			if(false === index){
				selected = 0;
			}else{
				selected = index;
			}
			if(delivery_method.length === 0) {
				$("#delivery_method_name").html('<input name="delivery_method_name" type="text" value="" />');
				$("#delivery_method_name2").html('');
				$("#delivery_method_time").val('');
				$("#delivery_method_button").html('<input name="add_delivery_method" id="add_delivery_method" type="button" value="<?php _e('Add', 'usces'); ?>" onclick="operation.add_delivery_method();" />');
//20101119ysk start
				$("#delivery_method_nocod").html('<input name="delivery_method_nocod" type="checkbox" value="1" />');
//20101119ysk end
//20110317ysk start
				$("#delivery_method_intl").html('<input name="delivery_method_intl" id="delivery_method_intl_0" type="radio" value="0" checked /><label for="delivery_method_intl_0"><?php _e('国内便', 'uesces'); ?></label>　<input name="delivery_method_intl" id="delivery_method_intl_1" type="radio" value="1" /><label for="delivery_method_intl_1"><?php _e('国際便', 'uesces'); ?></label>');
//20110317ysk end
				operation.make_delivery_method_charge(-1);
//20101208ysk start
				operation.make_delivery_method_days(-1);
//20101208ysk end
			}else{
				var name_select = '<select name="delivery_method_name_select" id="delivery_method_name_select" onchange="operation.onchange_delivery_select(this.selectedIndex);">'+"\n";
				for(var i=0; i<delivery_method.length; i++){
					if(selected === i){
						name_select += '<option value="'+delivery_method[i]['id']+'" selected="selected">'+(i)+' : '+delivery_method[i]['name']+'</option>'+"\n";
					}else{
						name_select += '<option value="'+delivery_method[i]['id']+'">'+(i)+' : '+delivery_method[i]['name']+'</option>'+"\n";
					}
				}
				name_select += "</select>\n";
				$("#delivery_method_name").html(name_select);
				$("#delivery_method_name2").html('<input name="delivery_method_name" type="text" value="'+delivery_method[selected]['name']+'" />');
				$("#delivery_method_time").val(delivery_method[selected]['time']);
				$("#delivery_method_button").html("<input name='delete_delivery_method' id='delete_delivery_method' type='button' value='<?php _e('Delete', 'usces'); ?>' onclick='operation.delete_delivery_method();' /><input name='update_delivery_method' id='update_delivery_method' type='button' value='<?php _e('update', 'usces'); ?>' onclick='operation.update_delivery_method();' />");
//20110317ysk start
//20101119ysk start
				var checked_nocod = (delivery_method[selected]['nocod'] == '1') ? ' checked' : '';
				$("#delivery_method_nocod").html('<input name="delivery_method_nocod" type="checkbox" value="1"'+checked_nocod+' />');
//20101119ysk end
				var checked_intl_0 = (delivery_method[selected]['intl'] == '0') ? ' checked' : '';
				var checked_intl_1 = (delivery_method[selected]['intl'] == '1') ? ' checked' : '';
				$("#delivery_method_intl").html('<input name="delivery_method_intl" id="delivery_method_intl_0" type="radio" value="0"'+checked_intl_0+' /><label for="delivery_method_intl_0"><?php _e('国内便', 'uesces'); ?></label>　<input name="delivery_method_intl" id="delivery_method_intl_1" type="radio" value="1"'+checked_intl_1+' /><label for="delivery_method_intl_1"><?php _e('国際便', 'uesces'); ?></label>');
//20110317ysk end
				operation.make_delivery_method_charge(get_delivery_method_charge(selected_method));
//20101208ysk start
				operation.make_delivery_method_days(get_delivery_method_days(selected_method));
//20101208ysk end
			}
		},
		
		add_delivery_method : function() {
			if($("input[name='delivery_method_name']").val() == "") return;
			
			$("#delivery_method_loading").html('<img src="<?php echo USCES_PLUGIN_URL; ?>/images/loading-publish.gif" />');
			var name = encodeURIComponent($("input[name='delivery_method_name']").val());
			var time = encodeURIComponent($("#delivery_method_time").val());
			var charge = $("#delivery_method_charge option:selected").val();
//20101208ysk start
			var days = $("#delivery_method_days option:selected").val();
//20101208ysk end
//20101119ysk start
			var nocod = ($(':input[name=delivery_method_nocod]').attr('checked') == true) ? '1' : '0';
//20101119ysk end
//20110317ysk start
			var intl = $(':radio[name=delivery_method_intl]:checked').val();
//20110317ysk end
			
			var s = operation.settings;
//20101208ysk start
//20101119ysk start
//20110317ysk start
			//s.data = "action=shop_options_ajax&mode=add_delivery_method&name=" + name + "&charge=" + charge + "&time=" + time;
			//s.data = "action=shop_options_ajax&mode=add_delivery_method&name=" + name + "&charge=" + charge + "&time=" + time + "&nocod=" + nocod;
			//s.data = "action=shop_options_ajax&mode=add_delivery_method&name=" + name + "&time=" + time + "&charge=" + charge + "&days=" + days + "&nocod=" + nocod;
			s.data = "action=shop_options_ajax&mode=add_delivery_method&name=" + name + "&time=" + time + "&charge=" + charge + "&days=" + days + "&nocod=" + nocod + "&intl=" + intl;
//20110317ysk end
//20101119ysk end
//20101208ysk end
			s.success = function(data, dataType){
				var strs = data.split('#usces#');
				var id = strs[0] - 0;
				var name = strs[1];
				var time = strs[2];
				var charge = strs[3] - 0;
//20101208ysk start
				var days = strs[4] - 0;
//20101119ysk start
				//var nocod = strs[4];
				var nocod = strs[5];
//20101119ysk end
//20101208ysk end
//20110317ysk start
				var intl = strs[6];
//20110317ysk end
				var index = delivery_method.length;
				delivery_method[index] = [];
				delivery_method[index]['id'] = id;
				delivery_method[index]['name'] = name;
				delivery_method[index]['time'] = time;
				delivery_method[index]['charge'] = charge;
//20101208ysk start
				delivery_method[index]['days'] = days;
//20101208ysk end
//20101119ysk start
				delivery_method[index]['nocod'] = nocod;
//20101119ysk end
//20110317ysk start
				delivery_method[index]['intl'] = intl;
//20110317ysk end
				operation.disp_delivery_method(id);
				$("#delivery_method_loading").html('');
			};
			$.ajax( s );
			return false;
		},
		
		update_delivery_method : function() {
			$("#delivery_method_loading").html('<img src="<?php echo USCES_PLUGIN_URL; ?>/images/loading-publish.gif" />');
			var id = $("#delivery_method_name_select option:selected").val();
			var name = encodeURIComponent($("input[name='delivery_method_name']").val());
			var time = encodeURIComponent($("#delivery_method_time").val());
			var charge = $("#delivery_method_charge option:selected").val();
//20101208ysk start
			var days = $("#delivery_method_days option:selected").val();
//20101208ysk end
//20101119ysk start
			var nocod = ($(':input[name=delivery_method_nocod]').attr('checked') == true) ? '1' : '0';
//20101119ysk end
//20110317ysk start
			var intl = $(':radio[name=delivery_method_intl]:checked').val();
//20110317ysk end
			
			var s = operation.settings;
//20101208ysk start
//20101119ysk start
//20110317ysk start
			//s.data = "action=shop_options_ajax&mode=update_delivery_method&name=" + name + "&id=" + id + "&time=" + time + "&charge=" + charge;
			//s.data = "action=shop_options_ajax&mode=update_delivery_method&name=" + name + "&id=" + id + "&time=" + time + "&charge=" + charge + "&nocod=" + nocod;
			//s.data = "action=shop_options_ajax&mode=update_delivery_method&name=" + name + "&id=" + id + "&time=" + time + "&charge=" + charge + "&days=" + days + "&nocod=" + nocod;
			s.data = "action=shop_options_ajax&mode=update_delivery_method&name=" + name + "&id=" + id + "&time=" + time + "&charge=" + charge + "&days=" + days + "&nocod=" + nocod + "&intl=" + intl;
//20110317ysk end
//20101119ysk end
//20101208ysk end
			s.success = function(data, dataType){
				var strs = data.split('#usces#');
				var id = strs[0] - 0;
				var name = strs[1];
				var time = strs[2];
				var charge = strs[3]-0;
//20101208ysk start
				var days = strs[4] - 0;
//20101119ysk start
				//var nocod = strs[4];
				var nocod = strs[5];
//20101119ysk end
//20101208ysk end
//20110317ysk start
				var intl = strs[6];
//20110317ysk end
				for(var i=0; i<delivery_method.length; i++){
					if(id === delivery_method[i]['id']){
						index = i;
					}
				}
				delivery_method[index]['name'] = name;
				delivery_method[index]['time'] = time;
				delivery_method[index]['charge'] = charge;
//20101208ysk start
				delivery_method[index]['days'] = days;
//20101208ysk end
//20101119ysk start
				delivery_method[index]['nocod'] = nocod;
//20101119ysk end
//20110317ysk start
				delivery_method[index]['intl'] = intl;
//20110317ysk end
				operation.disp_delivery_method(id);
				$("#delivery_method_loading").html('');
			};
			$.ajax( s );
			return false;
		},
		
		delete_delivery_method : function() {
			var delname = $("#delivery_method_name_select option:selected").html();
			if(!confirm(<?php _e("'Are you sure of deleting the delivery method ' + delname + ' ?'", 'usces'); ?>)) return false;
			
			$("#delivery_method_loading").html('<img src="<?php echo USCES_PLUGIN_URL; ?>/images/loading-publish.gif" />');
			var id = $("#delivery_method_name_select option:selected").val();
			
			var s = operation.settings;
			s.data = "action=shop_options_ajax&mode=delete_delivery_method&id=" + id;
			s.success = function(data, dataType){
				var strs = data.split('#usces#');
				var id = strs[0] - 0;
				for(var i=0; i<delivery_method.length; i++){
					if(id === delivery_method[i]['id']){
						index = i;
					}
				}
				delivery_method.splice(index, 1);
				//operation.disp_delivery_method(delivery_method[0]['id']);
				operation.disp_delivery_method(0);
				$("#delivery_method_loading").html('');
			};
			$.ajax( s );
			return false;
		},
		
		onchange_delivery_select : function(index) {
			var id = $("#delivery_method_name_select option:eq("+index+")").val()-0;
			operation.disp_delivery_method(id);
		},
		
		make_delivery_method_charge : function(selected) {
			var option = '<select name="delivery_method_charge" id="delivery_method_charge">'+"\n";
			if(selected === -1){
				option += '<option value="-1" selected="selected"><?php _e('Not fixing shipping.', 'usces'); ?></option>'+"\n";
			}else{
				option += '<option value="-1"><?php _e('Not fixing shipping.', 'usces'); ?></option>'+"\n";
			}
			for(var i=0; i<shipping_charge.length; i++){
				if(selected === shipping_charge[i]['id']){
					option += '<option value="'+shipping_charge[i]['id']+'" selected="selected">'+shipping_charge[i]['name']+'</option>'+"\n";
				}else{
					option += '<option value="'+shipping_charge[i]['id']+'">'+shipping_charge[i]['name']+'</option>'+"\n";
				}
			}
			option += '</select>';
			$("#delivery_method_charge_td").html(option);
		},
//20101208ysk start
		make_delivery_method_days : function(selected) {
			var option = '<select name="delivery_method_days" id="delivery_method_days">'+"\n";
			if(selected === -1){
				option += '<option value="-1" selected="selected"><?php _e('配送希望日を利用しない', 'usces'); ?></option>'+"\n";
			}else{
				option += '<option value="-1"><?php _e('配送希望日を利用しない', 'usces'); ?></option>'+"\n";
			}
			for(var i=0; i<delivery_days.length; i++){
				if(selected === delivery_days[i]['id']){
					option += '<option value="'+delivery_days[i]['id']+'" selected="selected">'+delivery_days[i]['name']+'</option>'+"\n";
				}else{
					option += '<option value="'+delivery_days[i]['id']+'">'+delivery_days[i]['name']+'</option>'+"\n";
				}
			}
			option += '</select>';
			$("#delivery_method_days_td").html(option);
		},
//20101208ysk end
		
		moveup_delivery_method : function(selected) {
			var index = 0;
			for(var i=0; i<delivery_method.length; i++){
				if(selected === delivery_method[i]['id']){
					index = i;
				}
			}
			if( 0 === index ) return;
			
			$("#delivery_method_loading").html('<img src="<?php echo USCES_PLUGIN_URL; ?>/images/loading-publish.gif" />');
			
			var s = operation.settings;
			s.data = "action=shop_options_ajax&mode=moveup_delivery_method&id=" + selected;
			s.success = function(data, dataType){
				var strs = data.split('#usces#');
				var id = strs[0].split(',');
				var name = strs[1].split(',');
				var time = strs[2].split(',');
				var charge = strs[3].split(',');
//20101208ysk start
				var days = strs[4].split(',');
//20101119ysk start
				//var nocod = strs[4].split(',');
				////var selected = strs[4]-0;
				//var selected = strs[5]-0;
				var nocod = strs[5].split(',');
//20110317ysk start
				var intl = strs[6].split(',');
				//var selected = strs[6]-0;
				var selected = strs[7]-0;
//20110317ysk end
//20101119ysk end
//20101208ysk end
				var ct = delivery_method.length;
				for(var i=0; i<ct; i++){
					delivery_method[i]['id'] = id[i]-0;
					delivery_method[i]['name'] = name[i];
					delivery_method[i]['time'] = time[i];
					delivery_method[i]['charge'] = charge[i]-0;
//20101208ysk start
					delivery_method[i]['days'] = days[i]-0;
//20101208ysk end
//20101119ysk start
					delivery_method[i]['nocod'] = nocod[i];
//20101119ysk end
//20110317ysk start
					delivery_method[i]['intl'] = intl[i];
//20110317ysk end
				}
				operation.disp_delivery_method(selected);
				$("#delivery_method_loading").html('');
			};
			$.ajax( s );
			return false;
		},
		
		movedown_delivery_method : function(selected) {
			var index = 0;
			var ct = delivery_method.length;
			for(var i=0; i<ct; i++){
				if(selected === delivery_method[i]['id']){
					index = i;
				}
			}
			if( index >= ct-1 ) return;
			
			$("#delivery_method_loading").html('<img src="<?php echo USCES_PLUGIN_URL; ?>/images/loading-publish.gif" />');
			
			var s = operation.settings;
			s.data = "action=shop_options_ajax&mode=movedown_delivery_method&id=" + selected;
			s.success = function(data, dataType){
				var strs = data.split('#usces#');
				var id = strs[0].split(',');
				var name = strs[1].split(',');
				var time = strs[2].split(',');
				var charge = strs[3].split(',');
//20101208ysk start
				var days = strs[4].split(',');
//20101119ysk start
				//var nocod = strs[4].split(',');
				////var selected = strs[4]-0;
				//var selected = strs[5]-0;
				var nocod = strs[5].split(',');
//20110317ysk start
				var intl = strs[6].split(',');
				//var selected = strs[6]-0;
				var selected = strs[7]-0;
//20110317ysk end
//20101119ysk end
//20101208ysk end
				var ct = delivery_method.length;
				for(var i=0; i<ct; i++){
					delivery_method[i]['id'] = id[i]-0;
					delivery_method[i]['name'] = name[i];
					delivery_method[i]['time'] = time[i];
					delivery_method[i]['charge'] = charge[i]-0;
//20101208ysk start
					delivery_method[i]['days'] = days[i]-0;
//20101208ysk end
//20101119ysk start
					delivery_method[i]['nocod'] = nocod[i];
//20101119ysk end
//20110317ysk start
					delivery_method[i]['intl'] = intl[i];
//20110317ysk end
				}
				operation.disp_delivery_method(selected);
				$("#delivery_method_loading").html('');
			};
			$.ajax( s );
			return false;
		},
		
		disp_shipping_charge :function (id){
			var valuehtml = '';
			if(shipping_charge.length === 0) {
//20110317ysk start
				//for(var i=0; i<pref.length; i++){
				//	valuehtml += "<div class='clearfix'><label class='shipping_charge_label'>" + pref[i] + "</label><input type='text' name='shipping_charge_value[" + pref[i] + "]' value='' class='charge_text' /><?php usces_crcode(); ?></div>\n";
				//}
				for(var i=0; i<pref[base_country].length; i++){
					valuehtml += "<div class='clearfix'><label class='shipping_charge_label'>" + pref[base_country][i] + "</label><input type='text' name='shipping_charge_value[" + pref[base_country][i] + "]' value='' class='charge_text' /><?php usces_crcode(); ?></div>\n";
				}
				$("#shipping_charge_country").val(base_country);
//20110317ysk end
				$("#shipping_charge_name").html('<input name="shipping_charge_name" type="text" value="" />');
				$("#shipping_charge_name2").html('');
				$("#shipping_charge_value").html(valuehtml);
				$("#shipping_charge_button").html('<input name="add_shipping_charge" id="add_shipping_charge" type="button" value="<?php _e('Add', 'usces'); ?>" onclick="operation.add_shipping_charge();" />');
			}else{
				var selected = 0;
//20110317ysk start
				var country = base_country;
//20110317ysk end
				var name_select = '<select name="shipping_charge_name_select" id="shipping_charge_name_select" onchange="operation.onchange_shipping_charge(this.selectedIndex);">'+"\n";
				for(var i=0; i<shipping_charge.length; i++){
					if(shipping_charge[i]['id'] === id){
						selected = i;
//20110317ysk start
						country = shipping_charge[i]['country'];
//20110317ysk end
						name_select += '<option value="'+shipping_charge[i]['id']+'" selected="selected">'+shipping_charge[i]['name']+'</option>'+"\n";
					}else{
						name_select += '<option value="'+shipping_charge[i]['id']+'">'+shipping_charge[i]['name']+'</option>'+"\n";
					}
				}
				name_select += "</select>\n";
//20110317ysk start
				var value = '';
				//for(var i=0; i<pref.length; i++){
				//	valuehtml += "<div class='clearfix'><label class='shipping_charge_label'>" + pref[i] + "</label><input type='text' name='shipping_charge_value[" + pref[i] + "]' value='" + shipping_charge[selected]['value'][pref[i]] + "' class='charge_text' /><?php usces_crcode(); ?></div>\n";
				//}
				for(var i=0; i<pref[country].length; i++){
					value = (shipping_charge[selected]['value'][pref[country][i]] == undefined) ? '' : shipping_charge[selected]['value'][pref[country][i]];
					valuehtml += "<div class='clearfix'><label class='shipping_charge_label'>" + pref[country][i] + "</label><input type='text' name='shipping_charge_value[" + pref[country][i] + "]' value='" + value + "' class='charge_text' /><?php usces_crcode(); ?></div>\n";
				}
				$("#shipping_charge_country").val(country);
//20110317ysk end
				$("#shipping_charge_name").html(name_select);
				$("#shipping_charge_name2").html('<input name="shipping_charge_name" type="text" value="'+shipping_charge[selected]['name']+'" />');
				$("#shipping_charge_value").html(valuehtml);
				$("#shipping_charge_button").html("<input name='delete_shipping_charge' id='delete_shipping_charge' type='button' value='<?php _e('Delete', 'usces'); ?>' onclick='operation.delete_shipping_charge();' /><input name='update_shipping_charge' id='update_shipping_charge' type='button' value='<?php _e('update', 'usces'); ?>' onclick='operation.update_shipping_charge();' />");
			}
		},
		
		add_shipping_charge : function() {
			if($("input[name='shipping_charge_name']").val() == "") return;
			
			$("#shipping_charge_loading").html('<img src="<?php echo USCES_PLUGIN_URL; ?>/images/loading-publish.gif" />');
			var name = encodeURIComponent($("input[name='shipping_charge_name']").val());
			var query = '';
//20110317ysk start
			var country = $("#shipping_charge_country").val();
			//for(var i=0; i<pref.length; i++){
			//	query += '&value[]=' + $("input[name='shipping_charge_value\[" + pref[i] + "\]']").val();
			//}
			for(var i=0; i<pref[country].length; i++){
				query += '&value[]=' + $("input[name='shipping_charge_value\[" + pref[country][i] + "\]']").val();
			}
//20110317ysk end
			
			var s = operation.settings;
//20110317ysk start
			//s.data = "action=shop_options_ajax&mode=add_shipping_charge&name=" + name + query;
			s.data = "action=shop_options_ajax&mode=add_shipping_charge&name=" + name + "&country=" + country + query;
//20110317ysk end
			s.success = function(data, dataType){
				var strs = data.split('#usces#');
				var id = strs[0] - 0;
				var name = strs[1];
//20110317ysk start
				var country = strs[2];
				//var value = strs[2].split(',');
				var value = strs[3].split(',');
//20110317ysk end
				var index = shipping_charge.length;
				shipping_charge[index] = [];
				shipping_charge[index]['id'] = id;
				shipping_charge[index]['name'] = name;
				shipping_charge[index]['value'] = [];
//20110317ysk start
				shipping_charge[index]['country'] = country;
				//for(var i=0; i<pref.length; i++){
				//	shipping_charge[index]['value'][pref[i]] = value[i];
				//}
				for(var i=0; i<pref[country].length; i++){
					shipping_charge[index]['value'][pref[country][i]] = value[i];
				}
//20110317ysk end
				operation.disp_shipping_charge(id);
				operation.make_delivery_method_charge(get_delivery_method_charge(selected_method));
				$("#shipping_charge_loading").html('');
				
			};
			$.ajax( s );
			return false;
		},
		
		update_shipping_charge : function() {
			$("#shipping_charge_loading").html('<img src="<?php echo USCES_PLUGIN_URL; ?>/images/loading-publish.gif" />');
			var id = $("#shipping_charge_name_select option:selected").val();
			var name = encodeURIComponent($("input[name='shipping_charge_name']").val());
			var query = '';
//20110317ysk start
			var country = $("#shipping_charge_country").val();
			//for(var i=0; i<pref.length; i++){
			//	query += '&value[]=' + $("input[name='shipping_charge_value\[" + pref[i] + "\]']").val();
			//}
			for(var i=0; i<pref[country].length; i++){
				query += '&value[]=' + $("input[name='shipping_charge_value\[" + pref[country][i] + "\]']").val();
			}
//20110317ysk end
			var s = operation.settings;
//20110317ysk start
			//s.data = "action=shop_options_ajax&mode=update_shipping_charge&id=" + id + "&name=" + name + query;
			s.data = "action=shop_options_ajax&mode=update_shipping_charge&id=" + id + "&name=" + name + "&country=" + country + query;
//20110317ysk end
			s.success = function(data, dataType){
				var strs = data.split('#usces#');
				var id = strs[0] - 0;
				var name = strs[1];
//20110317ysk start
				var country = strs[2];
				//var value = strs[2].split(',');
				var value = strs[3].split(',');
//20110317ysk end
				for(var i=0; i<shipping_charge.length; i++){
					if(id === shipping_charge[i]['id']){
						index = i;
					}
				}
				shipping_charge[index]['name'] = name;
//20110317ysk start
				shipping_charge[index]['value'] = [];
				shipping_charge[index]['country'] = country;
				//for(var i=0; i<pref.length; i++){
				//	shipping_charge[index]['value'][pref[i]] = value[i];
				//}
				for(var i=0; i<pref[country].length; i++){
					shipping_charge[index]['value'][pref[country][i]] = value[i];
				}
//20110317ysk end
				operation.disp_shipping_charge(id);
				operation.make_delivery_method_charge(get_delivery_method_charge(selected_method));
				$("#shipping_charge_loading").html('');
			};
			$.ajax( s );
			return false;
		},
		
		delete_shipping_charge : function() {
			var delname = $("#shipping_charge_name_select option:selected").html();
			if(!confirm(<?php _e("'Are you sure of deleting shipping [' + delname + ']?'", 'usces'); ?>)) return false;
			
			$("#shipping_charge_loading").html('<img src="<?php echo USCES_PLUGIN_URL; ?>/images/loading-publish.gif" />');
			var id = $("#shipping_charge_name_select option:selected").val();
			var s = operation.settings;
			s.data = "action=shop_options_ajax&mode=delete_shipping_charge&id=" + id;
			s.success = function(data, dataType){
				var strs = data.split('#usces#');
				var id = strs[0] - 0;
				for(var i=0; i<shipping_charge.length; i++){
					if(id === shipping_charge[i]['id']){
						index = i;
					}
				}
				shipping_charge.splice(index, 1);
				operation.disp_shipping_charge(0);
				operation.make_delivery_method_charge(get_delivery_method_charge(selected_method));
				$("#shipping_charge_loading").html('');
			};
			$.ajax( s );
			return false;
		},
		
		onchange_shipping_charge : function(index) {
			var id = $("#shipping_charge_name_select option:eq("+index+")").val()-0;
			operation.disp_shipping_charge(id);
		},
		
		allCharge : function () {
			var charge = $("#allcharge").val();
			if(charge == '') return;
			if( confirm(<?php echo sprintf(__("'Are you sure of setting shiping to %s' + charge + ' for all the prefecture?'", 'usces'), esc_js(usces_crsymbol('return', 'js'))); ?>) ){
//20110317ysk start
				var country = $("#shipping_charge_country").val();
				//for(var i=0; i<pref.length; i++){
				//	$("input[name='shipping_charge_value\[" + pref[i] + "\]']").val(charge);
				//}
				for(var i=0; i<pref[country].length; i++){
					$("input[name='shipping_charge_value\[" + pref[country][i] + "\]']").val(charge);
				}
//20110317ysk end
				$("#allcharge").val("");
			}
		},
		
//20101208ysk start
		disp_delivery_days :function (id){
			var valuehtml = '';
			if(delivery_days.length === 0) {
//20110317ysk start
				//for(var i=0; i<pref.length; i++){
				//	valuehtml += "<div class='clearfix'><label class='delivery_days_label'>" + pref[i] + "</label><input type='text' name='delivery_days_value[" + pref[i] + "]' value='' class='days_text' /><?php _e('day', 'usces'); ?></div>\n";
				//}
				for(var i=0; i<pref[base_country].length; i++){
					valuehtml += "<div class='clearfix'><label class='delivery_days_label'>" + pref[base_country][i] + "</label><input type='text' name='delivery_days_value[" + pref[base_country][i] + "]' value='' class='days_text' /><?php _e('day', 'usces'); ?></div>\n";
				}
				$("#delivery_days_country").val(base_country);
//20110317ysk end
				$("#delivery_days_name").html('<input name="delivery_days_name" type="text" value="" />');
				$("#delivery_days_name2").html('');
				$("#delivery_days_value").html(valuehtml);
				$("#delivery_days_button").html('<input name="add_delivery_days" id="add_delivery_days" type="button" value="<?php _e('Add', 'usces'); ?>" onclick="operation.add_delivery_days();" />');
			}else{
				var selected = 0;
//20110317ysk start
				var country = base_country;
//20110317ysk end
				var name_select = '<select name="delivery_days_name_select" id="delivery_days_name_select" onchange="operation.onchange_delivery_days(this.selectedIndex);">'+"\n";
				for(var i=0; i<delivery_days.length; i++){
					if(delivery_days[i]['id'] === id){
						selected = i;
//20110317ysk start
						country = delivery_days[i]['country'];
//20110317ysk end
						name_select += '<option value="'+delivery_days[i]['id']+'" selected="selected">'+delivery_days[i]['name']+'</option>'+"\n";
					}else{
						name_select += '<option value="'+delivery_days[i]['id']+'">'+delivery_days[i]['name']+'</option>'+"\n";
					}
				}
				name_select += "</select>\n";
//20110317ysk start
				var value = '';
				//for(var i=0; i<pref.length; i++){
				//	valuehtml += "<div class='clearfix'><label class='delivery_days_label'>" + pref[i] + "</label><input type='text' name='delivery_days_value[" + pref[i] + "]' value='" + delivery_days[selected]['value'][pref[i]] + "' class='days_text' /><?php _e('day', 'usces'); ?></div>\n";
				//}
				for(var i=0; i<pref[country].length; i++){
					value = (delivery_days[selected]['value'][pref[country][i]] == undefined) ? '' : delivery_days[selected]['value'][pref[country][i]];
					valuehtml += "<div class='clearfix'><label class='delivery_days_label'>" + pref[country][i] + "</label><input type='text' name='delivery_days_value[" + pref[country][i] + "]' value='" + value + "' class='days_text' /><?php _e('day', 'usces'); ?></div>\n";
				}
				$("#delivery_days_country").val(country);
//20110317ysk end
				$("#delivery_days_name").html(name_select);
				$("#delivery_days_name2").html('<input name="delivery_days_name" type="text" value="'+delivery_days[selected]['name']+'" />');
				$("#delivery_days_value").html(valuehtml);
				$("#delivery_days_button").html("<input name='delete_delivery_days' id='delete_delivery_days' type='button' value='<?php _e('Delete', 'usces'); ?>' onclick='operation.delete_delivery_days();' /><input name='update_delivery_days' id='update_delivery_days' type='button' value='<?php _e('update', 'usces'); ?>' onclick='operation.update_delivery_days();' />");
			}
		},
		
		add_delivery_days : function() {
			if($("input[name='delivery_days_name']").val() == "") return;
			
			$("#delivery_days_loading").html('<img src="<?php echo USCES_PLUGIN_URL; ?>/images/loading-publish.gif" />');
			var name = $("input[name='delivery_days_name']").val();
			var query = '';
//20110317ysk start
			var country = $("#delivery_days_country").val();
			//for(var i=0; i<pref.length; i++){
			//	query += '&value[]=' + $("input[name='delivery_days_value\[" + pref[i] + "\]']").val();
			//}
			for(var i=0; i<pref[country].length; i++){
				query += '&value[]=' + $("input[name='delivery_days_value\[" + pref[country][i] + "\]']").val();
			}
//20110317ysk end
			
			var s = operation.settings;
//20110317ysk start
			//s.data = "action=shop_options_ajax&mode=add_delivery_days&name=" + name + query;
			s.data = "action=shop_options_ajax&mode=add_delivery_days&name=" + name + "&country=" + country + query;
//20110317ysk end
			s.success = function(data, dataType){
				var strs = data.split('#usces#');
				var id = strs[0] - 0;
				var name = strs[1];
//20110317ysk start
				var country = strs[2];
				//var value = strs[2].split(',');
				var value = strs[3].split(',');
//20110317ysk end
				var index = delivery_days.length;
				delivery_days[index] = [];
				delivery_days[index]['id'] = id;
				delivery_days[index]['name'] = name;
				delivery_days[index]['value'] = [];
//20110317ysk start
				delivery_days[index]['country'] = country;
				//for(var i=0; i<pref.length; i++){
				//	delivery_days[index]['value'][pref[i]] = value[i];
				//}
				for(var i=0; i<pref[country].length; i++){
					delivery_days[index]['value'][pref[country][i]] = value[i];
				}
//20110317ysk end
				operation.disp_delivery_days(id);
				operation.make_delivery_method_days(get_delivery_method_days(selected_method));
				$("#delivery_days_loading").html('');
				
			};
			$.ajax( s );
			return false;
		},
		
		update_delivery_days : function() {
			$("#delivery_days_loading").html('<img src="<?php echo USCES_PLUGIN_URL; ?>/images/loading-publish.gif" />');
			var id = $("#delivery_days_name_select option:selected").val();
			var name = $("input[name='delivery_days_name']").val();
			var query = '';
//20110317ysk start
			var country = $("#delivery_days_country").val();
			//for(var i=0; i<pref.length; i++){
			//	query += '&value[]=' + $("input[name='delivery_days_value\[" + pref[i] + "\]']").val();
			//}
			for(var i=0; i<pref[country].length; i++){
				query += '&value[]=' + $("input[name='delivery_days_value\[" + pref[country][i] + "\]']").val();
			}
//20110317ysk end
			var s = operation.settings;
//20110317ysk start
			//s.data = "action=shop_options_ajax&mode=update_delivery_days&id=" + id + "&name=" + name + query;
			s.data = "action=shop_options_ajax&mode=update_delivery_days&id=" + id + "&name=" + name + "&country=" + country + query;
//20110317ysk end
			s.success = function(data, dataType){
				var strs = data.split('#usces#');
				var id = strs[0] - 0;
				var name = strs[1];
//20110317ysk start
				var country = strs[2];
				//var value = strs[2].split(',');
				var value = strs[3].split(',');
//20110317ysk end
				for(var i=0; i<delivery_days.length; i++){
					if(id === delivery_days[i]['id']){
						index = i;
					}
				}
				delivery_days[index]['name'] = name;
//20110317ysk start
				delivery_days[index]['value'] = [];
				delivery_days[index]['country'] = country;
				//for(var i=0; i<pref.length; i++){
				//	delivery_days[index]['value'][pref[i]] = value[i];
				//}
				for(var i=0; i<pref[country].length; i++){
					delivery_days[index]['value'][pref[country][i]] = value[i];
				}
//20110317ysk end
				operation.disp_delivery_days(id);
				operation.make_delivery_method_days(get_delivery_method_days(selected_method));
				$("#delivery_days_loading").html('');
			};
			$.ajax( s );
			return false;
		},
		
		delete_delivery_days : function() {
			var delname = $("#delivery_days_name_select option:selected").html();
			if(!confirm(<?php _e("'配達日数設定「' + delname + '」を削除してもよろしいですか?'", 'usces'); ?>)) return false;
			
			$("#delivery_days_loading").html('<img src="<?php echo USCES_PLUGIN_URL; ?>/images/loading-publish.gif" />');
			var id = $("#delivery_days_name_select option:selected").val();
			var s = operation.settings;
			s.data = "action=shop_options_ajax&mode=delete_delivery_days&id=" + id;
			s.success = function(data, dataType){
				var strs = data.split('#usces#');
				var id = strs[0] - 0;
				for(var i=0; i<delivery_days.length; i++){
					if(id === delivery_days[i]['id']){
						index = i;
					}
				}
				delivery_days.splice(index, 1);
				operation.disp_delivery_days(0);
				operation.make_delivery_method_days(get_delivery_method_days(selected_method));
				$("#delivery_days_loading").html('');
			};
			$.ajax( s );
			return false;
		},

		onchange_delivery_days : function(index) {
			var id = $("#delivery_days_name_select option:eq("+index+")").val()-0;
			operation.disp_delivery_days(id);
		},
		
		allDeliveryDays : function () {
			var days = $("#all_delivery_days").val();//20110106ysk [all]->[days]
			if(days == '') return;
			if( confirm(<?php _e("'配達日数を全て ' + days + ' 日に変更してもよろしいですか?'", 'usces'); ?>) ){
//20110317ysk start
				var country = $("#delivery_days_country").val();
				//for(var i=0; i<pref.length; i++){
				//	$("input[name='delivery_days_value\[" + pref[i] + "\]']").val(days);
				//}
				for(var i=0; i<pref[country].length; i++){
					$("input[name='delivery_days_value\[" + pref[country][i] + "\]']").val(days);
				}
//20110317ysk end
				$("#all_delivery_days").val("");
			}
		},
//20101208ysk end
		
		settings: {
			url: uscesL10n.requestFile,
			type: 'POST',
			cache: false,
			success: function(data, dataType){
				$("#delivery_method_loading").html('');
			}, 
			error: function(msg){
				//$("#ajax-response").html(msg);
				$("#delivery_method_loading").html('');
			}
		}
	};

//20110317ysk start
	$("#shipping_charge_country").change(function () {
		var id = $("#shipping_charge_name_select").val()-0;
		var selected = 0;
		for(var i=0; i<shipping_charge.length; i++){
			if(shipping_charge[i]['id'] === id){
				selected = i;
			}
		}
		var country = $("#shipping_charge_country").val();
		var value = '';
		var valuehtml = '';
		for(var i=0; i<pref[country].length; i++){
			value = (shipping_charge[selected]['value'][pref[country][i]] == undefined) ? '' : shipping_charge[selected]['value'][pref[country][i]];
			valuehtml += "<div class='clearfix'><label class='shipping_charge_label'>" + pref[country][i] + "</label><input type='text' name='shipping_charge_value[" + pref[country][i] + "]' value='" + value + "' class='charge_text' /><?php usces_crcode(); ?></div>\n";
		}
		$("#shipping_charge_value").html(valuehtml);
	});

	$("#delivery_days_country").change(function () {
		var id = $("#delivery_days_name_select").val()-0;
		var selected = 0;
		for(var i=0; i<delivery_days.length; i++){
			if(delivery_days[i]['id'] === id){
				selected = i;
			}
		}
		var country = $("#delivery_days_country").val();
		var value = '';
		var valuehtml = '';
		for(var i=0; i<pref[country].length; i++){
			value = (delivery_days[selected]['value'][pref[country][i]] == undefined) ? '' : delivery_days[selected]['value'][pref[country][i]];
			valuehtml += "<div class='clearfix'><label class='delivery_days_label'>" + pref[country][i] + "</label><input type='text' name='delivery_days_value[" + pref[country][i] + "]' value='" + value + "' class='days_text' /><?php _e('day', 'usces'); ?></div>\n";
		}
		$("#delivery_days_value").html(valuehtml);
	});
//20110317ysk end
});

function toggleVisibility(id) {
   var e = document.getElementById(id);
   if(e.style.display == 'block')
	  e.style.display = 'none';
   else
	  e.style.display = 'block';
}

jQuery(document).ready(function($){
	operation.disp_delivery_method(-1);
	operation.disp_shipping_charge(-1);
//20101208ysk start
	operation.disp_delivery_days(-1);

	var $tabs = $('#uscestabs_delivery').tabs({
		cookie: {
			// store cookie for a day, without, it would be a session cookie
			expires: 1
		}
	});
//20101208ysk end
});
</script>
<div class="wrap">
<div class="usces_admin">
<h2>Welcart Shop <?php _e('Shipping Setting','usces'); ?></h2>
<div id="aniboxStatus" class="<?php echo $status; ?>">
	<div id="anibox" class="clearfix">
		<img src="<?php echo USCES_PLUGIN_URL; ?>/images/list_message_<?php echo $status; ?>.gif" />
		<div class="mes" id="info_massage"><?php echo $message; ?></div>
	</div>
</div>
<p><?php _e('* It is necessary to perform the delivery setting before than the item registration.','usces'); ?><br />
<?php _e('* When you performed the addition and deletion of the delivery method after item registration, please be careful because the update of all items is necessary.','usces'); ?></p>
<div id="poststuff" class="metabox-holder">

<!--20101208ysk start-->
<div class="postbox">
<h3 class="hndle"><?php _e('Shipping Setting', 'usces'); ?></h3>
<div class="inside">
<form action="" method="post" name="option_form" id="option_form">
<table class="form_table">
	<tr style="height:40px;">
		<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_shipping_setting01');"><?php _e('配送業務締時間', 'usces'); ?></a></th>
		<td>
			<select name="delivery_time_limit[hour]">
<?php
			for($i = 0; $i < 24; $i++) {
				$hour = sprintf('%02d', $i);
?>
				<option value="<?php echo $hour; ?>"<?php if($delivery_time_limit['hour'] == $hour) echo ' selected'; ?>><?php echo $hour; ?></option>
<?php
			}
?>
			</select>
		</td>
		<td>:&nbsp;</td>
		<td>
			<select name="delivery_time_limit[min]">
<?php
			$i = 0;
			while($i < 60) {
				$min = sprintf('%02d', $i);
?>
				<option value="<?php echo $min; ?>"<?php if($delivery_time_limit['min'] == $min) echo ' selected'; ?>><?php echo $min; ?></option>
<?php
				$i += 10;
			}
?>
			</select>
		</td>
		<td><div id="ex_shipping_setting01" class="explanation"><?php _e('商品発送を締め切る時間を指定します。この値は最短発送日を算出するために使用します。', 'usces'); ?></div></td>
	</tr>
	<tr style="height:40px;">
		<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_shipping_setting02');"><?php _e('午前着の可否', 'usces'); ?></a></th>
		<td colspan="3">
			<select name="shortest_delivery_time">
				<option value="0"<?php if($shortest_delivery_time == '0') echo ' selected'; ?>><?php _e('No preference', 'usces'); ?></option>
				<option value="1"<?php if($shortest_delivery_time == '1') echo ' selected'; ?>><?php _e('午前着可', 'usces'); ?></option>
				<option value="2"<?php if($shortest_delivery_time == '2') echo ' selected'; ?>><?php _e('午前着不可', 'usces'); ?></option>
			</select>
		</td>
		<td><div id="ex_shipping_setting02" class="explanation"><?php _e('商品が最短発送日の午前中に到着が可能かどうかを指定します。', 'usces'); ?></div></td>
	</tr>
	<tr style="height:40px;">
		<th><a style="cursor:pointer;" onclick="toggleVisibility('ex_shipping_setting03');"><?php _e('配送希望日表示数', 'usces'); ?></a></th>
		<td colspan="3">
			<input name="delivery_after_days" type="text" value="<?php echo $delivery_after_days; ?>">
		</td>
		<td><div id="ex_shipping_setting03" class="explanation"><?php _e('お客様が選択する配送希望日の選択数を指定します。', 'usces'); ?></div></td>
	</tr>
	<tr>
		<th></th>
		<td colspan="3">
			<input name="usces_option_update" type="submit" class="button" value="<?php _e('change decision','usces'); ?>" />
		</td>
	</tr>
</table>
</form>
</div>
</div><!--postbox-->
<!--20101208ysk end-->

<div class="postbox">
<h3 class="hndle"><span><?php _e('shipping option','usces'); ?></span><a style="cursor:pointer;" onclick="toggleVisibility('ex_delivery_method');"> (<?php _e('explanation', 'usces'); ?>) </a></h3>
<div class="inside">
<table class="form_table" style="width:290px; margin-left:10px; float:left;">
	<tr style="height:20px;">
	    <th class="sec">&nbsp;</th>
	    <td><a href="javascript:void(0);" id="new_delivery_method_action"><?php _e('New addition', 'usces'); ?></a></td>
	</tr>
	<tr style="height:40px;">
	    <th><?php _e('Shipping name', 'usces'); ?></th>
	    <td width="150" height="30" id="delivery_method_name"></td>
	</tr>
	<tr style="height:30px;">
	    <th class="sec">&nbsp;</th>
	    <td id="delivery_method_name2"></td>
	</tr>
	<tr style="height:30px;">
	    <th class="sec" id="delivery_method_loading">&nbsp;</th>
	    <td id="delivery_method_button"></td>
	</tr>
	<tr style="height:20px;">
	    <th class="sec"></th>
	    <td><a href="#" id="moveup_action"><?php _e('Raise the priority', 'usces'); ?></a></td>
	</tr>
	<tr style="height:20px;">
	    <th class="sec"></th>
	    <td><a href="#" id="movedown_action"><?php _e('Lower the priority', 'usces'); ?></a></td>
	</tr>
</table>

<table class="form_table">
	<tr style="height:20px;">
	    <th class="sec"></th>
	    <td></td>
	</tr>
	<tr style="height:40px;">
	    <th class="sec"><a style="cursor:pointer;" onclick="toggleVisibility('ex_shipping_setting10');"><?php _e('配送対象地域', 'usces'); ?></a></th>
<!--20110317ysk start-->
<!--	    <td><input name="flights" type="radio" value="domestic" id="domestic_flights" /><label for="domestic_flights" style="margin-right:20px;"><?php _e('国内便', 'uesces'); ?></label><input name="flights" type="radio" value="internationa" id="internationa_flights" /><label for="internationa_flights"><?php _e('国際便', 'uesces'); ?></label></td>-->
		<td><div id="delivery_method_intl"></div></td>
		<td><div id="ex_shipping_setting10" class="explanation"><?php _e('この配送方法が対象とする配送可能地域を選択します。', 'usces'); ?></div></td>
<!--20110317ysk end-->
	</tr>
<!--	<tr>
	    <th class="sec"><a style="cursor:pointer;" onclick="toggleVisibility('ex_shipping_setting11');"><?php _e('適用モジュール', 'usces'); ?></a></th>
	    <td><select name="shipping_module">
	    		<option value="none"><?php _e('利用できるモジュールがありません', 'usces'); ?></option>
	    </select></td>
		<td><div id="ex_shipping_setting11" class="explanation"><?php _e('配送モジュールを選択した場合は、送料を含め全てのルールがモジュール優先となります。', 'usces'); ?></div></td>
	</tr>-->
	<tr style="height:40px;">
	    <th class="sec"><a style="cursor:pointer;" onclick="toggleVisibility('ex_shipping_setting12');"><?php _e('Deliverly time', 'usces'); ?></a></th>
	    <td><textarea name="delivery_method_time" id="delivery_method_time" style="height:100px;"></textarea></td>
		<td><div id="ex_shipping_setting12" class="explanation"><?php _e("配送可能な時間帯を入力します。お客様に時間帯を選択させない場合は空白にします。<br />例）<br />午前中", 'usces'); ?><br />12：00～14：00<br />14：00～16：00<br />16：00～18：00</div></td>
	</tr>
	<tr style="height:40px;">
	    <th class="sec"><a style="cursor:pointer;" onclick="toggleVisibility('ex_shipping_setting13');"><?php _e('Postage fixation', 'usces'); ?></a></th>
	    <td id="delivery_method_charge_td"></td>
		<td><div id="ex_shipping_setting13" class="explanation"><?php _e("It is fixed for above rate setting when I choose postage fixation. The postage set by an article is applied in the case of 'non-fixation'", 'usces'); ?></div></td>
	</tr>
<!--	<tr style="height:40px;">
	    <th class="sec"><a style="cursor:pointer;" onclick="toggleVisibility('ex_shipping_setting14');"><?php _e('重量加算', 'usces'); ?></th>
	    <td id="delivery_method_weight_td"></td>
		<td><div id="ex_shipping_setting14" class="explanation"><?php _e("商品の合計重量によって送料を加算します。", 'usces'); ?></div></td>
	</tr>-->
<!--20101208ysk start-->
	<tr style="height:40px;">
		<th class="sec"><a style="cursor:pointer;" onclick="toggleVisibility('ex_shipping_setting15');"><?php _e('配達日数', 'usces'); ?></a></th>
		<td id="delivery_method_days_td"></td>
		<td><div id="ex_shipping_setting15" class="explanation"><?php _e("下の配達日数設定で登録した配達日数名を選択します。最短配送日を算出するために使用します。お客様に配送希望日を選択させない場合は「配送希望日を利用しない」を指定します。", 'usces'); ?></div></td>
	</tr>
<!--20101208ysk end-->
<!--20101119ysk start-->
	<tr style="height:40px;">
		<th class="sec"><a style="cursor:pointer;" onclick="toggleVisibility('ex_shipping_setting16');"><?php _e('No COD', 'usces'); ?></a></th>
		<td><div id="delivery_method_nocod"></div></td>
		<td><div id="ex_shipping_setting16" class="explanation"><?php _e("代引き支払いができない場合はチェックを入れます。", 'usces'); ?></div></td>
	</tr>
<!--20101119ysk end-->
</table>

<hr size="1" color="#CCCCCC" />
<div id="ex_delivery_method" class="explanation"><?php _e('Please make entry of appointment time to a party by one.', 'usces'); ?></div>
</div>
</div><!--postbox-->

<!--20101208ysk start-->
<div class="uscestabs" id="uscestabs_delivery">
	<ul>
		<li><a href="#delivery_page_setting_1"><?php _e('Shipping','usces'); ?></a></li>
		<!--<li><a href="#delivery_page_setting_3"><?php _e('重量加算','usces'); ?></a></li>-->
		<li><a href="#delivery_page_setting_2"><?php _e('配達日数','usces'); ?></a></li>
	</ul>

<div id="delivery_page_setting_1">
<!--20101208ysk end-->

<div class="postbox">
<h3 class="hndle"><span><?php _e('Shipping', 'usces'); ?></span><a style="cursor:pointer;" onclick="toggleVisibility('ex_shipping_charge');"> (<?php _e('explanation', 'usces'); ?>) </a></h3>
<div class="inside">
<table class="form_table" style="width:290px; margin-left:10px; float:left;">
	<tr style="height:20px;">
	    <th class="sec">&nbsp;</th>
	    <td><a href="javascript:void(0);" id="new_shipping_charge_action"><?php _e('New addition', 'usces'); ?></a></td>
	</tr>
	<tr style="height:30px;">
	    <th class="sec"><?php _e('Shipping charge name', 'usces'); ?></th>
	    <td width="150" height="30" id="shipping_charge_name"></td>
	</tr>
	<tr style="height:30px;">
	    <th class="sec">&nbsp;</th>
	    <td width="150" height="30" id="shipping_charge_name2"></td>
	</tr>
	<tr style="height:30px;">
	    <th class="sec" id="shipping_charge_loading">&nbsp;</th>
	    <td id="shipping_charge_button"></td>
	</tr>
</table>
<table class="form_table">
	<tr style="height:20px;">
	    <th class="sec"></th>
	    <td></td>
	</tr>
	<tr style="height:40px;">
	    <th class="sec"><a style="cursor:pointer;" onclick="toggleVisibility('ex_shipping_setting20');"><?php _e('Country', 'usces'); ?></a></th>
	    <td><label class="shipping_charge_label"></label><select name="shipping_charge_country" id="shipping_charge_country">
	    		<?php usces_shipping_country_option( $selected ); ?>
	    </select></td>
<!--20110317ysk start-->
		<td><div id="ex_shipping_setting20" class="explanation"><?php _e('この送料を適用する国を選択します。', 'usces'); ?></div></td>
<!--20110317ysk end-->
	</tr>
	<tr style="height:40px;">
	    <th class="sec"><?php _e('Shipping', 'usces'); ?></th>
	    <td><label class="shipping_charge_label"><input name="allbutton" type="button" class="allbutton" onclick="operation.allCharge();" value="<?php _e('same as', 'usces'); ?>"  /></label><input name="allcharge" id="allcharge" type="text" class='charge_text' /><?php usces_crcode(); ?></td>
	</tr>
	<tr>
	    <th class="sec"></th>
		<td><div id="shipping_charge_value"></div></td>
	</tr>
</table>
<hr size="1" color="#CCCCCC" />
<div id="ex_shipping_charge" class="explanation"><?php _e('You can choose the shipping every item.', 'usces'); ?></div>
</div>
</div><!--postbox-->

<!--20101208ysk start-->
</div><!--delivery_page_setting_1-->
<div id="delivery_page_setting_2">
<div class="postbox">
<h3 class="hndle"><span><?php _e('配達日数', 'usces'); ?></span><a style="cursor:pointer;" onclick="toggleVisibility('ex_delivery_days');"> (<?php _e('explanation', 'usces'); ?>) </a></h3>
<div class="inside">
<table class="form_table" style="width:280px; margin-left:10px; float:left;">
	<tr style="height:20px;">
		<th class="sec">&nbsp;</th>
		<td><a href="javascript:void(0);" id="new_delivery_days_action"><?php _e('New addition', 'usces'); ?></a></td>
	</tr>
	<tr style="height:30px;">
		<th class="sec"><?php _e('配達日数名', 'usces'); ?></th>
		<td width="150" height="30" id="delivery_days_name"></td>
	</tr>
	<tr style="height:30px;">
		<th class="sec">&nbsp;</th>
		<td width="150" height="30" id="delivery_days_name2"></td>
	</tr>
	<tr style="height:30px;">
		<th class="sec" id="delivery_days_loading">&nbsp;</th>
		<td id="delivery_days_button"></td>
	</tr>
</table>
<table class="form_table">
	<tr style="height:20px;">
		<th class="sec"></th>
		<td></td>
	</tr>
	<tr style="height:40px;">
<!--20110317ysk start-->
<!--		<th class="sec"></th>
		<td></td>-->
		<th class="sec"><a style="cursor:pointer;" onclick="toggleVisibility('ex_delivery_days_setting20');"><?php _e('Country', 'usces'); ?></a></th>
		<td><label class="shipping_charge_label"></label><select name="delivery_days_country" id="delivery_days_country">
				<?php usces_shipping_country_option( $selected ); ?>
		</select></td>
		<td><div id="ex_delivery_days_setting20" class="explanation"><?php _e('この配達日数を適用する国を選択します。', 'usces'); ?></div></td>
<!--20110317ysk end-->
	</tr>
	<tr style="height:40px;">
		<th class="sec"><?php _e('配達日数', 'usces'); ?></th>
		<td><label class="delivery_days_label"><input name="allbutton_delivery_days" type="button" class="allbutton" onclick="operation.allDeliveryDays();" value="<?php _e('same as', 'usces'); ?>"  /></label><input name="all_delivery_days" id="all_delivery_days" type="text" class='days_text' /><?php _e('day', 'usces'); ?></td>
	</tr>
	<tr>
		<th class="sec"></th>
		<td><div id="delivery_days_value"></div></td>
	</tr>
</table>
<hr size="1" color="#CCCCCC" />
<div id="ex_delivery_days" class="explanation"><?php _e('商品ごとに配達日数を選ぶことができます。', 'usces'); ?></div>
</div>
</div><!--postbox-->
</div><!--delivery_page_setting_2-->
<!--
<div id="delivery_page_setting_3">
<div class="postbox">
<h3 class="hndle"><span><?php _e('重量加算', 'usces'); ?></span><a style="cursor:pointer;" onclick="toggleVisibility('ex_shipping_weight');"> (<?php _e('explanation', 'usces'); ?>) </a></h3>
<div class="inside">
<table class="form_table" style="width:280px; margin-left:10px; float:left;">
	<tr>
		<th class="sec">&nbsp;</th>
		<td><a href="javascript:void(0);" id="new_delivery_days_action"><?php _e('New addition', 'usces'); ?></a></td>
	</tr>
	<tr>
		<th class="sec"><?php _e('加算ルール名', 'usces'); ?></th>
		<td width="150" height="30" id="shipping_weight_name"></td>
	</tr>
	<tr>
		<th class="sec">&nbsp;</th>
		<td width="150" height="30" id="shipping_weight_name2"></td>
	</tr>
	<tr>
		<th class="sec" id="shipping_weight_loading">&nbsp;</th>
		<td id="shipping_weight_button"></td>
	</tr>
</table>
<table class="form_table">
	<tr>
		<th class="sec"></th>
		<td></td>
	</tr>
	<tr>
		<th class="sec"><?php _e('加算金額', 'usces'); ?></th>
		<td><label class="delivery_days_label"><input name="allbutton_shipping_weight" type="button" class="allbutton" onclick="operation.allShippingWeight();" value="<?php _e('same as', 'usces'); ?>"  /></label><input name="all_shipping_weight" id="all_shipping_weight" type="text" class='days_text' /><?php usces_crcode(); ?></td>
	</tr>
	<tr>
		<th class="sec"></th>
		<td></td>
	</tr>
	<tr>
		<th class="sec"></th>
		<td><div id="shipping_weight_value"></div></td>
	</tr>
</table>
<hr size="1" color="#CCCCCC" />
<div id="ex_shipping_weight" class="explanation"><?php _e('商品の合計重量を元に送料に料金を加算します。', 'usces'); ?></div>
</div>
</div>
</div>
-->

</div><!--tabs-->
<!--20101208ysk end-->

</div><!--poststuff-->

</div><!--usces_admin-->
</div><!--wrap-->