<tr>
	<th></th>
<?php if (!Yii::app()->user->isSingleCity()) : ?>
	<th>
		<?php echo TbHtml::link($this->getLabelName('city_name').$this->drawOrderArrow('city_name'),'#',$this->createOrderLink('code-list','city_name'))
			;
		?>
	</th>
<?php endif ?>
	<th>
		<?php echo TbHtml::link($this->getLabelName('trans_type_desc').$this->drawOrderArrow('trans_type_desc'),'#',$this->createOrderLink('code-list','trans_type_desc'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('trans_cat').$this->drawOrderArrow('trans_cat'),'#',$this->createOrderLink('code-list','trans_cat'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('account').$this->drawOrderArrow('account'),'#',$this->createOrderLink('code-list','account'))
			;
		?>
	</th>
</tr>
