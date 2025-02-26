<?php
//if($flashes = Yii::app()->user->getFlashes()) {
//    foreach($flashes as $key => $message) {
//        if($key != 'counters') {
//			if ($message['confirm']=='N') {
//				$content = '<p>'.$message['content'].'</p>';
//				$this->widget('bootstrap.widgets.TbModal', array(
//						'id'=>$key,
//						'header'=>Yii::t('dialog',$message['title']),
//						'content'=>$content,
//                        'footer'=>array(
//							TbHtml::button(Yii::t('dialog','OK'), array('data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY)),
//                            ),
//						'show'=>true,
//                        ));
//			}
//        }
//    }
//}

if($flashes = Yii::app()->user->getFlashes()) {
    foreach($flashes as $key => $message) {
        $content = '<p>'.$message['content'].'</p>';
        if($key != 'counters' && $message['confirm']=='N'){
            $footer = array(
                TbHtml::button(Yii::t('dialog','OK'), array('data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY)),
            );
        }
        $urlToRedirect = Yii::app()->createUrl('site/resetloginpassword');
        if($key == 1001) {
            $urlToRedirect .= '?username='.$message['title'];
            $footer = array(TbHtml::button(Yii::t('dialog', 'OK'), array('data-dismiss' => 'modal','color' => TbHtml::BUTTON_COLOR_PRIMARY,'onclick' => "window.location.href='{$urlToRedirect}'; return false;",)),);
        }
        if($key != 'counters' || $key == 1001) {
            $this->widget('bootstrap.widgets.TbModal', array(
                'id' => $key,
                'header' => Yii::t('dialog', $message['title']),
                'content' => $content,
                'footer' => $footer,
                'show' => true,
            ));
        }

    }
}


?>