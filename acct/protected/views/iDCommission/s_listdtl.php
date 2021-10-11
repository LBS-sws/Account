<tr  >
    <?php if ($this->model->type==1): ?>
    <td>
        <?php
        echo TbHtml::checkBox("IDCommissionBox[updateList][]",$this->record['commission']==1,array("class"=>'checkBlock',"value"=>$this->record['id']))
        ?>
    </td>
    <?php endif ?>
    <td><?php echo $this->record['service_no']; ?></td>
    <td><?php echo $this->record['city']; ?></td>
    <td><?php echo $this->record['back_date']; ?></td>
    <td><?php echo $this->record['company']; ?></td>
    <td><?php echo $this->record['cust_type_name']; ?></td>
    <td><?php echo $this->record['ctrt_period']; ?></td>
    <td><?php echo $this->record['back_money']; ?></td>
    <td><?php echo $this->record['back_ratio']; ?>%</td>
    <td><?php echo $this->record['comm_money']; ?></td>
    <td><?php echo $this->record['rate_num']; ?></td>
    <td><?php echo $this->record['all_money']; ?></td>
    <td><?php echo $this->record['commission_name']; ?></td>
</tr>
