<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */
 
namespace Ulmod\OrderComment\Plugin\Block\Adminhtml;

use Ulmod\OrderComment\Model\Data\OrderComment;
use Magento\Sales\Block\Adminhtml\Order\View\Info as ViewInfo;

class SalesOrderViewInfo
{
    /**
     * @param ViewInfo $subject
     * @param string $result
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterToHtml(
        ViewInfo $subject,
        $result
    ) {
        $commentBlock = $subject->getLayout()
            ->getBlock('order_comments');
            
        if ($commentBlock !== false) {
            $commentBlock->setOrderComment($subject->getOrder()
                ->getData(OrderComment::COMMENT_FIELD_NAME));
            $result = $result . $commentBlock->toHtml();
        }
        
        return $result;
    }
}
