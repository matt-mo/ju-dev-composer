<?php
namespace Justuno\Social\Model\Rewrite;

class Demo extends \Magento\Config\Model\ResourceModel\Config
{
    public function demo()
    {
        $this->saveConfig('justuno_social_control/loginapi/justuno_embed', '', 'default', 0);
        $this->saveConfig('justuno_social_control/loginapi/justuno_conversion', '', 'default', 0);
        $this->saveConfig('justuno_social_control/loginapi/justuno_guid', '', 'default', 0);
        $this->saveConfig('justuno_social_control/loginapi/justuno_appid', '', 'default', 0);
        $this->saveConfig('justuno_social_control/loginapi/justuno_secure_login_url', '', 'default', 0);
        $this->saveConfig('justuno_social_control/loginapi/justuno_email', '', 'default', 0);
        $this->saveConfig('justuno_social_control/loginapi/justuno_password', '', 'default', 0);

        $this->saveConfig('justuno_social_control/registerapi/justuno_new_email', '', 'default', 0);
        $this->saveConfig('justuno_social_control/registerapi/justuno_new_domain', '', 'default', 0);
        $this->saveConfig('justuno_social_control/registerapi/justuno_new_password', '', 'default', 0);
        $this->saveConfig('justuno_social_control/registerapi/justuno_new_phone', '', 'default', 0);
    }
}
