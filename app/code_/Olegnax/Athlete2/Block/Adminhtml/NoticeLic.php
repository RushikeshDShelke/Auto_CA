<?php

namespace Olegnax\Athlete2\Block\Adminhtml;

use Olegnax\Athlete2\Block\Template;

class NoticeLic extends Template
{

    protected function _toHtml()
    {

        if ('athlete2_license' == $this->getRequest()->getParam('section')) {
            $license = $this->getHelper()->get();
            $code = $this->getHelper()->getSystemDefaultValue('athlete2_license/general/code');
            $notice = [];
            $status = !empty($license)
                && isset($license->data->the_key)
                && $license->data->the_key == $code
                && $license->data->status == "active";
			$supportExpired = $status && $license->data->has_expired;
			$devLicense = $status && isset($license->notices->develop) ? ' developer' : '';
			$notice[] = '<div class="ox-license-status status-' . ($status ? 'active' : 'disable') . $devLicense .'"><span class="icon"></span><div class="inner"><h2 class="ox-license-status__title">' . __('Theme License ') . '<span class="undelined">' . ($status ? __('Activated') : __('Not Activated!')) . '</span></h2>'. ( $devLicense ? __('<strong class="b-info">' . implode(' ', $license->notices->develop) . '</strong>') : '') . '</div></div>';
			if ($supportExpired) {
                if ($license->data->has_expired) {
                    $notice[] = '<div class="ox-license-status support-expired"><span class="icon"></span><div class="inner"><h2 class="ox-license-status__title">' . __('Theme Support has ') . '<span class="undelined">' . __('Expired') . '</span></h2></div><div class="right"><a href="https://themeforest.net/item/athlete2-strong-magento-2-theme/23693737" target="_blank" class="button">' . __('renew') . '</a></div></div>';
                } else {
                    $notice[] = '<div class="ox-license-status support-active"><span class="icon"></span><div class="inner"><h2 class="ox-license-status__title">' . __('Theme Support is ') . '<span class="undelined">' . __('Active') . '</span></h2><strong class="b-info">' . __('Monday - Friday,	10 - 20 GMT +1') . '</strong><div class="support-policy"><a href="https://olegnax.com/support-policy/" class="underline-link">Support Policy</a></div></div><div class="right"><a href="https://olegnax.com/help/" target="_blank" class="button">' . __('get in touch') . '</a></div></div>';
                }
				/*
                foreach ($license->notices as $noticeGroup => $_notice) {
                    if ('support' !== $noticeGroup) {
                        $notice[] = '<div class="ox-license-status ' . $noticeGroup . '"><span class="icon"></span><div class="inner"><h2 class="ox-license-status__title">' . implode(' ', $_notice) . '</h2></div></div>';
                    }
                }*/
            }
            return sprintf(
                (1 < count($notice) ? '<div class="two-blocks">%s</div>' : '%s'),
                implode('', $notice)
            );
        }

        return '';
    }

}
