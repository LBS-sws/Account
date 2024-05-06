<tr>
    <th width="30%">
        <?php echo TbHtml::label($this->getLabelName('audit_user'), false); ?>
    </th>
	<th width="14%">
		<?php echo TbHtml::label($this->getLabelName('audit_tag'), false); ?>
	</th>
	<th width="14%">
		<?php echo TbHtml::label($this->getLabelName('amt_bool'), false); ?>
	</th>
	<th width="14%">
		<?php echo TbHtml::label($this->getLabelName('amt_min'), false); ?>
	</th>
	<th width="14%">
		<?php echo TbHtml::label($this->getLabelName('amt_max'), false); ?>
	</th>
	<th width="14%">
		<?php echo TbHtml::label($this->getLabelName('z_index'), false); ?>
	</th>
	<th>
		<?php echo Yii::app()->user->validRWFunction('DE06') ?
				TbHtml::Button('+',array('id'=>'btnAddRow','title'=>Yii::t('misc','Add'),'size'=>TbHtml::BUTTON_SIZE_SMALL))
				: '&nbsp;';
		?>
	</th>
</tr>
