<tr class='clickable-row' data-href='<?php echo $this->getLink("XS10", 'IDCommission/view', 'IDCommission/view', array('type'=>$this->model->type,'year'=>$this->record['year'],'month'=>$this->record['month'],'index'=>$this->record['id'],'employee_id'=>$this->record['employee_id']));?>'>
    <td><?php echo $this->record['employee_code']; ?></td>
    <td><?php echo $this->record['employee_name']; ?></td>
    <td><?php echo $this->record['city']; ?></td>
    <td><?php echo $this->record['description']; ?></td>
    <td><?php echo $this->record['time']; ?></td>
    <td><?php echo $this->record['sum_amount']; ?></td>
</tr>
