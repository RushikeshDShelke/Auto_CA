<?php

namespace Meetanshi\Mobilelogin\Api;


interface AccountLoginInterface
{


    /**
     * GET for Post api
     * @param string $emailmobile
     * @param string $mobpassword
     * @return string
     */

    public function getPost($emailmobile, $mobpassword);
}
