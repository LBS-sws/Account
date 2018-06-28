<tr>
<?php if (Yii::app()->user->validFunction('CN07')) : ?>
	<th><?php echo TbHtml::checkBox('chkboxAll',false); ?></th>
<?php endif ?>
<?php if (!Yii::app()->user->isSingleCity()) : ?>
	<th>
		<?php echo TbHtml::link($this->getLabelName('city_name').$this->drawOrderArrow('city_name'),'#',$this->createOrderLink('request-list','city_name'))
			;
		?>
	</th>
<?php endif ?>
	<th>
		<?php echo TbHtml::link($this->getLabelName('req_dt').$this->drawOrderArrow('req_dt'),'#',$this->createOrderLink('request-list','req_dt'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('user_name').$this->drawOrderArrow('user_name'),'#',$this->createOrderLink('request-list','user_name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('trans_type_desc').$this->drawOrderArrow('trans_type_desc'),'#',$this->createOrderLink('request-list','trans_type_desc'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('acct_type_desc').$this->drawOrderArrow('acct_type_desc'),'#',$this->createOrderLink('request-list','acct_type_desc'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('payee_name').$this->drawOrderArrow('payee_name'),'#',$this->createOrderLink('request-list','payee_name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('pitem_desc').$this->drawOrderArrow('pitem_desc'),'#',$this->createOrderLink('request-list','pitem_desc'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('item_desc').$this->drawOrderArrow('item_desc'),'#',$this->createOrderLink('request-list','item_desc'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('amount').$this->drawOrderArrow('amount'),'#',$this->createOrderLink('request-list','amount'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('ref_no').$this->drawOrderArrow('ref_no'),'#',$this->createOrderLink('request-list','ref_no'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('int_fee').$this->drawOrderArrow('int_fee'),'#',$this->createOrderLink('request-list','int_fee'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('payreqcountdoc'),'#',$this->createOrderLink('request-list','payreqcountdoc'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('taxcountdoc'),'#',$this->createOrderLink('request-list','taxcountdoc'))
			;
		?>
	</th>
</tr>
