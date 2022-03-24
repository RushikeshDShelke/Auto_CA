<?php

namespace Meetanshi\Mobilelogin\Model\Api;

use Meetanshi\Mobilelogin\Helper\ApiData;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ResetPassword
 * @package Meetanshi\Mobilelogin\Model\Api
 */
class ResetPassword
{

    /**
     * @var ApiData
     */
    protected $helper;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * ResetPassword constructor.
     * @param ApiData $helper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ApiData $helper,
        StoreManagerInterface $storeManager
    )
    {
        $this->helper = $helper;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function getPost($mobilenumber, $password)
    {
        $response = [
            'succeess' => "true",
            'errormsg' => "",
            'successmsg' => "",
            'customurl' => ''
        ];

        try {

            $customer = $this->helper->getCustomerCollectionMobile($mobilenumber);
            if ($customer->getId()) {
                $customer->setMobileNumber($mobilenumber);
                $customer->setRpToken($customer->getRpToken());
                $customer->setPassword($password);
                $customer->save();
                $response['customurl'] = $this->storeManager->getStore()->getUrl('customer/account/login');
                $response['successmsg'] = 'Password has been changed successfully. You can now login with your new credentials.';
            } else {
                $response['errormsg'] = 'Password change error, please try again.';
                $response['succeess'] = "false";
            }

            $returnArr = json_encode($response);
            return $returnArr;

        } catch (\Exception $e) {
            $response['errormsg'] = 'Password change error, please try again.';
            $response['succeess'] = "false";

            $returnArr = json_encode($response);
            return $returnArr;
        }
    }
}
