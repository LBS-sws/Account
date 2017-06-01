<tr class='clickable-row' data-href='<?php echo $this->getLink('D02', 'group/edit', 'group/view', array('index'=>$this->record['temp_id']));?>'>
	<td><?php echo $this->drawEditButton('D02', 'group/edit', 'group/view', array('index'=>$this->record['temp_id'])); ?></td>
	<td><?php echo $this->record['temp_name']; ?></td>
	<td><?php echo $this->record['system_name']; ?></td>
</tr>
