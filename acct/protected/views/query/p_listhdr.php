<tr>
    <th><input type="checkbox" value="" name="chkboxAll" id="chkboxAll"  checked="checked"></th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('city_name').$this->drawOrderArrow('city_name'),'#',$this->createOrderLink('tc-list','city_name'))
        ;
        ?>
    </th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('log_dt').$this->drawOrderArrow('log_dt'),'#',$this->createOrderLink('tc-list','log_dt'))
			;
		?>
	</th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('company_name').$this->drawOrderArrow('company_name'),'#',$this->createOrderLink('tc-list','company_name'))
        ;
        ?>
    </th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('description').$this->drawOrderArrow('description'),'#',$this->createOrderLink('tc-list','description'))
			;
		?>
	</th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('qty'))
        ;
        ?>
    </th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('money').$this->drawOrderArrow('money'),'#',$this->createOrderLink('tc-list','money'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('moneys').$this->drawOrderArrow('moneys'),'#',$this->createOrderLink('tc-list','moneys'))
			;
		?>
	</th>
</tr>
