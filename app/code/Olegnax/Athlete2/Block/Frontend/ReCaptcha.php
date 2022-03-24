<?php

namespace Olegnax\Athlete2\Block\Frontend;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template;
use MSP\ReCaptcha\Model\Config;
use MSP\ReCaptcha\Model\LayoutSettings;
use Zend\Json\Json;

class ReCaptcha extends Template
{
    /**
     * @var array
     */
    protected $data;
    /**
     * @var mixed
     */
    protected $layoutSettings;
    /**
     * @var mixed
     */
    protected $config;

    /**
     * ReCaptcha constructor.
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->data = $data;
        if (class_exists(LayoutSettings::class)) {
            $this->layoutSettings = ObjectManager::getInstance()->create(LayoutSettings::class);
            $this->config = ObjectManager::getInstance()->create(Config::class);
        }
    }


    /**
     * @inheritdoc
     */
    public function getJsLayout()
    {
        $layout = Json::decode(parent::getJsLayout(), Json::TYPE_ARRAY);

        if ($this->config && $this->config->isEnabledFrontend()) {
            // Backward compatibility with fixed scope name
            if (isset($layout['components']['msp-recaptcha'])) {
                $layout['components'][$this->getRecaptchaId()] = $layout['components']['msp-recaptcha'];
                unset($layout['components']['msp-recaptcha']);
            }

            $recaptchaComponentSettings = [];
            if (isset($layout['components'][$this->getRecaptchaId()]['settings'])) {
                $recaptchaComponentSettings = $layout['components'][$this->getRecaptchaId()]['settings'];
            }
            $layout['components'][$this->getRecaptchaId()]['settings'] = array_replace_recursive(
                $this->layoutSettings->getCaptchaSettings(),
                $recaptchaComponentSettings
            );

            $layout['components'][$this->getRecaptchaId()]['reCaptchaId'] = $this->getRecaptchaId();
        }

        return Json::encode($layout);
    }

    /**
     * Get current recaptcha ID
     */
    public function getRecaptchaId()
    {
        return (string)$this->getData('recaptcha_id') ?: 'msp-recaptcha-' . md5($this->getNameInLayout());
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        if (!$this->config || !$this->config->isEnabledFrontend()) {
            return '';
        }

        return parent::toHtml();
    }
}