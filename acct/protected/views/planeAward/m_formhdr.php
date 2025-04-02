<tr>
    <th width="58%">
        <?php echo TbHtml::label($this->getLabelName('money_txt'), false); ?>
    </th>
	<th width="25%">
		<?php echo TbHtml::label($this->getLabelName('money_amt'), false); ?>
	</th>

	<th width="17%">
        <?php
        if(!$this->model->isReadOnly()){
            echo TbHtml::Button('+',array('class'=>'btnAddRow','data-id'=>3,'data-title'=>Yii::t('misc','Add'),'size'=>TbHtml::BUTTON_SIZE_SMALL));
            echo TbHtml::Button('批量增加',array('class'=>'btnQuickRow','data-id'=>3,'data-title'=>"做单",'size'=>TbHtml::BUTTON_SIZE_SMALL));
        }else{
            echo  '&nbsp;';
        }
        ?>
	</th>
</tr>
