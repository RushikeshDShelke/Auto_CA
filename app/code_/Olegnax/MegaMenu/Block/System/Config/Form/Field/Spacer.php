<?php

namespace Olegnax\MegaMenu\Block\System\Config\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Spacer extends Field
{
	public function __construct(
		Context $context, array $data = []
	)
	{
		parent::__construct($context, $data);
	}

	protected function _decorateRowHtml(AbstractElement $element, $html)
	{
		return '<tr id="row_' . $element->getHtmlId() . '"><td colspan="3"><hr class="ox-settings-spacer"></td></tr>';
	}

}