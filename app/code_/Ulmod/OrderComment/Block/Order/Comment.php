<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderComment\Block\Order;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Ulmod\OrderComment\Model\Data\OrderComment;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Comment extends Template
{
    /**
     *  Config Path
     */
    const XML_PATH_GENERAL_IS_SHOW_IN_MYACCOUNT = 'ordercomment/general/is_show_in_myaccount';
	
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
	
    /**
     * @var Registry
     */
    protected $coreRegistry = null;

    /**
     * @param	Context $context
     * @param	Registry $registry
     * @param	ScopeConfigInterface $scopeConfig
     * @param   array $data
     */	
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $scopeConfig,		
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->scopeConfig = $scopeConfig;		
        $this->_isScopePrivate = true;
        $this->_template = 'order/view/comment.phtml';
        parent::__construct($context, $data);
    }
    
    /**
     * Check if show order comment to customer account
     *
     * @return bool
     */
    public function isShowCommentInAccount()
    {
          return $this->scopeConfig->getValue(
              self::XML_PATH_GENERAL_IS_SHOW_IN_MYACCOUNT,
              ScopeInterface::SCOPE_STORE
          );
    }
	
    /**
     * Get Order
     *
     * @return array|null
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }

    /**
     * Get Order Comment
     *
     * @return string
     */
    public function getOrderComment()
    {
        return trim($this->getOrder()->getData(OrderComment::COMMENT_FIELD_NAME));
    }

    /**
     * Retrieve html comment
     *
     * @return string
     */
    public function getOrderCommentHtml()
    {
        return nl2br($this->escapeHtml($this->getOrderComment()));
    }

    /**
     * Check if has order comment
     *
     * @return bool
     */
    public function hasOrderComment()
    {
        return strlen($this->getOrderComment()) > 0;
    }
}
