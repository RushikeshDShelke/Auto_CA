<?php

namespace Cminds\Supplierfrontendproductuploader\Block\Form;

use Magento\Framework\View\Element\Template\Context;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;
use Magento\Framework\Module\Manager;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url;

class Register extends \Magento\Customer\Block\Form\Register
{
    /**
     * Url builder object.
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * Object constructor.
     *
     * @param Context                  $context                  Context object.
     * @param DirectoryHelper          $directoryHelper          Helper object.
     * @param EncoderInterface         $jsonEncoder              Encoder object.
     * @param Config                   $configCacheType          Config object.
     * @param RegionCollectionFactory  $regionCollectionFactory  Collection object.
     * @param CountryCollectionFactory $countryCollectionFactory Collection object.
     * @param Manager                  $moduleManager            Manager object.
     * @param Session                  $customerSession          Session object.
     * @param Url                      $customerUrl              Url object.
     * @param array                    $data                     Data array.
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        DirectoryHelper $directoryHelper,
        EncoderInterface $jsonEncoder,
        Config $configCacheType,
        RegionCollectionFactory $regionCollectionFactory,
        CountryCollectionFactory $countryCollectionFactory,
        Manager $moduleManager,
        Session $customerSession,
        Url $customerUrl,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $directoryHelper,
            $jsonEncoder,
            $configCacheType,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $moduleManager,
            $customerSession,
            $customerUrl,
            $data
        );

        $this->urlBuilder = $context->getUrlBuilder();
        $this->_isScopePrivate = false;
    }

    /**
     * Prepare layout.
     *
     * @return Register
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this
            ->pageConfig
            ->getTitle()
            ->set(__('Create New Supplier Account'));
    }

    /**
     * Retrieve form posting url
     *
     * @return string
     */
    public function getPostActionUrl()
    {
        return $this->urlBuilder->getUrl('supplier/account/createpost');
    }
}
