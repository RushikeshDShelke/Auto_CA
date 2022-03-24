<?php

namespace Olegnax\Athlete2\Controller\Adminhtml\Import;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Olegnax\BannerSlider\Model\ResourceModel\Slides\CollectionFactory;

class Export extends Action
{

    const ADMIN_RESOURCE = 'Olegnax_Athlete2::export';

    protected $_filesystem;

    protected $_storeManager;
    private $website;
    private $store;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Filesystem $filesystem
    ) {
        $this->_filesystem = $filesystem;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $filename = $this->getFileName();
        $document = [];
        try {
            if (is_array($data) && !empty($data)) {
                foreach ($data as $key => $value) {
                    $methodName = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', strtolower($key))));
                    if (method_exists($this, $methodName)) {
                        $content = call_user_func([$this, $methodName], $value);
                        if (is_array($content) && !empty($content)) {
                            $document[$key] = $content;
                        }
                    }
                }
                if (!empty($document)) {
                    $this->loadFile()->filePutContents($this->getDemoPath(), "<root>\n" . $this->prepareToXML($document) . "</root>\n");
                } else {
                    throw new Exception(__('No content to export!'));
                }

                $this->messageManager->addSuccess(__('%1 was successfully exported.', $filename));
            }
        } catch (Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        return $this->_redirect($this->_redirect->getRefererUrl());
    }

    protected function getFileName()
    {
        $title = $this->getTitle();
        $title = strtolower($title);
        $title = str_replace(' ', '-', $title);
        $title = sprintf('demo-%s.xml', $title);
        return $title;
    }

    protected function getTitle()
    {
        $title = 'Default Config';
        $storeManager = $this->_loadObject(StoreManagerInterface::class);
        if ($storeId = (int)$this->getRequest()->getParam('store')) {
            $title = $storeManager->getStore($storeId)->getName();
        } elseif ($websiteId = (int)$this->getRequest()->getParam('website')) {
            $title = $storeManager->getWebsite($websiteId)->getName();
        }

        return $title;
    }

    protected function _loadObject($object)
    {
        return $this->_getObjectManager()->get($object);
    }

    protected function _getObjectManager()
    {
        return ObjectManager::getInstance();
    }

    protected function loadFile()
    {
        return $this->_loadObject(File::class);
    }

    protected function getDemoPath()
    {
        return $this->getAbsolutePath(Import::DEMO_DIR) . DIRECTORY_SEPARATOR . $this->getFileName();
    }

    public function getAbsolutePath($path)
    {
        return $this->_filesystem->getDirectoryRead(DirectoryList::APP)->getAbsolutePath($path);
    }

    protected function prepareToXML($document, $parent_key = null, $level = 0)
    {
        $content = '';
        foreach ($document as $key => $value) {
            if (!is_string($key)) {
                $key = 'item';
            }
            $tab_level = str_repeat('	', $level + 1);
            if (is_array($value)) {
                $value = $this->prepareToXML($value, $key, $level + 1);
                $content .= sprintf('%1$s<%2$s>
%3$s%1$s</%2$s>
', $tab_level, $key, $value);
            } else {
                if (preg_match('/[<>&]/i', $value)) {
                    $value = '<![CDATA[' . $value . ']]>';
                }
                $content .= sprintf('%1$s<%2$s>%3$s</%2$s>
', $tab_level, $key, $value);
            }
        }
        return $content;
    }

    public function getConfig($identifiers)
    {
        if (is_array($identifiers) && empty($identifiers)) {
            return;
        }
        $_identifiers = $identifiers;
        foreach ($_identifiers as $__identifier) {
            $reg_exp = '/^' . str_replace('/', '\/', $__identifier) . '\//i';
            foreach ($identifiers as $key => $identifier) {
                if (is_string($identifier)) {
                    if (preg_match($reg_exp, $identifier)) {
                        unset($identifiers[$key]);
                    } else {
                        $identifiers[$key] = explode('/', $identifier);
                    }
                }
            }
        }
        unset($_identifiers);
        $scopeConfig = $this->getScopeConfig();
        $config = [];
        foreach ($identifiers as $identifier) {
            $config = $this->mergeConfig($config, $this->_getSectionConfig($scopeConfig, $identifier));
        }

        return $config;
    }

    public function getScopeConfig()
    {
        $scopeConfig = $this->_loadObject(ScopeConfigInterface::class);
        if ($websiteId = $this->getRequest()->getParam('website')) {
            return $scopeConfig->getValue('', ScopeInterface::SCOPE_WEBSITE, $websiteId);
        }
        if ($storeId = $this->getRequest()->getParam('store')) {
            return $scopeConfig->getValue('', ScopeInterface::SCOPE_STORE, $storeId);
        }
        return $scopeConfig->getValue('');
    }

    private function mergeConfig($array1, $array2)
    {
        foreach ($array2 as $key => $value) {
            if (array_key_exists($key, $array1) && is_array($array1[$key]) && is_array($value)) {
                $value = $this->mergeConfig($array1[$key], $value);
            }
            $array1[$key] = $value;
        }
        return $array1;
    }

    private function _getSectionConfig($section, $key)
    {
        $_section = [];
        if (is_array($section) && !empty($section) && is_array($key) && !empty($key)) {
            $_key = array_shift($key);
            $value = null;
            if (array_key_exists($_key, $section)) {
                $value = $section[$_key];
            }
            if (!empty($key)) {
                $_section[$_key] = $this->_getSectionConfig($value, $key);
            } else {
                $_section[$_key] = $value;
            }
        }
        return $_section;
    }

    public function getBlocks($identifiers)
    {
        if (is_array($identifiers) && empty($identifiers)) {
            return;
        }
        $model = $this->_loadObject(\Magento\Cms\Model\ResourceModel\Block\CollectionFactory::class);
        $itemCollection = $model->create()->addFieldToFilter('identifier', ['in' => [$identifiers]]);
        $items = [];
        if (count($itemCollection) > 0) {
            foreach ($itemCollection as $item) {
                $item = $item->getData();
                $items[] = $this->removeAttrs($item, ['_first_store_id', 'block_id', 'creation_time', 'is_active', 'store_code', 'store_id', 'update_time']);
            }
        }

        return $items;
    }

    private function removeAttrs($array, $attrs = [])
    {
        $_array = [];
        foreach ($array as $key => $value) {
            if (!in_array($key, $attrs)) {
                $_array[$key] = $value;
            }
        }
        return $_array;
    }

    public function getPages($identifiers)
    {
        if (is_array($identifiers) && empty($identifiers)) {
            return;
        }
        $model = $this->_loadObject(\Magento\Cms\Model\ResourceModel\Page\CollectionFactory::class);
        $itemCollection = $model->create()->addFieldToFilter('identifier', ['in' => [$identifiers]]);
        $items = [];
        if (count($itemCollection) > 0) {
            foreach ($itemCollection as $item) {
                $item = $item->getData();
                $items[] = $this->removeAttrs($item, ['_first_store_id', 'creation_time', 'is_active', 'page_id', 'store_code', 'store_id', 'update_time']);
            }
        }

        return $items;
    }

    public function getBannersliders($identifiers)
    {
        if (is_array($identifiers) && empty($identifiers)) {
            return;
        }
        $model = $this->_loadObject(\Olegnax\BannerSlider\Model\ResourceModel\Group\CollectionFactory::class);
        $itemCollection = $model->create()->addFieldToFilter('identifier', ['in' => [$identifiers]]);
        $items = [];
        if (count($itemCollection) > 0) {
            foreach ($itemCollection as $item) {
                $item = $item->getData();
                $item['slides'] = $this->_getBannersliders($item['group_id']);
                $items[] = $this->removeAttrs($item, ['id', 'group_id', 'created_time', 'update_time']);
            }
        }

        return $items;
    }

    private function _getBannersliders($id)
    {
        $model = $this->_loadObject(CollectionFactory::class);
        $itemCollection = $model->create()->addFieldToFilter('slide_group', $id);
        $items = [];
        if (count($itemCollection) > 0) {
            foreach ($itemCollection as $item) {
                $item = $item->getData();
                $items[] = $this->removeAttrs($item, ['id', 'slider_id', 'store_id', 'slide_group', 'status', 'created_time', 'update_time']);
            }
        }

        return $items;
    }

    public function getCarousels($identifiers)
    {
        if (is_array($identifiers) && empty($identifiers)) {
            return;
        }
        $model = $this->_loadObject(\Olegnax\Carousel\Model\ResourceModel\Carousel\CollectionFactory::class);
        $itemCollection = $model->create()->addFieldToFilter('identifier', ['in' => [$identifiers]]);
        $items = [];
        if (count($itemCollection) > 0) {
            foreach ($itemCollection as $item) {
                $item = $item->getData();
                $item['slides'] = $this->_getCarousels($item['identifier']);
                $items[] = $this->removeAttrs($item, ['carousel_id', 'creation_time', 'update_time']);
            }
        }

        return $items;
    }

    private function _getCarousels($id)
    {
        $model = $this->_loadObject(\Olegnax\Carousel\Model\ResourceModel\Slide\CollectionFactory::class);
        $itemCollection = $model->create()->addFieldToFilter('carousel', $id);
        $items = [];
        if (count($itemCollection) > 0) {
            foreach ($itemCollection as $item) {
                $item = $item->getData();
                $items[] = $this->removeAttrs($item, ['slide_id', 'store_id', 'creation_time', 'update_time']);
            }
        }

        return $items;
    }

}
