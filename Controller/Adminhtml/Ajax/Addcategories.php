<?php
namespace Embraceit\OscommerceToMagento\Controller\Adminhtml\Ajax;

use Embraceit\OscommerceToMagento\Model\ExternalDb as ModelExternalDb;
//use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\JsonFactory;

class Addcategories extends \Magento\Framework\App\Action\Action
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
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        ModelExternalDb $modelExternalDb,
        JsonFactory $jsonFactory
    ) {
        $this->pageFactory = $pageFactory;
        $this->modelExternalDb = $modelExternalDb;
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
        $catCount = $this->modelExternalDb->getCategoryCount();
        $categories = $this->modelExternalDb->addCategory();
        return $this->jsonFactory->create()->setData(['importStatus' => $categories]);
    }
}
