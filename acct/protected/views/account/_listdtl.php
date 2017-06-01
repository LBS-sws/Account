<?php $params = array('index'=>$this->record['id'], 'city'=>(($this->record['city']=='99999')?Yii::app()->user->city():$this->record['city'])); ?>
<tr class='clickable-row' data-href='<?php echo $this->getLink('XC02', 'account/edit', 'account/view', $params);?>'>
	<td><?php echo $this->drawEditButton('XC02', 'account/edit', 'account/view', $params); ?></td>
	<td><?php echo $this->record['acct_type_desc']; ?></td>
	<td><?php echo $this->record['acct_no']; ?></td>
	<td><?php echo $this->record['acct_name']; ?></td>
	<td><?php echo $this->record['bank_name']; ?></td>
</tr>
