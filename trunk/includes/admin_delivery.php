<?php
$status = $this->action_status;
$message = $this->action_message;
$this->action_status = 'none';
$this->action_message = '';
$delivery_method = $this->options['delivery_method'];
$shipping_charge = $this->options['shipping_charge'];
//	$prefs = get_option('usces_pref');
	$prefs = $this->options['province'];
array_shift($prefs);
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

	var delivery_method = [];
<?php for($i=0; $i<count((array)$delivery_method); $i++){ $lines = split("\n", $delivery_method[$i]['time']); ?>
	delivery_method[<?php echo $i; ?>] = [];
	delivery_method[<?php echo $i; ?>]['id'] = <?php echo (int)$delivery_method[$i]['id']; ?>;
	delivery_method[<?php echo $i; ?>]['name'] = "<?php echo $delivery_method[$i]['name']; ?>";
	delivery_method[<?php echo $i; ?>]['charge'] = <?php echo (int)$delivery_method[$i]['charge']; ?>;
	sttr = '';
	<?php foreach((array)$lines as $line){ 	if(trim($line) != ''){ ?>
	sttr += "<?php echo trim($line); ?>\n";
	<?php } } ?>
	delivery_method[<?php echo $i; ?>]['time'] = sttr;
<?php } ?>

	var pref = [];
<?php foreach((array)$prefs as $pref){ ?>
	pref.push('<?php echo $pref; ?>');
<?php } ?>
	var shipping_charge = [];
