// JavaScript
(function($) {
	itemOpt = {
		settings : {
			url: uscesL10n.requestFile,
			type: 'POST',
			cache: false,
			success: function(data, dataType){
				$("tbody#item-opt-list").html( data );
			}, 
			error: function(msg){
				$("#ajax-response").html(msg);
			}
		},
		
		post : function(action, arg) {
			if( action == 'updatecommonopt' ) {
				itemOpt.updatecommonopt(arg);
			} else if( action == 'updateitemopt' ) {
				itemOpt.updateitemopt(arg);
			} else if( action == 'deletecommonopt' ) {
				itemOpt.deletecommonopt(arg);
			} else if( action == 'deleteitemopt' ) {
				itemOpt.deleteitemopt(arg);
			} else if( action == 'addcommonopt' ) {
				itemOpt.addcommonopt();
			} else if( action == 'additemopt' ) {
				itemOpt.additemopt();
			} else if( action == 'keyselect' ) {
				itemOpt.keyselect(arg);
			}
		},

		addcommonopt : function() {
			if($("#newoptname").val() == '') return;
			
			var id = $("#post_ID").val();
			var name = $("#newoptname").val();
			var value = $("#newoptvalue").val();
			var means = $("#newoptmeans").val();
			var essential = ($("input#newoptessential").attr("checked")) ? '1' : '0';
			var sku = ($("input#newoptsku").attr("checked")) ? '1' : '0';
			
			var s = itemOpt.settings;
			s.data = "action=common_option_ajax&ID=" + id + "&newoptname=" + encodeURIComponent(name) + "&newoptvalue=" +encodeURIComponent(value) + "&newoptmeans=" + encodeURIComponent(means) + "&newoptessential=" + encodeURIComponent(essential) + "&newoptsku=" + encodeURIComponent(sku);
			s.success = function(data, dataType){
				$("table#optlist-table").removeAttr("style");
				$("tbody#item-opt-list").html( data );
				$("#newoptname").val("");
				$("#newoptvalue").val("");
				$("#newoptmeans").attr({selectedIndex:0});
				$("#newoptessential").attr({checked: false});
				$("#newoptsku").attr({checked: false});
			};
			$.ajax( s );
			return false;
		},

		additemopt : function() {
			if($("#optkeyselect").val() == "#NONE#") return;
			
			var id = $("#post_ID").val();
			var name = $("#optkeyselect").val();
			var value = $("#newoptvalue").val();
			var means = $("#newoptmeans").val();
			var essential = ($("input#newoptessential").attr("checked")) ? '1' : '0';
			var sku = $("#newoptsku").val();
			
			var s = itemOpt.settings;
			s.data = "action=item_option_ajax&ID=" + id + "&newoptname=" + encodeURIComponent(name) + "&newoptvalue=" + encodeURIComponent(value) + "&newoptmeans=" + encodeURIComponent(means) + "&newoptessential=" + encodeURIComponent(essential) + "&newoptsku=" + encodeURIComponent(sku);
			s.success = function(data, dataType){
				strs = data.split('#usces#');
				$("table#optlist-table").removeAttr("style");
				$("tbody#item-opt-list").html( strs[0] );
				$("#optkeyselect").attr({selectedIndex:0});
				$("#newoptvalue").val("");
				$("#newoptmeans").attr({selectedIndex:0});
				$("#newoptessential").attr({checked: false});
				$("#newoptsku").val("");
				sku_id = strs[1].split(",");
				for(var i = 0; i < sku_id.length; i++) {
					$("td#itemsku-option-"+sku_id[i]).empty();
					$("td#itemsku-option-"+sku_id[i]).html( strs[i+2] );
				}
				$("td#newskuoption").empty();
				$("td#newskuoption").html( strs[strs.length-1] );
			};
			$.ajax( s );
			return false;
		},

		updatecommonopt : function(meta_id) {
			var id = $("#post_ID").val();
			vs = document.getElementById('itemopt\['+meta_id+'\]\[value\]');
			ms = document.getElementById('itemopt\['+meta_id+'\]\[means\]');
			es = document.getElementById('itemopt\['+meta_id+'\]\[essential\]');
			ss = document.getElementById('itemopt\['+meta_id+'\]\[sku\]');
			var value = $(vs).val();
			var means = $(ms).val();
			var essential = ($(es).attr("checked")) ? '1' : '0';
			var sku = ($(ss).attr("checked")) ? '1' : '0';
			
			var s = itemOpt.settings;
			s.data = "action=common_option_ajax&ID=" + id + "&update=1&optvalue=" + value + "&optmeans=" + means + "&optessential=" + essential + "&optsku=" + sku + "&optmetaid=" + meta_id;
			$.ajax( s );
			return false;
		},

		updateitemopt : function(meta_id) {
			var id = $("#post_ID").val();
			vs = document.getElementById('itemopt\['+meta_id+'\]\[value\]');
			ms = document.getElementById('itemopt\['+meta_id+'\]\[means\]');
			es = document.getElementById('itemopt\['+meta_id+'\]\[essential\]');
			ss = document.getElementById('itemopt\['+meta_id+'\]\[sku\]');
			var value = $(vs).val();
			var means = $(ms).val();
			var essential = ($(es).attr("checked")) ? '1' : '0';
			var sku = $(ss).val();

			var deleteitemopt = 'input[name="deleteitemopt\[' +  meta_id + '\]"]';
			if( !$("#opt_loading-" + meta_id).length ){
				$(deleteitemopt).before('<div id="opt_loading-' + meta_id + '"></div>');
			}
			$("#opt_loading-" + meta_id).html('<img src="' + uscesL10n.USCES_PLUGIN_URL + '/images/loading.gif" />');

			var s = itemOpt.settings;
			s.data = "action=item_option_ajax&ID=" + id + "&update=1&optvalue=" + value + "&optmeans=" + means + "&optessential=" + essential + "&optsku=" + sku + "&optmetaid=" + meta_id;
			s.success = function(data, dataType){
				$("#opt_loading-" + meta_id).html('');
				strs = data.split('#usces#');
				$("tbody#item-opt-list").html( strs[0] );
				sku_id = strs[1].split(",");
				for(var i = 0; i < sku_id.length; i++) {
					$("td#itemsku-option-"+sku_id[i]).empty();
					$("td#itemsku-option-"+sku_id[i]).html( strs[i+2] );
				}
				$("td#newskuoption").empty();
				$("td#newskuoption").html( strs[strs.length-1] );
			};
			$.ajax( s );
			return false;
		},

		deletecommonopt : function(meta_id) {
			var id = $("#post_ID").val();
			var s = itemOpt.settings;
			s.data = "action=common_option_ajax&ID=" + id + "&delete=1&optmetaid=" + meta_id;
			$.ajax( s );
			return false;
		},

		deleteitemopt : function(meta_id) {
			var id = $("#post_ID").val();
			ss = document.getElementById('itemopt\['+meta_id+'\]\[sku\]');
			var s = itemOpt.settings;
			s.data = "action=item_option_ajax&ID=" + id + "&delete=1&optmetaid=" + meta_id;
			s.success = function(data, dataType){
				strs = data.split('#usces#');
				$("tbody#item-opt-list").html( strs[0] );
				sku_id = strs[1].split(",");
				for(var i = 0; i < sku_id.length; i++) {
					$("td#itemsku-option-"+sku_id[i]).empty();
					$("td#itemsku-option-"+sku_id[i]).html( strs[i+2] );
				}
				$("td#newskuoption").empty();
				$("td#newskuoption").html( strs[strs.length-1] );
			};
			$.ajax( s );
			return false;
		},

		keyselect : function( key ) {
			if(key == '#NONE#'){
				$("#newoptvalue").val("");
				$("#newoptmeans").attr({selectedIndex:0});
				$("#newoptessential").attr({checked: false});
				$("#newoptsku").val("");
				return;
			}
			var id = uscesL10n.cart_number;

			var add_itemopt = '#add_itemopt';
			if( !$("#opt_loading").length ){
				$(add_itemopt).before('<div id="opt_loading"></div>');
			}
			$("#opt_loading").html('<img src="' + uscesL10n.USCES_PLUGIN_URL + '/images/loading.gif" />');

			var s = itemOpt.settings;
			s.data = "action=item_option_ajax&ID=" + id + "&select=1&key=" + encodeURIComponent(key);
			s.success = function(data, dataType){
				$("#opt_loading").html('');
				strs = data.split('#usces#');
				var means = strs[0];
				var essential = strs[1];
				var sku = strs[2];
				var value = strs[3];
				$("#newoptvalue").val(value);
				$("#newoptmeans").val(means);
				if( essential == '1') {
					$("#newoptessential").attr({checked: true});
				}else{
					$("#newoptessential").attr({checked: false});
				}
				$("#newoptsku").val(sku);
			};
			$.ajax( s );
			return false;
		}
	};

	itemSku = {
		settings : {
			url: uscesL10n.requestFile,
			type: 'POST',
			cache: false,
			success: function(data, dataType){
				strs = data.split('#usces#');
				//alert(strs[1]);return;
				$("tbody#item-sku-list").html( strs[0] );
				$("select#skukeyselect").html( strs[1] );
			}, 
			error: function(msg){
				//$("#skuajax-response").html(msg);
			}
		},
		
		post : function(action, arg) {
			if( action == 'updateitemsku' ) {
				itemSku.updateitemsku(arg);
			} else if( action == 'deleteitemsku' ) {
				itemSku.deleteitemsku(arg);
			} else if( action == 'additemsku' ) {
				itemSku.additemsku();
			} else if( action == 'keyselect' ) {
				itemSku.keyselect(arg);
			}
		},

		additemsku : function() {
			var id = $("#post_ID").val();
			if($("#skukeyselect").val() == undefined || $("#skukeyselect").css("display")  == 'none'){
				var name = $("#newskuname").val();
			}else{
				var name = $("#skukeyselect").val();
			}
			var cprice = $("#newskucprice").val();
			var price = $("#newskuprice").val();
			var zaikonum = $("#newskuzaikonum").val();
			var zaiko = $("#newskuzaikoselect").val();
			var skudisp = $("#newskudisp").val();
			var skuunit = $("#newskuunit").val();
			//var skugptekiyo = $("#newskugptekiyo").val();
			//if( undefined != $("#newcharging_type option:selected").val() )
			//	var charging_type = '&newcharging_type=' + $("#newcharging_type option:selected").val();
			//else
			//	var charging_type = '&newcharging_type=0';
				
			//if( undefined != $("#newskuadvance").val() )
			//	var skuadvance = '&newskuadvance=' + encodeURIComponent($("#newskuadvance").val());

			var gprice = $("#newskugprice").val();
			var mprice = $("#newskumprice").val();

			var skuoption_name = "newskuoption";
			var skukey = "";
			var skuoption = "";
			var sp = "";
			$(":input[name^='"+skuoption_name+"']").each(function(i, obj) {
				key = $(this).attr('name').substring(skuoption_name.length+1);
				key = key.replace("]", "");
				skukey += sp + key;
				skuoption += sp + $(this).val();
				sp = '#usces#';
			});

			//option check
			var newstr = '';
			var str = '';
			var elm_sku = $("input[name='updateitemsku']");
			var storage = {};
			var dep = false;
			$("select[name^='newskuoption']").each(function(i, obj) {
				newstr += $(obj).val();
			});
			storage[newstr] = 1;
			if( 0 < elm_sku.length ){
				$( elm_sku ).each(function(i1, obj1) {
					var id = $(obj1).attr('id');
					id = id.replace(/updateitemsku\[(.+)\]/, '$1');
					$("select[name^='itemsku\["+id+"\]\[skuoption\]']").each(function(i2, obj2) {
						str += $(obj2).val();
					});
					if ( str in storage ) {
						dep = true;
					}else{
						storage[str] = 1;
					}
					str = '';
				});
			}
			
			if( dep ){
				$mes = '<div class="error">';
				$mes += '<p>商品規格が他のSKUと重複しています。</p>';
				$mes += '</div>';
				$("#sku_ajax-response").html($mes);
				return false;
			}

			if( '' == name || '' == price ){
				$mes = '<div class="error">';
				if( '' == name )
					$mes += '<p>SKUコードの値を入力してください。</p>';
				if( '' == price )
					$mes += '<p>売価の値を入力してください。</p>';
				$mes += '</div>';
				$("#sku_ajax-response").html($mes);
				return false;
			}
			if( !$("#newsku_loading").length ){
				$("#add_itemsku").before('<div id="newsku_loading"></div>');
			}
			$("#newsku_loading").html('<img src="' + uscesL10n.USCES_PLUGIN_URL + '/images/loading.gif" />');


			var s = itemSku.settings;
			//s.data = "action=item_sku_ajax&ID=" + id + "&newskuname=" + encodeURIComponent(name) + "&newskucprice=" + cprice + "&newskuprice=" + price + "&newskuzaikonum=" + zaikonum + "&newskuzaikoselect=" + encodeURIComponent(zaiko) + "&newskudisp=" + encodeURIComponent(skudisp) + "&newskuunit=" + encodeURIComponent(skuunit) + "&newskugptekiyo=" + skugptekiyo + charging_type + skuadvance;
			s.data = "action=item_sku_ajax&ID=" + id + "&newskuname=" + encodeURIComponent(name) + "&newskucprice=" + cprice + "&newskuprice=" + price + "&newskuzaikonum=" + zaikonum + "&newskuzaikoselect=" + encodeURIComponent(zaiko) + "&newskudisp=" + encodeURIComponent(skudisp) + "&newskuunit=" + encodeURIComponent(skuunit) + "&newskugprice=" + gprice + "&newskumprice=" + mprice + "&newskukey=" + skukey + "&newskuoption=" + skuoption;
			s.success = function(data, dataType){
				$("#newsku_loading").html('');
				$("#sku_ajax-response").html('');
				strs = data.split('#usces#');
				if(0 < strs[2].length) {
					$mes = '<div class="error"><p>'+strs[2]+'</p></div>';
					$("#sku_ajax-response").html( $mes );
				} else {
					$("table#skulist-table").removeAttr("style");
					$("tbody#skukeyselect").html( strs[1] );
					$("tbody#item-sku-list").html( strs[0] );
					$("#skukeyselect").attr({selectedIndex:0});
					$("#newskuname").val("");
					$("#newskucprice").val("");
					$("#newskuprice").val("");
					$("#newskuzaikonum").val("");
					$("#newskuzaikonum").val("");
					$("#newskuzaikoselect").attr({selectedIndex:0});
					$("#newskudisp").val("");
					$("#newskuunit").val("");
					//$("#newskugptekiyo").attr({selectedIndex:0});
					//$("#newcharging_type").attr({selectedIndex:0});
					//if( undefined != $("input[name='newskuadvance']").val() )
					//	$("#newskuadvance").val("");
					//if( undefined != $("select[name='newskuadvance']").val() )
					//	$("#newskuadvance").attr({selectedIndex:0});
					$("#newskugprice").val("");
					$("#newskumprice").val("");
					$(":input[name^='"+skuoption_name+"']").each(function(i, obj) {
						//if($(this).attr('class') == 'item-sku-option') {
						//	$(this).attr('selectedIndex', 0);
						//} else {
							$(this).val("");
						//}
					});
				}
			};
			s.error = function(msg){
				$("#newsku_loading").html('');
				$("#sku_ajax-response").html(msg);
			};
			$.ajax( s );
			return false;
		},

		updateitemsku : function(meta_id) {
			var id = $("#post_ID").val();
			ks = document.getElementById('itemsku\['+meta_id+'\]\[key\]');
			cs = document.getElementById('itemsku\['+meta_id+'\]\[cprice\]');
			ps = document.getElementById('itemsku\['+meta_id+'\]\[price\]');
			ns = document.getElementById('itemsku\['+meta_id+'\]\[zaikonum\]');
			zs = document.getElementById('itemsku\['+meta_id+'\]\[zaiko\]');
			ds = document.getElementById('itemsku\['+meta_id+'\]\[skudisp\]');
			us = document.getElementById('itemsku\['+meta_id+'\]\[skuunit\]');
			//gs = document.getElementById('itemsku\['+meta_id+'\]\[skugptekiyo\]');
			//ct = document.getElementById('itemsku\['+meta_id+'\]\[charging_type\]');
			//ad = document.getElementById('itemsku\['+meta_id+'\]\[skuadvance\]');
			gs = document.getElementById('itemsku\['+meta_id+'\]\[gprice\]');
			ms = document.getElementById('itemsku\['+meta_id+'\]\[mprice\]');
			var name = $(ks).val();
			var cprice = $(cs).val();
			var price = $(ps).val();
			var zaikonum = $(ns).val();
			var zaiko = $(zs).val();
			var skudisp = $(ds).val();
			var skuunit = $(us).val();
			//var skugptekiyo = $(gs).val();
			//if( undefined != $(ct).val() )
			//	var charging_type = '&charging_type=' + $(ct).val();
			//else
			//	var charging_type = '&charging_type=0';
			
			//if( undefined != $(ad).val() )
			//	var skuadvance = '&skuadvance=' + encodeURIComponent($(ad).val());
			var gprice = $(gs).val();
			var mprice = $(ms).val();

			var skuoption_name = "itemsku\["+meta_id+"\]\[skuoption\]";
			var skukey = "";
			var skuoption = "";
			var sp = "";
			$(":input[name^='"+skuoption_name+"']").each(function(i, obj) {
				key = $(this).attr('name').substring(skuoption_name.length+1);
				key = key.replace("]", "");
				skukey += sp + key;
				skuoption += sp + $(this).val();
				sp = '#usces#';
			});
			//option check
			var str = '';
			var elm_sku = $("input[name='updateitemsku']");
			var storage = {};
			var dep = false;
			if( 1 < elm_sku.length ){
				$( elm_sku ).each(function(i1, obj1) {
					var id = $(obj1).attr('id');
					id = id.replace(/updateitemsku\[(.+)\]/, '$1');
					$("select[name^='itemsku\["+id+"\]\[skuoption\]']").each(function(i2, obj2) {
						str += $(obj2).val();
					});
					if ( str in storage ) {
						dep = true;
					}else{
						storage[str] = 1;
					}
					str = '';
				});
			}
			if( dep ){
				$mes = '<div class="error">';
				$mes += '<p>商品規格が他のSKUと重複しています。</p>';
				$mes += '</div>';
				$("#sku_ajax-response-" + meta_id).html($mes);
				return false;
			}
			if( '' == name || '' == price ){
				$mes = '<div class="error">';
				if( '' == name )
					$mes += '<p>SKUコードの値を入力してください。</p>';
				if( '' == price )
					$mes += '<p>売価の値を入力してください。</p>';
				$mes += '</div>';
				$("#sku_ajax-response").html($mes);
				return false;
			}
			var deleteitemsku = 'input[name="deleteitemsku\[' +  meta_id + '\]"]';
			if( !$("#sku_loading-" + meta_id).length ){
				$(deleteitemsku).before('<div id="sku_loading-' + meta_id + '"></div>');
			}
			$("#sku_loading-" + meta_id).html('<img src="' + uscesL10n.USCES_PLUGIN_URL + '/images/loading.gif" />');
			
			var s = itemSku.settings;
			//s.data = "action=item_sku_ajax&ID=" + id + "&update=1&skuprice=" + price + "&skucprice=" + cprice + "&skuzaikonum=" + zaikonum + "&skuzaiko=" + encodeURIComponent(zaiko) + "&skuname=" + encodeURIComponent(name) + "&skudisp=" + encodeURIComponent(skudisp) + "&skuunit=" + encodeURIComponent(skuunit) + "&skugptekiyo=" + skugptekiyo + "&skumetaid=" + meta_id + charging_type + skuadvance;
			s.data = "action=item_sku_ajax&ID=" + id + "&update=1&skuprice=" + price + "&skucprice=" + cprice + "&skuzaikonum=" + zaikonum + "&skuzaiko=" + encodeURIComponent(zaiko) + "&skuname=" + encodeURIComponent(name) + "&skudisp=" + encodeURIComponent(skudisp) + "&skuunit=" + encodeURIComponent(skuunit) + "&skumetaid=" + meta_id + "&skugprice=" + gprice + "&skumprice=" + mprice + "&skukey=" + skukey + "&skuoption=" + skuoption;
			s.success = function(data, dataType){
				$("#sku_ajax-response").html('');
				$("#sku_loading-" + meta_id).html('');
				strs = data.split('#usces#');
				if(0 < strs[2].length) {
					$mes = '<div class="error"><p>'+strs[2]+'</p></div>';
					$("#sku_ajax-response-" + meta_id).html( $mes );
				} else {
					$("#sku_ajax-response-" + meta_id).html('');
				}
			};
			s.error = function(msg){
				$("#sku_ajax-response").html('');
				$("#sku_loading-" + meta_id).html('');
				$("#sku_ajax-response-" + meta_id).html(msg);
			};
			$.ajax( s );
			return false;
		},

		deleteitemsku : function(meta_id) {
			var id = $("#post_ID").val();
			var s = itemSku.settings;
			s.data = "action=item_sku_ajax&ID=" + id + "&delete=1&skumetaid=" + meta_id;
			s.error = function(msg){
				$("#sku_ajax-response").html(msg);
			};
			$.ajax( s );
			return false;
		},
		
		keyselect : function( key ) {
			if(key == '#NONE#'){
				$("#newskuprice").val("");
				return;
			}
			var id = uscesL10n.cart_number;
			var s = itemOpt.settings;
			s.data = "action=item_sku_ajax&ID=" + id + "&select=1&key=" + encodeURIComponent(key);
			s.success = function(data, dataType){
				strs = data.split('#usces#');
				$("#newskucprice").val(strs[1]);
				$("#newskuprice").val(strs[0]);
				$("#newskugprice").val(strs[2]);
				$("#newskumprice").val(strs[3]);
			};
			s.error = function(msg){
				$("#sku_ajax-response").html(msg);
			};
			$.ajax( s );
			return false;
		}
	};

	payment = {
		settings: {
			url: uscesL10n.requestFile,
			type: 'POST',
			cache: false,
			success: function(data, dataType){
				$("tbody#payment-list").html( data );

			}, 
			error: function(msg){
				$("#payment-response").html(msg);
			}
		},
		
		post : function(action, arg) {
			if( action == 'update' ) {
				payment.update(arg);
			} else if( action == 'del' ) {
				payment.del(arg);
			} else if( action == 'add' ) {
				payment.add();
			}
		},

		add : function() {
			if($("#newname").val() == '') return;
			
			var name = $("#newname").val();
			var explanation = $("#newexplanation").val();
			var settlement = $("#newsettlement").val();
			var module = $("#newmodule").val();
			
			var s = payment.settings;
			s.data = "action=payment_ajax&newname=" + encodeURIComponent(name) + "&newexplanation=" + encodeURIComponent(explanation) + "&newsettlement=" + encodeURIComponent(settlement) + "&newmodule=" + encodeURIComponent(module);
			s.success = function(data, dataType){
					$("table#payment-table").removeAttr("style");
					$("tbody#payment-list").html( data );
					$("#newname").val("");
					$("#newexplanation").val("");
					$("#newsettlement").attr({selectedIndex:0});
					$("#newmodule").val("");
			};
			$.ajax( s );
			return false;
		},

		update : function(id) {
			vn = document.getElementById('payment\[' + id + '\]\[name\]');
			ve = document.getElementById('payment\[' + id + '\]\[explanation\]');
			vs = document.getElementById('payment\[' + id + '\]\[settlement\]');
			vm = document.getElementById('payment\[' + id + '\]\[module\]');
			var name = $(vn).val();
			var explanation = $(ve).val();
			var settlement = $(vs).val();
			var module = $(vm).val();
			var s = payment.settings;
			s.data = "action=payment_ajax&update=1&id=" + id + "&name=" + encodeURIComponent(name) + "&explanation=" + encodeURIComponent(explanation) + "&settlement=" + encodeURIComponent(settlement) + "&module=" + encodeURIComponent(module);
			$.ajax( s );
			return false;
		},

		del : function(id) {
			var s = payment.settings;
			s.data = "action=payment_ajax&delete=1&id=" + id;
			$.ajax( s );
			return false;
		}
	};

	orderItem = {
		settings: {
			url: uscesL10n.requestFile,
			type: 'POST',
			cache: false,
			success: function(data, dataType){
				$("#newitemform").html( data );

			}, 
			error: function(msg){
				$("#order-response").html(msg);
			}
		},
		
		add2cart : function(newid, newsku) {
			var ID = $("input[name='order_id']").val();
			var cnum = $("#orderitemlist").children().length;
			var priceob = $("input[name*='skuPrice']");
			var quantob = $("input[name*='quant']");
			var name = '';
			var strs = '';
			var post_ids = [];
			var skus = [];
			var optob;
			var optvalue = '';
			var query = 'action=order_item2cart_ajax&order_id='+ID;
			
			for( var i = 0; i < cnum; i++) {
				name = $(priceob[i]).attr("name");
				strs = name.split('[');
				post_ids[i] = strs[2].replace(/[\]]+$/g, '');
				skus[i] = strs[3].replace(/[\]]+$/g, '');

				query += "&skuPrice["+i+"]["+post_ids[i]+"]["+skus[i]+"]="+$("input[name='skuPrice\[" + i + "\]\[" + post_ids[i] + "\]\[" + skus[i] + "\]']").val();
				query += "&quant["+i+"]["+post_ids[i]+"]["+skus[i]+"]="+$("input[name='quant\[" + i + "\]\[" + post_ids[i] + "\]\[" + skus[i] + "\]']").val();

				optob = $("input[name*='optName\[" + i + "\]\[" + post_ids[i] + "\]\[" + skus[i] + "\]']");
				optvalue = '';
				for( var o = 0; o < optob.length; o++) {
					optvalue = $("input[name='itemOption\[" + i + "\]\[" + post_ids[i] + "\]\[" + skus[i] + "\]\[" + $(optob[o]).val() + "\]']").val();
					if( '#NONE#' != optvalue){
						query += "&itemOption["+i+"]["+post_ids[i]+"]["+skus[i]+"][" + $(optob[o]).val() + "]="+optvalue;
					}
				}
			}
			
			query += "&skuPrice["+cnum+"]["+newid+"]["+newsku+"]="+$("input[name='skuNEWPrice\[" + newid + "\]\[" + newsku + "\]']").val();
			query += "&quant["+cnum+"]["+newid+"]["+newsku+"]=1";
			var newoptob = $("input[name*='optNEWName\[" + newid + "\]\[" + newsku + "\]']");
			var newoptvalue = '';
			for( var n = 0; n < newoptob.length; n++) {
				newoptvalue = $("select[name='itemNEWOption\[" + newid + "\]\[" + newsku + "\]\[" + $(newoptob[n]).val() + "\]']").val();
				if( '#NONE#' != newoptvalue){
					query += "&itemOption["+cnum+"]["+newid+"]["+newsku+"][" + $(newoptob[n]).val() + "]="+newoptvalue;
				}
			}
				
				
				
			var s = orderItem.settings;
			s.data = query;
			s.success = function(data, dataType){
				if(data == 'nodata'){return;}
				var pict = "<img src='" + $("#newitemform img").attr("src") + "' height='60' width='60' alt='' />";
				var itemName = $("input[name='itemNEWName\["+newid+"\]\["+newsku+"\]']").val() + ' ' + $("input[name='itemNEWCode\["+newid+"\]\["+newsku+"\]']").val();
				var zaiko = $("input[name='zaiNEWko\["+newid+"\]\["+newsku+"\]']").val();
				var price = "<input name='skuPrice[" + cnum + "][" + newid + "][" + newsku + "]' class='text price' type='text' value='" + $("input[name='skuNEWPrice\["+newid+"\]\["+newsku+"\]']").val() + "' onchange='orderfunc.sumPrice()' />";
				var quant = "<input name='quant[" + cnum + "][" + newid + "][" + newsku + "]' class='text quantity' type='text' value='1' onchange='orderfunc.sumPrice()' />";
				var delButton = "<input name='delButton[" + cnum + "][" + newid + "][" + newsku + "]' class='delCartButton' type='submit' value='削除' />";
				
				var sucoptob = $("input[name*='optNEWName\[" + newid + "\]\[" + newsku + "\]']");
				var skuOptValue = '';
				var sucoptvalue = '';
				var hiddenopt = '';
				var hiddenoptn = '';
				for( var i = 0; i < sucoptob.length; i++) {
					sucoptname = $(sucoptob[i]).val();
					sucoptvalue = $("select[name='itemNEWOption\[" + newid + "\]\[" + newsku + "\]\[" + sucoptname + "\]']").val();
					if( '#NONE#' != sucoptvalue){
						skuOptValue += sucoptvalue + ' ';
						hiddenopt += "<input name='itemOption[" + cnum + "][" + newid + "][" + newsku + "][" + sucoptname + "]' class='text quantity' type='hidden' value='"+sucoptvalue+"' />";
						hiddenoptn += "<input name='optName[" + cnum + "][" + newid + "][" + newsku + "][" + sucoptname + "]' class='text quantity' type='hidden' value='"+sucoptname+"' />";
					}
				}
				var htm = "<tr>\n";
				htm += "<td>"+(cnum+1)+"</td>\n";
				htm += "<td>"+pict+"</td>\n";
				htm += "<td class='aleft'>"+itemName+"<br />"+skuOptValue+"</td>\n";
				htm += "<td>"+price+"</td>\n";
				htm += "<td>"+quant+"</td>\n";
				htm += "<td id='sub_total["+cnum+"]' class='aright'>&nbsp;</td>\n";
				htm += "<td>"+zaiko+"</td>\n";
				htm += "<td>"+delButton+hiddenopt+hiddenoptn+"</td>\n";
				htm += "</tr>\n";

				$("#orderitemlist").append( htm );
				orderfunc.sumPrice();
			};
			$.ajax( s );
			return false;
		}, 
		
		getitem : function() {
			if($("#newitemcode").val() == '') return;
			
			var itemcode = $("#newitemcode").val();
			var s = orderItem.settings;
			s.data = "action=order_item_ajax&mode=get_order_item&itemcode=" + encodeURIComponent(itemcode);
			s.success = function(data, dataType){
					$("#newitemform").html( data );
			};
			$.ajax( s );
			return false;
		},

		getmailmessage : function( flag ) {
			$("#sendmailmessage").val( uscesL10n.now_loading );
			var order_id = $("input[name='order_id']").val();
			var s = orderItem.settings;
			s.data = "action=order_item_ajax&mode=" + flag + "&order_id=" + order_id;
			s.success = function(data, dataType){
					$("#sendmailmessage").val( data );
			};
			$.ajax( s );
			return false;
		}

	};

	uscesInformation = {
		settings: {
			url: 'http://www.welcart.com/util/welcart_information.php',
			type: 'POST',
			cache: false,
			success: function(data, dataType){
				//$("#newitemform").html( data );

			}, 
			error: function(msg){
				$("#wc_information").html( 'error : ' +  msg );
			}
		},
		
		getinfo : function() {
			var s = uscesInformation.settings;
			s.data = "v=" + encodeURIComponent(uscesL10n.version);
			s.data += "&wcid=" + encodeURIComponent(uscesL10n.wcid);
			s.data += "&wcurl=" + encodeURIComponent(uscesL10n.USCES_PLUGIN_URL);
			s.data += "&locale=" + encodeURIComponent(uscesL10n.locale);
			s.data += "&theme=" + encodeURIComponent(uscesL10n.theme);
			s.data += "&wcex=";
			var de = '';
			for( var i = 0; i < uscesL10n.wcex.length; i++) {
				s.data += de + encodeURIComponent(uscesL10n.wcex[i]);
				de =',';
			}
			s.success = function(data, dataType){
					$("#wc_information").html( data );
			};
			$.ajax( s );
			return false;
		},
		
		getinfo2 : function() {
			var s = uscesInformation.settings;
			s.url = uscesL10n.requestFile;
			s.data = 'action=getinfo_ajax';
			s.success = function(data, dataType){
					$("#wc_information").html( data );
			};
			$.ajax( s );
			return false;
		}
		
	};
	
	uscesItem = {
		
		newdraft : function(itemCode) {
			if(jQuery("#title").val().length == 0 || jQuery("#title").val() == '') {
				$("#title").val(itemCode);
			}
			autosave();
			
		},

		cahngepict : function(code) {
			$("div#item-select-pict").html(code);
		}
		
	};
	
})(jQuery);
