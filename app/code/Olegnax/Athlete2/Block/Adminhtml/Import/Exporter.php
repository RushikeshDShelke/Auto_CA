<?php

namespace Olegnax\Athlete2\Block\Adminhtml\Import;

use Magento\Backend\Block\Template\Context;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filesystem;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Olegnax\Athlete2\Block\Adminhtml\Import;
use Olegnax\Athlete2\Helper\Helper;

class Exporter extends Import
{

    const DEMO_EXPORT_PATH = '*/*/export';

    protected $scopeConfig;
    protected $blockFactory;
    protected $pageFactory;
    protected $storeManager;
    protected $helper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        Filesystem $filesystem,
        ScopeConfigInterface $scopeConfig,
        \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $blockFactory,
        CollectionFactory $pageFactory,
        StoreManagerInterface $storeManager,
        Helper $helper,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->blockFactory = $blockFactory;
        $this->pageFactory = $pageFactory;
        $this->storeManager = $storeManager;
        $this->helper = $helper;
        parent::__construct($context, $filesystem, $data);
    }

    public function getConfig()
    {
        if ($websiteId = $this->getRequest()->getParam('website')) {
            return $this->scopeConfig->getValue('', ScopeInterface::SCOPE_WEBSITE, $websiteId);
        }
        if ($storeId = $this->getRequest()->getParam('store')) {
            return $this->scopeConfig->getValue('', ScopeInterface::SCOPE_STORE, $storeId);
        }
        return $this->scopeConfig->getValue('');
    }

    public function getBlocks()
    {
        $storeIds = $this->getStoreIds();
        $itemCollection = $this->blockFactory->create();
        $items = [];
        if (!empty($storeIds)) {
            $storeIds[] = 0;
            $itemCollection->addFieldToFilter('store_id', ['in' => [$storeIds]]);
        }
        if (count($itemCollection) > 0) {
            foreach ($itemCollection as $item) {
                $items[$item->getIdentifier()] = $item->getTitle();
            }
        }
        return $items;
    }

    protected function getStoreIds()
    {
        $storeIds = [];

        if ($storeId = (int)$this->getRequest()->getParam('store')) {
            $storeIds[] = $storeId;
        } elseif ($websiteId = (int)$this->getRequest()->getParam('website')) {
            $stores = $this->storeManager->getWebsite($websiteId)->getStores();
            foreach ($stores as $_store) {
                $storeIds[] = $_store->getId();
            }
        }

        return $storeIds;
    }

    public function getPages()
    {
        $storeIds = $this->getStoreIds();
        $itemCollection = $this->pageFactory->create();
        if (!empty($storeIds)) {
            $storeIds[] = 0;
            $itemCollection->addFieldToFilter('store_id', ['in' => [$storeIds]]);
        }
        $items = [];
        if (count($itemCollection) > 0) {
            foreach ($itemCollection as $item) {
                $items[$item->getIdentifier()] = $item->getTitle();
            }
        }
        return $items;
    }

    public function getBannerSlider()
    {
        $items = [];
        if ($this->helper->isActivePlugin('Olegnax_BannerSlider')) {
            $group = ObjectManager::getInstance()->get(\Olegnax\BannerSlider\Model\ResourceModel\Group\CollectionFactory::class)->create();
            $itemCollection = $group->addFieldToSelect('*');
            if (count($itemCollection) > 0) {
                foreach ($itemCollection as $item) {
                    $items[$item->getIdentifier()] = $item->getGroupName();
                }
            }
        }
        return $items;
    }

    public function getCarousel()
    {
        $items = [];
        if ($this->helper->isActivePlugin('Olegnax_Carousel')) {
            $group = ObjectManager::getInstance()->get(\Olegnax\Carousel\Model\ResourceModel\Carousel\CollectionFactory::class)->create();
            $itemCollection = $group->addFieldToSelect('*');
            if (count($itemCollection) > 0) {
                foreach ($itemCollection as $item) {
                    $items[$item->getIdentifier()] = $item->getTitle();
                }
            }
        }
        return $items;
    }

    public function actionExport(array $subArguments = [])
    {
        $arguments = [];
        if ($storeId = $this->getRequest()->getParam('store')) {
            $arguments['store'] = $storeId;
        } elseif ($websiteId = $this->getRequest()->getParam('website')) {
            $arguments['website'] = $websiteId;
        }
        if (is_array($subArguments)) {
            $arguments = array_merge($arguments, $subArguments);
        }

        $url = $this->getUrl(self::DEMO_EXPORT_PATH, $arguments);

        return $url;
    }

}
