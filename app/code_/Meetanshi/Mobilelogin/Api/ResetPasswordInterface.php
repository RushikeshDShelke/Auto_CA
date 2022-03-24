<?php

namespace Meetanshi\Mobilelogin\Api;


interface ResetPasswordInterface
{

    /**
     * GET for Post api
     * @param string $mobilenumber
     * @param string $password
     * @return string
     */

    public function getPost($mobilenumber, $password);

}
