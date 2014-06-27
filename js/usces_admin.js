// JavaScript
(function($) {
	itemOpt = {
		settings: {
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
			if( action == 'updateitemopt' ) {
				itemOpt.updateitemopt(arg);
			} else if( action == 'deleteitemopt' ) {
				itemOpt.deleteitemopt(arg);
			} else if( action == 'additemopt' ) {
				itemOpt.additemopt();
			} else if( action == 'addcommonopt' ) {
				itemOpt.addcommonopt();
			} else if( action == 'keyselect' ) {
				itemOpt.keyselect(arg);
			}
		},

		additemopt : function() {
			if($("#optkeyselect").val() == "#NONE#") return;
			
			var id = $("#post_ID").val();
			var name = $("#optkeyselect option:selected").html();
			var value = $("#newoptvalue").val();
			var means = $("#newoptmeans").val();
			if($("input#newoptessential").attr("checked")){
				var essential = '1';
			}else{
				var essential = '0';
			}
			
			var mes = '';
			if( '' == name ){
				mes += '<p>オプション名を入力してください。</p>';
			} else {
				var check = true;
				$("input[name*='\[name\]']").each(function(){ if( name == $(this).val() ){ check = false; }});
				if( !check ){
					mes += '<p>同じ名前のオプションが存在します。</p>';
				}
			}
			if( '' == value && (0 == means || 1 == means || 3 == means || 4 == means) ){
				mes += '<p>セレクト値を入力してください。</p>';
			}else if( '' != value && (2 == means || 5 == means) ){
				mes += '<p>テキスト、テキストエリアの場合はセレクト値を空白にしてください。</p>';
			}
			if( '' != mes ){
				$("#itemopt_ajax-response").html('<div class="error">' + mes + '</div>');
				return false;
			}
			
			$("#newitemopt_loading").html('<img src="' + uscesL10n.USCES_PLUGIN_URL + '/images/loading.gif" />');

			var s = itemOpt.settings;
			s.data = "action=item_option_ajax&ID=" + id + "&newoptname=" + encodeURIComponent(name) + "&newoptvalue=" + encodeURIComponent(value) + "&newoptmeans=" + encodeURIComponent(means) + "&newoptessential=" + encodeURIComponent(essential);
			s.success = function(data, dataType){
				$("#itemopt_ajax-response").html('');
				$("#newitemopt_loading").html('');
				$("table#optlist-table").removeAttr("style");
				strs = data.split('#usces#');
				meta_id = strs[1];
				$("tbody#item-opt-list").html( strs[0] );
				$("#optkeyselect").val('#NONE#');
				$("#newoptvalue").html('');
				$("#newoptmeans").val(0);
				$("#newoptessential").attr({checked: false});
				$("#itemopt-" + meta_id).css({'background-color': '#FF4'});
				$("#itemopt-" + meta_id).animate({ 'background-color': '#FFFFEE' }, 2000 );
			};
			s.error = function(msg){
				$("#itemopt_ajax-response").html(msg);
				$("#newitemopt_loading").html('');
			};
			$.ajax( s );
			return false;
		},

		addcommonopt : function() {
			var id = $("#post_ID").val();
			var name = $("#newoptname").val();
			var value = $("#newoptvalue").val();
			var means = $("#newoptmeans").val();
			if($("input#newoptessential").attr("checked")){
				var essential = '1';
			}else{
				var essential = '0';
			}

			var mes = '';
			if( '' == name ){
				mes += '<p>オプション名を入力してください。</p>';
			} else {
				var check = true;
				$("input[name*='\[name\]']").each(function(){ if( name == $(this).val() ){ check = false; }});
				if( !check ){
					mes += '<p>同じ名前のオプションが存在します。</p>';
				}
			}
			if( '' == value && (0 == means || 1 == means || 3 == means || 4 == means) ){
				mes += '<p>セレクト値を入力してください。</p>';
			}else if( '' != value && (2 == means || 5 == means) ){
				mes += '<p>テキスト、テキストエリアの場合はセレクト値を空白にしてください。</p>';
			}
			if( '' != mes ){
				$("#itemopt_ajax-response").html('<div class="error">' + mes + '</div>');
				return false;
			}
			
			$("#newcomopt_loading").html('<img src="' + uscesL10n.USCES_PLUGIN_URL + '/images/loading.gif" />');

			var s = itemOpt.settings;
			s.data = "action=item_option_ajax&ID=" + id + "&newoptname=" + encodeURIComponent(name) + "&newoptvalue=" +encodeURIComponent(value) + "&newoptmeans=" + encodeURIComponent(means) + "&newoptessential=" + encodeURIComponent(essential);
			s.success = function(data, dataType){
				$("#newcomopt_loading").html('');
				$("#itemopt_ajax-response").html('');
				strs = data.split('#usces#');
				$("table#optlist-table").removeAttr("style");
				var meta_id = strs[1];
				if( 0 > meta_id ){
					$("#itemopt_ajax-response").html('<div class="error"><p>同じ名前のオプションが存在します。</p></div>');
				}else{
					$("tbody#item-opt-list").html( strs[0] );
					$("#newoptname").val('');
					$("#newoptvalue").val('');
					$("#newoptmeans").val(0);
					$("#newoptessential").attr({checked: false});
					$("#itemopt-" + meta_id).css({'background-color': '#FF4'});
					$("#itemopt-" + meta_id).animate({ 'background-color': '#FFFFEE' }, 2000 );
				}
			};
			s.error = function(msg){
				$("#comopt_ajax-response").html(msg);
				$("#newcomopt_loading").html('');
			};
			$.ajax( s );
			return false;
		},

		updateitemopt : function(meta_id) {
			var id = $("#post_ID").val();
			nm = document.getElementById('itemopt\['+meta_id+'\]\[name\]');
			vs = document.getElementById('itemopt\['+meta_id+'\]\[value\]');
			ms = document.getElementById('itemopt\['+meta_id+'\]\[means\]');
			es = document.getElementById('itemopt\['+meta_id+'\]\[essential\]');
			so = document.getElementById('itemopt\['+meta_id+'\]\[sort\]');
			var name = $(nm).val();
			var value = uscesItem.trim($(vs).val());
			var means = $(ms).val();
			var sortnum = $(so).val();
			if($(es).attr("checked")){
				var essential = '1';
			}else{
				var essential = '0';
			}

			var mes = '';
			if( '' == name ){
				mes += '<p>オプション名を入力してください。</p>';
			}
			if( '' == value && (0 == means || 1 == means || 3 == means || 4 == means) ){
				mes += '<p>セレクト値を入力してください。</p>';
			}else if( '' != value && (2 == means || 5 == means) ){
				mes += '<p>テキスト、テキストエリアの場合はセレクト値を空白にしてください。</p>';
			}
			if( '' != mes ){
				$("#itemopt_ajax-response").html('<div class="error">' + mes + '</div>');
				return false;
			}

			$("#itemopt_loading-" + meta_id).html('<img src="' + uscesL10n.USCES_PLUGIN_URL + '/images/loading.gif" />');

			var s = itemOpt.settings;
			s.data = "action=item_option_ajax&ID=" + id + "&update=1&optname=" + encodeURIComponent(name) + "&optvalue=" + encodeURIComponent(value) + "&optmeans=" + means + "&optessential=" + essential + "&sort=" + sortnum + "&optmetaid=" + meta_id;
			s.success = function(data, dataType){
				$("#itemopt_ajax-response").html('');
				$("#itemopt_loading-" + meta_id).html('');
				strs = data.split('#usces#');
				$("tbody#item-opt-list").html( strs[0] );
				$("#itemopt-" + meta_id).css({'background-color': '#FF4'});
				$("#itemopt-" + meta_id).animate({ 'background-color': '#FFFFEE' }, 2000 );
			};
			s.error = function(msg){
				$("#itemopt_ajax-response").html(msg);
				$("#itemopt_loading-" + meta_id).html('');
			};
			$.ajax( s );
			return false;
		},

		deleteitemopt : function(meta_id) {
			$("#itemopt-" + meta_id).css({'background-color': '#F00'});
			$("#itemopt-" + meta_id).animate({ 'background-color': '#FFFFEE' }, 1000 );
			var id = $("#post_ID").val();
			var s = itemOpt.settings;
			s.data = "action=item_option_ajax&ID=" + id + "&delete=1&optmetaid=" + meta_id;
			s.success = function(data, dataType){
				$("#itemopt_ajax-response").html("");
				strs = data.split('#usces#');
				$("tbody#item-opt-list").html( strs[0] );
			};
			s.error = function(msg){

			};
			$.ajax( s );
			return false;
		},
		
		keyselect : function( meta_id ) {
			if(meta_id == '#NONE#'){
				$("#newoptvalue").val('');
				$("#newoptmeans").val(0);
				$("#newoptessential").attr({checked: false});
				return;
			}
			var id = uscesL10n.cart_number;
			
			$("#newitemopt_loading").html('<img src="' + uscesL10n.USCES_PLUGIN_URL + '/images/loading.gif" />');
			$("#add_itemopt").attr("disabled", true);
			
			var s = itemOpt.settings;
			s.data = "action=item_option_ajax&ID=" + id + "&select=1&meta_id=" + meta_id;
			s.success = function(data, dataType){
				$("#itemopt_ajax-response").html("");
				strs = data.split('#usces#');
				var means = strs[0];
				var essential = strs[1];
				var value = strs[2];
				if( 2 == means || 5 == means ){
					value = '';
				}
				$("#newoptvalue").html(value);
				$("#newoptmeans").val(means);
				if( essential == '1') {
					$("#newoptessential").attr({checked: true});
				}else{
					$("#newoptessential").attr({checked: false});
				}
				$("#newitemopt_loading").html('');
				$("#add_itemopt").attr("disabled", false);
			};
			s.error = function(msg){
				$("#itemopt_ajax-response").html(msg);
				$("#newitemopt_loading").html('');
			};
			$.ajax( s );
			return false;
		},
		
		dosort : function( str ) {
			if( !str ) return;
			var id = $("#post_ID").val();
			var meta_id_str = str.replace(/itemopt-/g, "");
			var meta_ids = meta_id_str.split(',');
			if( 2 > meta_ids.length ) return;

			for(i=0; i<meta_ids.length; i++){
				$("#itemopt_loading-" + meta_ids[i]).html('<img src="' + uscesL10n.USCES_PLUGIN_URL + '/images/loading.gif" />');
			}
			var s = itemOpt.settings;
			s.data = "action=item_option_ajax&ID=" + id + "&sort=1&meta=" + encodeURIComponent(meta_id_str);
			s.success = function(data, dataType){
				$("#itemopt_ajax-response").html("");
				strs = data.split('#usces#');
				$("tbody#item-opt-list").html( strs[0] );
				for(i=0; i<meta_ids.length; i++){
					$("#itemopt_loading-" + meta_ids[i]).html('');
					$("#itemopt-" + meta_ids[i]).css({'background-color': '#FF4'});
					$("#itemopt-" + meta_ids[i]).animate({ 'background-color': '#FFFFEE' }, 2000 );
				}
			};
			s.error = function(msg){
				$("#opt_ajax-response").html('<div class="error"><p>error sort</p></div>');
			};
			$.ajax( s );
			return false;
		}
	};

	itemSku = {
		settings: {
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
				$("#skuajax-response").html(msg);
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
			var name = $("#newskuname").val();
			var cprice = $("#newskucprice").val();
			var price = $("#newskuprice").val();
			var zaikonum = $("#newskuzaikonum").val();
			var zaiko = $("#newskuzaikoselect").val();
			var skudisp = $("#newskudisp").val();
			var skuunit = $("#newskuunit").val();
			var skugptekiyo = $("#newskugptekiyo").val();

			var mes = '';
			if( '' == name )
				mes += '<p>SKUコードの値を入力してください。</p>';
			if( '' == price )
				mes += '<p>売価の値を入力してください。</p>';
//			if( ! checkCode( name ) )
//				mes += '<p>SKUコードは半角英数（-_を含む）で入力して下さい。</p>';
			if( ! checkMoney( cprice ) )
				mes += '<p>通常価は数値で入力して下さい。</p>';
			if( ! checkMoney( price ) )
				mes += '<p>売価は数値で入力して下さい。</p>';
			if( ! checkNum( zaikonum ) )
				mes += '<p>在庫数は数値で入力して下さい。</p>';
			if( '' != mes ){
				$("#sku_ajax-response").html('<div class="error">' + mes + '</div>');
				return false;
			}

			var skuadvance = '';
			if( undefined != $("input[name*='newskuadvance']") ) {
				if( 1 == $("input[name='newskuadvance']").length ) {
					skuadvance = '&newskuadvance='+encodeURIComponent($("input[name*='newskuadvance']").val());
				} else {
					$("input[name*='newskuadvance']").each(function() {
						skuadvance += '&newskuadvance['+$(this).attr("id")+']='+encodeURIComponent($(this).val());
					});
				}
			}

			$("#newitemsku_loading").html('<img src="' + uscesL10n.USCES_PLUGIN_URL + '/images/loading.gif" />');

			var s = itemSku.settings;
			s.data = "action=item_sku_ajax&ID=" + id + "&newskuname=" + encodeURIComponent(name) + "&newskucprice=" + cprice + "&newskuprice=" + price + "&newskuzaikonum=" + zaikonum + "&newskuzaikoselect=" + encodeURIComponent(zaiko) + "&newskudisp=" + encodeURIComponent(skudisp) + "&newskuunit=" + encodeURIComponent(skuunit) + "&newskugptekiyo=" + skugptekiyo + skuadvance;
			s.success = function(data, dataType){
				$("#newitemsku_loading").html('');
				$("#sku_ajax-response").html("");
				strs = data.split('#usces#');
				$("table#skulist-table").removeAttr("style");
				var meta_id = strs[1];
				if( 0 > meta_id ){
					$("#sku_ajax-response").html('<div class="error"><p>同じSKUコードが存在します。</p></div>');
				}else{
					$("tbody#item-sku-list").html( strs[0] );
					$("#itemsku-" + meta_id).css({'background-color': '#FF4'});
					$("#itemsku-" + meta_id).animate({ 'background-color': '#FFFFEE' }, 2000 );
					$("#newskuname").val("");
					$("#newskucprice").val("");
					$("#newskuprice").val("");
					$("#newskuzaikonum").val("");
					$("#newskuzaikonum").val("");
					$("#newskuzaikoselect").val(0);
					$("#newskudisp").val("");
					$("#newskuunit").val("");
					$("#newskugptekiyo").val(0);
					if( 0 < $("input[name*='newskuadvance']").length ) {
						$("input[name*='newskuadvance']").each(function(index, element) {
							$(this).val("");
						});
					}
					if( 0 < $("select[name*='newskuadvance']").length ) {
						$("select[name*='newskuadvance']").each(function(index, element) {
							$(this).val("");
						});
					}
				}
				//$("#itemsku-" + meta_id).css({'background-color': '#FF4'});
				//$("#itemsku-" + meta_id).animate({ 'background-color': '#FFFFEE' }, 2000 );
			};
			s.error = function(msg){
				$("#sku_ajax-response").html(msg);
				$("#newitemsku_loading").html('');
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
			gs = document.getElementById('itemsku\['+meta_id+'\]\[skugptekiyo\]');
			//ad = document.getElementById('itemsku\['+meta_id+'\]\[skuadvance\]');
			ad = $("input[name*='itemsku\["+meta_id+"\]\[skuadvance\]']");
			so = document.getElementById('itemsku\['+meta_id+'\]\[sort\]');
			var name = $(ks).val();
			var cprice = $(cs).val();
			var price = $(ps).val();
			var zaikonum = $(ns).val();
			var zaiko = $(zs).val();
			var skudisp = $(ds).val();
			var skuunit = $(us).val();
			var skugptekiyo = $(gs).val();
			var sortnum = $(so).val();

			var mes = '';
			if( '' == name )
				mes += '<p>SKUコードの値を入力してください。</p>';
			if( '' == price )
				mes += '<p>売価の値を入力してください。</p>';
			if( ! checkMoney( cprice ) )
				mes += '<p>通常価は数値で入力して下さい。</p>';
			if( ! checkMoney( price ) )
				mes += '<p>売価は数値で入力して下さい。</p>';
			if( ! checkNum( zaikonum ) )
				mes += '<p>在庫数は数値で入力して下さい。</p>';
			if( '' != mes ){
				$("#sku_ajax-response").html('<div class="error">' + mes + '</div>');
				return false;
			}

			var skuadvance = '';
			if( undefined != $(ad) ) {
				if( 1 == $(ad).length ) {
					skuadvance = '&skuadvance='+encodeURIComponent($(ad).val());
				} else {
					$(ad).each(function() {
						skuadvance += '&skuadvance['+$(this).attr("id")+']='+encodeURIComponent($(this).val());
					});
				}
			}

			$("#itemsku_loading-" + meta_id).html('<img src="' + uscesL10n.USCES_PLUGIN_URL + '/images/loading.gif" />');
			var s = itemSku.settings;
			s.data = "action=item_sku_ajax&ID=" + id + "&update=1&skuprice=" + price + "&skucprice=" + cprice + "&skuzaikonum=" + zaikonum + "&skuzaiko=" + encodeURIComponent(zaiko) + "&skuname=" + encodeURIComponent(name) + "&skudisp=" + encodeURIComponent(skudisp) + "&skuunit=" + encodeURIComponent(skuunit) + "&skugptekiyo=" + skugptekiyo + "&sort=" + sortnum + "&skumetaid=" + meta_id + skuadvance;
			s.success = function(data, dataType){
				$("#itemsku_loading-" + meta_id).html('');
				$("#sku_ajax-response").html("");
				strs = data.split('#usces#');
				$("table#skulist-table").removeAttr("style");
				var id = strs[1];
				if( 0 > id ){
					$("#sku_ajax-response").html('<div class="error"><p>同じSKUコードが存在します。</p></div>');
				}else{
					$("tbody#item-sku-list").html( strs[0] );
					$("#itemsku-" + meta_id).css({'background-color': '#FF4'});
					$("#itemsku-" + meta_id).animate({ 'background-color': '#FFFFEE' }, 2000 );
				}
			};
			s.error = function(msg){
				$("#sku_ajax-response").html(msg);
				$("#itemsku_loading-" + meta_id).html('');
			};
			$.ajax( s );
			return false;
		},

		deleteitemsku : function(meta_id) {
			var data=[];
			$("#itemsku-" + meta_id).css({'background-color': '#F00'});
			$("#itemsku-" + meta_id).animate({ 'background-color': '#FFFFEE' }, 1000 );
			var id = $("#post_ID").val();
			var s = itemSku.settings;
			s.data = "action=item_sku_ajax&ID=" + id + "&delete=1&skumetaid=" + meta_id;
			s.success = function(data, dataType){
				$("#itemsku_loading-" + meta_id).html('');
				$("#sku_ajax-response").html("");
				strs = data.split('#usces#');
				$("tbody#item-sku-list").html( strs[0] );
			};
			s.error = function(msg){
				$("#sku_ajax-response").html(msg);
				$("#itemsku_loading-" + meta_id).html('');
			};
			$.ajax( s );
			return false;
		},
		
		dosort : function( str ) {
			if( !str ) return;
			var id = $("#post_ID").val();
			var meta_id_str = str.replace(/itemsku-/g, "");
			var meta_ids = meta_id_str.split(',');
			if( 2 > meta_ids.length ) return;

			for(i=0; i<meta_ids.length; i++){
				$("#itemsku_loading-" + meta_ids[i]).html('<img src="' + uscesL10n.USCES_PLUGIN_URL + '/images/loading.gif" />');
			}
			var s = itemSku.settings;
			s.data = "action=item_sku_ajax&ID=" + id + "&sort=1&meta=" + encodeURIComponent(meta_id_str);
			s.success = function(data, dataType){
				$("#sku_ajax-response").html("");
				strs = data.split('#usces#');
				$("tbody#item-sku-list").html( strs[0] );
				for(i=0; i<meta_ids.length; i++){
					//$("#itemsku_loading-" + meta_ids[i]).html('');
					$("#itemsku-" + meta_ids[i]).css({'background-color': '#FF4'});
					$("#itemsku-" + meta_ids[i]).animate({ 'background-color': '#FFFFEE' }, 2000 );
				}
			};
			s.error = function(msg){
				$("#sku_ajax-response").html('<div class="error"><p>error sort</p></div>');
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
			var name = $("#newname").val();
			var explanation = $("#newexplanation").val();
			var settlement = $("#newsettlement").val();
			var module = $("#newmodule").val();
			
			var mes = '';
			if( '' == name ){
				mes += '<p>支払方法名の値を入力してください。</p>';
			}
			if( '#NONE#' == settlement ) {
				mes += '<p>決済種別を選択してください。</p>';
			}
			if( 'acting' == settlement ) {//代行業者決済のとき
				if( '' == module ) {
					mes += '<p>決済モジュールの値を入力してください。</p>';
				}
			}
			if( '' != mes ){
				$("#payment_ajax-response").html('<div class="error">' + mes + '</div>');
				return false;
			}
			
			$("#newpayment_loading").html('<img src="' + uscesL10n.USCES_PLUGIN_URL + '/images/loading.gif" />');

			var s = payment.settings;
			s.data = "action=payment_ajax&newname=" + encodeURIComponent(name) + "&newexplanation=" + encodeURIComponent(explanation) + "&newsettlement=" + encodeURIComponent(settlement) + "&newmodule=" + encodeURIComponent(module);
			s.success = function(data, dataType){
				$("#newpayment_loading").html('');
				$("#payment_ajax-response").html('');
				strs = data.split('#usces#');
				$("table#payment-table").removeAttr("style");
				if( -1 == strs[1] ){
					$("#payment_ajax-response").html('<div class="error"><p>同じ支払方法名が存在します。</p></div>');
				}else{
					$("tbody#payment-list").html( strs[0] );
					$("#newname").val("");
					$("#newexplanation").val("");
					$("#newsettlement").val('acting');
					$("#newmodule").val("");
					$("#payment-" + strs[1]).css({'background-color': '#FF4'});
					$("#payment-" + strs[1]).animate({ 'background-color': '#FFFFEE' }, 2000 );
				}
			};
			s.error = function(msg){
				$("#payment_ajax-response").html(msg);
				$("#newpayment_loading").html('');
			};
			$.ajax( s );
			return false;
		},

		update : function(id) {
			vn = document.getElementById('payment\[' + id + '\]\[name\]');
			ve = document.getElementById('payment\[' + id + '\]\[explanation\]');
			vs = document.getElementById('payment\[' + id + '\]\[settlement\]');
			vm = document.getElementById('payment\[' + id + '\]\[module\]');
			so = document.getElementById('payment\[' + id + '\]\[sort\]');
			var name = $(vn).val();
			var explanation = $(ve).val();
			var settlement = $(vs).val();
			var module = $(vm).val();
			var sortid = $(so).val();
			var use = $("input[name='payment[" + id + "][use]']:checked").val();
			var s = payment.settings;

			var mes = '';
			if( '' == name ){
				mes += '<p>支払方法名の値を入力してください。</p>';
			}
			if( '#NONE#' == settlement ) {
				mes += '<p>決済種別を選択してください。</p>';
			}
			if( 'acting' == settlement ) {//代行業者決済のとき
				if( '' == module ) {
					mes += '<p>決済モジュールの値を入力してください。</p>';
				}
			}
			if( '' != mes ){
				$("#payment_ajax-response").html('<div class="error">' + mes + '</div>');
				return false;
			}
			
			$("#payment_loading-" + id).html('<img src="' + uscesL10n.USCES_PLUGIN_URL + '/images/loading.gif" />');

			s.data = "action=payment_ajax&update=1&id=" + id + "&name=" + encodeURIComponent(name) + "&explanation=" + encodeURIComponent(explanation) + "&settlement=" + encodeURIComponent(settlement) + "&module=" + encodeURIComponent(module) + "&sort=" + sortid + "&use=" + use;
			s.success = function(data, dataType){
				$("#payment_loading-" + id).html('');
				$("#payment_ajax-response").html("");
				strs = data.split('#usces#');
				if( -1 == strs[1] ){
					$("#payment_ajax-response").html('<div class="error"><p>同じ支払方法名が存在します。</p></div>');
				}else{
					$("tbody#payment-list").html( strs[0] );
					$("#payment-" + id).css({'background-color': '#FF4'});
					$("#payment-" + id).animate({ 'background-color': '#FFFFEE' }, 2000 );
				}
			};
			s.error = function(msg){
				$("#payment_ajax-response").html(msg);
				$("#newpayment_loading").html('');
			};
			$.ajax( s );
			return false;
		},

		del : function(id) {
			$("#payment-" + id).css({'background-color': '#F00'});
			$("#payment-" + id).animate({ 'background-color': '#FFFFEE' }, 1000 );
			var s = payment.settings;
			s.data = "action=payment_ajax&delete=1&id=" + id;
			s.success = function(data, dataType){
				strs = data.split('#usces#');
				$("tbody#payment-list").html( strs[0] );
			};
			s.error = function(msg){
				$("#payment_ajax-response").html(msg);
			};
			$.ajax( s );
			return false;
		},
		
		dosort : function( str ) {
			if( !str ) return;
			var meta_id_str = str.replace(/payment-/g, "");
			var meta_ids = meta_id_str.split(',');
			if( 2 > meta_ids.length ) return;

			for(i=0; i<meta_ids.length; i++){
				$("#payment_loading-" + meta_ids[i]).html('<img src="' + uscesL10n.USCES_PLUGIN_URL + '/images/loading.gif" />');
			}
			var s = payment.settings;
			s.data = "action=payment_ajax&sort=1&idstr=" + encodeURIComponent(meta_id_str);
			s.success = function(data, dataType){
				strs = data.split('#usces#');
				$("tbody#payment-list").html( strs[0] );
				for(i=0; i<meta_ids.length; i++){
					$("#payment_loading-" + meta_ids[i]).html('');
					$("#payment-" + meta_ids[i]).css({'background-color': '#FF4'});
					$("#payment-" + meta_ids[i]).animate({ 'background-color': '#FFFFEE' }, 2000 );
				}
			};
			s.error = function(msg){
				$("#payment_ajax-response").html('<div class="error"><p>error sort</p></div>');
			};
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
			var optob;
			var optvalue = '';
			var query = 'action=order_item2cart_ajax&order_id='+ID+'&post_id='+newid+'&sku='+newsku;
			
			var newoptob = $("input[name*='optNEWCode\[" + newid + "\]\[" + newsku + "\]']");
			var newoptvalue = '';
			var mes = '';
			for( var n = 0; n < newoptob.length; n++) {
				newoptvalue = $(":input[name='itemNEWOption\[" + newid + "\]\[" + newsku + "\]\[" + $(newoptob[n]).val() + "\]']").val();
				var newoptclass = $(":input[name='itemNEWOption\[" + newid + "\]\[" + newsku + "\]\[" + $(newoptob[n]).val() + "\]']").attr("class");
				var essential = $(":input[name='optNEWEssential\[" + newid + "\]\[" + newsku + "\]\[" + $(newoptob[n]).val() + "\]']").val();
				switch(newoptclass) {
				case 'iopt_select_multiple':
					var sel = 0;
					if( essential == 1 ) {
						$(":input[name='itemNEWOption\[" + newid + "\]\[" + newsku + "\]\[" + $(newoptob[n]).val() + "\]'] option:selected").each(function(idx, obj) {
							if( '#NONE#' != $(this).val()) {
								sel++;
							}
						});
						if( sel == 0 ) {
							mes += decodeURIComponent($(newoptob[n]).val())+'を選択してください'+"\n";
						}
					}
					$(":input[name='itemNEWOption\[" + newid + "\]\[" + newsku + "\]\[" + $(newoptob[n]).val() + "\]'] option:selected").each(function(idx, obj) {
						if( '#NONE#' != $(this).val()) {
							query += "&itemOption[" + $(newoptob[n]).val() + "][" + encodeURIComponent($(this).val()) + "]="+encodeURIComponent($(this).val());
						}
					});
					break;
				case 'iopt_select':
					if( essential == 1 && newoptvalue == '#NONE#' ) {
						mes += decodeURIComponent($(newoptob[n]).val())+'を選択してください'+"\n";
					} else {
						query += "&itemOption[" + $(newoptob[n]).val() + "]="+encodeURIComponent(newoptvalue);
					}
					break;
				case 'iopt_text':
				case 'iopt_textarea':
					if( essential == 1 && newoptvalue == '' ) {
						mes += decodeURIComponent($(newoptob[n]).val())+'を入力してください'+"\n";
					} else {
						query += "&itemOption[" + $(newoptob[n]).val() + "]="+encodeURIComponent(newoptvalue);
					}
					break;
				}
			}
			if( mes != '' ) {
				alert(mes);
				return;
			}

			var s = orderItem.settings;
			s.data = query;
			s.success = function(data, dataType){
				$("#orderitemlist").html(data);
				orderfunc.sumPrice(null);
			};
			$.ajax( s );
			return false;
		},
		
		add2cart_old : function(newid, newsku) {
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
//20110715ysk start 0000202
					//optvalue = $("input[name='itemOption\[" + i + "\]\[" + post_ids[i] + "\]\[" + skus[i] + "\]\[" + $(optob[o]).val() + "\]']").val();
					//if( '#NONE#' != optvalue){
					//	query += "&itemOption["+i+"]["+post_ids[i]+"]["+skus[i]+"][" + $(optob[o]).val() + "]="+optvalue;
					//}
					var cnt = 0;
					$("input[name^='itemOption\[" + i + "\]\[" + post_ids[i] + "\]\[" + skus[i] + "\]\[" + $(optob[o]).val() + "\]\[']").each(function(idx, obj) {
						cnt++;
					});
					if(0 < cnt) {
						$("input[name^='itemOption\[" + i + "\]\[" + post_ids[i] + "\]\[" + skus[i] + "\]\[" + $(optob[o]).val() + "\]\[']").each(function(idx, obj) {
							query += "&itemOption["+i+"]["+post_ids[i]+"]["+skus[i]+"][" + $(optob[o]).val() + "][" + $(this).val() + "]="+$(this).val();
						});
					} else {
						optvalue = $("input[name='itemOption\["+i+"\]\["+post_ids[i]+"\]\["+skus[i]+"\]\["+$(optob[o]).val()+"\]']").val();
						query += "&itemOption["+i+"]["+post_ids[i]+"]["+skus[i]+"]["+$(optob[o]).val()+"]="+optvalue;
					}
//20110715ysk end
				}
			}
			query += "&skuPrice["+cnum+"]["+newid+"]["+newsku+"]="+$("input[name='skuNEWPrice\[" + newid + "\]\[" + newsku + "\]']").val();
			query += "&quant["+cnum+"]["+newid+"]["+newsku+"]=1";
			var newoptob = $("input[name*='optNEWCode\[" + newid + "\]\[" + newsku + "\]']");
			var newoptvalue = '';
			var mes = '';
			for( var n = 0; n < newoptob.length; n++) {
//20110715ysk start 0000202
				//newoptvalue = $("select[name='itemNEWOption\[" + newid + "\]\[" + newsku + "\]\[" + $(newoptob[n]).val() + "\]']").val();
				newoptvalue = $(":input[name='itemNEWOption\[" + newid + "\]\[" + newsku + "\]\[" + $(newoptob[n]).val() + "\]']").val();
				var newoptclass = $(":input[name='itemNEWOption\[" + newid + "\]\[" + newsku + "\]\[" + $(newoptob[n]).val() + "\]']").attr("class");
				var essential = $(":input[name='optNEWEssential\[" + newid + "\]\[" + newsku + "\]\[" + $(newoptob[n]).val() + "\]']").val();
				switch(newoptclass) {
				case 'iopt_select_multiple':
					var sel = 0;
					if( essential == 1 ) {
						$(":input[name='itemNEWOption\[" + newid + "\]\[" + newsku + "\]\[" + $(newoptob[n]).val() + "\]'] option:selected").each(function(idx, obj) {
							if( '#NONE#' != $(this).val()) {
								sel++;
							}
						});
					}
					if( sel == 0 ) {
						mes += decodeURIComponent($(newoptob[n]).val())+'を選択してください'+"\n";
					} else {
						$(":input[name='itemNEWOption\[" + newid + "\]\[" + newsku + "\]\[" + $(newoptob[n]).val() + "\]'] option:selected").each(function(idx, obj) {
							if( '#NONE#' != $(this).val()) {
								query += "&itemOption["+cnum+"]["+newid+"]["+newsku+"][" + $(newoptob[n]).val() + "][" + $(this).val() + "]="+$(this).val();
							}
						});
					}
					break;
				case 'iopt_select':
					if( essential == 1 && newoptvalue == '#NONE#' ) {
						mes += decodeURIComponent($(newoptob[n]).val())+'を選択してください'+"\n";
					} else {
						query += "&itemOption["+cnum+"]["+newid+"]["+newsku+"][" + $(newoptob[n]).val() + "]="+newoptvalue;
					}
					break;
				case 'iopt_text':
				case 'iopt_textarea':
					if( essential == 1 && newoptvalue == '' ) {
						mes += decodeURIComponent($(newoptob[n]).val())+'を入力してください'+"\n";
					} else {
						query += "&itemOption["+cnum+"]["+newid+"]["+newsku+"][" + $(newoptob[n]).val() + "]="+newoptvalue;
					}
					break;
				}
//20110715ysk end
			}
			if( mes != '' ) {
				alert(mes);
				return;
			}
		
			var s = orderItem.settings;
			s.data = query;
			s.success = function(data, dataType){
//20120613ysk start 0000500
				//if(data == 'nodata'){return;}
				strs = data.split('#usces#');
				if( 0 > parseInt(strs[0]) ) return;
//20120613ysk end
				var pict = "<img src='" + $("#newitemform img").attr("src") + "' width='" + $("#newitemform img").attr("width") + "' height='" + $("#newitemform img").attr("height") + "' alt='' />";
//20120613ysk start 0000500
				//var itemName = $("input[name='itemNEWName\["+newid+"\]\["+newsku+"\]']").val() + ' ' + $("input[name='itemNEWCode\["+newid+"\]\["+newsku+"\]']").val() + ' ' + $("input[name='skuNEWName\["+newid+"\]\["+newsku+"\]']").val();
				var itemName = strs[1];
//20120613ysk end
				var zaiko = $("input[name='zaiNEWko\["+newid+"\]\["+newsku+"\]']").val();
//20120528ysk start 0000485
				//var price = "<input name='skuPrice[" + cnum + "][" + newid + "][" + newsku + "]' class='text price' type='text' value='" + $("input[name='skuNEWPrice\["+newid+"\]\["+newsku+"\]']").val() + "' onchange='orderfunc.sumPrice()' />";
				//var quant = "<input name='quant[" + cnum + "][" + newid + "][" + newsku + "]' class='text quantity' type='text' value='1' onchange='orderfunc.sumPrice()' />";
//20120613ysk start 0000500
				//var price = "<input name='skuPrice[" + cnum + "][" + newid + "][" + newsku + "]' class='text price' type='text' value='" + $("input[name='skuNEWPrice\["+newid+"\]\["+newsku+"\]']").val() + "' />";
				var price = "<input name='skuPrice[" + cnum + "][" + newid + "][" + newsku + "]' class='text price' type='text' value='" + strs[0] + "' />";
//20120613ysk end
				var quant = "<input name='quant[" + cnum + "][" + newid + "][" + newsku + "]' class='text quantity' type='text' value='1' />";
//20120528ysk end
				var delButton = "<input name='delButton[" + cnum + "][" + newid + "][" + newsku + "]' class='delCartButton' type='submit' value='削除' />\n<input name='advance[" + cnum + "][" + newid + "][" + newsku + "]' type='hidden' value='' />\n";
				
				var sucoptob = $("input[name*='optNEWCode\[" + newid + "\]\[" + newsku + "\]']");
				var skuOptValue = '';
				var skuoptval = '';
				var hiddenopt = '';
				var hiddenoptname = '';
				for( var i = 0; i < sucoptob.length; i++) {
					skuoptcode = $(sucoptob[i]).val();
					skuoptname = decodeURIComponent($(sucoptob[i]).val());
					hiddenoptname += "<input name='optName[" + newid + "][" + newsku + "][" + skuoptcode + "]' type='hidden' value='"+skuoptcode+"' />\n";
//20110715ysk start 0000202
					//skuoptval = $("select[name='itemNEWOption\[" + newid + "\]\[" + newsku + "\]\[" + skuoptcode + "\]']").val();
					skuoptval = $(":input[name='itemNEWOption\[" + newid + "\]\[" + newsku + "\]\[" + skuoptcode + "\]']").val();
					//if( '#NONE#' != skuoptval){
					//	skuOptValue += skuoptval + ' ';
					//	hiddenopt += "<input name='itemOption[" + cnum + "][" + newid + "][" + newsku + "][" + skuoptcode + "]' type='hidden' value='"+skuoptval+"' />";
					//	hiddenoptname += "<input name='optName[" + cnum + "][" + newid + "][" + newsku + "][" + skuoptcode + "]' type='hidden' value='"+skuoptcode+"' />";
					//}
					skuOptValue += skuoptname + ' : ';
					var sucoptclass = $(":input[name='itemNEWOption\[" + newid + "\]\[" + newsku + "\]\[" + skuoptcode + "\]']").attr("class");
					switch(sucoptclass) {
					case 'iopt_select_multiple':
						var c = '';
						$(":input[name='itemNEWOption\[" + newid + "\]\[" + newsku + "\]\[" + skuoptcode + "\]'] option:selected").each(function(idx, obj) {
							if( '#NONE#' != $(this).val() ) {
								skuOptValue += c + $(this).val();
								c = ', ';
								hiddenopt += "<input name='itemOption[" + cnum + "][" + newid + "][" + newsku + "][" + skuoptcode + "][" + encodeURIComponent($(this).val()) + "]' type='hidden' value='"+encodeURIComponent($(this).val())+"' />\n";
							}
						});
						break;
					case 'iopt_select':
						if( '#NONE#' != skuoptval ) {
							skuOptValue += skuoptval;
							hiddenopt += "<input name='itemOption[" + cnum + "][" + newid + "][" + newsku + "][" + skuoptcode + "]' type='hidden' value='"+encodeURIComponent(skuoptval)+"' />\n";
						}
						break;
					case 'iopt_text':
					case 'iopt_textarea':
						skuOptValue += skuoptval;
						hiddenopt += "<input name='itemOption[" + cnum + "][" + newid + "][" + newsku + "][" + skuoptcode + "]' type='hidden' value='"+encodeURIComponent(skuoptval)+"' />\n";
						break;
					}
					skuOptValue += '<br />';
//20110715ysk end
				}
				var htm = "<tr>\n";
				htm += "<td>"+(cnum+1)+"</td>\n";
				htm += "<td>"+pict+"</td>\n";
				htm += "<td class='aleft'>"+itemName+"<br />"+skuOptValue+"</td>\n";
				htm += "<td>"+price+"</td>\n";
				htm += "<td>"+quant+"</td>\n";
				htm += "<td id='sub_total["+cnum+"]' class='aright'>&nbsp;</td>\n";
				htm += "<td>"+zaiko+"</td>\n";
				htm += "<td>"+delButton+hiddenoptname+hiddenopt+"</td>\n";
				htm += "</tr>\n";

				$("#orderitemlist").append( htm );
//20120307ysk start 0000432
				orderfunc.sumPrice(null);

				$("input[name='skuPrice["+cnum+"]["+newid+"]["+newsku+"]']").bind("change", {index:cnum, post_id:newid, sku:newsku}, function(e){ orderfunc.sumPrice($(this)); });
				$("input[name='quant["+cnum+"]["+newid+"]["+newsku+"]']").bind("change", {index:cnum, post_id:newid, sku:newsku}, function(e){ orderfunc.sumPrice($(this)); });
				$("input[name='delButton["+cnum+"]["+newid+"]["+newsku+"]']").bind("click", {index:cnum, post_id:newid, sku:newsku}, function(e){ return delConfirm($(this)); });
//20120307ysk end
//20120528ysk start 0000485
				$("input[name*='skuPrice[" + cnum + "][" + newid + "][" + newsku + "]']").bind("change", function(){ orderfunc.sumPrice($(this)); });
				$("input[name*='quant[" + cnum + "][" + newid + "][" + newsku + "]']").bind("change", function(){ orderfunc.sumPrice($(this)); });
				$("input[name*='delButton[" + cnum + "][" + newid + "][" + newsku + "]']").bind("click", function(){ orderfunc.sumPrice(null); });
//20120528ysk end
			};
			$.ajax( s );
			return false;
		}, 
		
		getSelitem : function( cat_id ) {
			if(cat_id == '-1'){
				$("#newitemcode").html('');
				return false;
			}
			$("#loading").html('<img src="' + uscesL10n.USCES_PLUGIN_URL + '/images/loading.gif" />');
			var s = orderItem.settings;
			s.data = "action=order_item_ajax&mode=get_item_select_option&cat_id=" + cat_id;
			s.success = function(data, dataType){
				$("#loading").html('');
				$("#newitemcode").html( data );
			};
			$.ajax( s );
			return false;
		},

		getitem : function(itemcode) {
			if(itemcode == '-1'){
				$("#newitemform").html('');
				return false;
			}
			$("#loading").html('<img src="' + uscesL10n.USCES_PLUGIN_URL + '/images/loading.gif" />');
			var s = orderItem.settings;
			s.data = "action=order_item_ajax&mode=get_order_item&itemcode=" + itemcode;
			s.success = function(data, dataType){
				$("#loading").html('');
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
		
		newdraft : function(itemName) {
			if(jQuery("#title").val().length == 0 || jQuery("#title").val() == '') {
				$("#title").val(itemName);
			}
			//autosave();
			
		},

		cahngepict : function(code) {
			$("div#item-select-pict").html(code);
		},
		
		trim : function(target){
			target = target.replace(/(^\s+)|(\s+$)|(^\n+)|(\n+$)/g, "");
			return target;
		}
		
	};
	
	$("#newitemcategory").live( 'change', function(){
		orderItem.getSelitem( $(this).val() );
	});
	
	$("#newitemcode").live( 'change', function(){
		orderItem.getitem( $(this).val() );
	});
	
	$("#getitembutton").click( function(){
		orderItem.getitem( encodeURIComponent($("#newitemcodein").val()) );
	});
	
	
})(jQuery);

function usces_check_num(obj) {
	if(!checkNum(obj.val())) {
		alert('数値で入力して下さい。');
		obj.focus();
		return false;
	}
	return true;
}
function usces_check_money(obj) {
	if(!checkMoney(obj.val())) {
		alert('数値で入力して下さい。');
		obj.focus();
		return false;
	}
	return true;
}
function checkAlp(argValue) {
	if(argValue.match(/[^a-z|^A-Z]/g)) {
		return false;
	}
	return true;
}
function checkCode(argValue) {
	if(argValue.match(/[^0-9|^a-z|^A-Z|^\-|^_]/g)) {
		return false;
	}
	return true;
}
function checkNum(argValue) {
	if(argValue.match(/[^0-9]/g)) {
		return false;
	}
	return true;
}
function checkMoney(argValue) {
	if(argValue.match(/[^0-9|^\.]/g)) {
		return false;
	}
	return true;
}
function checkPrice(argValue) {
	if(argValue.match(/[^0-9|^\-|^\,|^\.]/g)) {
		return false;
	}
	return true;
}
