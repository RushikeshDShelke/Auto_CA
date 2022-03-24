<?php
/*

*/

namespace Dotzotfront\Base\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Base observer
 */
abstract class AbstractSystemConfig implements ObserverInterface
{
   
    /**
     * @var \Magento\Framework\Message\Manager
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $cacheTypeList;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Config\Model\Config\Structure
     */
    protected $configStructure;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfigInterface;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $resourceModelConfig;

    /**
     * @var \Dotzotfront\Base\Model\ProductFactory
     */
    protected $baseProductFactory;

    /**
     * Initialize model
     *
     * @param \Magento\Framework\Message\Manager                 $messageManager
     * @param \Magento\Framework\App\Cache\TypeListInterface     $cacheTypeList
     * @param \Magento\Framework\Event\ManagerInterface          $eventManager
     * @param \Magento\Config\Model\Config\Structure             $configStructure
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface
     * @param \Magento\Config\Model\ResourceModel\Config         $resourceModelConfig
     * @param \Dotzotfront\Base\Model\ProductFactory              $baseProductFactory
     */
    public function __construct(
        \Magento\Framework\Message\Manager $messageManager,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Config\Model\Config\Structure $configStructure,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface,
        \Magento\Config\Model\ResourceModel\Config $resourceModelConfig,
        \Dotzotfront\Base\Model\ProductFactory $baseProductFactory
    ) {
        $this->messageManager       = $messageManager;
        $this->cacheTypeList        = $cacheTypeList;
        $this->eventManager         = $eventManager;
        $this->configStructure      = $configStructure;
        $this->scopeConfigInterface = $scopeConfigInterface;
        $this->resourceModelConfig  = $resourceModelConfig;
        $this->baseProductFactory   = $baseProductFactory;
    }

    /**
     * Receive secttion
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return mixed
     */
    protected function _getSection($observer)
    {
        $controller = $observer->getEvent()->getControllerAction();
        $req     = $controller->getRequest();
        $current = $req->getParam('section');
        $website = $req->getParam('website');
        $store   = $req->getParam('store');

        if (!$current) {
            $section = $this->configStructure->getFirstSection();
        } else {
            $section = $this->configStructure->getElement($current);
        }

        if ($section) {
            if ($this->_hasS($section)) {
                return $section;
            }
        }

        return false;
    }

    /**
     * Receive true if section is related to Dotzotfront extension
     *
     * @param  string $section
     * @return boolean
     */
    protected function _isPlumSection($section)
    {
        $data = $section->getData();
        if (isset($data['tab'])) {
            return (string) $data['tab'] == 'plu' . 'mroc' . 'ket';
        }
        return false;
    }

    /**
     * Retrieve if section has s
     *
     * @param  mixed $section 
     * @return boolean
     */
    protected function _hasS($section)
    {
        if (!$this->_isPlumSection($section)) {
            return false;
        }

        $v = $this->scopeConfigInterface->getValue(
            $section->getId() . '/' . 'gen'.'eral', \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            0
        );
        if (is_array($v)) {
            return (array_key_exists('ser' . strrev('lai'), $v));
        }

        return false;
    }

    /**
     * Retrieve product
     *
     * @param  mixed $section
     * @return mixed
     */
    protected function _getProductBySection($section)
    {
        $i = 'ser' . strrev('lai'); $j = 'gen'.'eral';
        foreach ($section->getChildren() as $group) {

            if ($group->getId() == $j) {
                foreach ($group->getChildren() as $field) {
                    if ($field->getId() == 'version') {
                        $d = $field->getData();
                        $r = explode('\\', $d['frontend_model']);

                        return $this->baseProductFactory->create()->load($r[1]);
                    }
                }
            }
        }

        return null;
    }
}