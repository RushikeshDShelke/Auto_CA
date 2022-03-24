<?php

namespace Meetanshi\Mobilelogin\Api;


interface AccountCreateInterface
{


    /**
     * GET for Post api
     * @param string $mobile
     * @param string $password
     * @param string $firstname
     * @param string $lastname
     * @param string $email
     * @return string
     */

    public function getPost($mobile, $password,$firstname,$lastname,$email);
}
