<?php
class Script {
	public static function genLookupSelect() {
		$mesg = Yii::t('dialog','No Record Found');
		$str = <<<EOF
$('#btnLookupSelect').on('click',function() {
	$('#lookupdialog').modal('hide');
	lookupselect();
});
		
$('#btnLookupCancel').on('click',function() {
	$('#lookupdialog').modal('hide');
	lookupclear();
});

function lookupselect() {
	var codeval = "";
	var valueval = "";
	$("#lstlookup option:selected").each(function(i, selected) {
		codeval = ((codeval=="") ? codeval : codeval+"~") + $(selected).val();
		valueval = ((valueval=="") ? valueval : valueval+" ") + $(selected).text();
	});
	var ofstr = $('#lookupotherfield').val();

	if (codeval && valueval!='$mesg') {
		var codefield = $('#lookupcodefield').val();
		var valuefield = $('#lookupvaluefield').val();
		if (codefield!='') $('#'+codefield).val(codeval);
		$('#'+valuefield).val(valueval);
		
		var others = (ofstr!='') ? ofstr.split("/") : new Array();
		if (others.length > 0) {
			$.each(others, function(idx, item) {
				var field = item.split(",");
				if (field.length > 0) {
					var fldId = 'otherfld_'+codeval+'_'+field[0];
					var fldVal = $('#'+fldId).val();
					$('#'+field[1]).val(fldVal);
				}
			});
		}
	}
	
	lookupclear();
}

function lookupclear() {
//	$('#lookuptype').val('');
	$('#lookupcodefield').val('');
	$('#lookupvaluefield').val('');
	$("#txtlookup").val('');
	$("#lstlookup").empty();
	$('#fieldvalue').empty();
	$("#lstlookup").removeAttr('multiple');
	$("#lookup-label").removeAttr('style');
}
EOF;
		return $str;
	}
 
	public static function genLookupButton($btnName, $lookupType, $codeField, $valueField, $multiselect=false) {
		$multiflag = $multiselect ? 'true' : 'false';
		$str = <<<EOF
$('#$btnName').on('click',function() {
	var code = $("input[id*='$codeField']").attr("id");
	var value = $("input[id*='$valueField']").attr("id");
	var title = $("label[for='"+value+"']").text();
	$('#lookuptype').val('$lookupType');
	$('#lookupcodefield').val(code);
	$('#lookupvaluefield').val(value);
	if ($multiflag) $('#lstlookup').attr('multiple','multiple');
	if (!($multiflag)) $('#lookup-label').attr('style','display: none');
	$('#lookupdialog').find('.modal-title').text(title);
//	$('#lookupdialog').dialog('option','title',title);
	$('#lookupdialog').modal('show');
});
EOF;
		return $str;
	}
 
	public static function genLookupButtonEx($btnName, $lookupType, $codeField, $valueField, $otherFields=array(), $multiselect=false, $paramFields=array()) {
		$others = '';
		if (!empty($otherFields)) {
			foreach ($otherFields as $key=>$field) {
				$others .= ($others=='' ? '' : '/').$key.','.$field;
			}
		}
		$params = '';
		if (!empty($paramFields)) {
			foreach ($paramFields as $key=>$field) {
				$params .= ($params=='' ? '' : '/').$key.','.$field;
			}
		}
		$multiflag = $multiselect ? 'true' : 'false';
		$lookuptypeStmt = ($lookupType!=='*') ? "$('#lookuptype').val('$lookupType');" : '';
		
		$str = <<<EOF
$('#$btnName').on('click',function() {
	var code = $("input[id*='$codeField']").attr("id");
	var value = $("input[id*='$valueField']").attr("id");
	var title = $("label[for='"+value+"']").text();
	$lookuptypeStmt
	$('#lookupcodefield').val(code);
	$('#lookupvaluefield').val(value);
	$('#lookupotherfield').val('$others');
	$('#lookupparamfield').val('$params');
	if ($multiflag) $('#lstlookup').attr('multiple','multiple');
	if (!($multiflag)) $('#lookup-label').attr('style','display: none');
//	$('#lookupdialog').dialog('option','title',title);
	$('#lookupdialog').find('.modal-title').text(title);
	$('#lookupdialog').modal('show');
});
EOF;
		return $str;
	}

