<?php

namespace Cminds\Marketplace\Model\Supplier;

use Magento\Checkout\Model\Session as CheckoutSession;

/**
 * Cminds Marketplace supplier quote model.
 *
 * @category Cminds
 * @package  Cminds_Marketplace
 * @author   Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
class Quote
{
    /**
     * Checkout session object.
     *
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * Supplier items.
     *
     * @var array
     */
    protected $supplierItems;

    /**
     * Object constructor.
     *
     * @param CheckoutSession $checkoutSession Checkout session object.
     */
    public function __construct(
        CheckoutSession $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Return supplier cart items.
     * Array will be returned where the main key is supplier id
     * and value is sub array with quote items.
     *
     * @return array
     */
    public function getSupplierItems()
    {
        if ($this->supplierItems === null) {
            $items = $this->checkoutSession->getQuote()->getAllVisibleItems();
            $supplierItems = [];

            foreach ($items as $item) {
                $supplierId = $item->getProduct()->getCreatorId();
                if (!$supplierId) {
                    continue;
                }

                $supplierItems[$supplierId][] = $item;
            }

            $this->supplierItems = $supplierItems;
        }

        return $this->supplierItems;
    }

    /**
     * Return supplier ids.
     *
     * @return array
     */
    public function getSupplierIds()
    {
        $supplierItems = $this->getSupplierItems();
        $supplierIds = array_keys($supplierItems);

        return $supplierIds;
    }
}