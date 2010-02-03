// JavaScript Document
(function($) {
	uscesCart = {
		intoCart : function (post_id, sku) {
			
			var zaikonum = document.getElementById("zaikonum["+post_id+"]["+sku+"]").value;
			var zaiko = document.getElementById("zaiko["+post_id+"]["+sku+"]").value;
			if( (zaiko != '0' && zaiko != '1') ||  parseInt(zaikonum) == 0 ){
				alert("只今在庫切れです。" );
				return false;
			}
			
			var mes = '';
			if(document.getElementById("quant["+post_id+"]["+sku+"]")){
				var quant = document.getElementById("quant["+post_id+"]["+sku+"]").value;
				if( quant == '0' || quant == '' || !(uscesCart.isNum(quant))){
					mes += "数量を正しく入力して下さい。\n";
				}
				var checknum = '';
				var checkmode = '';
				if( parseInt(uscesL10n.itemRestriction) <= parseInt(zaikonum) && uscesL10n.itemRestriction != '' && uscesL10n.itemRestriction != '0' && zaikonum != '' ) {
					checknum = uscesL10n.itemRestriction;
					checkmode ='rest';
				} else if( parseInt(uscesL10n.itemRestriction) > parseInt(zaikonum) && uscesL10n.itemRestriction != '' && uscesL10n.itemRestriction != '0' && zaikonum != '' ) {
					checknum = zaikonum;
					checkmode ='zaiko';
				} else if( (uscesL10n.itemRestriction == '' || uscesL10n.itemRestriction == '0') && zaikonum != '' ) {
					checknum = zaikonum;
					checkmode ='zaiko';
				} else if( uscesL10n.itemRestriction != '' && uscesL10n.itemRestriction != '0' && zaikonum == '' ) {
					checknum = uscesL10n.itemRestriction;
					checkmode ='rest';
				}
								

				if( parseInt(quant) > parseInt(checknum) && checknum != '' ){
						if(checkmode == 'rest'){
							mes += "この商品は一度に"+checknum+"までの数量制限が有ります。\n";
						}else{
							mes += "この商品の在庫は残り"+checknum+"です。\n";
						}
				}
			}
			for(i=0; i<uscesL10n.key_opts.length; i++){
				var skuob = document.getElementById("itemOption["+post_id+"]["+sku+"]["+uscesL10n.key_opts[i]+"]");
				if( uscesL10n.opt_esse[i] == '1' ){
					
					if( uscesL10n.opt_means[i] < 2 && skuob.value == '#NONE#' ){
						mes += uscesL10n.mes_opts[i]+"\n";
					}else if( uscesL10n.opt_means[i] >= 2 && skuob.value == '' ){
						mes += uscesL10n.mes_opts[i]+"\n";
					}
				}
			}
			if( mes != '' ){
				alert( mes );
				return false;
			}else{
				return true;
			}
		},
		
		upCart : function () {
			
			var zaikoob = $("input[name*='zaikonum']");
			var quantob = $("input[name*='quant']");
			var postidob = $("input[name*='itempostid']");
			var skuob = $("input[name*='itemsku']");
			
			var zaikonum = '';
			var zaiko = '';
			var quant = '';
			var mes = '';
			var checknum = '';
			var post_id = '';
			var sku = '';
			var itemRestriction = '';
			
			var ct = zaikoob.length;
			for(var i=0; i< ct; i++){
				post_id = postidob[i].value;
				sku = skuob[i].value;
				itemRestriction = $("input[name='itemRestriction\[" + i + "\]']").val();
				zaikonum = $("input[name='zaikonum\[" + i + "\]\[" + post_id + "\]\[" + sku + "\]']").val();
//				zaiko = $("#stockid\["+i+"\]").val();
//				if( zaiko != '0' && zaiko != '1' ){
//					alert("只今在庫切れです。" );
//					return false;
//				}
		
				quant = $("input[name='quant\[" + i + "\]\[" + post_id + "\]\[" + sku + "\]']").val();
				if( $("input[name='quant\[" + i + "\]\[" + post_id + "\]\[" + sku + "\]']") ){
					if( quant == '' || !(uscesCart.isNum(quant))){
						mes += (i+1) + "番の商品の数量を正しく入力して下さい。\n";
					}
					var checknum = '';
					var checkmode = '';
					if( parseInt(itemRestriction) <= parseInt(zaikonum) && itemRestriction != '' && itemRestriction != '0' && zaikonum != '' ) {
						checknum = uscesL10n.itemRestriction;
						checkmode ='rest';
					} else if( parseInt(itemRestriction) > parseInt(zaikonum) && itemRestriction != '' && itemRestriction != '0' && zaikonum != '' ) {
						checknum = zaikonum;
						checkmode ='zaiko';
					} else if( (itemRestriction == '' || itemRestriction == '0') && zaikonum != '' ) {
						checknum = zaikonum;
						checkmode ='zaiko';
					} else if( itemRestriction != '' && itemRestriction != '0' && zaikonum == '' ) {
						checknum = itemRestriction;
						checkmode ='rest';
					}
					if( parseInt(quant) > parseInt(checknum) && checknum != '' ){
						if(checkmode == 'rest'){
							mes += (i+1) + "番の商品は一度に"+checknum+"までの数量制限が有ります。\n";
						}else{
							mes += (i+1) + "番の商品の在庫は残り"+checknum+"です。\n";
						}
					}
				}
			}

			if( mes != '' ){
				alert( mes );
				return false;
			}else{
				return true;
			}
		},
		
		previousCart : function () {
			location.href = uscesL10n.previous_url; 
		},
		
		isNum : function (num) {
			if (num.match(/[^0-9]/g)) {
				return false;
			}
			return true;
		}
	};
})(jQuery);

function addEvent(obj, evType, fn){ 
 if (obj.addEventListener){ 
   obj.addEventListener(evType, fn, false); 
   return true; 
 } else if (obj.attachEvent){ 
   var r = obj.attachEvent("on"+evType, fn); 
   return r; 
 } else { 
   return false; 
 } 
}