	public static function genLookupSearch() {
		$mesg = Yii::t('dialog','No Record Found');
		$link = Yii::app()->createAbsoluteUrl("lookup");
		$str = <<<EOF
$('#btnLookup').on('click',function(){
	var city = $("[id$='_city']").val();
	var data = "search="+$("#txtlookup").val();
	if (city !== undefined && city !==null) data += "&incity="+city;
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
	
	public static function genLookupSearchEx() {
		$mesg = Yii::t('dialog','No Record Found');
		$link = Yii::app()->createAbsoluteUrl("lookup");
		$str = <<<EOF
$('#btnLookup').on('click',function(){
	var data = "search="+$("#txtlookup").val();
	
	var pstr = $('#lookupparamfield').val();
	var params = (pstr!='') ? pstr.split("/") : new Array();
	if (params.length > 0) {
		$.each(params, function(idx, item) {
			var field = item.split(",");
			if (field.length > 0) {
				var fldid = '#'+field[1];
				var fldval = $(fldid).val();
				if (fldval !== undefined && fldval !==null) data += "&"+field[0]+"="+fldval;
			}
		});
	}
	
	var city = $("[id$='_city']").val();
	if (city !== undefined && city !==null) data += "&incity="+city;
	
	var link = "$link"+"/"+$("#lookuptype").val()+'ex';
	var ofstr = $('#lookupotherfield').val();
	$.ajax({
		type: 'GET',
		url: link,
		data: data,
		dataType: 'json',
		success: function(data) {
			$('#fieldvalue').empty();
			$("#lstlookup").empty();

			var others = (ofstr!='') ? ofstr.split("/") : new Array();
			
			$.each(data, function(index, element) {
				$("#lstlookup").append("<option value='"+element.id+"'>"+element.value+"</option>");
				if (others.length > 0) {
					$.each(others, function(idx, item) {
						var field = item.split(",");
						if (field.length > 0) {
							var hidden = $('<input/>',{type:'hidden',id:'otherfld_'+element.id+'_'+field[0], value:element[field[0]]});
							hidden.appendTo('#fieldvalue');
						}
					});
				}
			});
			
			var count = $("#lstlookup").children().length;
			if (count<=0) $("#lstlookup").append("<option value='-1'>$mesg</option>");
		},
		error: function(data) { // if error occured
			alert("Error occured.please try again");
		}
	});
});
EOF;
		return $str;
	}

	public static function genReadonlyField() {
		$str = <<<EOF
$('[readonly]').addClass('readonly');
EOF;
		return $str;
	}

	public static function genTableRowClick() {
		$str = <<<EOF
$('.clickable-row').click(function() {
	window.document.location = $(this).data('href');
});
EOF;
		return $str;
	}
	
	public static function genDatePicker($fields) {
		$str = "";
		foreach ($fields as $field) {
			$str .= "$('#$field').datepicker({autoclose: true, format: 'yyyy/mm/dd'});";
		}
		return $str;
	}

	public static function genDeleteData($link) {
		$str = "
$('#btnDeleteData').on('click',function() {
	$('#removedialog').modal('hide');
	deletedata();
});

function deletedata() {
	var elm=$('#btnDelete');
	jQuery.yii.submitForm(elm,'$link',{});
}
		";
		return $str;
	}
	
	public static function genFileDownload($model, $formname, $doctype) {
		$doc = new DocMan($doctype,0,get_class($model));
		$ctrlname = Yii::app()->controller->id;
		$dwlink = Yii::app()->createAbsoluteUrl($ctrlname."/filedownload");
		$dlfuncid = $doc->downloadFunctionName;
		$str = "
function $dlfuncid(mid, did, fid) {
	href = '$dwlink?mastId='+mid+'&docId='+did+'&fileId='+fid+'&doctype=$doctype';
	window.open(href);
}
		";
		Yii::app()->clientScript->registerScript('downloadfile1'.$doctype,$str,CClientScript::POS_HEAD);
	}
	
	public static function genFileUpload($model, $formname, $doctype) {
		$doc = new DocMan($doctype,$model->id,get_class($model));

		$msg = Yii::t('dialog','Are you sure to delete record?');
		$ctrlname = Yii::app()->controller->id;
		$rmlink = Yii::app()->createAbsoluteUrl($ctrlname."/fileremove",array('doctype'=>$doctype));
		$dwlink = Yii::app()->createAbsoluteUrl($ctrlname."/filedownload");
		$rmfldid = get_class($model).'_removeFileId_'.strtolower($doctype);
		$tblid = $doc->tableName;
		$rmfuncid = $doc->removeFunctionName;
		$dlfuncid = $doc->downloadFunctionName;
		$btnid = $doc->uploadButtonName;
		$typeid = strtolower($doctype);
		$modelname = get_class($model);
		
		$str = "
function $rmfuncid(id) {
	if (confirm('$msg')) {
		document.getElementById('$rmfldid').value = id;
		var form = document.getElementById('$formname');
		var formdata = new FormData(form);
		$.ajax({
			type: 'POST',
			url: '$rmlink',
			data: formdata,
			mimeType: 'multipart/form-data',
			contentType: false,
			processData: false,
			success: function(data) {
				if (data!='NIL') {
					$('#$tblid').find('tbody').empty().append(data);
					attmno = '$modelname'+'_no_of_attm_'+'$typeid';
					counter = $('#'+attmno).val();
					var d = $('#doc$typeid');
					if (counter==undefined || counter==0) {
						d.removeClass();
						d.html('');
					} else {
						d.removeClass().addClass('label').addClass('label-info');
						d.html(counter);
					}
				}
			},
			error: function(data) { // if error occured
				alert('Error occured.please try again');
			}
		});	
	}
}

function $dlfuncid(mid, did, fid) {
	href = '$dwlink?mastId='+mid+'&docId='+did+'&fileId='+fid+'&doctype=$doctype';
	window.open(href);
}
		";
		Yii::app()->clientScript->registerScript('removefile1'.$doctype,$str,CClientScript::POS_HEAD);

		$link = Yii::app()->createAbsoluteUrl($ctrlname."/fileupload",array('doctype'=>$doctype));
		$str = "
$('#$btnid').on('click', function() {
	var form = document.getElementById('$formname');
	var formdata = new FormData(form);
	$.ajax({
		type: 'POST',
		url: '$link',
		data: formdata,
		mimeType: 'multipart/form-data',
		contentType: false,
		processData: false,
		success: function(data) {
			if (data!='NIL') {
				$('#$tblid').find('tbody').empty().append(data);
				$('input:file').MultiFile('reset')
				attmno = '$modelname'+'_no_of_attm_'+'$typeid';
				counter = $('#'+attmno).val();
				var d = $('#doc$typeid');
				if (counter==undefined || counter==0) {
					d.removeClass();
					d.html('');
				} else {
					d.removeClass().addClass('label').addClass('label-info');
					d.html(counter);
				}
			}
		},
		error: function(data) { // if error occured
			alert('Error occured.please try again');
		}
	});
});
		";
		Yii::app()->clientScript->registerScript('fileUpload'.$doctype,$str,CClientScript::POS_READY);
	}
}
?>