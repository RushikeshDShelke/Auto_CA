<?php

namespace Meetanshi\Mobilelogin\Api;


interface PostManagementInterface
{


    /**
     * GET for Post api
     * @param string $mobilenumber
     * @param string $otptype
     * @param string $resendotp
     * @param string $oldmobile
     * @return string
     */

    public function getPost($mobilenumber, $otptype, $resendotp, $oldmobile);
}
