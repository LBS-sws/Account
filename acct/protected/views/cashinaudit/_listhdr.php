<tr>
<?php if (!Yii::app()->user->isSingleCity()) : ?>
	<th>
		<?php echo TbHtml::link($this->getLabelName('city_name').$this->drawOrderArrow('city_name'),'#',$this->createOrderLink('request-list','city_name'))
			;
		?>
	</th>
<?php endif ?>
	<th>
		<?php echo TbHtml::link($this->getLabelName('audit_dt').$this->drawOrderArrow('audit_dt'),'#',$this->createOrderLink('request-list','audit_dt'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('balance').$this->drawOrderArrow('balance'),'#',$this->createOrderLink('request-list','balance'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('rec_amt').$this->drawOrderArrow('rec_amt'),'#',$this->createOrderLink('request-list','rec_amt'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('req_user_name').$this->drawOrderArrow('req_user_name'),'#',$this->createOrderLink('request-list','req_user_name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('audit_user_name').$this->drawOrderArrow('audit_user_name'),'#',$this->createOrderLink('request-list','audit_user_name'))
			;
		?>
	</th>
</tr>
