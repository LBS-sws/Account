<tr>
	<td>
		<?php echo TbHtml::dropDownList($this->getFieldName('audit_user'),  $this->record['audit_user'],ExpenseSetAuditForm::getAppointAuditUserList($this->record['audit_user']),
								array('disabled'=>$this->model->scenario=='view','empty'=>'')
		); ?>
	</td>
	<td>
		<?php echo TbHtml::dropDownList($this->getFieldName('audit_tag'),  $this->record['audit_tag'],ExpenseSetAuditForm::getAppointAuditTagList(),
								array('disabled'=>$this->model->scenario=='view','empty'=>'')
		); ?>
	</td>
	<td>
		<?php echo TbHtml::dropDownList($this->getFieldName('amt_bool'),  $this->record['amt_bool'],ExpenseSetAuditForm::getAmtBoolList(),
								array('disabled'=>$this->model->scenario=='view','class'=>"amt_bool")
		); ?>
	</td>
	<td>
        <?php
        $amt_readonly = empty($this->record['amt_bool'])?true:false;
        ?>
		<?php echo TbHtml::numberField($this->getFieldName('amt_min'),  $this->record['amt_min'],
								array('disabled'=>$this->model->scenario=='view','readonly'=>$amt_readonly,'class'=>"amt_min")
		); ?>
	</td>
	<td>
		<?php echo TbHtml::numberField($this->getFieldName('amt_max'),  $this->record['amt_max'],
								array('disabled'=>$this->model->scenario=='view','readonly'=>$amt_readonly,'class'=>"amt_max")
		); ?>
	</td>
	<td>
		<?php echo TbHtml::numberField($this->getFieldName('z_index'),  $this->record['z_index'],
								array('disabled'=>$this->model->scenario=='view')
		); ?>
	</td>
	<td>
		<?php 
			echo Yii::app()->user->validRWFunction('DE06')
				? TbHtml::Button('-',array('id'=>'btnDelRow','title'=>Yii::t('misc','Delete'),'size'=>TbHtml::BUTTON_SIZE_SMALL))
				: '&nbsp;';
		?>
		<?php echo CHtml::hiddenField($this->getFieldName('uflag'),$this->record['uflag']); ?>
		<?php echo CHtml::hiddenField($this->getFieldName('id'),$this->record['id']); ?>
		<?php echo CHtml::hiddenField($this->getFieldName('set_id'),$this->record['set_id']); ?>
	</td>
</tr>
