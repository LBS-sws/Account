<tr class='clickable-row' data-href='<?php echo $this->getLink('XC03', 'transtype/edit', 'transtype/view', array('index'=>$this->record['trans_type_code']));?>'>
	<td><?php echo $this->drawEditButton('XC03', 'transtype/edit', 'transtype/view', array('index'=>$this->record['trans_type_code'])); ?></td>
	<td><?php echo $this->record['trans_type_code']; ?></td>
	<td><?php echo $this->record['trans_type_desc']; ?></td>
	<td><?php echo $this->record['trans_cat']; ?></td>
</tr>
