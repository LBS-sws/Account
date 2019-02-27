<?php
	$content = "";
	foreach (General::systemMapping() as $id=>$value) {
		if (Yii::app()->user->validSystem($id)) {
			$button = TbHtml::button('<span class="'.$value['icon'].'"></span> '.Yii::t('app',$value['name']), 
				array(
					'name'=>'btnSys'.$id,
					'id'=>'btnSys'.$id,
					'class'=>'btn btn-block',
				));

			$content .= "<div class=\"row\">$button</div>";
		}
	}
	$this->widget('bootstrap.widgets.TbModal', array(
					'id'=>'syschangedialog',
					'header'=>Yii::t('app','System Change'),
					'content'=>$content,
					'footer'=>array(
						TbHtml::button(Yii::t('dialog','Cancel'), array('data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY)),
					),
					'show'=>false,
				));
?>