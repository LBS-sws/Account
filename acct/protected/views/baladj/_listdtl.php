<tr class='clickable-row' data-href='<?php echo $this->getLink('XE06', 'baladj/edit', 'baladj/view', 
		array('city'=>$this->record['city'], 
			'year'=>$this->record['audit_year'], 
			'month'=>$this->record['audit_month'], 
			'acct_id'=>$this->record['acct_id']));?>'>
<?php if (!Yii::app()->user->isSingleCity()) : ?>
	<td><?php echo $this->record['city_name']; ?></td>
<?php endif ?>
	<td><?php echo $this->record['audit_year']; ?></td>
	<td><?php echo $this->record['audit_month']; ?></td>
	<td><?php echo $this->record['acct_name']; ?></td>
	<td><?php echo $this->record['bal_month_end']; ?></td>
	<td><?php echo $this->record['bal_t3']; ?></td>
</tr>
