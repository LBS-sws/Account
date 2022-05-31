<tr class='clickable-row' data-href='<?php echo $this->getLink('PS05', 'planeSetYear/edit', 'planeSetYear/view', array('index'=>$this->record['id']));?>'>
	<td><?php echo $this->drawEditButton('PS05', 'planeSetYear/edit', 'planeSetYear/view', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['set_name']; ?></td>
	<td><?php echo $this->record['start_date']; ?></td>
</tr>
