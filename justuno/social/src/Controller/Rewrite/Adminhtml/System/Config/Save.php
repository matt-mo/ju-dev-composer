<?php
namespace Justuno\Social\Controller\Rewrite\Adminhtml\System\Config;

class Save extends \Magento\Config\Controller\Adminhtml\System\Config\Save
{
    private $curl;
    private $domdocumentfactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Config\Model\Config\Structure $configStructure,
        \Magento\Config\Controller\Adminhtml\System\ConfigSectionChecker $sectionChecker,
        \Magento\Config\Model\Config\Factory $configFactory,
        \Magento\Framework\Cache\FrontendInterface $cache,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\DomDocument\DomDocumentFactory $domdocumentfactory
    ) {
        parent::__construct($context, $configStructure, $sectionChecker, $configFactory, $cache, $string);
        $this->curl = $curl;
        $this->domdocumentfactory = $domdocumentfactory;
    }

    public function execute()
    {
        try {
            // custom save logic
            $this->_saveSection();
            $section = $this->getRequest()->getParam('section');
            $website = $this->getRequest()->getParam('website');
            $store = $this->getRequest()->getParam('store');

            $configData = [
                'section' => $section,
                'website' => $website,
                'store' => $store,
                'groups' => $this->_getGroupsForSave(),
            ];

            if ($section=='justuno_social_control') {
                /* For Login Section*/
                $jLoginEmail    = $configData['groups']['loginapi']['fields']['justuno_email']['value'];
                $jLoginPassword = $configData['groups']['loginapi']['fields']['justuno_password']['value'];
                $jLoginKey      = '467cd758-5745-4385-906b-6c76271c343a';
                if ($jLoginEmail && $jLoginPassword) {
                    $params = [
                                    'apiKey'=>$jLoginKey,
                                    'email'=>$jLoginEmail,
                                    'domain'=>'',
                                    'guid'=>'',
                                    'appid'=>$jLoginPassword,
                                    'password'=>$jLoginPassword
                                    ];
                    $dashboard = (string)self::getDashboardLink($params);
                    $configData['groups']['loginapi']['fields']['justuno_secure_login_url']['value'] = $dashboard;
                }
                /* For Login Section*/

                /* For Register Section*/
                $jNewEmail    = $configData['groups']['registerapi']['fields']['justuno_new_email']['value'];
                $jNewDomain   = $configData['groups']['registerapi']['fields']['justuno_new_domain']['value'];
                $jNewPassword = $configData['groups']['registerapi']['fields']['justuno_new_password']['value'];
                $jNewKey      = '467cd758-5745-4385-906b-6c76271c343a';
                $jNewGuid     = "";
                $jNewAppid     = self::generateRandomString();
                if ($jNewEmail && $jNewDomain && $jNewPassword) {
                    $params = [
                        'apiKey'=>$jNewKey,
                        'email'=>$jNewEmail,
                        'domain'=>$jNewDomain,
                        'guid'=>$jNewGuid,
                        'appid'=>$jNewAppid,
                        'password'=> $jNewPassword
                    ];
                    $justuno = self::getWidgetConfig($params);
                    $dashboard = (string)self::getDashboardLink($params);
                    $embedcode = "<script type='text/javascript'>".$justuno['embed']."</script>";
                    $conversioncode = "<script type='text/javascript'>".$justuno['conversion']."</script>";
                    $configData['groups']['loginapi']['fields']['justuno_embed']['value'] = $embedcode;
                    $configData['groups']['loginapi']['fields']['justuno_conversion']['value'] = $conversioncode;
                    $configData['groups']['loginapi']['fields']['justuno_guid']['value'] = $justuno['guid'];
                    $configData['groups']['loginapi']['fields']['justuno_appid']['value'] = $jNewAppid;
                    $configData['groups']['loginapi']['fields']['justuno_secure_login_url']['value'] = $dashboard;
                    $configData['groups']['loginapi']['fields']['justuno_email']['value'] = $jNewEmail;
                    $configData['groups']['loginapi']['fields']['justuno_password']['value'] = $jNewPassword;
                    $configData['groups']['registerapi']['fields']['justuno_new_email']['value'] = null;
                    $configData['groups']['registerapi']['fields']['justuno_new_domain']['value'] = null;
                    $configData['groups']['registerapi']['fields']['justuno_new_password']['value'] = null;
                    $configData['groups']['registerapi']['fields']['justuno_new_phone']['value'] = null;
                }
            }
            /** @var \Magento\Config\Model\Config $ExconfigModel  */
            $ExconfigModel = $this->_configFactory->create(['data' => $configData]);
            $ExconfigModel->save();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $eXmessages = explode("\n", $e->getMessage());
            foreach ($eXmessages as $message) {
                $this->messageManager->addError($message);
            }
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __('Something went wrong while saving this configuration:') . ' ' . $e->getMessage()
            );
        }

        $this->_saveState($this->getRequest()->getPost('config_state'));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return parent::execute();
    }

    private function generateRandomString($length = 16)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    private function getWidgetConfig($settings)
    {
        if (!extension_loaded("curl")) {
            $message = 'Plug-in requires php `curl` extension which seems to be not activated on this server. 
            Please activate it and try again.';
            $this->messageManager->addError($message);
        }

        $params = [
          'key' => $settings['apiKey'],
          'email' => $settings['email'],
          'domain' => $settings['domain'],
          'password' => $settings['password'],
          'appid' => $settings['appid'],
          'action' => 'install',
        ];
        
        $params['password'] = isset($settings['password']) ? $settings['password'] : null;
        
        $apiEndpointUrl = 'https://www.justuno.com/api/endpoint.html';
        $query  = http_build_query($params);
        $api_url = $apiEndpointUrl.'?'.$query;
        $this->curl->setOption(CURLOPT_SSL_VERIFYPEER, false);
        $this->curl->get($api_url);
        $tudata = $this->curl->getBody();
        try {
            $dom = $this->domdocumentfactory->create();
            $dom->loadXML($tudata);
            $nodes = $dom->getElementsByTagName('result');
            if (!$nodes || ($nodes->length == 0)) {
                $this->messageManager->addError('Incorrect response from remote server');
            }

            if ($nodes->item(0)->nodeValue == 0) {
                $nodes = $dom->getElementsByTagName('error');
                $this->messageManager->addError($nodes->item(0)->nodeValue);
            }
            $justuno_conf = [];
            $nodes = $dom->getElementsByTagName('guid');
            if ($nodes && $nodes->length !== 0) {
                $settings['guid'] = $justuno_conf['guid'] = $nodes->item(0)->nodeValue;
            }
            $nodes = $dom->getElementsByTagName('embed');
            if ($nodes && $nodes->length !== 0) {
                $justuno_conf['embed'] = $nodes->item(0)->nodeValue;
            }
            $nodes = $dom->getElementsByTagName('conversion');
            if ($nodes && $nodes->length !== 0) {
                $justuno_conf['conversion'] = $nodes->item(0)->nodeValue;
            }
            return $justuno_conf;
        } catch (\Exception $e) {
            $message = __('Request error: %1', $e->getMessage());
            $this->messageManager->addError($message);
        }
    }

    /**
     * Get link to Jutsuno dashbord link using API
     */
    private function getDashboardLink($settings)
    {
        $params = [
          'key'=>$settings['apiKey'],
          'email'=>$settings['email'],
          'action'=>'login',
          'password'=>$settings['password'],
          'appid'=>$settings['appid']
        ];
        $params['password'] = isset($settings['password']) ? $settings['password'] : null;
        $apiEndpointUrl = 'https://www.justuno.com/api/endpoint.html';
        $query = http_build_query($params);
        $api_url = $apiEndpointUrl.'?'.$query;
        $this->curl->setOption(CURLOPT_SSL_VERIFYPEER, false);
        $this->curl->get($api_url);
        $tuData = $this->curl->getBody();
        try {
            $dom = $this->domdocumentfactory->create();
            $dom->loadXML($tuData);
            $nodes = $dom->getElementsByTagName('result');
            if (!$nodes || $nodes->length == 0) {
                $this->messageManager->addError('Incorrect response from remote server');
            }
            if ($nodes->item(0)->nodeValue == 0) {
                $this->messageManager->addError('Please check your Justuno account credentials and try again.');
            }
            $nodes = $dom->getElementsByTagName('secure_login_url');
            if ($nodes && $nodes->length !== 0) {
                if ($nodes->item(0)->nodeValue != "https://www.justuno.com/login.html?redir=") {
                    $secureLoginUrl = $nodes->item(0)->nodeValue;
                } else {
                    $secureLoginUrl = null;
                }
            }
            return $secureLoginUrl;
        } catch (\Exception $e) {
            $message = __('Request error: %1', $e->getMessage());
            $this->messageManager->addError($message);
        }
    }
}
