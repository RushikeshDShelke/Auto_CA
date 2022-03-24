<?php
/**
 * Copyright © Olegnax All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Olegnax\NewsletterPopupBasic\Helper;

use Magento\Framework\App\Helper\Context;
use Olegnax\Core\Helper\Helper as CoreHelperHelper;

class Helper extends CoreHelperHelper
{
    const CONFIG_MODULE = 'olegnax_newsletterpopupbasic';
    const TPL_PATH_LAYOUT = "Olegnax_NewsletterPopupBasic::popup/%s.phtml";
    const XML_PATH_ENABLE = 'general/enable';
    const XML_PATH_LAYOUT = 'general/layout';

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool)$this->getModuleConfig(static::XML_PATH_ENABLE);
    }

    /**
     * @return string
     */
    public function setLayout()
    {
        return sprintf(
            static::TPL_PATH_LAYOUT,
            $this->getModuleConfig(static::XML_PATH_LAYOUT)
        );
    }
}

