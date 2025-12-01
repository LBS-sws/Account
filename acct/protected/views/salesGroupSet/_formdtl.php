<tr class="tr_show">
    <td>
        <?php
        echo TbHtml::textField($this->getFieldName('employeeName'), $this->record['employeeName'],
            array('readonly'=>true,'class'=>'employeeName',
                'append'=>TbHtml::button('<span class="fa fa-search"></span> '.Yii::t('group','staff'),array('class'=>'searchUser','disabled'=>$this->model->isReadOnly())),
            ));
        ?>
        <?php echo TbHtml::hiddenField($this->getFieldName('employeeID'), $this->record['employeeID'],array("class"=>"employeeID")); ?>
    </td>
    <td>
        <?php echo TbHtml::dropDownList($this->getFieldName('employeeType'),  $this->record['employeeType'],
            array(1=>"自动获取",2=>"新入职",3=>"老员工"),
            array('disabled'=>$this->model->isReadOnly(),'class'=>'employeeType')
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
		<?php echo CHtml::hiddenField($this->getFieldName('setID'),$this->record['setID']); ?>
	</td>
</tr>
