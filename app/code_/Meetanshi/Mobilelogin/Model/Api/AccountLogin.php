<?php

namespace Meetanshi\Mobilelogin\Model\Api;

use Meetanshi\Mobilelogin\Helper\ApiData;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class AccountLogin
 * @package Meetanshi\Mobilelogin\Model\Api
 */
class AccountLogin
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
     * AccountLogin constructor.
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

    public function getPost($emailmobile, $mobpassword)
    {
        $data = ["emailmobile" => $emailmobile,
            "mobpassword" => $mobpassword];

        $response = $this->helper->loginPost($data);
        $returnArr = json_encode($response);
        return $returnArr;
    }
}
