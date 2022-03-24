<?php


namespace Olegnax\Athlete2\Block\Adminhtml;


use Magento\Backend\Block\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Helper\Js;
use Olegnax\Athlete2\Helper\Helper;
use Olegnax\Core\Helper\ModuleInfo;

class Info extends Fieldset
{
    /**
     * @var Helper
     */
    protected $_helper;
    /**
     * @var ModuleInfo
     */
    private $_moduleInfo;

    public function __construct(
        Context $context,
        Session $authSession,
        Js $jsHelper,
        ModuleInfo $moduleInfo,
        Helper $helper,
        array $data = []
    ) {
        $this->_moduleInfo = $moduleInfo;
        $this->_helper = $helper;
        parent::__construct($context, $authSession, $jsHelper, $data);
    }
	
	public function checkModulesVersion( $moduleName ) {
		$index = 0;		
		if ( !empty( $moduleName ) && is_array( $moduleName ) ){
			foreach ( $moduleName as $name ) {
				$module = $this->_moduleInfo->getModuleInfo( 'Olegnax_' . $name );
				if (empty($module)) {
					$module = [
						'update_status' => false,
						'setup_version' => ''
					];
				}
				if ( $module[ 'update_status' ] )
					$index++;
			}
		}
		return $index;
	}
	/**
     * Retrieve HTML markup for given form element
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
		$modulesList= array('Athlete2','Quickview','MegaMenu','ProductLabel','BrandSlider','BannerSlider','Athlete2Slideshow','ProductSlider','ProductReviewsSummary','Core');
        $license = $this->_helper->get();
        $code = $this->_helper->getSystemDefaultValue('athlete2_license/general/code');
        $status = !empty($license)
            && isset($license->data->the_key)
            && $license->data->the_key == $code
            && $license->data->status == "active";
        $devLicense = $status && isset($license->notices->develop);
        $supportExpired = $status && $license->data->has_expired;

        $notice = [];
        $notice[] = '<a href="https://olegnax.com/" target="_blank" class="ox-info-block__logo"></a>';
        $notice[] = '<div class="ox-info-block__title">' . __('Athlete2') . '</div>';
        if ($devLicense) {
            $notice[] = '<div class="ox-info-block__dev">' . __('Dev License Activated. <br>Do not use on live store.') . '</div>';
        }
        if ($status) {
            $module = $this->_moduleInfo->getModuleInfo($this->getModuleName());
            $theme = $this->_moduleInfo->getThemeInfo('frontend/Olegnax/athlete2', 'Athlete2');
            if (empty($theme)) {
                $theme = $this->_moduleInfo->getThemeInfo('frontend/Olegnax/Athlete2', 'Athlete2');
            }
            if (empty($theme)) {
                $theme = [
                    'update_status' => false,
                    'setup_version' => ''
                ];
            }
			$isExpired = '';
			if($theme['update_status']){
				$isExpired = ' expired';
			}
			$notice[] = '<div class="ox-info-block__version'. $isExpired .'">';
			// Current Theme version
			$notice[] = '<div class="ox-theme-version">Theme v' . $this->escapeHtml($theme['setup_version']);
			// Check if Theme is expired
            if ($theme['update_status']) {
                $notice[] = '<span class="ox-server-version">v'.$this->escapeHtml($theme['server_version']).'</span><a href="' . $this->escapeUrl($theme['url_changelog']) . '">' . __("What's New") . '</a>';
            }
			$notice[] = '</div>';
			// check if Athlete2 Module is expired
           /* if ($module['update_status']) {
				$notice[] = '<div class="module-expired">Athlete2 Module - v' . $this->escapeHtml($module['setup_version']) . '<span class="server-version">v'.$this->escapeHtml($module['server_version']).'</span>';
				if (array_key_exists('url_changelog', $module)) { 
					$notice[] = '<a href="' . $this->escapeUrl($module['url_changelog']) . '">' . __("What's New") . '</a>';
				}
				$notice[] = '</div>';
            }*/
			// check if any of Olegnax Modules are expired
			$expiredModules = $this->checkModulesVersion($modulesList);
			if($expiredModules > 0){
				$notice[] = '<div class="ox-modules-expired"><a href="'. $this->getUrl('olegnax/modules/update') .'">' . $expiredModules . ' of theme modules outdated.</a></div>';
			}
			//close ox-info-block__version
			$notice[] = '</div>';
						
            $notice[] = '<div class="ox-info-block__right">';
            $notice[] = '<div class="ox-info-block__docs"><a href="https://athlete2.com/documentation/" target="_blank">' . __('User Guide') . '</a></div>';

            $notice[] = '<div class="ox-info-block__support support-' . ($supportExpired ? 'expired' : 'active') . '"><div class="ox-wrapper"><span class="label">' . __('Support') . '</span>';
            if ($supportExpired) {
                $notice[] = '<a href="https://themeforest.net/item/athlete2-strong-magento-2-theme/23693737" target="_blank">' . __('Renew') . '</a>';
            } else {
                $notice[] = '<a href="https://olegnax.com/help" target="_blank">' . __('Active') . '</a>';
            }
            $notice[] = '</div></div></div>';
        } else {
            $notice[] = '<strong style="color:#a4a4a4">Theme is not Activated.</strong>';
        }
        $html = sprintf(
            ('<div class="ox-info-block">%s</div>'),
            implode('', $notice)
        );

        if ($element->getIsNested()) {
            $html = '<tr class="nested"><td colspan="4">' . $html . '</td></tr>';
        }
        return $html;
    }
}