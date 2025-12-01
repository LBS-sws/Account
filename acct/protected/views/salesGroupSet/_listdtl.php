<tr class='clickable-row' data-href='<?php echo $this->getLink('SG02', 'salesGroupSet/edit', 'salesGroupSet/view', array('index'=>$this->record['id']));?>'>
	<td><?php echo $this->drawEditButton('SG02', 'salesGroupSet/edit', 'salesGroupSet/view', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['employee_code']; ?></td>
	<td><?php echo $this->record['employee_name']; ?></td>
	<td><?php echo $this->record['start_date']; ?></td>
	<td><?php echo $this->record['end_date']; ?></td>
	<td><?php echo $this->record['group_staff_name']; ?></td>
</tr>
