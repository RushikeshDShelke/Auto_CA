<?php
declare(strict_types=1);

namespace Olegnax\InfiniteScroll\Plugin\Frontend\Mageplaza\AjaxLayer\Helper;

use Magento\Framework\App\Request\Http;
use Olegnax\InfiniteScroll\Helper\Helper;
use Olegnax\InfiniteScroll\Plugin\Ajax;

class Data
{
    /**
     * @var Http
     */
    protected $request;
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * Data constructor.
     * @param Helper $helper
     * @param Http $request
     */
    public function __construct(
        Helper $helper,
        Http $request
    ) {
        $this->helper = $helper;
        $this->request = $request;
    }

    /**
     * @param \Mageplaza\AjaxLayer\Helper\Data $subject
     * @param bool $result
     * @return bool
     */
    public function afterAjaxEnabled(
        \Mageplaza\AjaxLayer\Helper\Data $subject,
        $result
    ) {
        return $result && !$this->isAjax();
    }

    /**
     * @return bool
     */
    protected function isAjax()
    {
        return $this->helper->isEnabled() &&
            $this->request->isXmlHttpRequest() &&
            $this->request->isAjax() &&
            $this->request->getParam(Ajax::AJAX_ATTR, '');
    }
}