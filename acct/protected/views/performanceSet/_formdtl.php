<tr>
    <td>
        <?php echo TbHtml::dropDownList($this->getFieldName('name'),  $this->record['name'],$this->model->getPerformanceList() ,
            array('disabled'=>$this->model->isReadOnly())
        ); ?>
    </td>
	<td>
		<?php echo TbHtml::dropDownList($this->getFieldName('operator'),  $this->record['operator'], array('LE'=>'<','GT'=>'>='),
								array('disabled'=>$this->model->isReadOnly())
		); ?>
	</td>
	<td>
		<?php  
			echo TbHtml::numberField($this->getFieldName('new_amount'), $this->record['new_amount'],
							array('size'=>10,'min'=>0,
							'readonly'=>($this->model->isReadOnly()),
							)
						);
		?>
	</td>
	<td>
		<?php  
			echo TbHtml::numberField($this->getFieldName('bonus_amount'), $this->record['bonus_amount'],
							array('size'=>10,'min'=>0,
							'readonly'=>($this->model->isReadOnly()),
							)
						);
		?>
	</td>
<!--	<td>-->
<!--		--><?php //
//			echo TbHtml::numberField($this->getFieldName('inv_rate'), $this->record['inv_rate'],
//							array('size'=>5,'min'=>0,
//							'readonly'=>($this->model->isReadOnly()),
//							)
//						);
//		?>
<!--	</td>-->
	<td>
		<?php 
			echo !$this->model->isReadOnly() 
				? TbHtml::Button('-',array('id'=>'btnDelRow','title'=>Yii::t('misc','Delete'),'size'=>TbHtml::BUTTON_SIZE_SMALL))
				: '&nbsp;';
		?>
		<?php echo CHtml::hiddenField($this->getFieldName('uflag'),$this->record['uflag']); ?>
		<?php echo CHtml::hiddenField($this->getFieldName('id'),$this->record['id']); ?>
		<?php echo CHtml::hiddenField($this->getFieldName('hdr_id'),$this->record['hdr_id']); ?>
	</td>
</tr>
