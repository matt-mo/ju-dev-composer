<?php
namespace Justuno\Social\Block\System\Config\Form;

use Magento\Backend\Block\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\View\Helper\Js;
use Magento\Config\Block\System\Config\Form\Fieldset;

class Custom1 extends \Magento\Config\Block\System\Config\Form\Fieldset
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
            $html.="<span>Justuno Dashboard <a href=".$secure_login_url." target='_blank'> Click here </a>
            </span>";
        } else {
            $justuno_link = $this->getUrl('adminhtml/system_config/edit/section/justuno_social_control');
            $html.="<span>";
            $html.="Please <a href=".$justuno_link."> click here </a> to update your magento/justuno app settings.";
            $html.="</span>";
        }
        $html .= $this->_getFooterHtml($element);
        return $html;
    }
}
