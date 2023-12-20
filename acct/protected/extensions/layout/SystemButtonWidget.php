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
                }elseif($id=='nu'){
                    $url = $value['webroot'].$value['param'];
                    $incl_js = true;
                    $homeurl = $value['webroot'];
                    //构建数据
                    $data = array(
                        'user_id'=>Yii::app()->user->id,
                        'system'=>Yii::app()->session['system'],
                        'city'=>Yii::app()->session['city'],
                        'disp_name'=>Yii::app()->session['disp_name'],
                        'logon_time'=>Yii::app()->session['logon_time'],
                    );
                    // 加密数组
                    $jsonString = json_encode($data,true);
                    $String = base64_encode($jsonString);
                    $key = str_replace('=','',base64_encode('fda25643gg654365dfafdsa'));
                    $jsonString = ($key.$String);
                    $encryptedString = base64_encode($jsonString);
                    //拼接
                    $temp = '$("#'.$oid.'").on("click",function(){$("#syschangedialog").modal("hide");'.$value['script'].'("'.$id.'","'.$url.'","'.$homeurl.'","'.$encryptedString.'","'.$data['user_id'].'");});';
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
