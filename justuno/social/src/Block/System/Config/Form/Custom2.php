<?php
namespace Justuno\Social\Block\System\Config\Form;

use Magento\Backend\Block\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\View\Helper\Js;
use Magento\Config\Block\System\Config\Form\Fieldset;

class Custom2 extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $embed = $this->_scopeConfig->getValue(
            'justuno_social_control/loginapi/justuno_embed',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $secure_login_url = $this->_scopeConfig->getValue(
            'justuno_social_control/loginapi/justuno_secure_login_url',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        
        $this->setElement($element);
        $html = $this->_getHeaderHtml($element);

        if ($embed || $secure_login_url) {
            $adminUrl = $this->getUrl('justuno_social/custom/saveconfig');
            $html.= "<span>You are already connected. <a href=".$adminUrl.">Click here to disconnect if necessary</a>
            </span>";
        } else {
            foreach ($element->getElements() as $field) {
                if ($field instanceof \Magento\Framework\Data\Form\Element\Fieldset) {
                    $html .= '<tr id="row_' . $field->getHtmlId() . '"><td colspan="4">' . $field->toHtml() . '</td>
                    </tr>';
                } else {
                    $html .= $field->toHtml();
                }
            }
            $html .= $this->_getFooterHtml($element);
            return $html;
        }
    }
}
