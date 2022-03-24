<?php

namespace Meetanshi\Mobilelogin\Api;


interface OtpVerifyInterface
{

    /**
     * GET for Post api
     * @param string $mobilenumber
     * @param string $otptype
     * @param string $otpcode
     * @param string $oldmobile
     * @return string
     */

    public function getPost($mobilenumber, $otptype, $otpcode, $oldmobile);
}
