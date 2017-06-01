<tr>
<?php if (!Yii::app()->user->isSingleCity()) : ?>
	<th>
		<?php echo TbHtml::link($this->getLabelName('city_name').$this->drawOrderArrow('city_name'),'#',$this->createOrderLink('enquiry-list','city_name'))
			;
		?>
	</th>
<?php endif ?>
	<th>
		<?php echo TbHtml::link($this->getLabelName('acct_name').$this->drawOrderArrow('acct_name'),'#',$this->createOrderLink('enquiry-list','acct_name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('bank_name').$this->drawOrderArrow('bank_name'),'#',$this->createOrderLink('enquiry-list','bank_name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('acct_no').$this->drawOrderArrow('acct_no'),'#',$this->createOrderLink('enquiry-list','acct_no'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('acct_type_desc').$this->drawOrderArrow('acct_type_desc'),'#',$this->createOrderLink('enquiry-list','acct_type_desc'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('balance').$this->drawOrderArrow('balance'),'#',$this->createOrderLink('enquiry-list','balance'))
			;
		?>
	</th>
</tr>
