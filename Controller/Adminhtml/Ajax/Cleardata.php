<?php
namespace Embraceit\OscommerceToMagento\Controller\Adminhtml\Ajax;

use Embraceit\OscommerceToMagento\Model\ExternalDb as ModelExternalDb;
//use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Session\SessionManagerInterface;

class Cleardata extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $pageFactory;
    protected $modelExternalDb;
    protected $coreSession;
    protected $jsonFactory;
    const CLEARED_DATA = 'data has Clean';
    const CLEARED_ERROR = 'There is error in clearning data please try again';
    const DEFAULT_MESSAGE = 'No action taken';
    /**
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        ModelExternalDb $modelExternalDb,
        SessionManagerInterface $coreSession,
        JsonFactory $jsonFactory
    ) {
        $this->pageFactory = $pageFactory;
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
        $post = $this->getRequest()->getPostValue();
        if (isset($post['clean_type'])) {
            if ($post['clean_type'] == 'category') {
                $Isdelete = $this->modelExternalDb->deleteCategoryData();
                return $this->jsonFactory->create()->setData(
                    ['message' => $post['clean_type'] . ' ' . self::CLEARED_DATA]
                );
            } elseif ($post['clean_type'] == 'product') {
                $Isdelete = $this->modelExternalDb->deleteProductData();
                return $this->jsonFactory->create()->setData(
                    ['message' => $post['clean_type'] . ' ' . self::CLEARED_DATA]
                );
            } else {
                return $this->jsonFactory->create()->setData(
                    ['message' => self::DEFAULT_MESSAGE]
                );
            }
        }
    }
}
