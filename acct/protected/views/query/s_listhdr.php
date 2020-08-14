<tr>
<!--    <th><input type="checkbox" value="" name="chkboxAll" id="chkboxAll"  checked="checked"></th>-->
    <th>
        <?php echo TbHtml::link($this->getLabelName('city_name').$this->drawOrderArrow('city_name'),'#',$this->createOrderLink('tc-list','city_name'))
        ;
        ?>
    </th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('first_dt').$this->drawOrderArrow('first_dt'),'#',$this->createOrderLink('tc-list','first_dt'))
			;
		?>
	</th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('sign_dt').$this->drawOrderArrow('sign_dt'),'#',$this->createOrderLink('tc-list','sign_dt'))
        ;
        ?>
    </th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('company_name').$this->drawOrderArrow('company_name'),'#',$this->createOrderLink('tc-list','company_name'))
			;
		?>
	</th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('被跨区业务员'))
        ;
        ?>
    </th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('type_desc').$this->drawOrderArrow('type_desc'),'#',$this->createOrderLink('tc-list','type_desc'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('service').$this->drawOrderArrow('service'),'#',$this->createOrderLink('tc-list','service'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('服务年金额'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('amt_install').$this->drawOrderArrow('amt_install'),'#',$this->createOrderLink('tc-list','amt_install'))
			;
		?>
	</th>

    <th>
        <?php echo TbHtml::link($this->getLabelName('是否计算过提成'))
        ;
        ?>
    </th>
</tr>
