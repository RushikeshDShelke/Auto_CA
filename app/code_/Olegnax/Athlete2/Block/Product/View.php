<?php

namespace Olegnax\Athlete2\Block\Product;

use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filter\Template;
use Magento\Store\Model\StoreManagerInterface;

class View extends \Magento\Catalog\Block\Product\View
{

    /**
     *
     * @var ObjectManager
     */
    public $objectManager;

    protected function _toHtml()
    {
        if ($this->allowShowTab()) {
            $blockId = $this->getData('block');
            $attributeCode = $this->getData('attribute');
            if (!empty($blockId)) {
                return $this->getStaticBlockContent($blockId);
            }

            if (!empty($attributeCode)) {
                return $this->getAttributeContent($attributeCode);
            }
        }

        return '';
    }

    protected function allowShowTab()
    {
        $product_skus = $this->getData('product_skus');
        $category_ids = $this->getData('category_ids');
        if (empty($category_ids) && empty($product_skus)) {
            return true;
        }
        $product = $this->getProduct();
        if (!empty($product_skus)) {
            $product_skus = array_filter(explode(',', $product_skus));
            $sku = $product->getSku();
            $id = $product->getId();
            if (in_array($sku, $product_skus) || in_array($id, $product_skus)) {
                return true;
            }
        }
        if (!empty($category_ids)) {
            $category_ids = array_filter(explode(',', $category_ids));
            $product_cats = [];
            foreach ($product->getCategoryCollection() as $product_cat) {
                $product_cats[] = $product_cat->getId();
            }
            if (0 < count(array_intersect($category_ids, $product_cats))) {
                return true;
            }
        }

        return false;
    }

    protected function getStaticBlockContent($blockId)
    {
        $store_id = $this->getStoreId();
        /** @var BlockFactory $blockFactory */
        $blockFactory = $this->_loadObject(BlockFactory::class);
        $block = $blockFactory->create()->setStoreId($store_id)->load($blockId);
        $content = '';
        if ($block) {
            $block_content = $block->getContent();
            if ($block_content) {
                $content = $this->getBlockTemplateProcessor($block_content, $store_id);
            }
        }

        return $content;
    }

    public function getStoreId()
    {
        return $this->_loadObject(StoreManagerInterface::class)->getStore()->getId();
    }

    protected function _loadObject($object)
    {
        return ObjectManager::getInstance()->get($object);
    }

    public function getBlockTemplateProcessor($content = '', $store_id = null)
    {
        $content = htmlspecialchars_decode($content);
        $content = trim($content);
        /** @var Template $filter */
        $filter = $this->_loadObject(FilterProvider::class)->getBlockFilter();
        if (!empty($store_id)) {
            $filter = $filter->setStoreId($store_id);
        }

        $content = $filter->filter($content);

        return $content;
    }

    protected function getAttributeContent($attributeCode)
    {
        $product = $this->getProduct();
        $attribute = $product->getResource()->getAttribute($attributeCode);
        $content = '';
        if ($attribute) {
            $attr_value = $attribute->getFrontend()->getValue($product);
            if ($attr_value) {
                $content = $this->getBlockTemplateProcessor($attr_value, $this->getStoreId());
            }
        }
        return $content;
    }

}
