<tr class='clickable-row' data-href='<?php echo $this->getLink('XG01', 'productsrate/edit', 'productsrate/view', array('index'=>$this->record['id']));?>'>
	<td><?php echo $this->drawEditButton('XG01', 'productsrate/edit', 'productsrate/view', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['city_name']; ?></td>
	<td><?php echo $this->record['start_dt']; ?></td>
</tr>
