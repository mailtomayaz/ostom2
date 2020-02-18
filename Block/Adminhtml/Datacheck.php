<?php
/**
 * Copyright © Embrace-it, Inc. All rights reserved.
 */

namespace Embraceit\OscommerceToMagento\Block\Adminhtml;

use Embraceit\OscommerceToMagento\Model\ExternalDb as ModelExternalDb;
use Magento\Backend\Block\Template;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Datacheck block
 */
class Datacheck extends Template
{

    const HOST_NAME = 'Configure Host Name';
    const DATABASE_NAME = 'Configure Database';
    const USER_NAME = 'Configure User Name';
    const DATABAS_PASSWORD = 'Configure Password';
    const OSCOMMERCE_VERSION = 'Configure OsCommerce Version';
    const CATEGORY_IMAGE_PATH = 'Category Image Path';
    const PRODUCT_IMAGE_PATH = 'Product Image Path';
    const CHUNK_SIZE = 500;
    /**
     * @var modelExternalDb
     */
    protected $modelExternalDb;
    /**
     * @var SessionManagerInterface
     */
    protected $coreSession;

    /**
     * @var formKey
     */
    protected $formKey;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @param \Magento\Backend\Block\Template\Context           $context
     * @param Magento\Framework\Session\SessionManagerInterface $coreSession
     * @param Magento\Framework\Data\Form\FormKey               $formKey
     * @param array                                             $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        ModelExternalDb $modelExternalDb,
        SessionManagerInterface $coreSession,
        FormKey $formKey,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->modelExternalDb = $modelExternalDb;
        $this->formKey = $formKey;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }

    /**
     * Category count
     *
     * @return integer
     */
    public function getCategoryInfo()
    {
        return $this->modelExternalDb->getCategoryCount();
    }

    /**
     * Category button stat
     *
     * @return string
     */

    public function getButtonStatCategory()
    {
        $categoryButtonStat = '';
        $nCounter = $this->modelExternalDb->getCategoryCount();
        if ($nCounter == 0) {
            $categoryButtonStat = ' disabled=disabled';
        }
        return $categoryButtonStat;
    }
    /**
     * Import categories from Oscommerce to magento
     *
     * @return string
     * @throws \Magento\Exception if a non-existing or not imported
     */
    public function getCategoryAdd()
    {

        return $this->modelExternalDb->addCategory();
    }
    /**
     * Number of products in oscommerce database that need to import
     *
     * @return integer
     */
    public function getProductCount()
    {
        return $this->modelExternalDb->getTotalProductsCount();
    }
    /**
     * Button state of products
     *
     * @return string
     */
    public function getButtonStatProduct()
    {
        $buttonStatProduct = '';
        $nCount = $this->modelExternalDb->getTotalProductsCount();
        if ($nCount == 0) {
            $buttonStatProduct = ' disabled=disabled ';
        }
        return $buttonStatProduct;
    }
    /**
     * Number of products in oscommerce database that need to import
     *
     * @return integer
     */
    public function getProductInfo()
    {
        return $this->modelExternalDb->queryProductsInfo();
    }
    /**
     * Attribute set information by id
     *
     * @param int $id
     * @return array
     */
    public function getAttributeSet($id)
    {
        return $this->modelExternalDb->getAtributeSet($id);
    }
    /**
     *
     * @param string $option
     * @return array
     */
    public function getQueryCustomOptions($option)
    {
        return $this->modelExternalDb->queryCustomOptions($option);
    }
    /**
     * language by id
     * @param int $id
     * @return array
     */
    public function getLanguageByID($id)
    {
        return $this->modelExternalDb->getLanguageById($id);
    }
    /**
     * Get import progress
     * @return int
     */
    public function getProgressAjax()
    {
        return $this->modelExternalDb->getValue();
    }
    /**
     * unset value of progress
     *
     */
    public function getUnsetProgressBar()
    {
        return $this->modelExternalDb->setValue(0);
    }
    /**
     *  Query custom product attributes
     * @return string
     */
    public function getProductAtr()
    {
        return $this->modelExternalDb->queryCustomProductAttributes();
    }
    /**
     *  Query custom product attributes by id
     * @param int $id
     * @return string
     */

    public function getAtributeByProductId($id)
    {
        return $this->modelExternalDb->queryGetCustomProductAttributesById($id);
    }
    /**
     *  Query custom option by attribute id
     * @param int $id
     * @return string
     */
    public function getOptionDetial($id)
    {
        return $this->modelExternalDb->queryGetOptionDetial($id);
    }
    /**
     *  Query custom option by attribute id
     * @param int $optionId
     * @param int $productId
     * @return string
     */
    public function getOptionData($optionId, $productId)
    {
        return $this->modelExternalDb->queryGetOptionData($optionId, $productId);
    }
    /**
     * Form key
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }
    /**
     *  Query count custom options
     * @return int
     */
    public function getCustomOptionCount()
    {
        $nCount = $this->modelExternalDb->getTotalCustomOptionCount();
        if ($nCount == null) {
            $nCount = 0;
        }

        return $nCount;
    }
    /**
     *  enable/disable buttons custom options
     * @return int
     */
    public function getButtonStatCustomOption()
    {
        $nCount = $this->modelExternalDb->getTotalCustomOptionCount();
        $buttonStatus = '';
        if ($nCount == null) {
            $nCount = 0;
        }
        if ($nCount == 0) {
            $buttonStatus = ' disabled=disabled ';
        }
        return $buttonStatus;
    }
    /**
     *  config product image path
     * @return string
     */
    public function getProductImagePath()
    {
        $host = $this->scopeConfig->getValue('firstsection/firstgroup/productImagePath');
        if ($host !== null) {
            return $host;
        } else {
            return self::PRODUCT_IMAGE_PATH;
        }
    }
    /**
     *  Config category image path
     * @return string
     */
    public function getCategoryImagePath()
    {
        $host = $this->scopeConfig->getValue('firstsection/firstgroup/categoryImagePath');
        if ($host !== null) {
            return $host;
        } else {
            return self::CATEGORY_IMAGE_PATH;
        }
    }
    /**
     *  attribute sets
     * @return array
     */
    public function getAttributeSets()
    {
        return $this->modelExternalDb->getAllAtrributeSet();
    }
    /**
     *  retrive attribute
     * @param string $name
     * @return array|string
     */
    public function getAtributeByName($name)
    {
        return $this->modelExternalDb->getAttributeByName($name);
    }
    /**
     *  config database host name
     * @return string
     */
    public function getDatabaseHostName()
    {
        $host = $this->scopeConfig->getValue('firstsection/firstgroup/dbHostName');
        if ($host !== null) {
            return $host;
        } else {
            return self::HOST_NAME;
        }
    }
    /**
     *  config database name
     * @return string
     */
    public function getDatabaseName()
    {
        $host = $this->scopeConfig->getValue('firstsection/firstgroup/osDbName');
        if ($host !== null) {
            return $host;
        } else {
            return self::DATABASE_NAME;
        }
    }
    /**
     *  config database user name
     * @return string
     */
    public function getDatabaseUser()
    {
        $host = $this->scopeConfig->getValue('firstsection/firstgroup/dbUserName');
        if ($host !== null) {
            return $host;
        } else {
            return self::USER_NAME;
        }
    }
    /**
     *  config database password
     * @return string
     */
    public function getDatabasePassword()
    {
        $host = $this->scopeConfig->getValue('firstsection/firstgroup/dbPassword');
        if ($host !== null) {
            return $host;
        } else {
            return self::DATABAS_PASSWORD;
        }
    }
    /**
     *  config oscommerce version number
     * @return string
     */
    public function getOsCommerceVersion()
    {
        $host = $this->scopeConfig->getValue('firstsection/firstgroup/oscVersion');
        if ($host !== null) {
            return $host;
        } else {
            return self::OSCOMMERCE_VERSION;
        }
    }
    /**
     *  delete all categories
     * @return string
     * @throws \Magento\Exception
     */
    public function queryDeleteCatData()
    {
        return $this->modelExternalDb->deleteCategoryData();
    }
    /**
     *  Query OS categories by limit
     * @param int $startLimit
     * @param int $totalLimit
     * @return string
     * @throws \Magento\Exception
     */
    public function querygetAllCategories($startLimit, $totalLimit)
    {
        return $this->modelExternalDb->getAllCategories($startLimit, $totalLimit);
    }
    /**
     *  config chunksize
     * @return int
     */
    public function getChunkSize()
    {
        if ($this->modelExternalDb->getChunkSize() !== null) {
            return $this->modelExternalDb->getChunkSize();
        } else {
            return self::CHUNK_SIZE;
        }
    }
}
