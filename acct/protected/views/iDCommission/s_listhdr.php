<tr>
    <?php if ($this->model->type==1): ?>
    <th><input type="checkbox" value="" name="chkboxAll" id="chkboxAll" ></th>
    <?php endif ?>
    <th>
        <?php echo TbHtml::link($this->getLabelName('service_no').$this->drawOrderArrow('b.service_no'),'#',$this->createOrderLink('tc-list','b.service_no'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('city').$this->drawOrderArrow('b.city'),'#',$this->createOrderLink('tc-list','b.city'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('back_date').$this->drawOrderArrow('a.back_date'),'#',$this->createOrderLink('tc-list','a.back_date'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('company_name').$this->drawOrderArrow('f.name'),'#',$this->createOrderLink('tc-list','f.name'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('cust_type_name').$this->drawOrderArrow('g.cust_type_name'),'#',$this->createOrderLink('tc-list','g.cust_type_name'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('ctrt_period').$this->drawOrderArrow('b.ctrt_period'),'#',$this->createOrderLink('tc-list','b.ctrt_period'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('back_money').$this->drawOrderArrow('a.back_money'),'#',$this->createOrderLink('tc-list','a.back_money'))
        ;
        ?>
    </th>

    <th>
        <?php echo TbHtml::link($this->getLabelName('back_ratio'))
        ;
        ?>
    </th>

    <th>
        <?php echo TbHtml::link($this->getLabelName('comm_money'))
        ;
        ?>
    </th>

    <th>
        <?php echo TbHtml::link($this->getLabelName('rate_num'))
        ;
        ?>
    </th>

    <th>
        <?php echo TbHtml::link($this->getLabelName('all_money'))
        ;
        ?>
    </th>

    <th>
        <?php echo TbHtml::link($this->getLabelName('commission'))
        ;
        ?>
    </th>
</tr>
