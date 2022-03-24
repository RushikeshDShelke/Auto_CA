<?php

namespace Meetanshi\Mobilelogin\Block;

use \Magento\Framework\View\Element\Template;
use \Magento\Framework\View\Element\Template\Context;
use \Magento\Framework\Serialize\Serializer\Json;
use \Meetanshi\Mobilelogin\Helper\Data;

/**
 * Class Mobilelogin
 * @package Meetanshi\Mobilelogin\Block
 */
class Mobilelogin extends Template
{

    /**
     * @var Json
     */
    protected $jsonHelper;
    /**
     * @var Data
     */
    protected $helper;

    /**
     * Mobilelogin constructor.
     * @param Context $context
     * @param Json $jsonHelper
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        Json $jsonHelper,
        Data $helper
    )
    {
        $this->jsonHelper = $jsonHelper;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * @return bool|false|string
     */
    public function phoneConfig()
    {
        $config  = [
            "nationalMode" => false,
            "utilsScript"  => $this->getViewFileUrl('Meetanshi_Mobilelogin::js/utils.js'),
            "preferredCountries" => [$this->helper->preferedCountry()]
        ];

        if ($this->helper->allowedCountries()) {
            $config["onlyCountries"] = explode(",", $this->helper->allowedCountries());
        }

        return $this->jsonHelper->serialize($config);
    }
}
