<?php
/**
 * @author      Olegnax
 * @package     Olegnax_InfiniteScroll
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\InfiniteScroll\Plugin\Frontend\Magento\Theme\Block\Html;

use Olegnax\InfiniteScroll\Helper\Helper;

class Pager
{
    /**
     * @var Helper
     */
    public $helper;

    /**
     * Pager constructor.
     * @param Helper $helper
     */
    public function __construct(Helper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Theme\Block\Html\Pager $subject
     * @param string $result
     * @return string
     */
    public function afterToHtml(
        \Magento\Theme\Block\Html\Pager $subject,
        $result
    ) {
        if ($this->helper->isEnabled()) {
            $last = (int)$subject->getLastPageNum();
            $result .= '<div id="ox-page-count" style="display: none">' . $last . '</div>';
        }

        return $result;
    }
}
