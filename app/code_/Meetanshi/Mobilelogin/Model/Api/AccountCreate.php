<?php

namespace Meetanshi\Mobilelogin\Model\Api;

use Meetanshi\Mobilelogin\Helper\ApiData;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class AccountCreate
 * @package Meetanshi\Mobilelogin\Model\Api
 */
class AccountCreate
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
     * AccountCreate constructor.
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

    public function getPost($mobile, $password, $firstname, $lastname, $email)
    {
        $data = ["mobile" => $mobile,
            "password" => $password,
            "firstname" => $firstname,
            "lastname" => $lastname,
            "email" => $email];

        $response = $this->helper->createPost($data);
        $returnArr = json_encode($response);
        return $returnArr;
    }
}
