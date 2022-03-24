<?php

namespace Cminds\Supplierfrontendproductuploader\Block\Adminhtml\Plugin\Customer\Edit\BackButton;

use Magento\Customer\Block\Adminhtml\Edit\BackButton;
use Magento\Framework\App\Request\Http\Proxy as HttpRequest;
use Magento\Framework\UrlInterface;

/**
 * Cminds Supplierfrontendproductuploader customer edit back button plugin.
 *
 * @category Cminds
 * @package  Cminds_Supplierfrontendproductuploader
 * @author   Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
class Plugin
{
    /**
     * Http request object.
     *
     * @var HttpRequest
     */
    private $request;

    /**
     * Url builder object.
     *
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * Plugin constructor.
     *
     * @param HttpRequest $request
     * @param UrlInterface $urlBuilder
     */
    public function __construct(HttpRequest $request, UrlInterface $urlBuilder)
    {
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Plugin for replace method.
     *
     * @param BackButton $subject Subject object.
     * @param \Closure  $closure
     *
     * @return array
     */
    public function aroundGetBackUrl(
        $subject,
        \Closure $closure
    ) {
        $isSupplier = $this->request->getParam('supplier');
        if ($isSupplier) {
            return $this->urlBuilder->getUrl('supplier/suppliers');
        }

        return $closure();
    }
}
