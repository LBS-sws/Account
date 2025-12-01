<tr>
    <th width="60%">
        <?php echo TbHtml::label($this->getLabelName('employeeID'), false); ?>
    </th>
    <th width="40%">
        <?php echo TbHtml::label($this->getLabelName('employeeType'), false); ?>
    </th>
    <th>
        <?php echo !$this->model->isReadOnly() ?
            TbHtml::Button('+',array('id'=>'btnAddRow','title'=>Yii::t('misc','Add'),'size'=>TbHtml::BUTTON_SIZE_SMALL))
            : '&nbsp;';
        ?>
    </th>
</tr>
