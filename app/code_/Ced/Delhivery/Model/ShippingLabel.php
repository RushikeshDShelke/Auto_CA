<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Delhivery
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Delhivery\Model;
use Magento\Framework\App\Filesystem\DirectoryList;

class ShippingLabel extends \Magento\Framework\Model\AbstractModel {
	/**
	 * setting template
	 * @see Varien_Object::_construct()
	 */
    
    protected $_assetRepo;
	protected $_scopeConfig;
    protected $moduleReader;

    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo, 
        \Magento\Framework\Model\Context $context, 
        \Magento\Framework\View\Result\PageFactory $resultPageFactory, 
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
        \Magento\Framework\ObjectManagerInterface $objectInterface, 
        \Magento\Framework\Filesystem $filesystem, 
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Payment\Helper\Data $paymentData, 
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,  
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\Module\Dir\Reader $moduleReader

 ) {
        $this->_assetRepo = $assetRepo;
        $this->_session = $customerSession;
        $this->_scopeConfig = $scopeConfig;
        $this->_objectmanager=$objectInterface;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_rootDirectory = $filesystem->getDirectoryRead(DirectoryList::ROOT);
        $this->resultPageFactory = $resultPageFactory;
        $this->_localeDate = $localeDate;
        $this->_paymentData = $paymentData;
        $this->addressRenderer = $addressRenderer;
        $this->string = $string;
        $this->moduleReader = $moduleReader;
     
    }
    /**
     * Y coordinate
     *
     * @var int
     */
    public $y;

    /**
     * Zend PDF object
     *
     * @var Zend_Pdf
     */
    
    protected $_pdf;

	
    /**
     * Generate Shipment Label Content for each Waybill
     *
     * @param Zend_Pdf_Page $page
     * @param null $store
     */
    public function getContent(&$page, $shipmentId, $waybill, $order, $pos)
    {
    	$shipment = $this->_objectmanager->get('Magento\Sales\Model\Order\Shipment')->load($shipmentId);
        $shipmentItems = $shipment->getItemsCollection();
        $orderAmount = 0;
        $shipItems = array();
        if(count($shipmentItems)){
            foreach($shipmentItems as $shipmentItem){
                $shipItems[$shipmentItem['order_item_id']] = $shipmentItem['qty'];
            }
        }

        if(!$this->_scopeConfig->getValue('sales/identity/address')){ 
            $errorMessage = __('Sales address can not be empty. Kindly fill Stores->configuration->sales->Invoice and Packing Slip Design->address');
            $this->_objectmanager->get('Magento\Framework\Message\ManagerInterface')->addWarningMessage($errorMessage);
            $url = $this->_objectmanager->get('Magento\Framework\UrlInterface')->getUrl('delhivery/grid/awb');
            $this->_objectmanager->get('Magento\Framework\App\ResponseFactory')->create()->setRedirect($url)->sendResponse();
            throw new \Exception($errorMessage); 
        }  

        $items = $order->getAllItems();
        foreach ($items as $item){
            if(isset($shipItems[$item->getItemId()])){
            $qty = $shipItems[$item->getItemId()];
                if ($item->getQtyOrdered() != $item->getQtyCanceled() && ($item->getParentId() == null || $item->getParentId() == 0)){
                    $productId = $item->getProductId();
                    
                    if ($item->getHasChildren() && $item->getProductType() != 'configurable') {
                        continue;
                    }                                  
                    $orderAmount = $orderAmount + $qty * $this->_objectmanager->create('Ced\Delhivery\Helper\Data')->getItemAmount($item);
                } 
            }                
        }

        if(count($order->getShipmentsCollection())>1){
            foreach($order->getShipmentsCollection() as $value){
                if($shipmentId < $value->getEntityId()){
                    $orderAmount = $orderAmount+ $order->getShippingAmount();
                    break;
                }
            }
        }else{
            $orderAmount = $orderAmount+ $order->getShippingAmount();
        }

        $image = $this->_scopeConfig->getValue('sales/identity/logo');
        $baseUrl = $this->_objectmanager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getBaseUrl();      
        $rdir = $this->_rootDirectory->getAbsolutePath();
        $barcodeFilePath = $this->_assetRepo->getUrl("Ced_Delhivery::images/delhivery/font/FRE3OF9X.TTF");
        $barcodeFilePath = str_replace($baseUrl, $rdir, $barcodeFilePath);
        if($image=="")
		{        
            $imagePath =$this->_assetRepo->getUrl("Ced_Delhivery::images/delhivery/delhivery.jpg");
            $imagePath = str_replace($baseUrl, $rdir, $imagePath);      
		}
		else 
		{
            $imagePath=$this->_mediaDirectory->getAbsolutePath('/sales/store/logo/' . $image);
		} 
		
    	$warehouseaddress ='';
        $image       = \Zend_Pdf_Image::imageWithPath($imagePath);
        $top         = $pos; //top border of the page
        $widthLimit  = 100; //half of the page width
        $heightLimit = 70; //assuming the image is not a "skyscraper"
        $width=195;
        $height=137;
        $ratio = $width / $height;
        if ($ratio > 1 && $width > $widthLimit)
        {
            $width  = $widthLimit;
            $height = $width / $ratio;
        } elseif ($ratio < 1 && $height > $heightLimit)
        {
            $height = $heightLimit;
            $width  = $height * $ratio;
        } elseif ($ratio == 1 && $height > $heightLimit)
        {
            $height = $heightLimit;
            $width  = $widthLimit;
        }
        $y1 = $top - $height -20;
        $y2 = $top-20;
        $x1 = 25;
        $x2 = $x1 + $width;
        
        $methodcode = ($order->getPayment()->getMethodInstance()->getCode() == 'cashondelivery' ) ? "Post-Paid" :"Pre-Paid";
        $page->drawImage($image, $x1, $y1, $x2+20, $y2);
		$this->_setFontRegular($page, 7);
		$page->drawText(__('Order # ') . $order->getRealOrderId(), $x1+190, ($y1+25), 'UTF-8');
        $page->drawText(__('Order Date : ') .$this->_localeDate->formatDate($this->_localeDate->scopeDate($order->getStore(),$order->getCreatedAt(),true),\IntlDateFormatter::MEDIUM,false),$x1+190,($y1+15),'UTF-8');

		$codamount = ($order->getPayment()->getMethodInstance()->getCode() == 'cashondelivery' ) ? number_format($orderAmount,2) : "00.00";
		$page->drawText(__('COD Amount : ') . $codamount, $x1+190, ($y1+5), 'UTF-8');			
		$page->drawText(__('Total Collectable Amount : ') . number_format($orderAmount,2), $x1+190, ($y1-5), 'UTF-8');
		$page->drawRectangle(320, $y1-20, 220, $y1-50, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
		$this->_setFontBold($page, 9);
		$page->drawText(__('') . $methodcode, $x1+220, ($y1-35), 'UTF-8');
		$this->_setFontRegular($page, 7);
		$font = \Zend_Pdf_Font::fontWithPath(
            $barcodeFilePath
        );
        $page->setFont($font, 30);
        $barcodeimagepath= $this->moduleReader->getModuleDir(\Magento\Framework\Module\Dir::MODULE_VIEW_DIR,
                    'Ced_Delhivery').'/web/images/delhivery/';
        $barcodeOptions = array('text' => $waybill,'drawtext'=>false,); 

        $rendererOptions = array();
        $imageResource =    \Zend_Barcode::draw('code128', 'image', $barcodeOptions, $rendererOptions);
             
        imagejpeg($imageResource, $barcodeimagepath.'barcode.jpeg', 100);
        imagedestroy($imageResource);
        $image = \Zend_Pdf_Image::imageWithPath($barcodeimagepath.'barcode.jpeg');
        $page->drawImage($image, $x1+380, $y1+12, $x1+500, $y1+35);				
        $this->_setFontRegular($page, 7);
		$page->drawText("AWB#".trim($waybill), $x1+420, $y1+2);
		$this->_setFontBold($page, 8);
		$page->drawText("Ship to:", $x1+390, $y1-15);
		$page->drawText("From:", $x1, $y1-15);
		$this->_setFontRegular($page, 7);
		$shippingAddress =  $this->_formatAddress($this->addressRenderer->format($order->getShippingAddress(), 'pdf'));			
		$addressy = $y1-25;
		 foreach ($shippingAddress as $value) {
            if ($value !== '') {
                $text = [];
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $text[] = $_value;
                }
                foreach ($text as $part) {
                    $page->drawText(strip_tags(ltrim($part)), $x1+390, $addressy, 'UTF-8');
                    $addressy -= 11;
                }
            }
        }
        $customerAddress = $addressy;
		$addressy = $y1-25;
		foreach (explode("\n", $this->_scopeConfig->getValue('sales/identity/address')) as $value){
			if ($value !== '') {
				$value = preg_replace('/<br[^>]*>/i', "\n", $value);
				foreach (str_split($value, 45) as $_value) {
					$page->drawText(strip_tags(trim($_value)), $x1, $addressy, 'UTF-8');
					$addressy -= 11;
				}
			}
		}

        if($addressy<$customerAddress){
            $addressy = $customerAddress;
        }
		$page->drawLine($x1-25, $addressy-20, $x1+570, $addressy-20);							
       
    }
    /**
     * Set PDF object
     *
     * @param  Zend_Pdf $pdf
     * @return Mage_Sales_Model_Order_Pdf_Abstract
     */
    protected function _setPdf(Zend_Pdf $pdf)
    {
        $this->_pdf = $pdf;
        return $this;
    }

    /**
     * Retrieve PDF object
     *
     * @throws Mage_Core_Exception
     * @return Zend_Pdf
     */
    protected function _getPdf()
    {
        if (!$this->_pdf instanceof Zend_Pdf) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Please define PDF object before using.'));
        }

        return $this->_pdf;
    }

    /**
     * Format address
     *
     * @param  string $address
     * @return array
     */
    protected function _formatAddress($address)
    {
        $return = [];
        foreach (explode('|', $address) as $str) {
            foreach ($this->string->split($str, 45, true, true) as $part) {
                if (empty($part)) {
                    continue;
                }
                $return[] = $part;
            }
        }
        return $return;
    }

    /**
     * Set font as regular
     *
     * @param  Zend_Pdf_Page $object
     * @param  int $size
     * @return Zend_Pdf_Resource_Font
     */
   protected function _setFontRegular($object, $size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('lib/internal/LinLibertineFont/LinLibertine_Re-4.4.1.ttf')
        );
        $object->setFont($font, $size);
        return $font;
    }

    /**
     * Set font as bold
     *
     * @param  \Zend_Pdf_Page $object
     * @param  int $size
     * @return \Zend_Pdf_Resource_Font
     */
    protected function _setFontBold($object, $size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('lib/internal/LinLibertineFont/LinLibertine_Bd-2.8.1.ttf')
        );
        $object->setFont($font, $size);
        return $font;
    }

    /**
     * Set font as italic
     *
     * @param  \Zend_Pdf_Page $object
     * @param  int $size
     * @return \Zend_Pdf_Resource_Font
     */
    protected function _setFontItalic($object, $size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('lib/internal/LinLibertineFont/LinLibertine_It-2.8.2.ttf')
        );
        $object->setFont($font, $size);
        return $font;
    }
}