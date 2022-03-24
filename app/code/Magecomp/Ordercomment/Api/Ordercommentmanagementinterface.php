<?php
namespace Magecomp\Ordercomment\Api;

interface Ordercommentmanagementinterface
{
    /**
     * @param int $cartId
     * @param \Magecomp\Ordercomment\Api\Data\Ordercommentinterface $orderComment
     * @return string
     */
    public function saveOrdercomment(
        $cartId,
        \Magecomp\Ordercomment\Api\Data\Ordercommentinterface $orderComment
    );
}

