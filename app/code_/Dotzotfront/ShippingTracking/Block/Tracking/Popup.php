<?php
/**
 * Dotzot Shipping
 *
 */

namespace Dotzotfront\ShippingTracking\Block\Tracking;

class Popup extends \Magento\Shipping\Block\Tracking\Popup
{
    /**
     * @var \Magento\Shipping\Model\Tracking\Result\StatusFactory
     */
    private $trackingResultFactory;

    /**
     * @var \Dotzotfront\ShippingTracking\Helper\Data
     */
    private $dataHelper;

    /**
     * Popup constructor.
     *
     * @param \Dotzotfront\ShippingTracking\Helper\Data                      $dataHelper
     * @param \Magento\Shipping\Model\Tracking\Result\StatusFactory         $trackingResultFactory
     * @param \Magento\Framework\View\Element\Template\Context              $context
     * @param \Magento\Framework\Registry                                   $registry
     * @param \Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface $dateTimeFormatter
     * @param array                                                         $data
     */
    public function __construct(
        \Dotzotfront\ShippingTracking\Helper\Data $dataHelper,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackingResultFactory,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface $dateTimeFormatter,
        array $data = []
    ) {
        $this->trackingResultFactory = $trackingResultFactory;
        $this->dataHelper = $dataHelper;
        parent::__construct($context, $registry, $dateTimeFormatter, $data);
    }

    /**
     * Retrieve array of tracking info
     *
     * @return array
     */
    public function getTrackingInfo()
    {
        $results = parent::getTrackingInfo();

        if (!$this->dataHelper->moduleEnabled()) {
            return $results;
        }

        //~ foreach ($results as $shipping => $result) {
            //~ foreach($result as $key => $track) {
                //~ if (!is_object($track)) {
                    //~ continue;
                //~ }

                //~ $carrier = $track->getCarrier();

                //~ if ($this->dataHelper->getSysConfig()->getEnabledMethodByName($carrier)) {
                    //~ $url = $this->getUrl('shippingtracking/result/index', [$carrier => trim($track->getTracking())]);
                    //~ $results[$shipping][$key] = $this->trackingResultFactory->create()->setData($track->getAllData())
                        //~ ->setErrorMessage(null)
                        //~ ->setUrl($url);
                //~ }
            //~ }
        //~ }
        
        return $results;
    }
}
