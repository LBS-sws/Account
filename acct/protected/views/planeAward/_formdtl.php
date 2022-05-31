<tr class="tr_show">
    <td>
        <?php echo TbHtml::dropDownList($this->getFieldName('other_id'),  $this->record['other_id'],PlaneSetOtherForm::getPlaneOtherList($this->record['other_id']),
            array('disabled'=>$this->model->isReadOnly(),'class'=>'other_id')
        ); ?>
    </td>
    <td>
        <?php echo TbHtml::numberField($this->getFieldName('other_num'),  $this->record['other_num'],
            array('disabled'=>$this->model->isReadOnly(),'class'=>'other_num')
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
		<?php echo CHtml::hiddenField($this->getFieldName('plane_id'),$this->record['plane_id']); ?>
	</td>
</tr>
