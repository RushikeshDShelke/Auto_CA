<?php

namespace Cminds\Marketplace\Block\Supplier\Rate;

use Cminds\Marketplace\Model\Rating;
use Cminds\Marketplace\Model\Torate;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Exception\LocalizedException;

class Ratelist extends Template
{
    protected $customerSession;
    protected $customerFactory;
    protected $rating;
    protected $torate;

    /**
     * Object constructor.
     *
     * @param Context         $context         Context object.
     * @param CustomerSession $customerSession Customer session object.
     * @param CustomerFactory $customerFactory Customer factory object.
     * @param Rating          $rating          Rating object.
     * @param Torate          $torate          To rate object.
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        CustomerFactory $customerFactory,
        Rating $rating,
        Torate $torate
    ) {
        $this->_isScopePrivate = true;

        $this->customerSession = $customerSession;
        $this->customerFactory = $customerFactory;
        $this->rating = $rating;
        $this->torate = $torate;

        parent::__construct($context);
    }

    /**
     * Retrieve logged in customer.
     *
     * @return Customer
     * @throws LocalizedException
     */
    public function getCustomer()
    {
        if ($this->customerSession->isLoggedIn()) {
            return $this->customerFactory->create()
                ->load($this->customerSession->getId());
        }

        throw new LocalizedException(__('No user is logged in.'));
    }

    /**
     * Return suppliers collection.
     *
     * @return \Cminds\Marketplace\Model\ResourceModel\Torate\Collection
     * @throws LocalizedException
     */
    public function getAvailableSuppliers()
    {
        $collection = $this->torate
            ->getCollection()
            ->addFieldToFilter(
                'main_table.customer_id',
                $this->getCustomer()->getId()
            );

        return $collection;
    }

    /**
     * Retrieve customer.
     *
     * @param int $id Customer id.
     *
     * @return Customer
     */
    public function getCustomerModel($id)
    {
        return $this->customerFactory->create()->load($id);
    }
}
