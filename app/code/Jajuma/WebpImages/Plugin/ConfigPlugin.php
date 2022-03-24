<?php
/**
 * @author    JaJuMa GmbH <info@jajuma.de>
 * @copyright Copyright (c) 2020 JaJuMa GmbH <https://www.jajuma.de>. All rights reserved.
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Jajuma\WebpImages\Plugin;

class ConfigPlugin
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    protected $scopeConfig;

    /**
     * ConfigPlugin constructor.
     *
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->messageManager = $messageManager;
        $this->scopeConfig = $scopeConfig;
    }

    public function aroundSave(
        \Magento\Config\Model\Config $subject,
        \Closure $proceed
    ) {
        $data = $subject->getData();
        if (isset($data['section']) && $data['section'] === 'webp') {
            if (isset($data['groups']['advance_mode']['fields']['cwebp_command']['value']) && $data['groups']['advance_mode']['fields']['cwebp_command']['value'] !== '') {
                $regexCwebp = \Jajuma\WebpImages\Helper\Data::REGX_CWEBP;
                if (!preg_match($regexCwebp, $data['groups']['advance_mode']['fields']['cwebp_command']['value'])) {
                    $this->messageManager->addNoticeMessage(__('Invalid Cwepb Custom Command. Custom Command must only include underscore (_), minus (-), space ( ) and alphanumeric characters.'));
                    $data['groups']['advance_mode']['fields']['cwebp_command']['value'] = $this->scopeConfig->getValue('webp/advance_mode/cwebp_command');
                }
            }
            if (isset($data['groups']['advance_mode']['fields']['imagemagick_command']['value']) && $data['groups']['advance_mode']['fields']['imagemagick_command']['value'] !== '') {
                $regexImagemagick = \Jajuma\WebpImages\Helper\Data::REGX_IMAGEMAGICK;
                if (!preg_match($regexImagemagick,$data['groups']['advance_mode']['fields']['imagemagick_command']['value'])) {
                    $this->messageManager->addNoticeMessage(__('Invalid Imagemagick Custom Command. Custom Command must only include underscore (_), minus (-), space ( ), comma (,), colon (:), equals sign (=) and alphanumeric characters.'));
                    $data['groups']['advance_mode']['fields']['imagemagick_command']['value'] = $this->scopeConfig->getValue('webp/advance_mode/imagemagick_command');
                }
            }
            if (isset($data['groups']['advance_mode']['fields']['path_to_cwebp']['value']) && $data['groups']['advance_mode']['fields']['path_to_cwebp']['value'] !== '') {
                $regexCwebpPath = \Jajuma\WebpImages\Helper\Data::REGX_CWEBP_PATH;
                if (!preg_match($regexCwebpPath, $data['groups']['advance_mode']['fields']['path_to_cwebp']['value'])) {
                    $this->messageManager->addNoticeMessage(__('Invalid Cwebp Path. Path must only include underscore (_), minus (-), dot (.), slash(/) and alphanumeric characters.'));
                    $data['groups']['advance_mode']['fields']['path_to_cwebp']['value'] = $this->scopeConfig->getValue('webp/advance_mode/path_to_cwebp');
                }
            }
            if (isset($data['groups']['advance_mode']['fields']['path_to_imagemagick']['value']) && $data['groups']['advance_mode']['fields']['path_to_imagemagick']['value'] !== '') {
                $regexImageMagickPath = \Jajuma\WebpImages\Helper\Data::REGX_IMAGEMAGICK_PATH;
                if (!preg_match($regexImageMagickPath, $data['groups']['advance_mode']['fields']['path_to_imagemagick']['value'])) {
                    $this->messageManager->addNoticeMessage(__('Invalid ImageMagick Path. Path must only include underscore (_), minus (-), dot (.), slash(/) and alphanumeric characters.'));
                    $data['groups']['advance_mode']['fields']['path_to_imagemagick']['value'] = $this->scopeConfig->getValue('webp/advance_mode/path_to_imagemagick');
                }
            }
            $subject->setData($data);
        }
        return $proceed();
    }
}