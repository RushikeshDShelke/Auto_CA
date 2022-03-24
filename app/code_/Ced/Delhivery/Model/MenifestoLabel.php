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
/**
 * Sales Order Shipment PDF model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MenifestoLabel extends  \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    protected $moduleReader;

    /**
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param Config $pdfConfig
     * @param \Magento\Sales\Model\Order\Pdf\Total\Factory $pdfTotalFactory
     * @param \Magento\Sales\Model\Order\Pdf\ItemsFactory $pdfItemsFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
   public function __construct(
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
    \Magento\Framework\Module\Dir\Reader $moduleReader) {
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

    public function getContent($page, $shipmentId, $waybill, $order, $pos)
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
        $top = $pos; //top border of the page
        $widthLimit  = 120; //half of the page width
        $heightLimit = 70; //assuming the image is not a "skyscraper"
        $width=120;
        $height=15;
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
        $logoTop=$top-20;
        $y1 = $logoTop - $height;
        $y2 = $logoTop;
        $x1 = 25;
        $x2 = $x1 + $width;
        $storeAdd = explode("\n", $this->_scopeConfig->getValue('sales/identity/address')); 

        if(!$this->_scopeConfig->getValue('sales/identity/address')){ 
            $eMsg = __('Sales address can not be empty. Kindly fill Stores->configuration->sales->Invoice and Packing Slip Design->address');
            $this->_objectmanager->get('Magento\Framework\Message\ManagerInterface')->addWarningMessage($eMsg);
            $url = $this->_objectmanager->get('Magento\Framework\UrlInterface')->getUrl('delhivery/grid/awb');
            $this->_objectmanager->get('Magento\Framework\App\ResponseFactory')->create()->setRedirect($url)->sendResponse();
            throw new \Exception($eMsg);
            
        }       
        $y11=$top-50;
        $this->_setFontRegular($page, 6);
        $page->drawText(__('Order # ') . $order->getRealOrderId(), $x1+225, ($y11+25), 'UTF-8');
        $page->drawText(__('Order Date : ') .$this->_localeDate->formatDate($this->_localeDate->scopeDate($order->getStore(),$order->getCreatedAt(),true),\IntlDateFormatter::MEDIUM,false),$x1+225,($y11+17),'UTF-8');
        $aa=$order->getStatus();
        $top = $pos; //top border of the page                
        $top = $pos-70;
        /* shipping address starts */
        
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $top, 285, ($top - 25));
        $page->drawRectangle(285, $top, 570, ($top - 25));

        /* Calculate blocks info */

        /* Billing Address */

        /* Payment */
        $paymentDetail = $this->_paymentData->getInfoBlock($order->getPayment())->setIsSecureMode(true)->toPdf();
        $paymentDetail = htmlspecialchars_decode($paymentDetail, ENT_QUOTES);
        $paymentInfo = explode('{{pdf_row_separator}}', $paymentDetail);
        foreach ($paymentInfo as $key => $value) {
            if (strip_tags(trim($value)) == '') {
                unset($paymentInfo[$key]);
            }
        }
        reset($paymentInfo);
       
        if (!$order->getIsVirtual()) {
            /* Shipping Address */
            $shippingAddress = $this->_formatAddress($this->addressRenderer->format($order->getShippingAddress(), 'pdf'));
            $shippingMethod = $order->getShippingDescription();
        }      

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontBold($page, 8);
        $page->drawText(__('Ship From:'), 35, ($top - 15), 'UTF-8');

        if (!$order->getIsVirtual()) {
            $page->drawText(__('Ship To:'), 300, ($top - 15), 'UTF-8');
        } else {
            $page->drawText(__('Payment Method:'), 300, ($top - 15), 'UTF-8');
        }
        
        $addressesHeight=60;
    
        //$addressesHeight = $this->_calcAddressHeight($billingAddress);
        if (isset($shippingAddress)) {
            //$addressesHeight = max($addressesHeight, $this->_calcAddressHeight($shippingAddress));
            $addressesHeight=60;
        }

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->drawRectangle(25, ($top - 25), 285, $top - 50 - $addressesHeight);
        $page->drawRectangle(285, ($top - 25), 570, $top - 50 - $addressesHeight);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 7);
        $this->y = $top - 40;
        $addressesStartY = $this->y;

        foreach ($storeAdd as $value) {
            if ($value !== '') {
                $text = [];
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $text[] = $_value;
                }
                foreach ($text as $part) {
                    $page->drawText(strip_tags(ltrim($part)), 35, $this->y, 'UTF-8');
                    $this->y -= 10;
                }
            }
        }

        $addressesEndY = $this->y;
        if (!$order->getIsVirtual()) {
            $this->y = $addressesStartY;
            foreach ($shippingAddress as $value) {
                if ($value !== '') {
                    $text = [];
                    foreach ($this->string->split($value, 45, true, true) as $_value) {
                        $text[] = $_value;
                    }
                    foreach ($text as $part) {
                        $page->drawText(strip_tags(ltrim($part)), 300, $this->y, 'UTF-8');
                        $this->y -= 10;
                    }
                }
            }
        }

        /* shipping address ends */
        $barcodeImagePath = $this->moduleReader->getModuleDir(\Magento\Framework\Module\Dir::MODULE_VIEW_DIR,
                            'Ced_Delhivery').'/web/images/delhivery/font/FRE3OF9X.TTF';
        $image=$this->_scopeConfig->getValue('sales/identity/logo');  
        if($image==""){
            $imagePath = $this->moduleReader->getModuleDir(\Magento\Framework\Module\Dir::MODULE_VIEW_DIR,
                        'Ced_Delhivery').'/web/images/delhivery/delhivery.jpg';
        }
        else 
        {
            $imagePath= $this->_mediaDirectory->getAbsolutePath('/sales/store/logo/' . $image);
        }

        $image       = \Zend_Pdf_Image::imageWithPath($imagePath);
        $top         = $pos-20; //top border of the page
        $widthLimit  = 100; //half of the page width
        $heightLimit = 70; //assuming the image is not a "skyscraper"
                        
        $width=195;
        $height=137;
        $ratio = $width / $height;
        if ($ratio > 1 && $width > $widthLimit) {
            $width  = $widthLimit;
            $height = $width / $ratio;
        } elseif ($ratio < 1 && $height > $heightLimit) {
            $height = $heightLimit;
            $width  = $height * $ratio;
        } elseif ($ratio == 1 && $height > $heightLimit) {
            $height = $heightLimit;
            $width  = $widthLimit;
        }

        $y1 = $top - $height;
        $y2 = $top;
        $x1 = 25;
        $x2 = $x1 + $width;
        $page->drawImage($image, $x1, $y1+32, $x2, $y2);
        $items = $order->getAllVisibleItems();
        $x = 30;
        $this->y = 550;
        $this->_setFontBold($page, 15);
        $page->drawText("MANIFEST REPORT::". $x + 15, $this->y-119, 8);
        
        $this->_setFontRegular($page, 7);
        $font = \Zend_Pdf_Font::fontWithPath($barcodeImagePath);
        $page->setFont($font, 30);
        $barcodeImage = trim($waybill);
        $barcodeimagepath= $this->moduleReader->getModuleDir(\Magento\Framework\Module\Dir::MODULE_VIEW_DIR,
                    'Ced_Delhivery').'/web/images/delhivery/';
        $barcodeOptions = array('text' => $waybill,'drawtext'=>false,); 

        $rendererOptions = array();
        $imageResource =    \Zend_Barcode::draw(
            'code128', 'image', $barcodeOptions, $rendererOptions
        );
             
        imagejpeg($imageResource, $barcodeimagepath.'barcode.jpeg', 100);
        imagedestroy($imageResource);
        $image = \Zend_Pdf_Image::imageWithPath($barcodeimagepath.'barcode.jpeg');
        $page->drawImage($image, $x1+380, $y1+48, $x1+505, $y1+70); 
        $this->_setFontRegular($page, 7);
        $this->_setFontRegular($page, 7);
        $page->drawText("AWB#".trim($waybill), $x1+410, $y1+38);        
        $this->_setFontRegular($page, 4);
        $this->y=$this->y+55;
        $addressy = $this->y+20; 
        $namey = $this->y;
        $this->_setFontBold($page, 7);
        $page->drawRectangle($x-5, $addressy - 8, $page->getWidth()-25, $addressy + 15, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
       
        $page->drawText('S.NO', $x + 0, $addressy, 'UTF-8');
        $page->drawText('Product Name', $x + 90, $addressy, 'UTF-8'); 
        $page->drawText('SKU', $x + 280, $addressy, 'UTF-8');
        $page->drawText('Quantity', $x + 440, $addressy, 'UTF-8');
        $this->_setFontRegular($page, 7);
        $count = 0;
        $products = array();
        $quantity = 0;
        $weight = 0;
        $productTotal=array();
        $productTotalNumber=1;
        $skuValues=array();
        $quantityValues=array();
   
        foreach ($items as $item){
            if(isset($shipItems[$item->getItemId()])){
                $qty = $shipItems[$item->getItemId()];
                if($item->getQtyOrdered() != $item->getQtyCanceled() && ($item->getParentId() == null || $item->getParentId() == 0))
                {
                    if ($item->getHasChildren() && $item->getProductType() != 'configurable') {
                        continue;
                    }                                   
                    $products[] = $item->getName();                    
                    $skuValues[] = $item->getSku();
                    $orderAmount = $orderAmount + $qty * $this->_objectmanager->create('Ced\Delhivery\Helper\Data')->getItemAmount($item);        
                    $quantityValues[]= $qty;
                    $weight = $weight + $item->getWeight(); 
                    $productTotal[]=$productTotalNumber;
                    $productTotalNumber++; 
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
        $codamount = ($order->getPayment()->getMethodInstance()->getCode() == 'cashondelivery' ) ? number_format($orderAmount,2) : "00.00";
        $page->drawText(__('COD Amount : ') . $codamount, $x1+225, ($y11+9), 'UTF-8');         
        $page->drawText(__('Total Collectable Amount : ') . number_format($orderAmount,2), $x1+225, ($y11+1), 'UTF-8');

        $productTotalOriginal=$productTotalNumber;                
        $masterArray=array('products'=>$products,'skuValues'=>$skuValues,'quantityValues'=>$quantityValues);

        foreach($masterArray['products'] as $key=>$value){
            $productCount=0;   
            if ($productCount !== '') {
                /* print name starts */
                
                $nameyOriginal=$namey;
                $text = array();
                foreach ($this->string->split($value, 42, true, true) as $_value) {
                    $text[] = $_value;
                }
                foreach ($text as $part) {                             
                    $page->drawText(strip_tags(ltrim($part)), $x+50, $namey - ($productCount * 10), 'UTF-8');
                    $namey -= 80;
                }
                /* print name ends */
                
                /* print serial starts */
                $namey=$nameyOriginal;
                $textSerial = array();
                foreach ($this->string->split($key+1, 32, true, true) as $_value) {
                    $textSerial[] = $_value;
                }
                foreach ($textSerial as $part) {                               
                    $page->drawText(strip_tags(ltrim($part)), $x+10, $namey -($productCount * 10), 'UTF-8');
                    $namey -= 8;
                }
                /* print serial ends */
                
                /* print sku starts */
                $namey=$nameyOriginal;
                $textSku = array();
                foreach ($this->string->split($masterArray['skuValues'][$key], 42, true, true) as $_value) {
                    $textSku[] = $_value;
                }
                foreach ($textSku as $part) {                              
                    $page->drawText(strip_tags(ltrim($part)), $x+220, $namey -($productCount * 10), 'UTF-8');
                    $namey -= 8;
                }
                /* print sku ends */
                
                /* print quantity starts */
                $namey=$nameyOriginal;
                $textQuantity = array();
                foreach ($this->string->split($masterArray['quantityValues'][$key], 10, true, true) as $_value) {
                    $textQuantity[] = $_value;
                }
                foreach ($textQuantity as $part) {                             
                    $page->drawText(strip_tags(ltrim($part)), $x+450, $namey -($productCount * 10), 'UTF-8');
                    $namey -= 8;
                }
                /* print quantity ends */
            }
            $productCount++;
        }
        $count++;
        $page->drawLine($x -5, $this->y+35, $x-5, $namey - ($productCount * 10) + 8 );
        $page->drawLine($x + 30, $this->y+35, $x + 30, $namey - ($productCount * 10) + 8 );
        $page->drawLine($x + 200, $this->y+35, $x + 200, $namey - ($productCount * 10) + 8 );
        $page->drawLine($x + 370, $this->y+35, $x + 370, $namey - ($productCount * 10) + 8 );
        $page->drawLine($page->getWidth()-25, $this->y+35, $page->getWidth()-25, $namey - ($productCount * 10) + 8 ); 
        $page->drawLine($x - 5, $namey - ($productCount * 10) + 8, $page->getWidth()-25, $namey - ($productCount * 10) + 8 );         
    }

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
