<?php

namespace Olegnax\Athlete2\Block\Adminhtml;

use Olegnax\Athlete2\Block\Template;

class Notice extends Template
{

    protected function _toHtml()
    {
		$license = $this->getHelper()->get();
		$section = $this->getRequest()->getParam('section');
		$notice = '';
		if (empty($license)) {
			if ('athlete2_settings' == $section || 'athlete2_design' == $section) {
				$closeNum = rand(1, 3);
				$notice .='<div class="ox-admin__modal ox-admin__modal-license opened"><div class="ox-admin-modal__inner" style="text-align: center;">'
				. ' <h2>' . __( 'Athlete 2 Theme is not Activated.' ) . '</h2>'
					. '<h3>' . __( 'Please add your purchase code in ') . '<a href="' . $this->getLicenseUrl() . '">' . __('Athlete 2 / Theme License') . '</a></h3>'
					. '<hr style=" margin: 30px 0;border: 0; border-bottom: 1px solid #dbdbdb;">'
					. '<h3>Press <span style="color:red">'. $closeNum .'</span> to close modal.</h3>'
					. '<button style="font-size: 18px; margin: 6px;" class="action-primary '. (($closeNum === 1) ? 'ox-close-modal-license' : '') .'">1</button>'
					. '<button style="font-size: 18px; margin: 6px;" class="action-primary '. (($closeNum === 2) ? 'ox-close-modal-license' : '') .'">2</button>'
					. '<button style="font-size: 18px; margin: 6px;" class="action-primary '. (($closeNum === 3) ? 'ox-close-modal-license' : '') .'">3</button>'
				. '</div></div>';
				$notice .='<script>require( [\'jquery\',], function ( $ ) { \'use strict\'; $( \'body\' ).on( \'click\', \'.ox-close-modal-license\', function ( e ) {  e.preventDefault(); $( this ).closest( \'.ox-admin__modal-license\' ).removeClass( \'opened\' ); } );   } );</script>';
			}
			if ('athlete2_license' !== $section) {
					$notice .= '<div class="ox-license-status ox-notice-license status-disable"><span class="icon"></span><div class="inner"><h2 class="ox-license-status__title">' . __('Athlete2 Theme ') . '<span class="undelined">' . __('Not Activated!') . '</span><a href="' . $this->getLicenseUrl() . '">' . __('Click here to Activate') . '</a></h2></div></div>';
			}
			return $notice;
		}

        return '';
    }

    protected function getLicenseUrl()
    {
        return $this->getUrl('*/*/*', ['_current' => true, 'section' => 'athlete2_license']);
    }

}
