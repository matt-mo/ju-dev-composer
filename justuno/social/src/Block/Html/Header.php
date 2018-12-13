<?php
namespace Justuno\Social\Block\Html;

use Magento\Framework\View\Element\Template;

/**
 * Html page header block
 */
class Header extends \Magento\Theme\Block\Html\Header
{
    public function getEmbed()
    {
        $jusData = $this->_scopeConfig->getValue(
            'justuno_social_control/loginapi/justuno_embed',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        
        $this->_data['embed'] = $jusData;
        
        return $this->_data['embed'];
    }

    public function getConversion()
    {
        $jusData = $this->_scopeConfig->getValue(
            'justuno_social_control/loginapi/justuno_conversion',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        
        $this->_data['conversion'] = $jusData;
        
        return $this->_data['conversion'];
    }
}
