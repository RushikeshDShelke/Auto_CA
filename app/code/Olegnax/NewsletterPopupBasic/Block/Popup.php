<?php
/**
 * @author      Olegnax
 * @package     Olegnax_HotSpotQuickview
 * @copyright   Copyright (c) 2021 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Olegnax\NewsletterPopupBasic\Block;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Olegnax\NewsletterPopupBasic\Helper\Helper;
use Olegnax\NewsletterPopupBasic\Model\Config\Backend\Image;
use Magento\Framework\Escaper;

class Popup extends Template
{
    const XML_PATH_CONTENT = 'content/content';
    const TEMPLATE_JS = 'Olegnax_NewsletterPopupBasic::js.phtml';
    const TEMPLATE_CSS = 'Olegnax_NewsletterPopupBasic::css.phtml';
    /**
     * @var Helper
     */
    protected $helper;
    /**
     * @var string
     */
    protected $blockId;
    /**
     * @var Json
     */
    protected $json;
    /**
     * @var Escaper
     */
    protected $escaper;
    /**
     * Constructor
     *
     * @param Context $context
     * @param Helper $helper
     * @param array $data
     * @param Json|null $json
	 * @param Escaper|null $escaper
     */
    public function __construct(
        Context $context,
        Helper $helper,
        array $data = [],
        Json $json = null,
		Escaper $escaper = null
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
        $this->json = $json ?: ObjectManager::getInstance()->get(Json::class);
		$this->escaper = $escaper ?: ObjectManager::getInstance()->get(Escaper::class);
    }

    /**
     * @param string|null $storeCode
     * @return string
     */
    public function getContent($storeCode = null)
    {
        $content = $this->getModuleConfig(static::XML_PATH_CONTENT, $storeCode);
        $content = (string)$this->helper->getBlockTemplateProcessor($content);
        return $content;
    }

    /**
     * @param string $path
     * @param string|null $storeCode
     * @return mixed
     */
    public function getModuleConfig($path = '', $storeCode = null)
    {
        return $this->helper->getModuleConfig($path, $storeCode);
    }

    /**
     * @param null $storeCode
     * @return string
     */
    public function getStyle($storeCode = null)
    {
        $content = $this->renderStyle($storeCode);
        if (!empty($content)) {
            $content = preg_replace('/[\r\n\t]/', ' ', $content);
            $content = preg_replace('/[\r\n\t ]{2,}/', ' ', $content);
            $content = preg_replace('/\s+([:;{}])\s+/', '\1', $content);
            $content = trim($content);
            return sprintf('<style>%s</style>', $content);
        }
        return '';
    }

    /**
     * @param int $storeCode
     * @return string
     */
    public function renderStyle($storeCode = null)
    {
        $appearance = $this->getModuleConfig('appearance', $storeCode);

        foreach ($appearance as $key => $value) {
            $_value = $this->getData('appearance_' . $key);
            if (null !== $_value) {
                $appearance[$key] = $_value;
            }
        }

        $general = $this->getModuleConfig('general', $storeCode);
        foreach ($general as $key => $value) {
            $_value = $this->getData($key);
            if (null !== $_value) {
                $general[$key] = $_value;
            }
        }

        try {
            return $this->getLayout()->createBlock(
                Template::class,
                '',
                [
                    'data' => [
                        'appearance' => $appearance,
                        'general' => $general,
                        'template' => static::TEMPLATE_CSS,
                        'background_image' => $this->getBackgroundImage('appearance/background_image', $storeCode),
                    ],
                ]
            )
                ->toHtml();
        } catch (LocalizedException $e) {
            $this->_logger->warning($e->getMessage());
            return '';
        }
    }

    /**
     * @param string $path
     * @param string|null $storeCode
     * @return string
     */
    public function getBackgroundImage($path = 'content/column_image', $storeCode = null)
    {
        $image = $this->getModuleConfig($path, $storeCode);
        if ($image) {
            try {
                $imageBg = $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) .
                    Image::UPLOAD_DIR .
                    '/' .
                    $image;
            } catch (NoSuchEntityException $e) {
                $imageBg = '';
            }
            return 'background-image: url(\'' . $imageBg . '\');';
        }
        return '';
    }

    /**
     * @param array $data
     * @param int|null $storeCode
     * @return string
     */
    public function createJs($data = [], $storeCode = null)
    {
        $selector = '.ox-newsletter-modal__container';
        if (array_key_exists('selector', $data) && !empty($data['selector'])) {
            $selector = $data['selector'];
            unset($data['selector']);
        }
        try {
            return $this->getLayout()->createBlock(
                Template::class,
                '',
                [
                    'data' => [
                        'selector' => $selector,
                        'config' => $this->getConfig($data, $storeCode),
                    ],
                ]
            )->setTemplate(static::TEMPLATE_JS)->toHtml();
        } catch (LocalizedException $e) {
            $this->_logger->warning($e->getMessage());
            return '';
        }
    }

    /**
     * @param array $data
     * @param string|null $storeCode
     * @return string
     */
    public function getConfig($data = [], $storeCode = null)
    {
		$overlayClass = (bool)$this->getModuleConfig('general/show_shadow', $storeCode) ? 'modals-overlay' : 'ox-newsletter-modal__overlay';
        $config = array_replace([			
			'showMobile' => (bool)$this->getModuleConfig('general/show_mobile', $storeCode),
            'many_times' => $this->getModuleConfig('general/many_times', $storeCode),
            'timeout' => abs((int)$this->getModuleConfig('general/timeout', $storeCode)),
            'width' => abs((int)$this->getModuleConfig('general/width', $storeCode)),
            'height' => abs((int)$this->getModuleConfig('general/height', $storeCode)),
            'vAlign' => $this->getModuleConfig('general/position_v', $storeCode) == 'v-center',
            'hAlign' => $this->getModuleConfig('general/position_h', $storeCode) == 'h-center',
			'cookieExpire' => abs((int)$this->getModuleConfig('general/cookie_expire', $storeCode) ?: '24'),
			'cookieName' => 'ox_load_newsletter_activity' . $this->escaper->escapeHtmlAttr($this->getModuleConfig('general/cookie_sufix', $storeCode)),
            'cookieNameAlways' => 'ox_load_newsletter_activity_always' . $this->escaper->escapeHtmlAttr($this->getModuleConfig('general/cookie_sufix', $storeCode)),			
			'dialogOptions' => [
				'modalClass' => (bool)$this->getModuleConfig('general/show_shadow', $storeCode) ? 'ox-newsletter-modal' : 'ox-newsletter-modal no-overlay',
                'overlayClass' => $overlayClass,
				'clickableOverlay' => (bool)$this->getModuleConfig('general/clickable_overlay', $storeCode)
            ]
        ], $data);

        return $this->json->serialize($config);
    }
}

