<?php
if($flashes = Yii::app()->user->getFlashes()) {
    foreach($flashes as $key => $message) {
        if($key != 'counters') {
			if ($message['confirm']=='N') {
				$content = '<p>'.$message['content'].'</p>';
				$this->widget('bootstrap.widgets.TbModal', array(
						'id'=>$key,
						'header'=>Yii::t('dialog',$message['title']),
						'content'=>$content,
                        'footer'=>array(
							TbHtml::button(Yii::t('dialog','OK'), array('data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY)),
                            ),
						'show'=>true,
                        ));
			}
        }
    }
}
?>