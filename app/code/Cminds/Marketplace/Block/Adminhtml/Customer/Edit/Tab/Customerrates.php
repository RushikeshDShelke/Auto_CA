<?php

namespace Cminds\Marketplace\Block\Adminhtml\Customer\Edit\Tab;

use Cminds\Marketplace\Helper\Data as HelperData;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Ui\Component\Layout\Tabs\TabWrapper;

class Customerrates extends TabWrapper
{
    protected $coreRegistry = null;
    protected $isAjaxLoaded = true;
    protected $_helperData;

    public function __construct(
        Context $context,
        Registry $registry,
        HelperData $helperData,
        array $data = []
    ) {
        $this->_helperData = $helperData;

        parent::__construct($context, $data);

        $this->coreRegistry = $registry;
    }

    public function canShowTab()
    {
        $id = $this->_request->getParam('id');
        if ($this->_helperData->isSupplier($id)) {
            return true;
        }

        return false;
    }

    /**
     * Return Tab label
     *
     * @codeCoverageIgnore
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Customer Rates');
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl(
            'marketplace/customer/customerrates',
            ['_current' => true]
        );
    }
}
