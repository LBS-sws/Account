<tr class='clickable-row' data-href='<?php echo $this->getLink('PS06', 'planeSetOther/edit', 'planeSetOther/view', array('index'=>$this->record['id']));?>'>
	<td><?php echo $this->drawEditButton('PS06', 'planeSetOther/edit', 'planeSetOther/view', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['set_name']; ?></td>
	<td><?php echo $this->record['z_display']; ?></td>
</tr>
