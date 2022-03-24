<?php
namespace Magecomp\Ordercomment\Helper\Data;

use Magecomp\Ordercomment\Api\Data\Ordercommentinterface;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Ordercomment extends AbstractSimpleObject implements Ordercommentinterface
{
    const ORDERCOMMENT_IS_ENABLED = 'ordcomments/general/enable';
    const ORDERCOMMENT_MAX_LENGTH = 'ordcomments/ordercomments/max_length';
    const ORDERCOMMENT_FIELD_DEFAULT_SHOW = 'ordcomments/ordercomments/defualt_show';
    const ORDERCOMMENT_FIELD_CHECKOUT_TITLE = 'ordcomments/ordercomments/checkouttitle';
    const COMMENT_FIELD_NAME = 'magecomp_order_comment';

    protected $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig, array $data = []) {
        parent::__construct($data);
        $this->scopeConfig = $scopeConfig;
    }

    public function isEnabled($storeId = NULL){
        return $this->scopeConfig->getValue(self::ORDERCOMMENT_IS_ENABLED, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getMaxLength($storeId = NULL){
        return $this->isEnabled($storeId) ? $this->scopeConfig->getValue(self::ORDERCOMMENT_MAX_LENGTH, ScopeInterface::SCOPE_STORE, $storeId) : false;
    }

    public function isDefaultShow($storeId = NULL){
        return $this->isEnabled($storeId) ? $this->scopeConfig->getValue(self::ORDERCOMMENT_FIELD_DEFAULT_SHOW, ScopeInterface::SCOPE_STORE, $storeId) : false;
    }

    public function getCheckoutTitle($storeId = NULL){
        return $this->isEnabled($storeId) ? $this->scopeConfig->getValue(self::ORDERCOMMENT_FIELD_CHECKOUT_TITLE, ScopeInterface::SCOPE_STORE, $storeId) : false;
    }

    public function getComment($storeId = NULL) {
        return $this->isEnabled($storeId) ? $this->_get(static::COMMENT_FIELD_NAME) : false;
    }

    public function setComment($comment) {
        return $this->isEnabled() ? $this->setData(static::COMMENT_FIELD_NAME, $comment): false;
    }

    public function getTemplate() {
        $template = $this->isEnabled() ? 'Magecomp_Ordercomment::order/view/comments.phtml' : 'Magento_Sales::email/items.phtml';
        return $template;
    }
}
