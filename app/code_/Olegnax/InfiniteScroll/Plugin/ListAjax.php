<?php
/**
 * @author      Olegnax
 * @package     Olegnax_InfiniteScroll
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\InfiniteScroll\Plugin;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\View\Result\Page;

class ListAjax extends Ajax
{
    /**
     * @param Action $controller
     * @param Page $page
     * @return Raw
     */
    public function afterExecute(Action $controller, $page)
    {
        if ($this->isAjax() && $page instanceof Page) {
            $page = $this->json($this->getAjaxContent($page));
        }

        return $page;
    }
}
