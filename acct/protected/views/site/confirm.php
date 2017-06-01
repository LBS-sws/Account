<?php
if($flashes = Yii::app()->user->getFlashes()) {
    foreach($flashes as $key => $message) {
        if($key != 'counters') {
			if ($message['confirm']=='Y') {
				$content = '<p>'.$message['content'].'</p>';
				$this->widget('bootstrap.widgets.TbModal', array(
						'id'=>$key,
						'header'=>Yii::t('dialog',$message['title']),
						'content'=>$content,
                        'footer'=>$message['buttons'],
						'show'=>true,
                        ));
			}
        }
    }
}
?>