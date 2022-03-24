<?php
/**
 * Dotzot Shipping
 *
 */

namespace Dotzotfront\ShippingTracking\Controller\Result;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    /**
     * @var \Dotzotfront\ShippingTracking\Model\AbstractService
     */
    private $trackingModel;

    /**
     * @var \Dotzotfront\ShippingTracking\Helper\Data
     */
    private $dataHelper;

    /**
     * Index constructor.
     *
     * @param \Magento\Framework\App\Action\Context                $context
     * @param \Magento\Framework\View\Result\PageFactory           $resultPageFactory
     * @param \Dotzotfront\ShippingTracking\Model\AbstractService   $trackingModel
     * @param \Dotzotfront\ShippingTracking\Helper\Data             $dataHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Dotzotfront\ShippingTracking\Model\AbstractService $trackingModel,
        \Dotzotfront\ShippingTracking\Helper\Data $dataHelper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->trackingModel = $trackingModel;
        $this->dataHelper = $dataHelper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if (!$this->dataHelper->moduleEnabled()) {
            return $this->resultRedirectFactory->create()->setPath('404notfound');
        }

        $resultPage = $this->resultPageFactory->create();
        $data = $this->getRequest()->getParams();

        if (!empty($data)) {
            $carrier = key($data);
            if (isset($data[$carrier])) {
                $trackingNumber = $data[$carrier];
                $resultPage->getLayout()->getBlock('pr_shippingtracking_result')
                    ->setData([
                        'carrier' => $carrier,
                        'tracking_number' => $trackingNumber,
                        'order_ids' => $this->trackingModel->getOrderIdsByTrackingNumber($trackingNumber)
                    ]);
            }
        }

        return $resultPage;
    }
}