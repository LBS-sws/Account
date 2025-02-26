<tr class='clickable-row' data-href='<?php echo $this->getLink('XS13', 'performanceSet/edit', 'performanceSet/view', array('index'=>$this->record['id']));?>'>
	<td><?php echo $this->drawEditButton('XS13', 'performanceSet/edit', 'performanceSet/view', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['name']; ?></td>
	<td><?php echo $this->record['start_dt']; ?></td>
</tr>
