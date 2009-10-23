// JavaScript Document
(function($) {
	uscesCart = {
		intoCart : function (post_id, sku) {
			
			var zaikonum = document.getElementById("zaikonum["+post_id+"]["+sku+"]").value;
			var zaiko = document.getElementById("zaiko["+post_id+"]["+sku+"]").value;
			if( zaiko != '0' && zaiko != '1' ){
				alert( uscesL10n.mes_zaiko );
				return false;
			}
			
			var mes = '';
			if(document.getElementById("quant["+post_id+"]["+sku+"]")){
				var quant = document.getElementById("quant["+post_id+"]["+sku+"]").value;
				if( quant == '0' || quant == '' || !(uscesCart.isNum(quant))){
					mes += uscesL10n.mes_quant+"\n";
				}
				var checknum = '';
				if( parseInt(uscesL10n.itemRestriction) <= parseInt(zaikonum) && uscesL10n.itemRestriction != '' && uscesL10n.itemRestriction != '0' && zaikonum != '' ) {
					checknum = uscesL10n.itemRestriction;
				} else if( parseInt(uscesL10n.itemRestriction) > parseInt(zaikonum) && uscesL10n.itemRestriction != '' && uscesL10n.itemRestriction != '0' && zaikonum != '' ) {
					checknum = zaikonum;
				} else if( (uscesL10n.itemRestriction == '' || uscesL10n.itemRestriction == '0') && zaikonum != '' ) {
					checknum = zaikonum;
				} else if( uscesL10n.itemRestriction != '' && uscesL10n.itemRestriction != '0' && zaikonum == '' ) {
					checknum = uscesL10n.itemRestriction;
				}
				if( parseInt(quant) > parseInt(checknum) && checknum != '' ){
					mes += uscesL10n.mes_quantover(checknum)+"\n";
				}
			}
			for(i=0; i<uscesL10n.key_opts.length; i++){
				if( document.getElementById("itemOption["+post_id+"]["+sku+"]["+uscesL10n.key_opts[i]+"]") ){
					if( document.getElementById("itemOption["+post_id+"]["+sku+"]["+uscesL10n.key_opts[i]+"]").value == '#NONE#' ){
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
//					alert( uscesL10n.mes_zaiko );
//					return false;
//				}
		
				quant = $("input[name='quant\[" + i + "\]\[" + post_id + "\]\[" + sku + "\]']").val();
				if( $("input[name='quant\[" + i + "\]\[" + post_id + "\]\[" + sku + "\]']") ){
					if( quant == '' || !(uscesCart.isNum(quant))){
						mes += (i+1) + "番の商品の" + uscesL10n.mes_quant + "\n";
					}
					checknum = '';
					if( parseInt(itemRestriction) <= parseInt(zaikonum) && itemRestriction != '' && itemRestriction != '0' && zaikonum != '' ) {
						checknum = uscesL10n.itemRestriction;
					} else if( parseInt(itemRestriction) > parseInt(zaikonum) && itemRestriction != '' && itemRestriction != '0' && zaikonum != '' ) {
						checknum = zaikonum;
					} else if( (itemRestriction == '' || itemRestriction == '0') && zaikonum != '' ) {
						checknum = zaikonum;
					} else if( itemRestriction != '' && itemRestriction != '0' && zaikonum == '' ) {
						checknum = itemRestriction;
					}
					if( parseInt(quant) > parseInt(checknum) && checknum != '' ){
						mes += (i+1) + "番の商品の" + uscesL10n.mes_quantover2(checknum)+"\n";
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
