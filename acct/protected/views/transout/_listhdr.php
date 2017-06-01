<tr>
	<th></th>
<?php if (!Yii::app()->user->isSingleCity()) : ?>
	<th>
		<?php echo TbHtml::link($this->getLabelName('city_name').$this->drawOrderArrow('city_name'),'#',$this->createOrderLink('trans-list','city_name'))
			;
		?>
	</th>
<?php endif ?>
	<th>
		<?php echo TbHtml::link($this->getLabelName('trans_dt').$this->drawOrderArrow('trans_dt'),'#',$this->createOrderLink('trans-list','trans_dt'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('trans_type_desc').$this->drawOrderArrow('trans_type_desc'),'#',$this->createOrderLink('trans-list','trans_type_desc'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('acct_type_desc').$this->drawOrderArrow('acct_type_desc'),'#',$this->createOrderLink('trans-list','acct_type_desc'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('bank_name').$this->drawOrderArrow('bank_name'),'#',$this->createOrderLink('trans-list','bank_name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('acct_no').$this->drawOrderArrow('acct_no'),'#',$this->createOrderLink('trans-list','acct_no'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('amount').$this->drawOrderArrow('amount'),'#',$this->createOrderLink('trans-list','amount'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('status').$this->drawOrderArrow('status'),'#',$this->createOrderLink('trans-list','status'))
			;
		?>
	</th>
</tr>
