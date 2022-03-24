<?php
namespace Magecomp\Ordercomment\Plugin\Block\Adminhtml;

use Magecomp\Ordercomment\Helper\Data\Ordercomment;
use Magento\Sales\Block\Adminhtml\Order\View\Info;

class Salesorderviewinfo
{
    public function afterToHtml(Info $subject, $result)
    {
        $commentBlock = $subject->getLayout()->getBlock('magecompcommerce_order_comments');
        if ($commentBlock !== false && $subject->getNameInLayout() == 'order_info') {
            $commentBlock->setOrdercomment($subject->getOrder()->getData(Ordercomment::COMMENT_FIELD_NAME));
            $result = $result . $commentBlock->toHtml();
        }
        return $result;
    }
}
