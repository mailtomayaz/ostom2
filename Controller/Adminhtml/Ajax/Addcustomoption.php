<?php
namespace Embraceit\OscommerceToMagento\Controller\Adminhtml\Ajax;

use Magento\Framework\Controller\Result\JsonFactory;
//use Magento\Framework\Controller\Result\JsonFactory;
use Embraceit\OscommerceToMagento\Model\ExternalDb as ModelExternalDb;

class Addcustomoption extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $pageFactory;
    protected $modelExternalDb;
    protected $jsonFactory;
    /**
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        ModelExternalDb $modelExternalDb,
        JsonFactory $jsonFactory
    ) {
        $this->pageFactory = $pageFactory;
        $this->modelExternalDb=$modelExternalDb;
        $this->jsonFactory = $jsonFactory;
        return parent::__construct($context);
    }
    /**
     * View page action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        $startLimit = $this->getRequest()->getParam('start_limit');
        $totalLimit = $this->getRequest()->getParam('total_limit');
        $status = $this->modelExternalDb->addCustomOption($startLimit, $totalLimit);
        //$status=$this->modelExternalDb->addCustomOption();
        return $this->jsonFactory->create()->setData(['importStatus'=>$status]);
    }
}
