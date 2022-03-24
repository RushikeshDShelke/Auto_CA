<?php

namespace Cminds\Marketplace\Block\Form;

use Magento\Framework\View\Element\Template\Context;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;
use Magento\Framework\Module\Manager;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url;
use Cminds\Marketplace\Model\Fields;
use Magento\Framework\View\Element\Html\Date;

class Register extends \Magento\Customer\Block\Form\Register
{
    /**
     * Url builder object.
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * Field Object.
     *
     * @var Fields
     */
    protected $fields;

    /**
     * Date Element Onject.
     *
     * @var array
     */
    protected $dateElement;

    /**
     * Register constructor.
     *
     * @param Context $context
     * @param DirectoryHelper $directoryHelper
     * @param EncoderInterface $jsonEncoder
     * @param Config $configCacheType
     * @param RegionCollectionFactory $regionCollectionFactory
     * @param CountryCollectionFactory $countryCollectionFactory
     * @param Manager $moduleManager
     * @param Session $customerSession
     * @param Url $customerUrl
     * @param Fields $fields
     * @param Date $date
     * @param array $data
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
        Fields $fields,
        Date $date,
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
        $this->fields = $fields;
        $this->dateElement = $data;
    }

    public function getCustomFields()
    {
        $collection = $this->fields->getCollection();

        return $collection;
    }

    public function getCustomField($field, $data = null)
    {
        $fieldHtml = '';

        switch ($field->getType()) {
            case 'text':
                $fieldHtml = $this->getTextField($field, $data);
                break;
            case 'textarea':
                $fieldHtml = $this->getTextareaField($field, $data);
                break;
            case 'date':
                $fieldHtml = $this->getDateField($field, $data);
                break;
        }

        return $fieldHtml;
    }

    public function getFieldLabel($name)
    {
        $label = $this->fields->load($name, 'name')->getData('label');

        return $label;
    }

    private function getTextField($attribute, $data)
    {
        $class = $attribute->getIsRequired() ? ' required' : '';

        return '<input type="text" value="'
            . '" name="supplier_custom_attribute[' . $attribute->getName()
            . ']" id="' . $attribute->getName()
            . '" class="input-text form-control' . $class . '">';
    }

    private function getTextareaField($attribute, $data)
    {
        $class = $attribute->getIsRequired() ? ' required' : '';
        $class .= $attribute->getIsWysiwyg() ? ' wysiwyg' : '';

        return '<textarea name="supplier_custom_attribute[' . $attribute->getName()
            . ']" id="' . $attribute->getName()
            . '" class="input-text form-control' . $class
            . '"">' . '</textarea>';
    }

    private function getDateField($attribute, $data)
    {
        $class = $attribute->getIsRequired() ? ' required' : '';

        $html = '<input type="text" name="supplier_custom_attribute[' . $attribute->getName() . ']" id="' . $attribute->getName() . '" ';
        $html .= 'value= ""';
        $html .= ' ' . $this->getHtmlExtraParams($attribute) . ' ';
        $html .= 'class="_has_datepicker " ' . $class . '/> ';

        $html .= '<script type="text/javascript">
            require(["jquery", "mage/calendar"], function($){
                    $("#' .
            $attribute->getName() .
            '").calendar({
                        showsTime: false' . ',
                        dateFormat: "' . $this->getDateFormat() . '"})}); </script>';

        return $html;
    }

    /**
     * Return data-validate rules
     *
     * @return string
     */
    public function getHtmlExtraParams($attribute)
    {
        $validators = [];

        $validators['validate-date'] = [
            'dateFormat' => $this->getDateFormat()
        ];

        return 'data-validate="' . $this->_escaper->escapeHtml(json_encode($validators)) . '"';
    }

    /**
     * Get date format for the input field.
     *
     * @return string
     */
    public function getDateFormat()
    {
        return $this->_localeDate->getDateFormatWithLongYear();
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
