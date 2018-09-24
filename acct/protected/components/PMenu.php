<?php
class Script {
	public function genLookupSelect() {
		$mesg = Yii::t('dialog','No Record Found');
		$str = <<<EOF
function lookupselect() {
	var codeval = $("#lstlookup option:selected").val();
	var valueval = $("#lstlookup option:selected").text();

	if (codeval && valueval!='$mesg') {
		var codefield = $('#lookupcodefield').val();
		var valuefield = $('#lookupvaluefield').val();
		$('#'+codefield).val(codeval);
		$('#'+valuefield).val(valueval);
	}
	
	lookupclear();
}

function lookupclear() {
	$('#lookuptype').val('');
	$('#lookupcodefield').val('');
	$('#lookupvaluefield').val('');
	$("#txtlookup").val('');
	$("#lstlookup").empty();
}
EOF;
		return $str;
	}
 
	public function genLookupButton($btnName, $lookupType, $codeField, $valueField) {
		$str = <<<EOF
$('#$btnName').on('click',function() {
	var code = $("input[id*='$codeField']").attr("id");
	var value = $("input[id*='$valueField']").attr("id");
	var title = $("label[for='"+value+"']").text();
	$('#lookuptype').val('$lookupType');
	$('#lookupcodefield').val(code);
	$('#lookupvaluefield').val(value);
	$('#lookupdialog').dialog('option','title',title);
	$('#lookupdialog').dialog('open');
});
EOF;
		return $str;
	}
 
	public function genLookupSearch() {
		$mesg = Yii::t('dialog','No Record Found');
		$link = Yii::app()->createAbsoluteUrl("lookup");
		$str = <<<EOF
$('#btnLookup').on('click',function(){
	var data = "search="+$("#txtlookup").val();
	var link = "$link"+"/"+$("#lookuptype").val();
	$.ajax({
		type: 'GET',
		url: link,
		data: data,
		success: function(data) {
			jQuery("#lookup-list").html(data);
			var count = $("#lstlookup").children().length;
			if (count<=0) $("#lstlookup").append("<option value='-1'>$mesg</option>");
		},
		error: function(data) { // if error occured
			alert("Error occured.please try again");
		},
		dataType:'html'
	});
});
EOF;
		return $str;
	}
	
	public function genReadonlyField() {
		$str = <<<EOF
$('[readonly]').addClass('readonly');
EOF;
		return $str;
	}
}
?>