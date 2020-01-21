<?php
namespace Embraceit\OscommerceToMagento\Controller\Adminhtml\Ajax;

use Magento\Framework\Controller\Result\JsonFactory;
//use Magento\Framework\Controller\Result\JsonFactory;
use Embraceit\OscommerceToMagento\Model\ExternalDb as ModelExternalDb;

class Addproducts extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_pageFactory;
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
        $this->_pageFactory = $pageFactory;
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
        //$catCount=$this->modelExternalDb->getCategoryCount();
        $products=$this->modelExternalDb->addProducts();
        return $this->jsonFactory->create()->setData(['importStatus'=>$products]);
    }
}
