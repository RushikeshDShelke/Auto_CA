<?php


namespace Olegnax\InstagramMin\Block\Adminhtml\System\Config\Form;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Olegnax\InstagramMin\Model\Client;

class LabelInput extends Field
{
    /**
     * @var Client
     */
    protected $client;

    public function __construct(
        Context $context,
        Client $client,
        array $data = []
    ) {
        $this->client = $client;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve element HTML markup
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $value = $element->getValue();
        $originalData = $element->getOriginalData();
        $button_label = $value ? __('Re-generate Access Token') : __($originalData['button_label']);
        return
            '<div class="message" id="' . $element->getHtmlId() . '_message" style="display: none"></div>
        <button
            id="' . $element->getHtmlId() . '"
            type="button" class="' . ($value ? 'regenerate' : '') . '"
            data-mage-init=\'{"Olegnax_InstagramMin/js/popup":' . $this->getJsonConfig($element->getScopeId()) . '}\'><span class="generate">' . $originalData['button_label']
            . '</span><span class="regenerate">' . __('Re-generate Access Token')
            . '</span></button>';
    }

    private function getJsonConfig($store_id)
    {
        return \GuzzleHttp\json_encode([
            'url' => $this->client->getAuthUrl($store_id),
            'windowName' => 'Instagram',
        ]);
    }

}