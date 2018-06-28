<?php
	switch ($this->record['wfstatus']) {
		case '1PC': $textcolor = "text-red"; break;
		case '2PB': $textcolor = "text-orange"; break;
		case '3PA': $textcolor = "text-purple"; break;
		case '4QR': $textcolor = empty($this->record['trans_id']) ? "text-yellow" : "text-teal"; break;
		case '5PR': $textcolor = empty($this->record['trans_id']) ? "text-yellow" : "text-teal"; break;
		case '6PS': $textcolor = "text-aqua"; break;
		case '7ED': $textcolor = "text-green"; break;
		default: $textcolor = "";
	}
	$strikeB = $this->record['statusx']=='V' ? '<strike>' : '';
	$strikeE = $this->record['statusx']=='V' ? '</strike>' : '';
?>
<tr class='clickable-row <?php echo $textcolor; ?>' data-href='<?php echo $this->getLink('XA04', 'payreq/edit', 'payreq/view', array('index'=>$this->record['id']));?>'>
<?php if (!Yii::app()->user->isSingleCity()) : ?>
	<td><?php echo $strikeB.$this->record['city_name']; ?></td>
<?php endif ?>
	<td><?php echo $strikeB.$this->record['req_dt'].$strikeE; ?></td>
	<td><?php echo $strikeB.$this->record['trans_type_desc'].$strikeE; ?></td>
	<td><?php echo $strikeB.$this->record['acct_type_desc'].$strikeE; ?></td>
	<td><?php echo $strikeB.$this->record['payee_name'].$strikeE; ?></td>
	<td width=25%><?php echo $strikeB.$this->record['item_desc'].$strikeE; ?></td>
	<td><?php echo $strikeB.$this->record['amount'].$strikeE; ?></td>
	<td><?php echo $strikeB.$this->record['ref_no'].$strikeE; ?></td>
	<td><?php echo $strikeB.$this->record['int_fee'].$strikeE; ?></td>
	<td><?php echo $strikeB.$this->record['wfstatusdesc'].$strikeE; ?></td>
</tr>
