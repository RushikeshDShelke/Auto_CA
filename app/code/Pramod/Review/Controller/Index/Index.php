<?php
namespace Pramod\Review\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;
    private $jsonResultFactory;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
		\Magento\Framework\View\Result\PageFactory $pageFactory)
	{
		$this->_pageFactory = $pageFactory;
        $this->jsonResultFactory = $jsonResultFactory;
		return parent::__construct($context);
	}

	public function execute()
	{
    
        $pincode = $_GET['id'];
        // print_r($pincode);
        $state = "";
        $District = "";
        $file = fopen('https://uat.craftmaestros.com/livereplica/test.csv', 'r');
        while (($line = fgetcsv($file)) !== FALSE) {
            // print_r($line);
            if($pincode == $line[0]){                
               $state = $line[1];
               $District = $line[2];
            }
         
        //   exit();
        }
        fclose($file);
        
        $data = ['state' => $state, 'district' => $District];
        // print_r($data);
        // exit();
        $result = $this->jsonResultFactory->create();
        $result->setData($data);
        return $result;
	}
}