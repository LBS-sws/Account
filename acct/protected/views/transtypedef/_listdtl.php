<tr class='clickable-row' data-href='<?php echo $this->getLink('XC05', 'transtypedef/edit', 'transtypedef/view', array('code'=>$this->record['trans_type_code'],'city'=>$this->record['city']));?>'>
	<td><?php echo $this->drawEditButton('XC05', 'transtypedef/edit', 'transtypedef/view', array('code'=>$this->record['trans_type_code'],'city'=>$this->record['city'])); ?></td>
<?php if (!Yii::app()->user->isSingleCity()) : ?>
	<td><?php echo $this->record['city_name']; ?></td>
<?php endif ?>
	<td><?php echo $this->record['trans_type_desc']; ?></td>
	<td><?php echo $this->record['trans_cat']; ?></td>
	<td><?php echo $this->record['account']; ?></td>
</tr>
