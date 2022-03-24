<?php


namespace Olegnax\LayeredNavigation\Plugin\Framework\App;


use Closure;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Url\DecoderInterface;
use Magento\Framework\Url\Helper\Data as UrlHelper;
use Magento\Framework\View\Result\Page;
use Olegnax\LayeredNavigation\Helper\Helper;
use Olegnax\LayeredNavigation\Plugin\Ajax;

class View extends Ajax
{
    /**
     * @var ResponseHttp
     */
    private $response;
    public function __construct(
        Helper $helper,
        RawFactory $resultRaw,
        Http $request,
        UrlHelper $urlHelper,
        DecoderInterface $urlDecoder,
        ResponseHttp $response,
        Registry $registry,
        Json $json
    ) {
        $this->response = $response;
        parent::__construct($helper, $resultRaw, $request, $urlHelper, $urlDecoder, $registry, $json);
    }

    public function aroundRenderLayout(
        \Magento\Framework\App\View $subject,
        Closure $proceed,
        $output = ''
    ) {
        $page = $subject->getPage();

        if ($this->isAjax() && $page instanceof Page && $this->request->getRouteName() === 'catalogsearch') {
            $content = $this->getAjaxContent($page);
            $content = $this->json->serialize($content);
            $this->response->setBody($content);
        } else {
            return $proceed($output);
        }
    }
}