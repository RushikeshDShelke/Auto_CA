<?php
namespace Magecomp\Ordercomment\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magecomp\Ordercomment\Helper\Data\Ordercomment;
use Magento\Store\Model\StoreManagerInterface;

class Ordercommentconfigprovider implements ConfigProviderInterface
{
    private $scopeConfig;
    private $storeManager;
    private $helperOrderComment;

    public function __construct(ScopeConfigInterface $scopeConfig, Ordercomment $helperOrderComment, StoreManagerInterface $storeManager)
    {
        $this->scopeConfig = $scopeConfig;
        $this->helperOrderComment = $helperOrderComment;
        $this->storeManager = $storeManager;
    }

    public function getConfig()
    {
        return [
            'max_length' => (int)$this->helperOrderComment->getMaxLength($this->storeManager->getStore()->getId()),
            'comment_initial_defualt_show' => (int)$this->helperOrderComment->isDefaultShow($this->storeManager->getStore()->getId()),
            'checkout_title' => $this->helperOrderComment->getCheckoutTitle($this->storeManager->getStore()->getId()),
            'is_ordercomment_enable' => (int) $this->helperOrderComment->isEnabled($this->storeManager->getStore()->getId())
        ];
    }
}
