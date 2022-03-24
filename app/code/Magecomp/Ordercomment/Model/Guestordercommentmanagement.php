<?php
namespace Magecomp\Ordercomment\Model;

use Magento\Quote\Model\QuoteIdMaskFactory;
use Magecomp\Ordercomment\Api\Guestordercommentmanagementinterface;
use Magecomp\Ordercomment\Api\Ordercommentmanagementinterface;
use Magecomp\Ordercomment\Api\Data\Ordercommentinterface;

class Guestordercommentmanagement implements Guestordercommentmanagementinterface
{
    protected $quoteIdMaskFactory;
    protected $orderCommentManagement;

    public function __construct(
        QuoteIdMaskFactory $quoteIdMaskFactory,
        Ordercommentmanagementinterface $orderCommentManagement
    )
    {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->orderCommentManagement = $orderCommentManagement;
    }

    public function saveOrdercomment($cartId, Ordercommentinterface $orderComment)
    {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->orderCommentManagement->saveOrdercomment($quoteIdMask->getQuoteId(), $orderComment);
    }
}
