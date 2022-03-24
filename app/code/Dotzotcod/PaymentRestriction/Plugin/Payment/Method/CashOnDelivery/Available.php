<?php

namespace Dotzotcod\PaymentRestriction\Plugin\Payment\Method\CashOnDelivery;
 
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Backend\Model\Auth\Session as BackendSession;
use Magento\OfflinePayments\Model\Cashondelivery;
use Magento\Checkout\Model\Session;
 
class Available
{
 
    /**
     * @var CustomerSession
     */
    protected $customerSession;
    
    /**
     * @var BackendSession
     */
    protected $backendSession;
 
    /**
     * @param CustomerSession $customerSession
     * @param BackendSession $backendSession
     */
    public function __construct(
        CustomerSession $customerSession,
        BackendSession $backendSession

        
    ) {
        $this->customerSession = $customerSession;
        $this->backendSession = $backendSession;
    }
 
    /**
     *
     * @param Cashondelivery $subject
     * @param $result
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterIsAvailable(Cashondelivery $subject, $result)
    {
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cart = $objectManager->get('\Magento\Checkout\Model\Cart'); 
        $isLogged = $this->customerSession->isLoggedIn();
        
        if (!$isLogged) {
            return false;
        }
        else
        {
            $pcode = $cart->getQuote()->getShippingAddress()->getPostcode();
            if(isset($pcode)){
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
                $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                $connection = $resource->getConnection();
                $tableName = $resource->getTableName('wk_grid_records');

                $sql = "SELECT cod, product from " . $tableName."  WHERE pincode = " . $pcode . " AND cod = 'Y'";
                
                $result = $connection->fetchall($sql); 
                
                if($result)
                { 
                    return $result;
                }
                else
                {
                    return false;
                }
            } else{
                return false;
            }     
        }

        return $result;
    }
}
