<?php
	switch ($this->record['wfstatus']) {
		case '1PC': $textcolor = "text-red"; break;
		case '2PA': $textcolor = "text-purple"; break;
		case '3PR': $textcolor = "text-yellow"; break;
		case '4PS': $textcolor = "text-aqua"; break;
		case '5ED': $textcolor = "text-green"; break;
		default: $textcolor = "";
	}
?>
<tr class='clickable-row <?php echo $textcolor; ?>' data-href='<?php echo $this->getLink('XA04', 'payreq/edit', 'payreq/view', array('index'=>$this->record['id']));?>'>
<?php if (!Yii::app()->user->isSingleCity()) : ?>
	<td><?php echo $this->record['city_name']; ?></td>
<?php endif ?>
	<td><?php echo $this->record['req_dt']; ?></td>
	<td><?php echo $this->record['trans_type_desc']; ?></td>
	<td><?php echo $this->record['payee_name']; ?></td>
	<td><?php echo $this->record['item_desc']; ?></td>
	<td><?php echo $this->record['amount']; ?></td>
	<td><?php echo $this->record['ref_no']; ?></td>
	<td><?php echo $this->record['int_fee']; ?></td>
	<td><?php echo $this->record['wfstatusdesc']; ?></td>
</tr>
