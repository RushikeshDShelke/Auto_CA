<?php
namespace Olegnax\Athlete2\Model\Config\Settings\Header;
class Layout implements \Magento\Framework\Option\ArrayInterface
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

    public function toArray() {
        return [
            'header_1' => $this->_assetRepo->getUrl( 'Olegnax_Athlete2::images/header-layout-01.png' ),
			'header_2' => $this->_assetRepo->getUrl( 'Olegnax_Athlete2::images/header-layout-02.png' ),
			'header_4' => $this->_assetRepo->getUrl( 'Olegnax_Athlete2::images/header-layout-04.png' ),
			'header_5' => $this->_assetRepo->getUrl( 'Olegnax_Athlete2::images/header-layout-05.png' ),
			'header_6' => $this->_assetRepo->getUrl( 'Olegnax_Athlete2::images/header-layout-06.png' ),
        ];
    }
}