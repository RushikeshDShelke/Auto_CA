<?php

namespace Meetanshi\Mobilelogin\Model\Api;

use Meetanshi\Mobilelogin\Helper\ApiData;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class OtpVerify
 * @package Meetanshi\Mobilelogin\Model\Api
 */
class OtpVerify
{

    /**
     * @var ApiData
     */
    protected $helper;
    /**
     * @var
     */
    protected $storeManager;

    /**
     * OtpVerify constructor.
     * @param ApiData $helper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ApiData $helper,
        StoreManagerInterface $storeManager
    )
    {
        $this->helper = $helper;
    }

    /**
     * @inheritdoc
     */

    public function getPost($mobilenumber, $otptype, $otpcode, $oldmobile)
    {
        $data = ["mobilenumber" => $mobilenumber,
            "otptype" => $otptype,
            "otpcode" => $otpcode,
            "oldmobile" => $oldmobile];

        $response = $this->helper->otpVerify($data);
        $returnArr = json_encode($response);
        return $returnArr;
    }
}
