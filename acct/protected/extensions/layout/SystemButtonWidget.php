<?php
class SystemButtonWidget extends CWidget {
	public function run() {
// System Change Button
		$js = <<<EOF
$(function () {
  $('[data-toggle=\"tooltip\"]').tooltip()
});

$('#btnSysChange').on('click',function() {
	$('#syschangedialog').modal('show');
});
EOF;
		$incl_js = false;
		foreach (General::systemMapping() as $id=>$value) {
			if (Yii::app()->user->validSystem($id)) {
				$oid = 'btnSys'.$id;
				$url = $value['webroot'];
				if (!isset($value['script'])) {
					$temp = '$("#'.$oid.'").on("click",function(){$("#syschangedialog").modal("hide");window.location="'.$url.'";});';
				} else {
					$func_name = $value['script'];
					$lang = Yii::app()->language;
					$homeurl = Yii::app()->createUrl("");
					$incl_js = true;
					$temp = '$("#'.$oid.'").on("click",function(){$("#syschangedialog").modal("hide");'.$func_name.'("'.$id.'","'.$url.'","'.$homeurl.'");});';
				}
				$js .= $temp;
			}
		}
	
		if ($incl_js) {
			$sfile = Yii::app()->baseUrl.'/js/systemlink.js';
			Yii::app()->clientScript->registerScriptFile($sfile,CClientScript::POS_HEAD);
		}
		Yii::app()->clientScript->registerScript('systemchange',$js,CClientScript::POS_READY);
// End - System Chnage Button
	}
}
