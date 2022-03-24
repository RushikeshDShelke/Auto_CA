<?php

namespace Cminds\Marketplace\Block\Adminhtml\Customer\Edit\Tab\Tabs\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Backend\Block\Context;
use Magento\Framework\DataObject ;

class Action extends AbstractRenderer
{
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function render(DataObject $row)
    {
        $rateId = $row->getData($this->getColumn()->getIndex());

        $html = '<a href="'.$this->getUrl('*/suppliers/removerate/rateId/'.$rateId).'">Cancel</a>';

        return $html;
    }
}