<?php
/**
 * Dotzot Shipping
 *
 */

namespace Dotzotfront\ShippingTracking\Model;

class ServiceManager
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Initialize model
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getServiceByName($name)
    {
        return $this->objectManager->create("Dotzotfront\ShippingTracking\Model\Service\\" . ucfirst($name));
    }
}