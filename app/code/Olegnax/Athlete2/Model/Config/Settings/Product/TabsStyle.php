<?php
namespace Olegnax\Athlete2\Model\Config\Settings\Product;

class TabsStyle implements \Magento\Framework\Option\ArrayInterface
{
    protected $_assetRepo;

    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo
    ) {
        $this->_assetRepo = $assetRepo;
    }
    public function toOptionArray() {
        $optionArray = [ ];
        $array		 = $this->toArray();
        foreach ( $array as $key => $value ) {
            $optionArray[] = [ 'value' => $key, 'label' => $value ];
        }

        return $optionArray;
    }

    public function toArray()
    {
        return [
            '' => $this->_assetRepo->getUrl( 'Olegnax_Athlete2::images/tabs-style-01.png' ),
            'minimal' => $this->_assetRepo->getUrl( 'Olegnax_Athlete2::images/tabs-style-02.png' ),
        ];
    }
}
