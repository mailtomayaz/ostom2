<?php
namespace Embraceit\OscommerceToMagento\Controller\Adminhtml\Ajax;

use Embraceit\OscommerceToMagento\Model\ExternalDb as ModelExternalDb;
//use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Session\SessionManagerInterface;

class Getproductdata extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_pageFactory;
    protected $modelExternalDb;
    protected $coreSession;
    protected $jsonFactory;
    /**
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        ModelExternalDb $modelExternalDb,
        SessionManagerInterface $coreSession,
        JsonFactory $jsonFactory
    ) {
        $this->_pageFactory = $pageFactory;
        $this->modelExternalDb = $modelExternalDb;
        $this->jsonFactory = $jsonFactory;
        $this->coreSession = $coreSession;
        return parent::__construct($context);
    }
    /**
     * View page action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $countRecord = $this->modelExternalDb->getProductprogress();
        if ($countRecord == '') {
            $countRecord = 0;
        }
        return $this->jsonFactory->create()->setData(['counter' => $countRecord]);
    }
}
