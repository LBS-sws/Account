<tr class="tr_show">
    <td>
        <?php echo TbHtml::dropDownList($this->getFieldName('set_id'),  $this->record['set_id'],ConsultSetForm::getConsultSetList($this->record['set_id']),
            array('disabled'=>$this->model->isReady(),'class'=>'set_id')
        ); ?>
    </td>
    <td>
        <?php echo TbHtml::numberField($this->getFieldName('good_money'),  $this->record['good_money'],
            array('disabled'=>$this->model->isReady(),'class'=>'good_money')
        ); ?>
    </td>
	<td>
		<?php 
			echo !$this->model->isReady()
				? TbHtml::Button('-',array('id'=>'btnDelRow','title'=>Yii::t('misc','Delete'),'size'=>TbHtml::BUTTON_SIZE_SMALL))
				: '&nbsp;';
		?>
		<?php echo CHtml::hiddenField($this->getFieldName('uflag'),$this->record['uflag']); ?>
		<?php echo CHtml::hiddenField($this->getFieldName('id'),$this->record['id']); ?>
		<?php echo CHtml::hiddenField($this->getFieldName('consult_id'),$this->record['consult_id']); ?>
	</td>
</tr>
