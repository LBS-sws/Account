<tr>
    <th width="1%"><input name="Fruit"  type="checkbox"  id="all"></th>
    <th width="1%"></th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('code').$this->drawOrderArrow('b.code'),'#',$this->createOrderLink('Appraisal-list','b.code'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('name').$this->drawOrderArrow('b.name'),'#',$this->createOrderLink('Appraisal-list','b.name'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('entry_time').$this->drawOrderArrow('b.entry_time'),'#',$this->createOrderLink('Appraisal-list','b.entry_time'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('city_name').$this->drawOrderArrow('e.name'),'#',$this->createOrderLink('Appraisal-list','e.name'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('dept_name').$this->drawOrderArrow('c.name'),'#',$this->createOrderLink('Appraisal-list','c.name'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('time'),'#')
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('status_type').$this->drawOrderArrow('f.status_type'),'#',$this->createOrderLink('Appraisal-list','f.status_type'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('appraisal_money').$this->drawOrderArrow('f.appraisal_amount'),'#',$this->createOrderLink('Appraisal-list','f.appraisal_amount'))
        ;
        ?>
    </th>
</tr>
