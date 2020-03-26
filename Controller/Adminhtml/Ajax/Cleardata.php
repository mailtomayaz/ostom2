<?php
/**
 * Copyright © Embrace-it, Inc. All rights reserved.
 */

namespace Embraceitechnologies\OscommerceToMagento\Controller\Adminhtml\Ajax;

use Embraceitechnologies\OscommerceToMagento\Model\ExternalDb as ModelExternalDb;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Ajax controller for delete catetory/product data
 */
class Cleardata extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $pageFactory;
    /**
     * @var Embraceitechnologies\OscommerceToMagento\Model\ExternalDb
     */
    protected $modelExternalDb;
    /**
     * @var SessionManagerInterface
     */
    protected $coreSession;
    /**
     * @var Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    const CLEARED_DATA = 'data has Cleaned';
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
        //get requests parameters
        $post = $this->getRequest()->getPostValue();
        if (isset($post['clean_type'])) {
            if ($post['clean_type'] == 'Categories') {
                $Isdelete = $this->modelExternalDb->deleteCategoryData();
                return $this->jsonFactory->create()->setData(
                    ['message' => $post['clean_type'] . ' ' . self::CLEARED_DATA]
                );
            } elseif ($post['clean_type'] == 'Products') {
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
