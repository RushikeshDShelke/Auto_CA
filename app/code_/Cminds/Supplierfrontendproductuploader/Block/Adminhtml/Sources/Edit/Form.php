<?php
namespace Cminds\Supplierfrontendproductuploader\Block\Adminhtml\Sources\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory as DataFormFactory;
use Magento\Store\Model\System\Store as Store;
use Magento\Directory\Model\ResourceModel\Region\Collection as RegionCollection;
use Magento\Directory\Model\Region;
use Magento\Directory\Model\CountryFactory;
use Magento\Store\Model\WebsiteFactory as WebsiteFactory;
use Cminds\Supplierfrontendproductuploader\Api\Data\SourcesInterface;
use Cminds\Supplierfrontendproductuploader\Block\Adminhtml\Sources\Status;

/**
 * Adminhtml blog post edit form.
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var Magento\Directory\Model\ResourceModel\Region\Collection
     */
    protected $regionCollection;

    /**
     * @var Magento\Directory\Model\Region
     */
    protected $region;

    /**
     * @var Magento\Directory\Model\CountryFactory
     */
    protected $_countryFactory;

    /**
     * @var Cminds\Supplierfrontendproductuploader\Block\Adminhtml\Sources\Status
     */
    protected $sourceStatusBock;

    /**
     * @var Magento\Store\Model\ResourceModel\Website\CollectionFactory
     */
    protected $_websiteFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Directory\Model\ResourceModel\Region\Collection $regionCollection
     * @param \Magento\Directory\Model\Region $region
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Cminds\Supplierfrontendproductuploader\Block\Adminhtml\Sources\Status $sourceStatusBock
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        DataFormFactory $formFactory,
        Store $systemStore,
        RegionCollection $regionCollection,
        Region $region,
        CountryFactory $countryFactory,
        Status $sourceStatusBock,
        WebsiteFactory $websiteFactory,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->regionCollection = $regionCollection;
        $this->region = $region;
        $this->_countryFactory = $countryFactory;
        $this->sourceStatusBock = $sourceStatusBock;
        $this->_websiteFactory = $websiteFactory;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Init form.
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('source_form');
        $this->setTitle(__('Source Information'));
    }

    /**
     * Prepare form.
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Cminds\Supplierfrontendproductuploader\Api\Data\SourcesInterface $model */
        $model = $this->_coreRegistry->registry('source_item');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $form->setHtmlIdPrefix('post_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('General'), 'class' => 'fieldset-wide']
        );

        if ($model->getEntityId()) {
            $fieldset->addField(SourcesInterface::ENTITY_ID, 'hidden', ['name' => SourcesInterface::ENTITY_ID]);
        }

        $fieldset->addField(
            SourcesInterface::STATUS,
            'note',
            [
                'name' => SourcesInterface::STATUS,
                'label' => __('Status'),
                'title' => __('Status'),
                'text' =>  $this->sourceStatusBock->getStatusLabel($model->getData(SourcesInterface::STATUS)),
            ]
        );

        $fieldset->addField(
            SourcesInterface::NAME,
            'note',
            [
                'name' => SourcesInterface::NAME,
                'label' => __('Name'),
                'title' => __('Name'),
                'text' => $model->getData(SourcesInterface::NAME),
            ]
        );

        $fieldset->addField(
            SourcesInterface::SOURCE_CODE,
            'note',
            [
                'name' => SourcesInterface::SOURCE_CODE,
                'label' => __('Source Code'),
                'title' => __('Source Code'),
                'text' => $model->getData(SourcesInterface::SOURCE_CODE),
            ]
        );

        $fieldset->addField(
            SourcesInterface::DESCRIPTION,
            'note',
            [
                'name' => SourcesInterface::DESCRIPTION,
                'label' => __('Description'),
                'title' => __('Description'),
                'text' => $model->getData(SourcesInterface::DESCRIPTION),
            ]
        );

        $fieldset->addField(
            SourcesInterface::LATITUDE,
            'note',
            [
                'name' => SourcesInterface::LATITUDE,
                'label' => __('Latitude'),
                'title' => __('Latitude'),
                'text' => $model->getData(SourcesInterface::LATITUDE),
            ]
        );

        $fieldset->addField(
            SourcesInterface::LONGITUDE,
            'note',
            [
                'name' => SourcesInterface::LONGITUDE,
                'label' => __('Longitude'),
                'title' => __('Longitude'),
                'text' => $model->getData(SourcesInterface::LONGITUDE),
            ]
        );

        $fieldsetContactInfo = $form->addFieldset(
            'contact_info_fieldset',
            ['legend' => __('Contact Info'), 'class' => 'fieldset-wide']
        );

        $fieldsetContactInfo->addField(
            SourcesInterface::CONTACT_NAME,
            'note',
            [
                'name' => SourcesInterface::CONTACT_NAME,
                'label' => __('Contect Name'),
                'title' => __('Contect Name'),
                'text' => $model->getData(SourcesInterface::CONTACT_NAME),
            ]
        );

        $fieldsetContactInfo->addField(
            SourcesInterface::EMAIL,
            'note',
            [
                'name' => SourcesInterface::EMAIL,
                'label' => __('Email'),
                'title' => __('Email'),
                'text' => $model->getData(SourcesInterface::EMAIL),
            ]
        );

        $fieldsetContactInfo->addField(
            SourcesInterface::PHONE,
            'note',
            [
                'name' => SourcesInterface::PHONE,
                'label' => __('Phone'),
                'title' => __('Phone'),
                'text' => $model->getData(SourcesInterface::PHONE),
            ]
        );

        $fieldsetContactInfo->addField(
            SourcesInterface::FAX,
            'note',
            [
                'name' => SourcesInterface::FAX,
                'label' => __('Fax'),
                'title' => __('Fax'),
                'text' => $model->getData(SourcesInterface::FAX),
            ]
        );

        $fieldsetAddressData = $form->addFieldset(
            'address_data_fieldset',
            ['legend' => __('Address Data'), 'class' => 'fieldset-wide']
        );

        $country = $this->_countryFactory
                    ->create()
                    ->loadByCode($model->getData(SourcesInterface::COUNTRY_ID));

        $model->setData(SourcesInterface::COUNTRY_ID, $country->getName());

        $fieldsetAddressData->addField(
            SourcesInterface::COUNTRY_ID,
            'note',
            [
                'name' => SourcesInterface::COUNTRY_ID,
                'label' => __('Country'),
                'title' => __('Country'),
                'text' => $country->getName(),
            ]
        );

        $regionName = $model->getData(SourcesInterface::REGION);
        $region = $this->region->loadByCode($regionName, $country->getCountryId());

        if ($region->getName()) {
            $regionName = $region->getName();
        }

        $fieldsetAddressData->addField(
            SourcesInterface::REGION,
            'note',
            [
                'name' => SourcesInterface::REGION,
                'label' => __('Region'),
                'title' => __('Region'),
                'text' => $regionName,
            ]
        );

        $fieldsetAddressData->addField(
            SourcesInterface::CITY,
            'note',
            [
                'name' => SourcesInterface::CITY,
                'label' => __('City'),
                'title' => __('City'),
                'text' => $model->getData(SourcesInterface::CITY)
            ]
        );

        $fieldsetAddressData->addField(
            SourcesInterface::STREET,
            'note',
            [
                'name' => SourcesInterface::STREET,
                'label' => __('Street'),
                'title' => __('Street'),
                'text' => $model->getData(SourcesInterface::STREET)
            ]
        );

        $fieldsetAddressData->addField(
            SourcesInterface::POSTCODE,
            'note',
            [
                'name' => SourcesInterface::POSTCODE,
                'label' => __('Post Code'),
                'title' => __('Post Code'),
                'text' => $model->getData(SourcesInterface::POSTCODE)
            ]
        );

        $fieldsetOtherInfo = $form->addFieldset(
            'other_info_fieldset',
            ['legend' => __('Other Info'), 'class' => 'fieldset-wide']
        );


        $fieldsetOtherInfo->addField(
            SourcesInterface::CUSTOMER_EMAIL,
            'note',
            [
                'name' => SourcesInterface::CUSTOMER_EMAIL,
                'label' => __('Suggested By'),
                'title' => __('Suggested By'),
                'text' => $model->getData(SourcesInterface::CUSTOMER_EMAIL)
            ]
        );

        $fieldsetOtherInfo->addField(
            SourcesInterface::WEBSITE_ID,
            'note',
            [
                'name' => SourcesInterface::WEBSITE_ID,
                'label' => __('Website'),
                'title' => __('Website'),
                'text' =>
                $this->getWebsiteName(
                    $model->getData(SourcesInterface::WEBSITE_ID)
                )
            ]
        );

        $fieldsetOtherInfo->addField(
            SourcesInterface::CREATED_AT,
            'note',
            [
                'name' => SourcesInterface::CREATED_AT,
                'label' => __('Created at'),
                'title' => __('Created at'),
                'text' => $model->getData(SourcesInterface::CREATED_AT)
            ]
        );

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function getWebsiteName($websiteId)
    {
        $website = $this->_websiteFactory->create()->load(
            $websiteId
        );
        
        return $website ? $website->getName() : $websiteId;
    }
}
