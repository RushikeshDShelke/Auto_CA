<?php

namespace Cminds\Marketplace\Model\Plugin;

use Braintree\Exception;
use DateTime;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Framework\App\RequestInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Cminds\Marketplace\Model\Fields;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class SupplierCreate
{
    private $request;
    private $customerRepository;
    private $fields;
    private $localeDate;

    public function __construct(
        RequestInterface $request,
        CustomerRepositoryInterface $customerRepository,
        Fields $fields,
        TimezoneInterface $localeDate
    ) {
        $this->request = $request;
        $this->customerRepository = $customerRepository;
        $this->fields = $fields;
        $this->localeDate = $localeDate;
    }

    public function beforeCreateAccount(
        AccountManagement $accountManagement,
        CustomerInterface $customer,
        $password = null,
        $redirectUrl = ""
    ) {
        $supplierCustomAttributes = $this->request->getParam('supplier_custom_attribute');
        if (!$supplierCustomAttributes) {
            return [$customer, $password, $redirectUrl];
        }

        $postSupplierCustomAttributes = $this->request->getParam('supplier_custom_attribute');
        $supplierCustomAttributesToApprove = [];
        $supplierCustomAttributes = [];

        $fields = $this->fields->getCollection();
        foreach ($postSupplierCustomAttributes as $customAttributeKey => $customerAttributeValue) {
            foreach ($fields as $field) {
                if ($field->getName() === $customAttributeKey) {
                    $this->validateValue($field, $customerAttributeValue);

                    if ($field->getMustBeApproved() && !$field->getIsSystem()) {
                        $supplierCustomAttributesToApprove[] = [
                            'name' => $customAttributeKey,
                            'value' => $customerAttributeValue
                        ];
                    } else {
                        $supplierCustomAttributes[] = [
                            'name' => $customAttributeKey,
                            'value' => $customerAttributeValue
                        ];
                    }
                }
            }
        }

        if (!empty($supplierCustomAttributesToApprove)) {
            $customer
                ->setCustomAttribute(
                    'new_custom_fields_values',
                    serialize($supplierCustomAttributesToApprove));
        }

        $customer
            ->setCustomAttribute(
                'custom_fields_values',
                serialize($supplierCustomAttributes));

        return [$customer, $password, $redirectUrl];
    }

    /**
     * Validate values, which were inserted from the form.
     *
     * @param $field
     * @param $attributeValue
     *
     * @return $this
     * @throws \Exception
     */
    private function validateValue($field, $attributeValue)
    {
        if ($field->getType() !== 'date') {
            if ($field->getIsRequired()) {
                if (!$attributeValue) {
                    throw new \Exception(__('Something wrong happened while creating the supplier'));
                }
            }
        } else {
            if (!$field->getIsRequired() && !$attributeValue) {
                return $this;
            }

            if (!$this->validateDate($attributeValue)) {
                throw new \Exception(__('Something wrong happened while creating the supplier'));
            }
        }

        return $this;
    }

    /**
     * Validate if the date has correct format.
     *
     * @param $date
     *
     * @return bool
     */
    function validateDate($date)
    {
        list($m, $d, $y) = explode('/', $date);
        if(checkdate($m, $d, $y)){
            return true;
        }

        return false;
    }
}
