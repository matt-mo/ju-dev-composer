<?php
namespace Justuno\Social\Controller\Adminhtml\Custom;

class Saveconfig extends \Magento\Backend\App\Action
{
    private $customerModel;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Justuno\Social\Model\Rewrite\Demo $customerModel
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->customerModel = $customerModel;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->customerModel->demo();
        $this->_redirect('adminhtml/system_config/edit/section/justuno_social_control');
    }
}
