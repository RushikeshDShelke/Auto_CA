<?php
/**
 * Delhivery_Lastmile extension
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category  Delhivery
 * @package   Delhivery_Lastmile
 * @copyright Copyright (c) 2018
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Delhivery\Lastmile\Controller\Adminhtml\Pincode;

class Save extends \Delhivery\Lastmile\Controller\Adminhtml\Pincode
{
    /**
     * Manage Pincode factory
     * 
     * @var \Delhivery\Lastmile\Api\Data\PincodeInterfaceFactory
     */
    protected $pincodeFactory;

    /**
     * Data Object Processor
     * 
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * Data Object Helper
     * 
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * Uploader pool
     * 
     * @var \Delhivery\Lastmile\Model\UploaderPool
     */

    /**
     * Data Persistor
     * 
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * constructor
     * 
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Delhivery\Lastmile\Api\PincodeRepositoryInterface $pincodeRepository
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Delhivery\Lastmile\Api\Data\PincodeInterfaceFactory $pincodeFactory
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Delhivery\Lastmile\Model\UploaderPool $uploaderPool
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Delhivery\Lastmile\Api\PincodeRepositoryInterface $pincodeRepository,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Delhivery\Lastmile\Api\Data\PincodeInterfaceFactory $pincodeFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
    ) {
        $this->pincodeFactory      = $pincodeFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->dataObjectHelper    = $dataObjectHelper;
        $this->dataPersistor       = $dataPersistor;
        parent::__construct($context, $coreRegistry, $pincodeRepository, $resultPageFactory);
    }

    /**
     * run the action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Delhivery\Lastmile\Api\Data\PincodeInterface $pincode */
        $pincode = null;
        $postData = $this->getRequest()->getPostValue();
        $data = $postData;
        $id = !empty($data['pincode_id']) ? $data['pincode_id'] : null;
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            if ($id) {
                $pincode = $this->pincodeRepository->getById((int)$id);
            } else {
                unset($data['pincode_id']);
                $pincode = $this->pincodeFactory->create();
            }
            $this->dataObjectHelper->populateWithArray($pincode, $data, \Delhivery\Lastmile\Api\Data\PincodeInterface::class);
            $this->pincodeRepository->save($pincode);
            $this->messageManager->addSuccessMessage(__('You saved the Manage&#x20;Pincode'));
            $this->dataPersistor->clear('delhivery_lastmile_pincode');
            if ($this->getRequest()->getParam('back')) {
                $resultRedirect->setPath('delhivery_lastmile/pincode/edit', ['pincode_id' => $pincode->getId()]);
            } else {
                $resultRedirect->setPath('delhivery_lastmile/pincode');
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->dataPersistor->set('delhivery_lastmile_pincode', $postData);
            $resultRedirect->setPath('delhivery_lastmile/pincode/edit', ['pincode_id' => $id]);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('There was a problem saving the Manage&#x20;Pincode'));
            $this->dataPersistor->set('delhivery_lastmile_pincode', $postData);
            $resultRedirect->setPath('delhivery_lastmile/pincode/edit', ['pincode_id' => $id]);
        }
        return $resultRedirect;
    }

    /**
     * @param string $type
     * @return \Delhivery\Lastmile\Model\Uploader
     * @throws \Exception
     */
   
}
