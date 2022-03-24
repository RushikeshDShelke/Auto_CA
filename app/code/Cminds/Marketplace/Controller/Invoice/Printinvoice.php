<?php

namespace Cminds\Marketplace\Controller\Invoice;

use Cminds\Marketplace\Controller\AbstractController;
use Cminds\Supplierfrontendproductuploader\Helper\Data;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Pdf\Invoice as PdfInvoice;
use Magento\Store\Model\StoreManagerInterface;

class Printinvoice extends AbstractController
{
    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var Invoice
     */
    private $invoice;

    /**
     * @var PdfInvoice
     */
    private $pdfInvoice;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * Printinvoice constructor.
     *
     * @param Context               $context
     * @param Data                  $helper
     * @param FileFactory           $fileFactory
     * @param DateTime              $dateTime
     * @param Invoice               $invoice
     * @param PdfInvoice            $pdfInvoice
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface  $scopeConfig
     * @param Filesystem            $fileSystem
     */
    public function __construct(
        Context $context,
        Data $helper,
        FileFactory $fileFactory,
        DateTime $dateTime,
        Invoice $invoice,
        PdfInvoice $pdfInvoice,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        Filesystem $fileSystem
    ) {
        parent::__construct(
            $context,
            $helper,
            $storeManager,
            $scopeConfig
        );

        $this->fileFactory = $fileFactory;
        $this->dateTime = $dateTime;
        $this->invoice = $invoice;
        $this->pdfInvoice = $pdfInvoice;
        $this->fileSystem = $fileSystem;
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     * @throws \Exception
     */
    public function execute()
    {
        if (!$this->canAccess()) {
            return $this->redirectToLogin();
        }

        $invoiceId = $this->getRequest()->getParam('id');
        if ($invoiceId) {
            $invoice = $this->invoice->load($invoiceId);
            if ($invoice) {
                $pdf = $this->pdfInvoice
                    ->setIsSupplier(true)
                    ->getPdf(array($invoice));

                $fileName = 'marketplace_invoice_' . $this->dateTime->date('Y-m-d_H-i-s') . '.pdf';
                $file = $pdf->render();
                $baseDir = \Magento\Framework\App\Filesystem\DirectoryList::TMP;
                $dir = $this->fileSystem->getDirectoryWrite($baseDir);
                $dir->writeFile($fileName, $file);

                $content = [
                    'value' => $fileName,
                    'type' => 'filename',
                    'rm' => true
                ];

                $this->fileFactory->create(
                    $fileName,
                    $content,
                    $baseDir
                );
            }
        } else {
            $this->_forward('noRoute');
        }
    }
}
