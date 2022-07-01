<tr>
	<th width="20%">
		<?php echo TbHtml::label($this->getLabelName('date'), false); ?>
	</th>
	<th width="25%">
		<?php echo TbHtml::label($this->getLabelName('customer'), false); ?>
	</th>
    <th width="10%">
        <?php echo TbHtml::label($this->getLabelName('type'), false); ?>
    </th>
	<th width="30%">
		<?php echo TbHtml::label($this->getLabelName('information'), false); ?>
	</th>
	<th width="10%">
		<?php echo TbHtml::label($this->getLabelName('commission'), false); ?>
	</th>
	<th width="1%">
		<?php
        if($this->model->getReadonly()){
            echo TbHtml::Button('+',array('id'=>'btnAddRow','title'=>Yii::t('misc','Add'),'size'=>TbHtml::BUTTON_SIZE_SMALL))
            ;
        }
		?>
	</th>
</tr>
