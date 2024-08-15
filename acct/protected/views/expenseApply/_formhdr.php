<colgroup>
    <col width="64px" />
    <col width="200px" />
    <col width="200px" />
    <col width="200px" />
    <col width="300px" />
    <col width="200px" />
    <col width="70px" />
    <col width="200px" />
    <col width="200px" />
    <col width="200px" />
    <col width="200px" />
    <col width="200px" />
    <col width="200px" />
    <col width="200px" />
    <col width="200px" />
    <col width="200px" />
    <col width="200px" />
    <col width="200px" />
</colgroup>
<tr>
    <th>&nbsp;</th>
    <th colspan="5" class="bg-primary">报销明细：</th>
    <th class="td_none" rowspan="3">
        <?php
        if($this->model->readonly()){
            echo "&nbsp;";
        }else{
            echo TbHtml::Button('增加',array('id'=>'btnAddRow','title'=>Yii::t('misc','Add'),'class'=>'btn-primary'));
        }
        ?>
    </th>
    <th colspan="11" class="bg-info">费用明细：</th>
</tr>
<tr>
    <th>&nbsp;</th>
    <th rowspan="2" class="bg-primary">费用归属</th>
    <th rowspan="2" class="bg-primary">日期</th>
    <th rowspan="2" class="bg-primary">费用类别</th>
    <th rowspan="2" class="bg-primary">摘要</th>
    <th rowspan="2" class="bg-primary">金额</th>
    <th rowspan="1" colspan="2" class="bg-info">本地费用</th>
    <th rowspan="1" colspan="5" class="bg-info">差旅费用</th>
    <th rowspan="2" class="bg-info">办公费</th>
    <th rowspan="2" class="bg-info">快递费</th>
    <th rowspan="2" class="bg-info">通讯费</th>
    <th rowspan="2" class="bg-info">其他</th>
</tr>
<tr>
    <th>&nbsp;</th>
    <th class="bg-info">市内交通费</th>
    <th class="bg-info">餐费</th>
    <th class="bg-info">机票/火车票/汽车票</th>
    <th class="bg-info">酒店</th>
    <th class="bg-info">交通费</th>
    <th class="bg-info">餐费</th>
    <th class="bg-info">其他</th>
</tr>
