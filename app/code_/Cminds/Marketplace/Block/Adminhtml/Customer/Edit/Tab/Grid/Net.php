<?php

namespace Cminds\Marketplace\Block\Adminhtml\Customer\Edit\Tab\Grid;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Backend\Block\Context;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Registry;
use Cminds\Marketplace\Helper\Profits;
use Magento\Framework\DataObject;

class Net extends AbstractRenderer
{
    protected $_currencyHelper;
    protected $_registry;
    protected $_profits;

    public function __construct(
        Context $context,
        Data $currencyHelper,
        Registry $registry,
        Profits $profits,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_currencyHelper = $currencyHelper;
        $this->_registry = $registry;
        $this->_profits = $profits;

    }

    public function render(DataObject $row)
    {
        $id = $this->_request->getParam('id');
        $value = $row->getData($this->getColumn()->getIndex());

        return $this->_currencyHelper->currency(
            $this->_profits->calculateNetIncome($id, $value),
            true,
            false
        );
    }
}
