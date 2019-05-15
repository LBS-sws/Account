<tr>
	<td>
		<?php 
			echo TbHtml::dropDownList($this->getFieldName('adjtype'),  $this->record['adjtype'], 
								array(''=>Yii::t('misc','-- None --'),'L1'=>Yii::t('trans','LBS Rec. T3 Not'),'L2'=>Yii::t('trans','LBS Paid T3 Not')),
								array('disabled'=>$this->model->isReadOnly())
			); 
			if ($this->model->isReadOnly())
				echo TbHtml::hiddenField($this->getFieldName('adjtype'),$this->record['adjtype']);
		?>
	</td>
	<td>
		<?php echo TbHtml::textField($this->getFieldName('remarks'), $this->record['remarks'], 
			array('readonly'=>$this->model->isReadOnly(), 
				'size'=>'100', 'maxlength'=>'500',)
		); ?>
	</td>
	<td>
		<?php echo TbHtml::textField($this->getFieldName('amount'), $this->record['amount'], 
			array('readonly'=>$this->model->isReadOnly(), 
				'size'=>'10', 'maxlength'=>'10',)
		); ?>
	</td>
	<td>
		<?php 
			echo !$this->model->isReadOnly()
				? TbHtml::Button('-',array('id'=>'btnDelRow','title'=>Yii::t('misc','Delete'),'size'=>TbHtml::BUTTON_SIZE_SMALL))
				: '&nbsp;';
		?>
		<?php echo CHtml::hiddenField($this->getFieldName('uflag'),$this->record['uflag']); ?>
		<?php echo CHtml::hiddenField($this->getFieldName('id'),$this->record['id']); ?>
	</td>
</tr>
