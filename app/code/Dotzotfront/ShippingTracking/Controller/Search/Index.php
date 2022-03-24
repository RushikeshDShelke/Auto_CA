<?php
/**
 * Dotzot Shipping
 *
 */

namespace Dotzotfront\ShippingTracking\Controller\Search;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * Base Url
     */
    const BASE_URL = 'shippingtracking/index/index';

    /**
     * Result Url
     */
    const RESULT_URL = 'shippingtracking/result/index';

    /**
     * @var \Dotzotfront\ShippingTracking\Model\AbstractService
     */
    private $trackingModel;

    /**
     * @var \Dotzotfront\ShippingTracking\Helper\Data
     */
    private $dataHelper;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * Index constructor.
     *
     * @param \Magento\Framework\App\Action\Context                $context
     * @param \Dotzotfront\ShippingTracking\Model\AbstractService   $trackingModel
     * @param \Dotzotfront\ShippingTracking\Helper\Data             $dataHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Dotzotfront\ShippingTracking\Model\AbstractService $trackingModel,
        \Dotzotfront\ShippingTracking\Helper\Data $dataHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        parent::__construct($context);
        $this->dataHelper = $dataHelper;
        $this->trackingModel = $trackingModel;
        $this->messageManager = $messageManager;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if (!$this->dataHelper->moduleEnabled()) {
            return $resultRedirect->setPath('404notfound');
        }

        $data = $this->getRequest()->getParams();

        if (isset($data['shippingtracking']['order'])
            && isset($data['shippingtracking']['info'])
        ) {
            $orderId = trim($data['shippingtracking']['order']);
            $info = trim($data['shippingtracking']['info']);
            $data = $this->trackingModel->getTrackingNumberByOrderData($orderId, $info);
            $params = [];
            if (!empty($data)) {
                $carrier = key($data);
                if (isset($data[$carrier])) {
                    $params[$carrier] = $data[$carrier];
                }
            }

            if (!empty($params)) {
                return $resultRedirect->setPath(self::RESULT_URL, $params);
            }

            $this->messageManager->addError(__('Make sure that you have entered the Order Number and phone number (or email address) correctly.'));
        } elseif (isset($data['number'])
            && isset($data['carrier'])
        ) {
            return $resultRedirect->setPath(self::RESULT_URL, [$data['carrier'] => $data['number']]);
        }

        return  $resultRedirect->setPath(self::BASE_URL);
    }
}