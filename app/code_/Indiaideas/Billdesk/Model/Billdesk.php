<?php

namespace Indiaideas\Billdesk\Model;

use Indiaideas\Billdesk\Helper\Data as DataHelper;

class Billdesk extends \Magento\Payment\Model\Method\AbstractMethod
{
    const CODE = 'billdesk';
    protected $_code = self::CODE;
    protected $_isInitializeNeeded = true;
    protected $_isGateway = true;
    protected $_isOffline = true;
    protected $helper;
    protected $_minAmount = null;
    protected $_maxAmount = null;
    protected $_supportedCurrencyCodes = array('INR');
    protected $_formBlockType = 'Indiaideas\Billdesk\Block\Form\Billdesk';
    protected $_infoBlockType = 'Indiaideas\Billdesk\Block\Info\Billdesk';
    protected $urlBuilder;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Indiaideas\Billdesk\Helper\Data $helper
    ) {
        $this->helper = $helper;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger
        );

        $this->_minAmount = "0.50";
        $this->_maxAmount = "1000000";
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Instantiate state and set it to state object.
     *
     * @param string                        $paymentAction
     * @param \Magento\Framework\DataObject $stateObject
     */
    public function initialize($paymentAction, $stateObject)
    {
        $payment = $this->getInfoInstance();
        $order = $payment->getOrder();
        $order->setCanSendNewEmailFlag(false);      
    
        $stateObject->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
        $stateObject->setStatus('pending_payment');
        $stateObject->setIsNotified(false);
    }

    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        if ($quote && (
                $quote->getBaseGrandTotal() < $this->_minAmount
                || ($this->_maxAmount && $quote->getBaseGrandTotal() > $this->_maxAmount))
        ) {
            return false;
        }

        return parent::isAvailable($quote);
    }

    public function canUseForCurrency($currencyCode)
    {
        if (!in_array($currencyCode, $this->_supportedCurrencyCodes)) {
            return false;
        }
        return true;
    }

    public function buildBilldeskRequest($order)
    {
        $url = $this->getConfigData('transaction_url');
        return $url;
    }

    public function buildParams($order)
    {
        $callBackUrl=$this->urlBuilder->getUrl('billdesk/Standard/Response', ['_secure' => true]);
        if($this->getConfigData("custom_callbackurl")=='1'){
            $callBackUrl=$this->getConfigData("callback_url")!=''?$this->getConfigData("callback_url"):$callBackUrl;
        }
        $mid=$this->getConfigData("MID");
        $securityId=$this->getConfigData("security_id");
        $amt=round($order->getGrandTotal(), 2);
        $customerId=$order->getCustomerEmail();
        $orderId= $order->getRealOrderId();
        $params = $mid.'|'.$orderId.'|NA|'.$amt.'|NA|NA|NA|INR|NA|R|'.$securityId.'|NA|NA|F|NA|NA|NA|NA|NA|NA|NA|'.$callBackUrl;
//      sample:  HMACUAT|BDtest001|NA|2|NA|NA|NA|INR|NA|R|hmacuat|NA|NA|F|NA|NA|NA|NA|NA|NA|NA|http://uat.billdesk.com|
        $encrypted = $this->helper->encrypt_e($params, $this->getConfigData("secret_key"));
        $encrypted=strtoupper($encrypted);
        $urlparam = $params.'|'.$encrypted;
        return $urlparam;
    }

    public function verifychecksum_e($responseMsg)
    {
        $splitPos = strripos($responseMsg, '|');
        $msgString = substr($responseMsg, 0, $splitPos);
        $msgEncrypted = substr($responseMsg, $splitPos + 1);
        $msgEncryptedNew = strtoupper($this->helper->encrypt_e($msgString, $this->getConfigData("secret_key")));
        if ($msgEncrypted == $msgEncryptedNew) {
            $validFlag = TRUE;
        } else {
            $validFlag = FALSE;
        }
        return $validFlag;
    }

    public function autoInvoiceGen()
    {
        $result = $this->getConfigData("payment_action");            
        return $result;
    }

    public function getRedirectUrl()
    {
        $url = $this->getConfigData('transaction_url');
        return $url;
    }

    public function getReturnUrl()
    {

    }

    public function getCancelUrl()
    {

    }
}
