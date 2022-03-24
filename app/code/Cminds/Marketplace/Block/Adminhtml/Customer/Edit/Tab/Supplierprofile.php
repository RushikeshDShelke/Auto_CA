<?php

namespace Cminds\Marketplace\Block\Adminhtml\Customer\Edit\Tab;

use Cminds\Marketplace\Helper\Data as DataHelper;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Ui\Component\Layout\Tabs\TabWrapper;

class Supplierprofile extends TabWrapper
{
    protected $isAjaxLoaded = true;

    /**
     * Registry object.
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * Data helper object.
     *
     * @var DataHelper
     */
    protected $dataHelper;

    /**
     * Supplierprofile constructor.
     *
     * @param Context    $context    Context object.
     * @param Registry   $registry   Registry object.
     * @param DataHelper $dataHelper Data helper object.
     * @param array      $data       Data array.
     */
    public function __construct(
        Context $context,
        Registry $registry,
        DataHelper $dataHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );

        $this->coreRegistry = $registry;
        $this->dataHelper = $dataHelper;
    }

    /**
     * Check if tab can be displayed or not.
     *
     * @return bool
     */
    public function canShowTab()
    {
        $id = $this->getRequest()->getParam('id');
        if ($this->dataHelper->isSupplier($id)) {
            return true;
        }

        return false;
    }

    /**
     * Return tab label.
     *
     * @return Phrase
     */
    public function getTabLabel()
    {
        return __('Supplier Profile');
    }

    /**
     * Return url link to tab content.
     *
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl(
            'marketplace/customer/supplierprofile',
            ['_current' => true]
        );
    }
}
