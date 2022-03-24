<?php


namespace Olegnax\InfiniteScroll\Plugin\Frontend\Magento\Framework\App;


use Closure;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Url\DecoderInterface;
use Magento\Framework\Url\Helper\Data as UrlHelper;
use Magento\Framework\View\LayoutInterface;
use Olegnax\InfiniteScroll\Helper\Helper;
use Olegnax\InfiniteScroll\Plugin\Ajax;

class View extends Ajax
{
    /**
     * @var ResponseHttp
     */
    private $response;

    /**
     * View constructor.
     * @param Helper $helper
     * @param RawFactory $resultRaw
     * @param Http $request
     * @param UrlHelper $urlHelper
     * @param DecoderInterface $urlDecoder
     * @param LayoutInterface $layout
     * @param ResponseHttp $response
     * @param Registry $registry
     * @param Json $json
     */
    public function __construct(
        Helper $helper,
        RawFactory $resultRaw,
        Http $request,
        UrlHelper $urlHelper,
        DecoderInterface $urlDecoder,
        LayoutInterface $layout,
        ResponseHttp $response,
        Registry $registry,
        Json $json
    ) {
        $this->response = $response;
        parent::__construct($helper, $resultRaw, $request, $urlHelper, $urlDecoder, $layout, $registry, $json);
    }

    public function aroundRenderLayout(
        \Magento\Framework\App\View $subject,
        Closure $proceed,
        $output = ''
    ) {

        if ($this->isAjax() && $this->request->getRouteName() === 'catalogsearch') {
            $content = $this->getAjaxContent();
            $content = $this->json->serialize($content);
            $this->response->setBody($content);
        } else {
            return $proceed($output);
        }
    }
}