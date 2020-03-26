<?php
/**
 * Copyright © Embrace-it, Inc. All rights reserved.
 */
namespace Embraceitechnologies\OscommerceToMagento\Controller\Adminhtml\Ajax;

use Embraceitechnologies\OscommerceToMagento\Model\ExternalDb as ModelExternalDb;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Ajax controller for custom product import progress
 */
class Getdataoptions extends \Magento\Backend\App\Action
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
    /**
     * @param \Magento\Framework\App\Action\Context $context
     */
    protected $context;

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

        $countRecord = $this->modelExternalDb->getOptionValue();
        if ($countRecord == '') {
            $countRecord = 0;
        }

        return $this->jsonFactory->create()->setData(['counter' => $countRecord]);
    }
}
