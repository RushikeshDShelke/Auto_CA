<?php

namespace Cminds\Supplierfrontendproductuploader\Helper;

use Cminds\Supplierfrontendproductuploader\Model\Config as ModuleConfig;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Email extends AbstractHelper
{
    /**
     * Transport builder object.
     *
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * Stock manager object.
     *
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Stock state object.
     *
     * @var StockStateInterface
     */
    private $stockState;

    /**
     * Module config object.
     *
     * @var ModuleConfig
     */
    private $moduleConfig;

    /**
     * Object constructor.
     *
     * @param Context               $context Context object.
     * @param TransportBuilder      $transportBuilder Transport builder object.
     * @param StoreManagerInterface $storeManager Store manager object.
     * @param StockStateInterface   $stockState Stock state object.
     * @param ModuleConfig          $moduleConfig Module config object.
     */
    public function __construct(
        Context $context,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        StockStateInterface $stockState,
        ModuleConfig $moduleConfig
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->stockState = $stockState;
        $this->moduleConfig = $moduleConfig;

        parent::__construct($context);
    }

    /**
     * Return config value.
     *
     * @param string $slug Field name.
     *
     * @return string|null
     */
    private function getConfig($slug)
    {
        return $this->scopeConfig->getValue(
            'configuration/suppliers_notifications/' . $slug
        );
    }

    /**
     * Send corresponding email template
     *
     * @param string   $template Email template path.
     * @param array    $sendTo Recipient information.
     * @param array    $templateParams Template params.
     * @param int|null $storeId Store id.
     *
     * @return Email
     */
    public function sendEmailTemplate(
        $template,
        $sendTo = [],
        $templateParams = [],
        $storeId = null
    ) {
        $senderName = $this->scopeConfig->getValue(
            'trans_email/ident_general/name'
        );
        $senderEmail = $this->scopeConfig->getValue(
            'trans_email/ident_general/email'
        );
        $sender = ['name' => $senderName, 'email' => $senderEmail];

        $templateId = $this->scopeConfig->getValue(
            $template,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $transport = $this->transportBuilder
            ->setTemplateIdentifier($templateId)
            ->setTemplateOptions(
                [
                    'area' => Area::AREA_FRONTEND,
                    'store' => $storeId,
                ]
            )
            ->setTemplateVars($templateParams)
            ->setFrom($sender)
            ->addTo($sendTo['email'], $sendTo['name'])
            ->getTransport();

        try {
            $transport->sendMessage();
        } catch (MailException $e) {
            $this->_logger->critical($e);
        }

        return $this;
    }

    /**
     * Send email to supplier that his product has been ordered.
     *
     * @param       $supplier
     * @param array $itemsData
     *
     * @return Email
     */
    public function newOrderEmail($supplier, array $itemsData)
    {
        $orderId = $itemsData[0]['order_id'];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
        $order = $objectManager->create('\Magento\Sales\Model\OrderRepository')->get($orderId);
        $Incrementid = $order->getIncrementId();
   
        $generalNotificationEnabled = $this->moduleConfig
            ->isSupplierOrderedProductsNotificationEnabled();
        if ($generalNotificationEnabled === false) {
            return $this;
        }

        $notificationConfigurationEnabled = $this->moduleConfig
            ->isSupplierOrderedProductsNotificationConfigurationEnabled();
        $supplierNotificationEnabled = (bool)$supplier
            ->getNotificationProductOrdered();
        if ($notificationConfigurationEnabled === true
            && $supplierNotificationEnabled === false
        ) {
            return $this;
        }

        $orderData = current($itemsData);
        $message = $this->getConfig('email_text_on_product_was_ordered');

        $placeHolders = [
                            '{{supplierName}}',
                            '{{productName}}',
                            '{{productQty}}',
                            '{{price}}',
                            '{{sku}}',
                            '{{firstname}}',
                            '{{lastname}}',
                            '{{street}}',
                            '{{city}}',
                            '{{email}}',
                            '{{postcode}}',
                            '{{region}}',
                            '{{getCountryId}}',
                            '{{order_id}}',
                            '{{id}}',
                            '{{artisannumber}}',
                            '{{ordstatus}}',
                            '{{orderdate}}',
                            '{{productimage}}',
                            '{{productsize}}',
                            '{{deliverydate}}'
        ];

        $customerFullName = $supplier->getFirstname()
            . ' ' . $supplier->getLastname();

        if (is_array($orderData['street'])) {
            $street = implode(' ', $orderData['street']);
        } else {
            $street = $orderData['street'];
        }

        $replacements = array(
              $customerFullName,
            $itemsData[0]['name'],
            $itemsData[0]['qty_ordered'],
            $itemsData[0]['price'],
            $itemsData[0]['sku'],
            $orderData['firstname'],
            $orderData['lastname'],
            $street,
            $orderData['city'],
            $orderData['email'],
            $orderData['postcode'],
            $orderData['region'],
            $orderData['getCountryId'],
            $Incrementid,
            $itemsData[0]['id'],
            $itemsData[0]['artisannumber'],
            $itemsData[0]['ordstatus'],
            $itemsData[0]['orderdate'],
            $itemsData[0]['image'],
            $itemsData[0]['productsize'],
            $itemsData[0]['deliverydate']
        );

        $message = $this->prepareTemplateVar(
            $message,
            $placeHolders,
            $replacements
        );

        $productSectionPlaceHolders = [
                             '{{productName}}',
                            '{{productLink}}',
                            '{{productQty}}',
                            '{{price}}',
                            '{{sku}}',
        ];

        preg_match(
            '/{{productSectionStart}}(.*){{productSectionEnd}}/',
            $message,
            $matches
        );
        if (count($matches) > 1) {
            $productSectionTemplate = $matches[1];
            $productSection = '';

            foreach ($itemsData as $itemData) {
                $productSectionReplacements = [
                    $itemData['product_name'],
                    $itemData['product_url'],
                    $itemData['qty_ordered'],
                    $itemData['price'],
                    $itemData['sku'],
                ];

                $productSection .= $this->prepareTemplateVar(
                    $productSectionTemplate,
                    $productSectionPlaceHolders,
                    $productSectionReplacements
                );
            }

            $message = preg_replace(
                '/{{productSectionStart}}(.*){{productSectionEnd}}/',
                $productSection,
                $message
            );
        }
        $subject  = 'Craft Maestros Order Receipt: Artisan Order number '.$itemsData[0]['artisannumber'].', Product ID: '. $itemsData[0]['sku'];
        $vars = ['message' => $message, 'subject' => $subject];

        $this->sendEmailTemplate(
            'email_templates/order_new_email',
            [
                'email' => $supplier->getEmail(),
                'name' => $customerFullName,
            ],
            $vars,
            $this->storeManager->getStore()->getId()
        );

        return $this;
    }

    /**
     * Send email to supplier that his product has been approved.
     *
     * @param $supplier
     * @param $product
     *
     * @return Email
     */
    public function productApproved($supplier, $product)
    {
        if (!$this->getConfig('notify_supplier_when_product_was_approved')
            || (int)$supplier->getNotificationProductApproved() !== 1
        ) {
            return $this;
        }

        $subject = $this->getConfig('email_when_product_was_approved');
        $message = $this->getConfig('email_text_on_product_approvation');

        $placeHolders = [
            '{{supplierName}}',
            '{{productName}}',
            '{{productLink}}',
            '{{productQty}}',
        ];

        $customerFullName = $supplier->getFirstname()
            . ' ' . $supplier->getLastname();
        $productLink = $product->getProductUrl();

        $replacements = [
            $customerFullName,
            $product->getName(),
            $productLink,
            $this->stockState->getStockQty(
                $product->getId(),
                $product->getStore()->getWebsiteId()
            ),
        ];

        $subject = $this->prepareTemplateVar(
            $subject,
            $placeHolders,
            $replacements
        );
        $message = $this->prepareTemplateVar(
            $message,
            $placeHolders,
            $replacements
        );

        $vars = ['message' => $message, 'subject' => $subject];

        $this->sendEmailTemplate(
            'email_templates/product_approved_email',
            [
                'email' => $supplier->getEmail(),
                'name' => $customerFullName,
            ],
            $vars,
            $this->storeManager->getStore()->getId()
        );

        return $this;
    }

    /**
     * Prepare template variable.
     *
     * @param string $string String to prepare.
     * @param array  $placeHolders Place holders.
     * @param array  $replacements Replacement values.
     *
     * @return string
     */
    private function prepareTemplateVar(
        $string,
        array $placeHolders,
        array $replacements
    ) {
        $string = str_replace($placeHolders, $replacements, $string);
        $string = trim($string);
        $string = preg_replace('/\n\s+/', '', $string);

        return $string;
    }
}
