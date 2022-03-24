<?php
/**
 * Dotzot Shipping
 *
 */

namespace Dotzotfront\ShippingTracking\Block;

use Dotzotfront\ShippingTracking\Model\System\Config\Image;
use Magento\Framework\UrlInterface;

class Tracking extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Dotzotfront\ShippingTracking\Helper\Data
     */
    private $dataHelper;

    /**
     * @var \Dotzotfront\ShippingTracking\Model\ServiceManager
     */
    private $serviceManager;

    /**
     * Tracking constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\ObjectManagerInterface        $objectManager
     * @param \Magento\Sales\Api\OrderRepositoryInterface      $orderRepository
     * @param \Dotzotfront\ShippingTracking\Helper\Data         $dataHelper
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Dotzotfront\ShippingTracking\Helper\Data $dataHelper,
        \Dotzotfront\ShippingTracking\Model\ServiceManager $serviceManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->objectManager = $objectManager;
        $this->orderRepository = $orderRepository;
        $this->dataHelper = $dataHelper;
        $this->serviceManager = $serviceManager;
    }

    /**
     * @return \Magento\Framework\View\Element\Template
     */
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    /**
     * @param $orderId
     * @return null|string
     */
    public function getOrderStatus($orderId = null)
    {
        if (empty($orderId)) {
            $orderId = $this->getOrderId();
        }

        return $this->orderRepository->get($orderId)->getState();
    }

    /**
     * @return mixed
     */
    public function getTrackingInfo()
    {
        $data = $this->getResultData();
        $carrier = $data['carrier'];
        $result = [];
        $availableServices = array_keys($this->getAvailableServices());

        if (in_array($carrier, $availableServices) && !empty($data['tracking_number'])) {
            $serviceModel = $this->serviceManager->getServiceByName($carrier);
            $result = $serviceModel->getTrackingInfo($data['tracking_number']);
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getAvailableServices()
    {
        $availableServices = [];
        $system = $this->dataHelper->getSysConfig();
        
        if ($system->enabledUpsApi()) {
            $availableServices['ups'] = __('UPS');
        }
        
        if ($system->enabledFedexApi()) {
            $availableServices['fedex'] = __('FedEx');
        }
        
        if ($system->enabledUspsApi()) {
            $availableServices['usps'] = __('USPS');
        }

        return $availableServices;
    }

    /**
     * @return string
     */
    public function getSearchUrl()
    {
        return $this->getUrl("shippingtracking/search/index");
    }

    /**
     * @return mixed
     */
    public function getResultData()
    {
        return $this->getData();
    }

    /**
     * @return int|mixed
     */
    public function getOrderId()
    {
        $data = $this->getResultData();
        return isset($data['order_ids']) ? array_shift($data['order_ids']) : 0;
    }

    /**
     * @return null|string
     */
    public function getOrderIncrementId()
    {
        return $this->orderRepository->get($this->getOrderId())->getIncrementId();
    }

    /**
     * @return mixed
     */
    public function getServiceName()
    {
        return $this->getData('carrier');
    }

    /**
     * @return bool
     */
    public function getServiceIcon()
    {
        $carrier = $this->getServiceName();

        return $this->getViewFileUrl(
            'Dotzotfront_ShippingTracking::images/icons/' . $carrier . '.png'
        );
    }

    /**
     * @return mixed
     */
    public function getTrackingNumber()
    {
        return $this->getData('tracking_number');
    }

    /**
     * @return bool
     */
    public function canShow()
    {
        $data = $this->getResultData();
        if (empty($data['carrier'])
            || empty($data['tracking_number'])
            || empty($data['order_ids'])
        ) {
            return false;
        }

        return true;
    }
}