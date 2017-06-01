<?php
class Dialog {
    public static function message($title, $message, $id = 0) {
        if($id == 0)
            $id = rand(1, 999999);
        Yii::app()->user->setflash($id, array('title' => $title, 'content' => $message, 'confirm'=>'N') );
    }
	
    public static function confirm($title, $message, $buttons, $id = 0) {
        if($id == 0)
            $id = rand(1, 999999);
        Yii::app()->user->setflash($id, array('title' => $title, 'content' => $message, 'confirm'=>'Y', 'buttons'=>$buttons) );
    }

	public static function savedialog($link, $url) {
		$js = "
			$('#".$link."').on('click',function() {
				$('#savedialog').dialog('open');
			});

			function savedata() {
				var elm=$('#".$link."');
				jQuery.yii.submitForm(elm,'".Yii::app()->createUrl($url)."',{});
			}
		";
		Yii::app()->clientScript->registerScript('saveButton',$js,CClientScript::POS_READY);
	}

	public static function dialogConfirm($link, $url) {
		$js = "
			$('body').on('click','#".$link."',function() {
				$('#savedialog').dialog('open');
			});

			function savedata() {
				var elm=$('#".$link."');
				jQuery.yii.submitForm(elm,'".Yii::app()->createUrl($url)."',{});
			}
		";
	}
}
?>