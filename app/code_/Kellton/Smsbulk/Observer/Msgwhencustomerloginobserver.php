<?php
namespace Kellton\Smsbulk\Observer;

class Msgwhencustomerloginobserver implements \Magento\Framework\Event\ObserverInterface
{

  protected $_customer;
  protected $date;  

    public function __construct(
      \Magento\Catalog\Block\Product\Context $context,     
      \Magento\Customer\Model\Customer $customer,
      \Magento\Framework\Stdlib\DateTime\DateTime $date
      ) {       
       
        $this->_customer         =  $customer;
        $this->date              =  $date;       

    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
  	    $currentdate      = $this->date->gmtDate();
        $customerobj      = $observer->getCustomer();
        $customerId       = $customerobj->getId();        
        $customer         = $this->_customer->load($customerId);

        $currentdate = date('Y-m-d H:i:s',strtotime($currentdate.'+330 minutes', 0));  
      
        //echo '<pre>'; print_r($customer->getData()); echo '</pre>'; 
         $email            = $customer->getEmail();        
         $mobile          =  $customer->getMobileNumber();       
        
         $customerFullName = $customer->getFirstname() . ' ' . $customer->getLastname();
        
        if ($mobile) {
            $msg  = 'Dear ' . $customerFullName . ', Login successful at ' . $currentdate . ' If it was not you, please report this here.Have delightful times at craftmaestros.com With appreciation,care@craftmaestros.com';
            
            $path = "http://bulkpush.mytoday.com/BulkSms/SingleMsgApi?feedid=374549&username=9810411189&password=Craft@biz!sm5&To=" . $mobile . "&Text=" . urlencode($msg);
            $ch   = curl_init($path);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $output = curl_exec($ch);
            curl_close($ch);
        }
    }
  }
