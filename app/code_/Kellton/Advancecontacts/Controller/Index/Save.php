<?php

namespace Kellton\Advancecontacts\Controller\Index;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\MailException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
class Save extends \Magento\Framework\App\Action\Action
{
    // public function execute()
    // {

    //     $this->_view->loadLayout();
    //     $this->_view->getLayout()->initMessages();
    //     $this->_view->renderLayout();
    // }

     /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
	 
	protected $scopeConfig;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    private $fileUploaderFactory;
	private $fileSystem;
	
    public function __construct(
	    Context $context,
		Filesystem $fileSystem,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
		\Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        StoreManagerInterface $storeManager
    ) {
		parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
		$this->fileSystem = $fileSystem;
        $this->transportBuilder = $transportBuilder;
		$this->fileUploaderFactory = $fileUploaderFactory;
        $this->storeManager = $storeManager;
    }

	 
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
		
		//$filesData = $this->getRequest()->getFiles('image');
		//print_r($filesData);
		//die('ss1');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
		
        $resultRedirect = $this->resultRedirectFactory->create();
		
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id);
                $model->setCreatedAt(date('Y-m-d H:i:s'));
            }
			
		//$data = $this->getRequest()->getParams();
        if ($data) {
			 $model = $this->_objectManager->create('Kellton\Advancecontacts\Model\Advancecontact');
			/*$files = $this->getRequest()->getFiles();
			if (isset($files['image']) && !empty($files['image']["name"])){
			try{	
            $model = $this->_objectManager->create('Kellton\Advancecontacts\Model\Advancecontact');

				     $uploader = $this->fileUploaderFactory->create(['fileId' => 'image']);
					 
			         $uploader->setAllowRenameFiles(true);
			         $uploader->setFilesDispersion(true);
			         $uploader->setAllowCreateFolders(true);
					 $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
			         $path = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('advancecontacts');
			         $result = $uploader->save($path);
					 //print_r($result);
					 //die;
					 $filePath = $result['path'].$result['file'];
					 $fileName = $result['name'];
	               
			} catch (\Exception $e) {
			
			}
			} */
            $model->setData($data);
			//die('welcome1');

            try {
                $model->save();
				
				 /** Email Send Code */
        $templateId     = 27;
        $store = $this->storeManager->getStore()->getId();
        $sender         = array('name' => $data['first_name'], 'email' => $data['email_id']);
        $templateVars   = array(
		'first_name' => $data['first_name'],
		'last_name' => $data['last_name'],
		'telephone' => $data['telephone'],
		'email_id' => $data['email_id'],
		'category' => $data['category'],
		'description' => $data['description']
		);
        $templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $store);
        $from           = $sender;
	$to             = 'care@craftmaestros.com,cm.techlead@craftmaestros.com';//$data['email_id'];
	$to = ['care@craftmaestros.com','cm.techlead@craftmaestros.com'];

        $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
                                ->setTemplateOptions($templateOptions)
                                ->setTemplateVars($templateVars)
                                ->setFrom($from)
                                ->addTo($to)
								//->addAttachment($filePath, $fileName)
                                ->getTransport();
				$transport->sendMessage();
				//unlink($filePath);
				
                $this->messageManager->addSuccess(__('Your query has been submitted. You will hear from us at the earliest. Thank You.'));
                
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Advancecontact.'));
            }

            //$this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
	
	public function getStorename()
    {
        return $this->_scopeConfig->getValue(
            'trans_email/ident_support/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getStoreEmail()
    {
        return $this->_scopeConfig->getValue(
            'trans_email/ident_support/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
	
	
}
