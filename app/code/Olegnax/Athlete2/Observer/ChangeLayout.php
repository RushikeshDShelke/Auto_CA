<?php
/**
 * Set layout
 *
 * @category    Olegnax
 * @package     Olegnax_Athlete2
 * @copyright   Copyright (c) 2021 Olegnax (http://www.olegnax.com/)
 * @license     https://www.olegnax.com/license
 */

namespace Olegnax\Athlete2\Observer;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\View\Page\Config;
use Olegnax\Athlete2\Helper\Helper;

class ChangeLayout implements ObserverInterface
{

    /**
     * @var Config
     */
    protected $config;
    /**
     * @var RequestInterface
     */
    private $_request;
    /**
     * @var Helper
     */
    private $_helper;
    /**
     * @var Registry
     */
    private $_registry;
    /**
     * @var PageRepositoryInterface
     */
    private $_pageRepository;

    /**
     * ChangeLayout constructor.
     * @param Config $config
     * @param RequestInterface $request
     * @param Registry $registry
     * @param PageRepositoryInterface $pageRepository
     * @param Helper $helper
     */
    public function __construct(
        Config $config,
        RequestInterface $request,
        Registry $registry,
        PageRepositoryInterface $pageRepository,
        Helper $helper
    ) {
        $this->config = $config;
        $this->_request = $request;
        $this->_registry = $registry;
        $this->_pageRepository = $pageRepository;
        $this->_helper = $helper;
    }

    /**
     * @param Observer $observer
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        if (!$this->_helper->getSystemValue(Helper::XML_ENABLED)) {
            return;
        }
        $variable = '';
        $actionName = $this->_request->getFullActionName();
        switch ($actionName) {
            case 'catalog_category_view':
                $category = $this->getCurrentCategory();
                if ($category) {
                    $variable = $category->getData('page_layout');
                }

                if (($category
                        && !$category->getData('custom_use_parent_settings')
                        && empty($variable)
                    )
                    || !$category
                ) {
                    $variable = $this->getModuleConfig('products_listing/catalog_page_layout');
                }
                break;
            case 'cms_page_view':
                $page = $this->getCurrentPage();
                if ($page) {
                    $variable = $page->getData('page_layout');
                }

                if (empty($variable) || 'empty' !== $variable) {
                    $variable = $this->getModuleConfig('cms_pages/cms_page_layout');
                }
                break;
            case 'catalogsearch_result_index':
                $variable = $this->getModuleConfig('products_listing/search_results_layout');
                break;
            case 'catalog_product_view':
                $product = $this->getCurrentProduct();
                if ($product) {
                    $variable = $product->getData('page_layout');
                }

                if (empty($variable)) {
                    $variable = $this->getModuleConfig('product/product_page_layout');
                }
                break;
            case 'blog_index_index':
            case 'blog_search_index':
            case 'blog_archive_view':
                $variable = $this->getModuleConfig('blog/blog_list_page_layout');
                break;
        }

        if (!empty($variable)) {
            $this->config->setPageLayout($variable);
        }
    }

    /**
     * @return Category
     */
    protected function getCurrentCategory()
    {
        return $this->_registry->registry('current_category') ?: $this->_registry->registry('category');
    }

    /**
     * @param string $path
     * @return mixed
     */
    protected function getModuleConfig($path = '')
    {
        return $this->_helper->getModuleConfig($path);
    }

    /**
     * @return PageInterface
     */
    protected function getCurrentPage()
    {
        try {
            $pageId = $this->_request->getParam('page_id', $this->_request->getParam('id', false));
            if ($pageId) {
                return $this->_pageRepository->getById($pageId);
            }
        } catch (LocalizedException $e) {
            return null;
        }

        return null;
    }

    /**
     * @return Product
     */
    public function getCurrentProduct()
    {
        return $this->_registry->registry('current_product') ?: $this->_registry->registry('product');
    }
}
