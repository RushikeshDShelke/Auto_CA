<?php
namespace Magecomp\Ordercomment\Api;

interface Guestordercommentmanagementinterface
{
    /**
     * @param string $cartId
     * @param \Magecomp\Ordercomment\Api\Data\Ordercommentinterface $orderComment
     * @return \Magento\Checkout\Api\Data\PaymentDetailsInterface
     */
    public function saveOrdercomment(
        $cartId,
        \Magecomp\Ordercomment\Api\Data\Ordercommentinterface $orderComment
    );
}


