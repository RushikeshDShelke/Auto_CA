<?php

namespace Meetanshi\Mobilelogin\Controller\Otp;

use Magento\Customer\Controller\AbstractAccount;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Meetanshi\Mobilelogin\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Resetpassword
 * @package Meetanshi\Mobilelogin\Controller\Otp
 */
class Resetpassword extends AbstractAccount
{
    /**
     * @var Data
     */
    private $helper;
    /**
     * @var
     */
    protected $mobileloginFactory;
    /**
     * @var JsonFactory
     */
    private $jsonFactory;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Resetpassword constructor.
     * @param Context $context
     * @param Data $helperData
     * @param JsonFactory $jsonFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        Data $helperData,
        JsonFactory $jsonFactory,
        StoreManagerInterface $storeManager
    ) {
    
        $this->helper = $helperData;
        $this->jsonFactory = $jsonFactory;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result = $this->jsonFactory->create();
        $response = [
            'succeess' => "true",
            'errormsg' => "Something went wrong.",
            'successmesg' => "",
            'customurl' => ''
        ];

        try {
            $param = $this->getRequest()->getParams();

            $customer = $this->helper->getCustomerCollectionMobile($param['mobilenumber']);
            if ($customer->getId()) {
                $customer->setMobileNumber($param['mobilenumber']);
                $customer->setRpToken($customer->getRpToken());
                $customer->setPassword($param['password']);
                $customer->save();
                $response['customurl'] = $this->storeManager->getStore()->getUrl('customer/account/login');
                $response['successmsg'] = 'Password has been changed successfully. You can now login with your new credentials.';
            } else {
                $response['errormsg'] = 'Password change error, please try again.';
                $response['succeess'] = "false";
            }

            $result->setData($response);
            return $result;
        } catch (\Exception $e) {
            $response['errormsg'] = 'Password change error, please try again.';
            $response['succeess'] = "false";
            $result->setData($response);
            return $result;
        }
    }
}
