<tr>
    <th width="58%">
        <?php echo TbHtml::label($this->getLabelName('take_txt'), false); ?>
    </th>
	<th width="25%">
		<?php echo TbHtml::label($this->getLabelName('take_money'), false); ?>
	</th>

	<th width="17%">
        <?php
        if(!$this->model->isReadOnly()){
            echo TbHtml::Button('+',array('class'=>'btnAddRow','data-id'=>2,'data-title'=>Yii::t('misc','Add'),'size'=>TbHtml::BUTTON_SIZE_SMALL));
            echo TbHtml::Button('批量增加',array('class'=>'btnQuickRow','data-id'=>2,'data-title'=>"提成",'size'=>TbHtml::BUTTON_SIZE_SMALL));
        }else{
            echo  '&nbsp;';
        }
        ?>
	</th>
</tr>
