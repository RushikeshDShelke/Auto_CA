<?php

namespace Olegnax\Athlete2\Model\DynamicStyle;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface as ViewLayout;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Olegnax\Athlete2\Block\Template;
use Olegnax\Athlete2\Helper\CssFiles;
use Olegnax\Athlete2\Helper\Helper;

class Generator
{

    /**
     * Store Manager
     *
     * @var StoreManager
     */
    private $_storeManager;

    /**
     * Theme css files
     *
     * @var CssFiles
     */
    private $_cssFiles;

    /**
     * Registry
     *
     * @var Registry
     */
    private $_registry;

    /**
     * File
     *
     * @var File
     */
    private $_file;

    /**
     * View Layout
     *
     * @var ViewLayout
     */
    private $_viewLayout;

    /**
     * Store Manager
     *
     * @var MessageManager
     */
    private $_messageManager;
    /**
     * @var Helper
     */
    private $_helper;

    /**
     * Generator constructor.
     * @param StoreManager $storeManager
     * @param CssFiles $cssFiles
     * @param Registry $registry
     * @param File $file
     * @param ViewLayout $viewLayout
     * @param MessageManager $messageManager
     * @param Helper $helper
     */
    public function __construct(
        StoreManager $storeManager,
        CssFiles $cssFiles,
        Registry $registry,
        File $file,
        ViewLayout $viewLayout,
        MessageManager $messageManager,
        Helper $helper
    ) {
        $this->_storeManager = $storeManager;
        $this->_cssFiles = $cssFiles;
        $this->_registry = $registry;
        $this->_file = $file;
        $this->_viewLayout = $viewLayout;
        $this->_messageManager = $messageManager;
        $this->_helper = $helper;
    }

    public function generate($type, $website, $store)
    {
        $this->generateCSS($type, $website, $store);
    }

    public function generateCSS($type, $website, $store)
    {
        if (!empty($website)) {
            $this->generateWebsite($type, $website, 'css');
        } elseif (!empty($store)) {
            $this->generateStore($type, $store, 'css');
        } else {
            $this->generateAll($type, 'css');
        }
    }

    private function generateWebsite($type, $id, $format)
    {
        $website = $this->_storeManager->getWebsite($id);
        $stores = $website->getStoreIds();
        if (!empty($stores) && is_array($stores)) {
            foreach ($stores as $store) {
                $this->generateStore($type, $store, $format);
            }
        }
    }

    private function generateStore($type, $id, $format)
    {
        $store = $this->_storeManager->getStore($id);
        if (!$store->isActive()) {
            return;
        }
        $storeCode = $store->getCode();
        $dynamicTemplate = sprintf('dynamic_%s/%s.phtml', $format, $type);
        $dynamicFile = sprintf('%s/%s_%s.%s', $this->_cssFiles->getDymanicDir(), $type, $storeCode, $format);

        try {
            $this->_file->createDirectory(dirname($dynamicFile), 0775);
            $block = $this->_viewLayout->getBlock($type . $storeCode . $format);
            if (!$block) {
                $block = $this->_viewLayout->createBlock(
                    Template::class,
                    $type . $storeCode . $format
                );
            }
            $content = $block->setData([
                'area' => 'frontend',
                'dynamic_store_code' => $storeCode
            ])
                ->setTemplate($dynamicTemplate)
                ->toHtml();

            if (!empty($content)) {
                $content = preg_replace('/[\r\n\t]/', ' ', $content);
                $content = preg_replace('/[\r\n\t ]{2,}/', ' ', $content);
                $content = preg_replace('/\s+(\:|\;|\{|\})\s+/', '\1', $content);
                $content = preg_replace('/<[^<>]+>(.*?)<\/[^<>]+>/m', '/* Forbidden tags in styles */', $content);
            }
            $this->_file->filePutContents($dynamicFile, $content);

            $dynamic_file_arg = $this->_helper->getSystemValue(
                'athlete2_design/appearance_custom/dynamic_file_arg'
            );
            $dynamic_file_arg = abs((int) $dynamic_file_arg);
            $dynamic_file_arg++;
            $this->_helper->setSystemValue(
                'athlete2_design/appearance_custom/dynamic_file_arg',
                $dynamic_file_arg,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT
            );
        } catch (Exception $e) {
            $this->_messageManager->addError(sprintf(__('Failed generaing file: %s<br/>Message: %s'),
                str_replace(BP, '', $dynamicFile), $e->getMessage()));
        }
    }

    private function generateAll($type, $format)
    {
        $websites = $this->_storeManager->getWebsites(false, false);
        if (!empty($websites) && is_array($websites)) {
            foreach ($websites as $website => $value) {
                $this->generateWebsite($type, $website, $format);
            }
        }
    }
}
