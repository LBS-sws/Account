<tr class='clickable-row' data-href='<?php echo $this->getLink('PS01', 'planeAward/edit', 'planeAward/view', array('index'=>$this->record['id']));?>'>
    <td><?php echo $this->drawEditButton('PS01', 'planeAward/edit', 'planeAward/view', array('index'=>$this->record['id'])); ?></td>

    <td><?php echo $this->record['code']; ?></td>
	<td><?php echo $this->record['name']; ?></td>
	<td><?php echo $this->record['city_name']; ?></td>
	<td><?php echo $this->record['job_num']; ?></td>
	<td><?php echo $this->record['money_num']; ?></td>
	<td><?php echo $this->record['year_num']; ?></td>
	<td><?php echo $this->record['other_sum']; ?></td>
	<td><?php echo $this->record['plane_sum']; ?></td>
</tr>
