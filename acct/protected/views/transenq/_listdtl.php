<tr class='clickable-row' data-href='<?php echo $this->getLink('XE02', 'transenq/index2', 'transenq/index2', array('index'=>$this->record['id'],'city'=>$this->record['trans_city']));?>'>
<?php if (!Yii::app()->user->isSingleCity()) : ?>
	<td><?php echo $this->record['city_name']; ?></td>
<?php endif ?>
	<td><?php echo $this->record['acct_name']; ?></td>
	<td><?php echo $this->record['bank_name']; ?></td>
	<td><?php echo $this->record['acct_no']; ?></td>
	<td><?php echo $this->record['acct_type_desc']; ?></td>
	<td><?php echo $this->record['balance']; ?></td>
</tr>
