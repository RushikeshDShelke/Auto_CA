<?php

namespace Cminds\Marketplace\Block\Supplier;

use Cminds\Marketplace\Model\Rating;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;

/**
 * Cminds Marketplace rated supplier block.
 *
 * @category Cminds
 * @package  Cminds_Marketplace
 */
class Rated extends Template
{
    /**
     * Customer session object.
     *
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * Customer repository object.
     *
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Rating object.
     *
     * @var Rating
     */
    protected $rating;

    /**
     * Object constructor.
     *
     * @param Context                     $context            Context object.
     * @param CustomerSession             $customerSession    Customer session object.
     * @param CustomerRepositoryInterface $customerRepository Customer repository object.
     * @param Rating                      $rating             Rating object.
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        CustomerRepositoryInterface $customerRepository,
        Rating $rating
    ) {
        parent::__construct($context);

        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->rating = $rating;
    }

    /**
     * Return currently logged in customer data object.
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getLoggedInCustomer()
    {
        if ($this->customerSession->isLoggedIn()) {
            return $this->getCustomerById($this->customerSession->getId());
        }

        throw new \Magento\Framework\Exception\LocalizedException(
            __('Customer is not logged in or not found.')
        );
    }

    /**
     * Return rates collection.
     *
     * @return \Cminds\Marketplace\Model\Resource\Rating\Collection
     */
    public function getRates()
    {
        $collection = $this->rating
            ->getCollection()
            ->addFieldToFilter(
                'customer_id',
                $this->getLoggedInCustomer()->getId()
            );

        return $collection;
    }

    /**
     * Return customer data object by id.
     *
     * @param int $customerId Customer id.
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function getCustomerById($customerId)
    {
        return $this->customerRepository->getById($customerId);
    }
}
