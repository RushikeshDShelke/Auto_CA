<?php

namespace Jajuma\WebpImages\Controller\Image;

use function GuzzleHttp\json_encode;

class Convert extends \Magento\Framework\App\Action\Action
{

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Jajuma\WebpImages\Helper\Data $helper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->helper = $helper;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();

        $images = $this->getRequest()->getParam('images');
        $isInProductView = $this->getRequest()->getParam('isInProductView');
        if ($images) {
            $webpUrls = [];
            foreach ($images as $imageUrl) {
                if (array_key_exists('thumb', $imageUrl)) {
                    $webpUrlThumb = $this->helper->convert($imageUrl['thumb']);
                    if ($webpUrlThumb) {
                        $imageUrl['thumb'] = $webpUrlThumb;
                    }
                }

                if (array_key_exists('img', $imageUrl)) {
                    $webpUrlImg = $this->helper->convert($imageUrl['img']);
                    if ($webpUrlImg) {
                        $imageUrl['img'] = $webpUrlImg;
                    }
                }

                if (array_key_exists('full', $imageUrl)) {
                    $webpUrlFull = $this->helper->convert($imageUrl['full']);
                    if ($webpUrlFull) {
                        $imageUrl['full'] = $webpUrlFull;
                    }
                }

                if (!empty($imageUrl)) {
                    array_push($webpUrls, $imageUrl);
                }
            }
        }

        return $resultJson->setData(['webpUrls' => $webpUrls]);
    }
}
