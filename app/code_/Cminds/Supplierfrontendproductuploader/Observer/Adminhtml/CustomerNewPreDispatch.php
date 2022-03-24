<?php

namespace Cminds\Supplierfrontendproductuploader\Observer\Adminhtml;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;

/**
 * Cminds Supplierfrontendproductuploader customer new pre dispatch observer.
 *
 * @category Cminds
 * @package  Cminds_Marketplace
 * @author   Piotr Pierzak <piotr@cminds.com>
 */
class CustomerNewPreDispatch implements ObserverInterface
{
    const LOAD_SUPPLIER_GROUPS_FLAG = 'load_supplier_group_flag';

    private $request;
    private $coreRegistry;

    public function __construct(
        Context $context,
        Registry $coreRegistry
    ) {
        $this->request = $context->getRequest();
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * @param Observer $observer
     *
     * @return CustomerNewPreDispatch
     * @throws \RuntimeException
     */
    public function execute(Observer $observer) // @codingStandardsIgnoreLine
    {
        if ($this->request->getParam('supplier')) {
            $this->coreRegistry->register(self::LOAD_SUPPLIER_GROUPS_FLAG, true);
        }

        return $this;
    }
}
