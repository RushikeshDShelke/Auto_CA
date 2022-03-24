<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Athlete2
 * @copyright   Copyright (c) 2021 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Athlete2\Plugin\Frontend\Magento\Customer\Model\Account;


use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;

class Redirect
{
    /**
     * @var RedirectInterface
     */
    protected $redirect;
    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        RedirectInterface $redirect,
        RequestInterface $request
    ) {
        $this->redirect = $redirect;
        $this->request = $request;
    }

    public function afterGetRedirectCookie(
        \Magento\Customer\Model\Account\Redirect $subject,
        $result
    ) {
        if (empty($result)) {
            $login = $this->request->getPost('login_redirect');
            if ('referer' === $login) {
                $result = $this->redirect->getRefererUrl();
            }
        }
        return $result;
    }
}