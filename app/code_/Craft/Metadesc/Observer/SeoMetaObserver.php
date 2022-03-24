<?php
namespace Craft\Metadesc\Observer;

use Magento\Framework\Event\ObserverInterface;

class SeoMetaObserver implements ObserverInterface
{
    const XML_PRODUCT_AUTO_METADESCRIPTION = 'catalog/fields_masks/meta_description';
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * SeoMetaObserver constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getProduct();
        $metaDesc = trim($product->getMetaDescription());
        if ($metaDesc == '') {
            if ($product->getDescription() != '') {
                $metaDesc = substr(strip_tags($product->getDescription()),12); //If no SEO Meta Description is set in the product’s information use the Short Description
			
			} else {
                $configMeta = $this->scopeConfig->getValue(self::XML_PRODUCT_AUTO_METADESCRIPTION, \Magento\Store\Model\ScopeInterface::SCOPE_STORE); //If no Short Description is set in the product’s information use configuration settings for the Meta Description
                $string = str_replace("{{","",str_replace("}}","",$configMeta));
                $finalMeta = explode(' ', $string);
                $i = 0;
                foreach ($finalMeta as $meta) {
                    if ($i == 0) {
                        $metaDesc .= $product->getData($meta);
                    } else {
                        $metaDesc .= ' ' . $product->getData($meta);
                    }
                    $i++;
                }
            }
        }
        $product->setMetaDescription($metaDesc);
    }
}