<?php
/**
 * Dotzot Shipping
 *
 */

namespace Dotzotfront\ShippingTracking\Helper;

/**
 * Class Data Helper
 */
class Data extends \Dotzotfront\ShippingTracking\Helper\Main
{
    /**
     * Config section id
     */
    const SECTION_ID = 'prshippingtracking';

    /**
     * @var string
     */
    protected $_configSectionId = self::SECTION_ID;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var \Magento\Config\Model\ConfigFactory
     */
    private $configFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\Helper\Context     $context
     * @param Config                                    $config
     * @param \Magento\Config\Model\ConfigFactory       $configFactory
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Helper\Context $context,
        \Dotzotfront\ShippingTracking\Helper\Config $config,
        \Magento\Config\Model\ConfigFactory $configFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        parent::__construct($objectManager, $context);
        $this->config = $config;
        $this->configFactory = $configFactory;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param null $store
     * @return bool
     */
    public function moduleEnabled($store = null)
    {
        return (bool)$this->getConfig($this->_configSectionId . '/general/enabled', $store);
    }

    /**
     * @return Config
     */
    public function getSysConfig()
    {
        return $this->config;
    }
}