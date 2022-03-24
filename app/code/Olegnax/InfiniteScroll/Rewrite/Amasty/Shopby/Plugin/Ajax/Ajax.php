<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Olegnax\InfiniteScroll\Rewrite\Amasty\Shopby\Plugin\Ajax;

use Magento\Framework\App\RequestInterface;

if (class_exists('\Amasty\Shopby\Plugin\Ajax\Ajax')) {
    class Ajax extends \Amasty\Shopby\Plugin\Ajax\Ajax
    {
        /**
         * @param RequestInterface $request
         * @return bool
         */
        protected function isAjax(RequestInterface $request)
        {
            $result = parent::isAjax($request);
            if ($result) {
                $result = $result && !$request->getParam(\Olegnax\InfiniteScroll\Plugin\Ajax::AJAX_ATTR);
            }

            return $result;
        }
    }
} else {
    class Ajax
    {

    }
}