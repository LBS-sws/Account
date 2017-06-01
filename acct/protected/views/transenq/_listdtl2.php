<tr class='clickable-row' data-href='#'>
	<td><?php echo $this->record['trans_dt']; ?></td>
	<td><?php echo $this->record['trans_type_desc']; ?></td>
	<td><?php echo $this->record['pay_subject']; ?></td>
	<td><?php echo $this->record['amount_in']; ?></td>
	<td><?php echo $this->record['amount_out']; ?></td>
	<td>
		<?php 
			$docId = $this->record['id'];
			$type = $this->record['trans_cat'];
			$title1 = Yii::t('dialog','Transaction Detail');
			$title2 = Yii::t('dialog','Attachment');
			echo "<a href='javascript:showdtl($docId, \"$type\");'><span class='fa fa-info-circle' title='$title1'></span></a>&nbsp;&nbsp;&nbsp;";
			echo ($this->record['no_of_attm'] > 0) ? "<a href='javascript:showattm($docId);'><span class='fa fa-paperclip' title='$title2'></span></a>" : '&nbsp;';
		?>
	</td> 
</tr>
