<tr class='clickable-row' data-href='<?php echo $this->getLink('XS01', 'commission/view', 'commission/view', array('year'=>$this->record['year'],'month'=>$this->record['month'],'index'=>$this->record['id']));?>'>
    <td><?php echo $this->record['employee_code']; ?></td>
    <td><?php echo $this->record['employee_name']; ?></td>
    <td><?php echo $this->record['city']; ?></td>
    <td><?php echo $this->record['user_name']; ?></td>
    <td><?php echo $this->record['time']; ?></td>
    <td><?php echo $this->record['comm_total_amount']; ?></td>
</tr>
