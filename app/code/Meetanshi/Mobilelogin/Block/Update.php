<?php

namespace Meetanshi\Mobilelogin\Block;

use Magento\Framework\View\Element\Template;
use Meetanshi\Mobilelogin\Helper\Data;
use \Magento\Framework\Serialize\Serializer\Json;

/**
 * Class Update
 * @package Meetanshi\Mobilelogin\Block
 */
class Update extends Template
{
    /**
     * @var Data
     */
    private $helper;
    /**
     * @var Json
     */
    protected $jsonHelper;

    /**
     * Update constructor.
     * @param Template\Context $context
     * @param Json $jsonHelper
     * @param Data $helper
     */
    public function __construct(
        Template\Context $context,
        Json $jsonHelper,
        Data $helper
    )
    {
        $this->helper = $helper;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context);
    }

    /**
     * @return mixed
     */
    public function getCustomerMobileNumber()
    {
        return $this->helper->getCustomerMobile();
    }

    /**
     * @return bool|false|string
     */
    public function phoneConfig()
    {
        $config = [
            "nationalMode" => false,
            "utilsScript" => $this->getViewFileUrl('Meetanshi_Mobilelogin::js/utils.js'),
            "preferredCountries" => [$this->helper->preferedCountry()]
        ];

        if ($this->helper->allowedCountries()) {
            $config["onlyCountries"] = explode(",", $this->helper->allowedCountries());
        }

        return $this->jsonHelper->serialize($config);
    }
}
