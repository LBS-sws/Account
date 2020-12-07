<tr>
	<th style="width: 180px;">
		<?php echo TbHtml::label($this->getLabelName('date'), false); ?>
	</th>
	<th style="width: 220px;">
		<?php echo TbHtml::label($this->getLabelName('customer'), false); ?>
	</th>
    <th style="width: 100px;">
        <?php echo TbHtml::label($this->getLabelName('type'), false); ?>
    </th>
	<th>
		<?php echo TbHtml::label($this->getLabelName('information'), false); ?>
	</th>
	<th style="width: 120px;">
		<?php echo TbHtml::label($this->getLabelName('commission'), false); ?>
	</th>
	<th>
		<?php echo Yii::app()->user->validRWFunction('E01') ?
				TbHtml::Button('+',array('id'=>'btnAddRow','title'=>Yii::t('misc','Add'),'size'=>TbHtml::BUTTON_SIZE_SMALL))
				: '&nbsp;';
		?>
	</th>
</tr>
