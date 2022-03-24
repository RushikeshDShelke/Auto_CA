<?php /**/
namespace Olegnax\Athlete2\Model\Config\Settings\Footer;
class NewsletterStyle implements \Magento\Framework\Option\ArrayInterface
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
            'small' => $this->_assetRepo->getUrl( 'Olegnax_Athlete2::images/newsletter_small.png' ),
            'big' => $this->_assetRepo->getUrl( 'Olegnax_Athlete2::images/newsletter_big.png' ),
        ];
    }
}