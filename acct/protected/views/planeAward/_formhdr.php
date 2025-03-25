<tr>
    <th>
        <?php echo TbHtml::label($this->getLabelName('value_name'), false); ?>
    </th>
	<th>
		<?php echo TbHtml::label($this->getLabelName('value_money'), false); ?>
	</th>

	<th width="20%">
		<?php
        if(!$this->model->isReadOnly()){
            echo TbHtml::Button('+',array('class'=>'btnAddRow','data-id'=>1,'data-title'=>Yii::t('misc','Add'),'size'=>TbHtml::BUTTON_SIZE_SMALL));
            echo TbHtml::Button('批量增加',array('class'=>'btnQuickRow','data-id'=>1,'data-title'=>"杂项",'size'=>TbHtml::BUTTON_SIZE_SMALL));
        }else{
            echo  '&nbsp;';
        }
        ?>
	</th>
</tr>
