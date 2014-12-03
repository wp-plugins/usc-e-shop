// JavaScript
(function($) {
/*
	ppset = {
		get_key : function( pptype ) {
	alert(pptype);
			
			//var defer = $.Deferred();
			if ($.browser.msie && parseInt($.browser.version, 10) <= 9 && window.XDomainRequest) {
				// Use Microsoft XDR
				var xdr = new XDomainRequest();
				xdr.onload = function(){
                    var result = xdr.responseText;
                    alert('XDR ' + result);
                }
                xdr.onerror = function(){
                    alert('XDR error');
                }
                xdr.open('POST', 'https://dev.welcart.org/nanbu/trunk/wordpress/');
                xdr.send('paypal=1&type=' + pptype);
			} else {
				$.ajax({
					url: 'https://paypal-demo.ebay.jp/listeners/welcart/listener.php',
					data: 'paypal=1&type=' + pptype,
					type: 'POST',
					dataType: 'xml',
					crossDomain: true,
					cache: false
				}).done(function(data, dataType){
					alert('success');
					//	var key_value = $(data).find('key').text();
					//	var type_value = $(data).find('type').text();
					//	ppset.put_data( type_value, key_value );
				}).fail(function(data){
					alert('error');
				});
			}
	alert('end');
			return true;
		},
		
		put_data : function(pptype, key_value) {
				alert('OK1');
			
		//	var defer = $.Deferred();
			
			if( 'ppwp' == pptype ){
				var agree_paypal_wpp = $("#agree_paypal_wpp:checked").val();
				var activate = $("input[name='wpp_activate']:checked").val();
				if( "2" == $(".wp_sandbox:checked").val() ){
					var operating = 'production';
				}else{
					var operating = 'sandbox';
				}
				var id = $("#id_paypal_wpp").val();
				var query = 'key=' + key_value + '&type=' + pptype + '&activate=' + activate + '&operating=' + operating + '&id=' + id;

				if( !id || !agree_paypal_wpp ){
				alert('NG');
					return;
				}
			}else if( 'ppec' == pptype ){
				var agree_paypal_ec = $("#agree_paypal_ec:checked").val();
				var activate = $("input[name='ec_activate']:checked").val();
				if( "2" == $(".ec_sandbox:checked").val() ){
					var operating = 'production';
				}else{
					var operating = 'sandbox';
				}
				var user = $("#user_paypal").val();
				var pwd = $("#pwd_paypal").val();
				var signature = $("#signature_paypal").val();
				var acount = $("#acount_paypal").val();
				var query = 'key=' + key_value + '&type=' + pptype + '&activate=' + activate + '&operating=' + operating + '&user=' + user + '&pwd=' + pwd + '&signature=' + signature + '&acount=' + acount;
				if( !user || !pwd || !signature || !acount || !user || !agree_paypal_ec ){
					return;
				}
			}else{
				alert('OK3');
				return;
			}
			$.ajax({
				url: 'https://paypal-demo.ebay.jp/listeners/welcart/listener.php',
				data: query,
				type: 'POST',
				cache: false
//				success:defer.resolve,
//				error:defer.reject,
			});
//			return defer.promise();
		}
	};
	$("#paypal_wpp").click( function(){
		ppset.get_key( 'ppwp' );
		return false;
	});
	$("#paypal_ec").click( function(){
		
		ppset.get_key( 'ppec' ).then(
			function(data) {
				//console.log(data);//debug
				var key_value = $(data).find('key').text();
				var type_value = $(data).find('type').text();
				ppset.put_data( type_value, key_value );
			}
//			,
//			function(data) {
//				//console.log(data);//debug
//			}
		);
		return true;
	});
*/

	$(".ec_sandbox").click( function(){
		if( 1 == $(this).val() ){
			$("#get_paypal_signature").html('<br />テスト環境（Sandbox）用APIユーザ名、APIパスワード、署名の情報は<a target="_blank" href="https://www.sandbox.paypal.com/jp/ja/cgi-bin/webscr?cmd=_get-api-signature&generic-flow=true">こちら</a>から取得可能です。');
		}else{
			$("#get_paypal_signature").html('<br />本番環境用APIユーザ名、APIパスワード、署名の情報は<a target="_blank" href="https://www.paypal.com/jp/ja/cgi-bin/webscr?cmd=_get-api-signature&generic-flow=true">こちら</a>から取得可能です。');
		}
	});
	if( 1 == $(".ec_sandbox:checked").val() ){
		$("#get_paypal_signature").html('<br />テスト環境（Sandbox）用APIユーザ名、APIパスワード、署名の情報は<a target="_blank" href="https://www.sandbox.paypal.com/jp/ja/cgi-bin/webscr?cmd=_get-api-signature&generic-flow=true">こちら</a>から取得可能です。');
	}else{
		$("#get_paypal_signature").html('<br />本番環境用APIユーザ名、APIパスワード、署名の情報は<a target="_blank" href="https://www.paypal.com/jp/ja/cgi-bin/webscr?cmd=_get-api-signature&generic-flow=true">こちら</a>から取得可能です。');
	}

})(jQuery);
