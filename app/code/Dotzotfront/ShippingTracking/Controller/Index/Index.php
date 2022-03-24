<?php
/**
 * Dotzot Shipping
 *
 */

namespace Dotzotfront\ShippingTracking\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    /**
     * @var \Dotzotfront\ShippingTracking\Helper\Data
     */
    private $dataHelper;

    /**
     * Index constructor.
     *
     * @param \Magento\Framework\App\Action\Context                $context
     * @param \Magento\Framework\View\Result\PageFactory           $resultPageFactory
     * @param \Dotzotfront\ShippingTracking\Helper\Data             $dataHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Dotzotfront\ShippingTracking\Helper\Data $dataHelper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->dataHelper = $dataHelper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if (!$this->dataHelper->moduleEnabled()) {
            return $this->resultRedirectFactory->create()->setPath('404notfound');
        }
        $resultPage = $this->resultPageFactory->create();

        return $resultPage;
    }
}