<?php

namespace Meetanshi\Mobilelogin\Model\Api;

use Meetanshi\Mobilelogin\Helper\ApiData;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class PostManagement
 * @package Meetanshi\Mobilelogin\Model\Api
 */
class PostManagement
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
     * PostManagement constructor.
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

    public function getPost($mobilenumber, $otptype, $resendotp, $oldmobile)
    {
        $data = ["mobilenumber" => $mobilenumber,
            "otptype" => $otptype,
            "resendotp" => $resendotp,
            "oldmobile" => $oldmobile];

        $response = $this->helper->otpSave($data);
        $returnArr = json_encode($response);
        return $returnArr;
    }
}
