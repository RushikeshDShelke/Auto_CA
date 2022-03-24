<?php

namespace Dotzotfront\ShippingTracking\Plugin;

class Invoice
{
  /**
   * Add Invoice # barcode to invoice PDF.
   * @param \Magento\Sales\Model\Order\Pdf\Invoice $subject
   * @param \Zend_Pdf_Page $page
   * @param string $text
   */
  public function beforeInsertDocumentNumber($subject, $page, $text) {

   
    $docHeader = $subject->getDocHeaderCoordinates();
    $image = $this->_generateBarcode($text);
    //Convert barcode px dimensions to points
    $width = $image->getPixelWidth() * 96 / 100;
    $height = $image->getPixelHeight() * 90 / 100;
    $page->drawImage($image, $docHeader[2] - $width, $docHeader[1] - $height, $docHeader[2], $docHeader[1]);
  }

  /**
   * @param string $text
   * @return \Zend_Pdf_Resource_Image_Png
   */
  protected function _generateBarcode($text) {
      
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
    $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
    $connection = $resource->getConnection();
    $tableName = $resource->getTableName('sales_order');
    $t1 = $resource->getTableName('sales_invoice');
    
    $check = substr($text,10,100000);
    
    $newsql = "SELECT `main_table`.*, `ot`.* FROM " .$tableName. " AS `main_table` INNER JOIN " .$t1. " AS `ot` ON main_table.entity_id = ot.order_id WHERE (ot.increment_id = $check)";

    $resultdot = $connection->fetchRow($newsql);    

    if($resultdot['docketno'] != NULL)
    {
    
        $text = 'Invoice # '.$resultdot['docketno'];  
    }
    else
    {
        $text = $text;
    }
      
    $config = new \Zend_Config([
      'barcode' => 'code128',
      'barcodeParams' => [
        'text' => $this->_extractInvoiceNumber($text),
        'drawText' => true
      ],
      'renderer' => 'image',
      'rendererParams' => ['imageType' => 'png']
    ]);

    $barcodeResource = \Zend\Barcode\Barcode::factory($config)->draw();

    ob_start();
    imagepng($barcodeResource);
    $barcodeImage = ob_get_clean();

    $image = new \Zend_Pdf_Resource_Image_Png('data:image/png;base64,'.base64_encode($barcodeImage));

    return $image;
  }

  /**
   * Strip "Invoice # " from input string.
   * @param string $text
   * @return string
   */
  protected function _extractInvoiceNumber($text) {
    preg_match("/.*#\s(.*)/", $text, $matches);

    return $matches[1];
  }
}