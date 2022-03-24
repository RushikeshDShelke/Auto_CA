<?php /**/
namespace Olegnax\Athlete2\Model\Config\Settings\Product;
class GalleryLayout implements \Magento\Framework\Option\ArrayInterface
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
            'fast' => $this->_assetRepo->getUrl( 'Olegnax_Athlete2::images/gallery-layout-02.png' ),
            '1col' => $this->_assetRepo->getUrl( 'Olegnax_Athlete2::images/gallery-layout-01.jpg' ),
			'2cols' => $this->_assetRepo->getUrl( 'Olegnax_Athlete2::images/gallery-layout-03.jpg' ),
        ];
    }
}