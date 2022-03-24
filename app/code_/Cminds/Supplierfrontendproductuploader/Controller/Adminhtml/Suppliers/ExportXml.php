<?php

namespace Cminds\Supplierfrontendproductuploader\Controller\Adminhtml\Suppliers;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;

class ExportXml extends Action
{
    const ADMIN_RESOURCE = 'Cminds_Supplierfrontendproductuploader::manage_suppliers';

    /**
     * Response Http File factory.
     *
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * ExportXml constructor.
     *
     * @param Context $context
     * @param FileFactory $fileFactory
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory
    ) {
        $this->fileFactory = $fileFactory;

        parent::__construct($context);
    }

    /**
     * Execute controller main logic. Export data in xml format.
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $fileName = 'suppliers.xml';
        $content = $this->_view->getLayout()
            ->createBlock(
                'Cminds\Supplierfrontendproductuploader\Block\Adminhtml'
                . '\Supplier\Supplierlist\Grid'
            );

        return $this->fileFactory->create(
            $fileName,
            $content->getExcelFile($fileName),
            DirectoryList::VAR_DIR
        );
    }
}
