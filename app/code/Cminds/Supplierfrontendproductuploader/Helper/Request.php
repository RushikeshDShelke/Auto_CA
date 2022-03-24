<?php

namespace Cminds\Supplierfrontendproductuploader\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Cminds Supplierfrontendproductuploader request helper.
 *
 * @category Cminds
 * @package  Cminds_Supplierfrontendproductuploader
 * @author   Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
class Request extends AbstractHelper
{
    /**
     * Return redirect url.
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        $uenc = $this->_request->getParam('uenc');
        if (!is_null($uenc)) {
            $uenc = base64_decode($uenc);
        } else {
            $uenc = $this->_urlBuilder->getUrl();
        }

        return $uenc;
    }
}
