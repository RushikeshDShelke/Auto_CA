<?php

namespace Cminds\Supplierfrontendproductuploader\Block\Adminhtml\Plugin\Catalog\Product\Edit\Button\Back;

use Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Back;
use Magento\Framework\App\Request\Http\Proxy as HttpRequest;
use Magento\Framework\UrlInterface;

/**
 * Cminds Supplierfrontendproductuploader product edit back button plugin.
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
     * @param Back $subject Subject object.
     * @param \Closure  $closure
     *
     * @return array
     */
    public function aroundGetButtonData(
        $subject,
        \Closure $closure
    ) {
        $isSupplierProduct = $this->request->getParam('supplier');
        if ($isSupplierProduct) {
            return [
                'label' => __('Back'),
                'on_click' => sprintf(
                    "location.href = '%s';",
                    $this->urlBuilder->getUrl('supplier/supplier/products')
                ),
                'class' => 'back',
                'sort_order' => 10
            ];
        }

        return $closure();
    }
}