<?php for($i=0; $i<count((array)$shipping_charge); $i++){ ?>
	shipping_charge[<?php echo $i; ?>] = [];
	shipping_charge[<?php echo $i; ?>]['id'] = <?php echo (int)$shipping_charge[$i]['id']; ?>;
	shipping_charge[<?php echo $i; ?>]['name'] = "<?php echo $shipping_charge[$i]['name']; ?>";
	shipping_charge[<?php echo $i; ?>]['value'] = [];
	<?php foreach((array)$prefs as $pref){ ?>;
	shipping_charge[<?php echo $i; ?>]['value']['<?php echo $pref; ?>'] = <?php echo (int)$shipping_charge[$i]['value'][$pref]; ?>;
<?php }} ?>

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

	$("#new_delivery_method_action").click(function () {
		if(delivery_method.length === 0) return false;
		$("#delivery_method_name").html('<input name="delivery_method_name" type="text" value="" />');
		$("#delivery_method_name2").html('');
		$("#delivery_method_time").val('');
		$("#delivery_method_button").html('<input name="cancel_delivery_method" id="cancel_delivery_method" type="button" value="<?php _e('Cancel', 'usces'); ?>" onclick="operation.disp_delivery_method(0);" /><input name="add_delivery_method" id="add_delivery_method" type="button" value="<?php _e('Add', 'usces'); ?>" onclick="operation.add_delivery_method();" />');
		$("input[name='delivery_method_name']").focus().select();
		operation.make_delivery_method_charge(-1);
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
		for(var i=0; i<pref.length; i++){
			valuehtml += "<div class='clearfix'><label class='shipping_charge_label'>" + pref[i] + "</label><input type='text' name='shipping_charge_value[" + pref[i] + "]' value='' class='charge_text' /><?php _e('dollars', 'usces'); ?></div>\n";
		}
		$("#shipping_charge_name").html('<input name="shipping_charge_name" type="text" value="" />');
		$("#shipping_charge_name2").html('');
		$("#shipping_charge_value").html(valuehtml);
		$("#shipping_charge_button").html('<input name="cancel_shipping_charge" id="cancel_shipping_charge" type="button" value="<?php _e('Cancel', 'usces'); ?>" onclick="operation.disp_shipping_charge(0);" /><input name="add_shipping_charge" id="add_shipping_charge" type="button" value="<?php _e('Add', 'usces'); ?>" onclick="operation.add_shipping_charge();" />');
		$("input[name='shipping_charge_name']").focus().select();
	});
	
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
				operation.make_delivery_method_charge(-1);
			}else{
				var name_select = '<select name="delivery_method_name_select" id="delivery_method_name_select" onchange="operation.onchange_delivery_select(this.selectedIndex);">'+"\n";
				for(var i=0; i<delivery_method.length; i++){
					if(selected === i){
						name_select += '<option value="'+delivery_method[i]['id']+'" selected="selected">'+(i+1)+' : '+delivery_method[i]['name']+'</option>'+"\n";
					}else{
						name_select += '<option value="'+delivery_method[i]['id']+'">'+(i+1)+' : '+delivery_method[i]['name']+'</option>'+"\n";
					}
				}
				name_select += "</select>\n";
				$("#delivery_method_name").html(name_select);
				$("#delivery_method_name2").html('<input name="delivery_method_name" type="text" value="'+delivery_method[selected]['name']+'" />');
				$("#delivery_method_time").val(delivery_method[selected]['time']);
				$("#delivery_method_button").html("<input name='delete_delivery_method' id='delete_delivery_method' type='button' value='<?php _e('Delete', 'usces'); ?>' onclick='operation.delete_delivery_method();' /><input name='update_delivery_method' id='update_delivery_method' type='button' value='<?php _e('update', 'usces'); ?>' onclick='operation.update_delivery_method();' />");
				operation.make_delivery_method_charge(get_delivery_method_charge(selected_method));
			}
		},
		
		add_delivery_method : function() {
			if($("input[name='delivery_method_name']").val() == "") return;
			
			$("#delivery_method_loading").html('<img src="<?php echo USCES_PLUGIN_URL; ?>/images/loading-publish.gif" />');
			var name = $("input[name='delivery_method_name']").val();
			var time = $("#delivery_method_time").val();
			var charge = $("#delivery_method_charge option:selected").val();
			
			var s = operation.settings;
			s.data = "action=shop_options_ajax&mode=add_delivery_method&name=" + name + "&charge=" + charge + "&time=" + time;
			s.success = function(data, dataType){
				var strs = data.split('#usces#');
				var id = strs[0] - 0;
				var name = strs[1];
				var time = strs[2];
				var charge = strs[3] - 0;
				var index = delivery_method.length;
				delivery_method[index] = [];
				delivery_method[index]['id'] = id;
				delivery_method[index]['name'] = name;
				delivery_method[index]['time'] = time;
				delivery_method[index]['charge'] = charge;
				operation.disp_delivery_method(id);
				$("#delivery_method_loading").html('');
			};
			$.ajax( s );
			return false;
		},
		
		update_delivery_method : function() {
			$("#delivery_method_loading").html('<img src="<?php echo USCES_PLUGIN_URL; ?>/images/loading-publish.gif" />');
			var id = $("#delivery_method_name_select option:selected").val();
			var name = $("input[name='delivery_method_name']").val();
			var time = $("#delivery_method_time").val();
			var charge = $("#delivery_method_charge option:selected").val();
			var s = operation.settings;
			s.data = "action=shop_options_ajax&mode=update_delivery_method&name=" + name + "&id=" + id + "&time=" + time + "&charge=" + charge;
			s.success = function(data, dataType){
				var strs = data.split('#usces#');
				var id = strs[0] - 0;
				var name = strs[1];
				var time = strs[2];
				var charge = strs[3]-0;
				for(var i=0; i<delivery_method.length; i++){
					if(id === delivery_method[i]['id']){
						index = i;
					}
				}
				delivery_method[index]['name'] = name;
				delivery_method[index]['time'] = time;
				delivery_method[index]['charge'] = charge;
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
				operation.disp_delivery_method(delivery_method[0]['id']);
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
				var selected = strs[4]-0;
				var ct = delivery_method.length;
				for(var i=0; i<ct; i++){
					delivery_method[i]['id'] = id[i]-0;
					delivery_method[i]['name'] = name[i];
					delivery_method[i]['time'] = time[i];
					delivery_method[i]['charge'] = charge[i]-0;
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
				var selected = strs[4]-0;
				var ct = delivery_method.length;
				for(var i=0; i<ct; i++){
					delivery_method[i]['id'] = id[i]-0;
					delivery_method[i]['name'] = name[i];
					delivery_method[i]['time'] = time[i];
					delivery_method[i]['charge'] = charge[i]-0;
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
				for(var i=0; i<pref.length; i++){
					valuehtml += "<div class='clearfix'><label class='shipping_charge_label'>" + pref[i] + "</label><input type='text' name='shipping_charge_value[" + pref[i] + "]' value='' class='charge_text' /><?php _e('dollars', 'usces'); ?></div>\n";
				}
				$("#shipping_charge_name").html('<input name="shipping_charge_name" type="text" value="" />');
				$("#shipping_charge_name2").html('');
				$("#shipping_charge_value").html(valuehtml);
				$("#shipping_charge_button").html('<input name="add_shipping_charge" id="add_shipping_charge" type="button" value="<?php _e('Add', 'usces'); ?>" onclick="operation.add_shipping_charge();" />');
			}else{
				var selected = 0;
				var name_select = '<select name="shipping_charge_name_select" id="shipping_charge_name_select" onchange="operation.onchange_shipping_charge(this.selectedIndex);">'+"\n";
				for(var i=0; i<shipping_charge.length; i++){
					if(shipping_charge[i]['id'] === id){
						selected = i;
						name_select += '<option value="'+shipping_charge[i]['id']+'" selected="selected">'+shipping_charge[i]['name']+'</option>'+"\n";
					}else{
						name_select += '<option value="'+shipping_charge[i]['id']+'">'+shipping_charge[i]['name']+'</option>'+"\n";
					}
				}
				name_select += "</select>\n";
				for(var i=0; i<pref.length; i++){
					valuehtml += "<div class='clearfix'><label class='shipping_charge_label'>" + pref[i] + "</label><input type='text' name='shipping_charge_value[" + pref[i] + "]' value='" + shipping_charge[selected]['value'][pref[i]] + "' class='charge_text' /><?php _e('dollars', 'usces'); ?></div>\n";
				}
				$("#shipping_charge_name").html(name_select);
				$("#shipping_charge_name2").html('<input name="shipping_charge_name" type="text" value="'+shipping_charge[selected]['name']+'" />');
				$("#shipping_charge_value").html(valuehtml);
				$("#shipping_charge_button").html("<input name='delete_shipping_charge' id='delete_shipping_charge' type='button' value='<?php _e('Delete', 'usces'); ?>' onclick='operation.delete_shipping_charge();' /><input name='update_shipping_charge' id='update_shipping_charge' type='button' value='<?php _e('update', 'usces'); ?>' onclick='operation.update_shipping_charge();' />");
			}
		},
		
		add_shipping_charge : function() {
			if($("input[name='shipping_charge_name']").val() == "") return;
			
			$("#shipping_charge_loading").html('<img src="<?php echo USCES_PLUGIN_URL; ?>/images/loading-publish.gif" />');
			var name = $("input[name='shipping_charge_name']").val();
			var query = '';
			for(var i=0; i<pref.length; i++){
				query += '&value[]=' + $("input[name='shipping_charge_value\[" + pref[i] + "\]']").val();
			}
			
			var s = operation.settings;
			s.data = "action=shop_options_ajax&mode=add_shipping_charge&name=" + name + query;
			s.success = function(data, dataType){
				var strs = data.split('#usces#');
				var id = strs[0] - 0;
				var name = strs[1];
				var value = strs[2].split(',');
				var index = shipping_charge.length;
				shipping_charge[index] = [];
				shipping_charge[index]['id'] = id;
				shipping_charge[index]['name'] = name;
				shipping_charge[index]['value'] = [];
				for(var i=0; i<pref.length; i++){
					shipping_charge[index]['value'][pref[i]] = value[i];
				}
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
			var name = $("input[name='shipping_charge_name']").val();
			var query = '';
			for(var i=0; i<pref.length; i++){
				query += '&value[]=' + $("input[name='shipping_charge_value\[" + pref[i] + "\]']").val();
			}
			var s = operation.settings;
			s.data = "action=shop_options_ajax&mode=update_shipping_charge&id=" + id + "&name=" + name + query;
			s.success = function(data, dataType){
				var strs = data.split('#usces#');
				var id = strs[0] - 0;
				var name = strs[1];
				var value = strs[2].split(',');
				for(var i=0; i<shipping_charge.length; i++){
					if(id === shipping_charge[i]['id']){
						index = i;
					}
				}
				shipping_charge[index]['name'] = name;
				for(var i=0; i<pref.length; i++){
					shipping_charge[index]['value'][pref[i]] = value[i];
				}
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
			confirm(<?php _e("'Are you sure of setting shiping to ' + charge + ' dollars for all the prefecture?'", 'usces'); ?>);
			for(var i=0; i<pref.length; i++){
				$("input[name='shipping_charge_value\[" + pref[i] + "\]']").val(charge);
			}
			$("#allcharge").val("");
		},
		
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
<div id="poststuff" class="metabox-holder">

<div class="postbox">
<h3 class="hndle"><span><?php _e('shipping option','usces'); ?></span><a style="cursor:pointer;" onclick="toggleVisibility('ex_delivery_method');">（<?php _e('explanation', 'usces'); ?>）</a></h3>
<div class="inside">
<table class="form_table">
	<tr>
	    <th>&nbsp;</th>
	    <td><a href="#" id="new_delivery_method_action"><?php _e('New addition', 'usces'); ?></a></td>
	    <th class="sec"></th>
	    <td></td>
		<td></td>
	</tr>
	<tr>
	    <th><?php _e('Shipping name', 'usces'); ?></th>
	    <td width="150" height="30" id="delivery_method_name"></td>
	    <th class="sec"><?php _e('Deliverly time', 'usces'); ?></th>
	    <td rowspan="5"><textarea name="delivery_method_time" class="long_txt" id="delivery_method_time"></textarea></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
	    <th>&nbsp;</th>
	    <td id="delivery_method_name2"></td>
	    <th class="sec"></th>
		<td></td>
	</tr>
	<tr>
	    <th id="delivery_method_loading">&nbsp;</th>
	    <td id="delivery_method_button"></td>
	    <th class="sec"></th>
		<td></td>
	</tr>
	<tr>
	    <th></th>
	    <td><a href="#" id="moveup_action"><?php _e('Raise the priority', 'usces'); ?></a></td>
	    <th rowspan="2" class="sec"></th>
		<td rowspan="2"></td>
	</tr>
	<tr>
	    <th></th>
	    <td><a href="#" id="movedown_action"><?php _e('Lower the priority', 'usces'); ?></a></td>
	</tr>
	<tr>
	    <th></th>
	    <td></td>
	    <th class="sec"><?php _e('Postage fixation', 'usces'); ?></th>
	    <td id="delivery_method_charge_td"></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
	    <th></th>
	    <td></td>
	    <th class="sec"></th>
	    <td><?php _e("It is fixed for above rate setting when I choose postage fixation. The postage set by an article is applied in the case of 'non-fixation'.", 'usces'); ?></td>
		<td>&nbsp;</td>
	</tr>
</table>

<hr size="1" color="#CCCCCC" />
<div id="ex_delivery_method" class="explanation"><?php _e('Please make entry of appointment time to a party by one.', 'usces'); ?></div>
</div>
</div><!--postbox-->

<div class="postbox">
<h3 class="hndle"><span><?php _e('Shipping', 'usces'); ?></span><a style="cursor:pointer;" onclick="toggleVisibility('ex_shipping_charge');">（<?php _e('explanation', 'usces'); ?>）</a></h3>
<div class="inside">
<table class="form_table">
	<tr>
	    <th>&nbsp;</th>
	    <td><a href="#" id="new_shipping_charge_action"><?php _e('New addition', 'usces'); ?></a></td>
	    <th class="sec"></th>
	    <td></td>
		<td></td>
	</tr>
	<tr>
	    <th><?php _e('Shipping charge name', 'usces'); ?></th>
	    <td width="150" height="30" id="shipping_charge_name"></td>
	    <th class="sec"><?php _e('Shipping', 'usces'); ?></th>
	    <td><label class="shipping_charge_label"><input name="allbutton" type="button" class="allbutton" onclick="operation.allCharge();" value="一括設定"  /></label><input name="allcharge" id="allcharge" type="text" class='charge_text' /><?php _e('dollars', 'usces'); ?></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
	    <th>&nbsp;</th>
	    <td width="150" height="30" id="shipping_charge_name2"></td>
	    <th class="sec"></th>
	    <td></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
	    <th id="shipping_charge_loading">&nbsp;</th>
	    <td id="shipping_charge_button"></td>
	    <th class="sec"></th>
		<td><div id="shipping_charge_value"></div></td>
		<td></td>
	</tr>
</table>
<hr size="1" color="#CCCCCC" />
<div id="ex_shipping_charge" class="explanation"><?php _e('You can choose the shipping every item.', 'usces'); ?></div>
</div>
</div><!--postbox-->


</div><!--poststuff-->
</div><!--usces_admin-->
</div><!--wrap-->