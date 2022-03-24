<?php

/**
 * Athlete2 Theme
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Olegnax.com license that is
 * available through the world-wide-web at this URL:
 * https://www.olegnax.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Olegnax
 * @package     Olegnax_Athlete2
 * @copyright   Copyright (c) 2021 Olegnax (http://www.olegnax.com/)
 * @license     https://www.olegnax.com/license
 */

namespace Olegnax\Athlete2\Block;

use Magento\Customer\Model\Context;
use Magento\Customer\Model\Url;
use Olegnax\Athlete2\Helper\Helper as HelperHelper;

class TopLinks extends SimpleTemplate
{
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;
    /**
     * @var Url
     */
    protected $_customerUrl;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        Url $customerUrl,
        array $data = []
    ) {
        $this->httpContext = $httpContext;
        $this->_customerUrl = $customerUrl;
        parent::__construct($context, $data);
    }

    public function getCompareListUrl()
    {
        return $this->_loadObject(HelperHelper::class)->getCompareListUrl();
    }

    /**
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->_customerUrl->getLoginUrl();
    }

    /**
     * @return string
     */
    public function getLogoutUrl()
    {
        return $this->_customerUrl->getLogoutUrl();
    }

    /**
     * Is logged in
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->httpContext->getValue(Context::CONTEXT_AUTH);
    }

}
