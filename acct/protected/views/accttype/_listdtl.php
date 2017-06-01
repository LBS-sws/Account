<tr class='clickable-row' data-href='<?php echo $this->getLink('XC01', 'accttype/edit', 'accttype/view', array('index'=>$this->record['id']));?>'>
	<td><?php echo $this->drawEditButton('XC01', 'accttype/edit', 'accttype/view', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['acct_type_desc']; ?></td>
	<td><?php echo $this->record['rpt_cat']; ?></td>
</tr>
