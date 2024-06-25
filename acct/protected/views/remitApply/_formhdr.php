<colgroup>
    <col width="200px" />
    <col width="200px" />
    <col width="200px" />
    <col width="300px" />
    <col width="200px" />
    <col width="70px" />
</colgroup>
<tr>
    <th colspan="5" class="bg-primary">报销明细：</th>
    <th class="td_none" rowspan="2">
        <?php
        if($this->model->readonly()){
            echo "&nbsp;";
        }else{
            echo TbHtml::Button('增加',array('id'=>'btnAddRow','title'=>Yii::t('misc','Add'),'class'=>'btn-primary'));
        }
        ?>
    </th>
</tr>
<tr>
    <th class="bg-primary">费用归属</th>
    <th class="bg-primary">日期</th>
    <th class="bg-primary">费用类别</th>
    <th class="bg-primary">摘要</th>
    <th class="bg-primary">金额</th>
</tr>
