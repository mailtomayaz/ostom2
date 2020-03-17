<?php
/**
 * Copyright © Embrace-it, Inc. All rights reserved.
 */
namespace Embraceit\OscommerceToMagento\Model;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Product as productModel;
use Magento\Catalog\Model\ProductFactory as productFactory;
use Magento\Catalog\Model\ProductRepository as modelProductRepostry;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\OptionFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection as productCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as productCollectionFactory;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Model\AttributeManagement;
use Magento\Eav\Model\AttributeSetManagement;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Model\Entity\TypeFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as eavCollectionFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection\ConnectionFactory;
use Magento\Framework\App\State;
use Magento\framework\Filesystem\Driver\File;
use Magento\Framework\Serialize\Serializer\Serialize;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\View\Asset\Repository as assetRepo;
use Magento\Store\Model\Store as storeModel;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Main class to import oscommerece data
 *
 */
class ExternalDb
{
    /**
     * @var ConnectionFactory
     */
    protected $connectionFactory;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;
    /**
     * @var CategoryRepositoryInterface
     */
    protected $repository;
    /**
     * @var Category
     */
    protected $modelCategory;
    /**
     * @var eavCollectionFactory
     */
    protected $eavCollectionFactory;
    /**
     * @var productModel
     */
    protected $productModel;
    /**
     * @var State
     */
    protected $state;
    /**
     * @var productCollection
     */
    protected $productCollection;
    /**
     * @var ProductInterfaceFactory
     */
    protected $productFactory;
    /**
     * @var productCollectionFactory
     */
    protected $productCollectionFactory;
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;
    /**
     * @var Option
     */
    protected $option;
    /**
     * @var modelProductRepostry
     */
    protected $modelProductRepostry;
    /**
     * @var OptionFactory
     */
    protected $productOptionFactory;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManagerInterface;
    /**
     * @var storeModel
     */
    protected $storeModel;
    /**
     * @var SessionManagerInterface
     */
    protected $coreSession;
    /**
     * @var AttributeSetFactory
     */
    protected $attributeSetFactory;
    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;
    /**
     * @var TypeFactory
     */
    protected $eavTypeFactory;
    /**
     * @var AttributeSetManagement
     */
    protected $attributeSetManagement;
    /**
     * @var AttributeManagement
     */
    protected $attributeManagement;
    /**
     * @var File
     */
    protected $file;
    /**
     * @var Serialize
     */
    protected $serialize;
    /**
     * @var $this->scopeConfig->getValue('firstsection/firstgroup/dbHostName')
     */
    protected $hostName;
    /**
     * @var $this->scopeConfig->getValue('firstsection/firstgroup/osDbName')
     */
    protected $databaseName;
    /**
     * @var  $this->scopeConfig->getValue('firstsection/firstgroup/dbUserName');
     */
    protected $databaseUser;
    /**
     * @var  $this->scopeConfig->getValue('firstsection/firstgroup/dbPassword');
     */
    protected $databasPassword;
    /**
     * @var  $this->scopeConfig->getValue('firstsection/firstgroup/oscVersion');
     */
    protected $osCommerceVersion;
    /**
     * @var ModuleDataSetupInterface
     */
    protected $setup;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var CollectionFactory
     */
    protected $categoryCollection;
    /**
     *
     * @var CategorySetupFactory
     */
    protected $assetRepo;
    /**
     *
     * @var assetRepo
     */
    protected $request;
    /**
     *
     * @var RequestInterface
     */
    protected $categorySetupFactory;
    /**
     * @param ConnectionFactory $connectionFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param CategoryFactory $categoryFactory
     * @param CategoryRepositoryInterface $repository
     * @param Category $modelCategory
     * @param eavCollectionFactory $eavCollectionFactory
     * @param productModel $productModel
     * @param State $state
     * @param productCollection $productCollection
     * @param productCollectionFactory $productCollectionFactory
     * @param ProductInterfaceFactory $productFactory
     * @param ProductRepositoryInterface $productRepository
     * @param StockRegistryInterface $stockRegistry
     * @param Option $option
     * @param modelProductRepostry $modelProductRepostry
     * @param OptionFactory $productOptionFactory
     * @param StoreManagerInterface $storeManagerInterface
     * @param storeModel $storeModel
     * @param SessionManagerInterface $coreSession
     * @param AttributeSetFactory $attributeSetFactory
     * @param CategorySetupFactory $categorySetupFactory
     * @param EavSetupFactory $eavSetupFactory
     * @param TypeFactory $eavTypeFactory
     * @param AttributeSetManagement $attributeSetManagement
     * @param AttributeManagement $attributeManagement
     * @param File $file
     * @param Serialize $serialize
     * @param ModuleDataSetupInterface $setup
     * @param LoggerInterface $logger
     * @param CollectionFactory $categoryCollection
     * @param assetRepo $assetRepo
     * @param RequestInterface $request
     *
     */
    public function __construct(
        ConnectionFactory $connectionFactory,
        ScopeConfigInterface $scopeConfig,
        CategoryFactory $categoryFactory,
        CategoryRepositoryInterface $repository,
        Category $modelCategory,
        eavCollectionFactory $eavCollectionFactory,
        productModel $productModel,
        State $state,
        productCollection $productCollection,
        productCollectionFactory $productCollectionFactory,
        ProductInterfaceFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        StockRegistryInterface $stockRegistry,
        Option $option,
        modelProductRepostry $modelProductRepostry,
        OptionFactory $productOptionFactory,
        StoreManagerInterface $storeManagerInterface,
        storeModel $storeModel,
        SessionManagerInterface $coreSession,
        AttributeSetFactory $attributeSetFactory,
        CategorySetupFactory $categorySetupFactory,
        EavSetupFactory $eavSetupFactory,
        TypeFactory $eavTypeFactory,
        AttributeSetManagement $attributeSetManagement,
        AttributeManagement $attributeManagement,
        File $file,
        Serialize $serialize,
        ModuleDataSetupInterface $setup,
        LoggerInterface $logger,
        CollectionFactory $categoryCollection,
        assetRepo $assetRepo,
        RequestInterface $request
    ) {
        $this->connectionFactory = $connectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->categoryFactory = $categoryFactory;
        $this->repository = $repository;
        $this->modelCategory = $modelCategory;
        $this->eavCollectionFactory = $eavCollectionFactory;
        $this->productModel = $productModel;
        $this->state = $state;
        $this->productCollection = $productCollection;
        $this->productFactory = $productFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productRepository = $productRepository;
        $this->stockRegistry = $stockRegistry;
        $this->option = $option;
        $this->modelProductRepostry = $modelProductRepostry;
        $this->productOptionFactory = $productOptionFactory;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->storeModel = $storeModel;
        $this->coreSession = $coreSession;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->categorySetupFactory = $categorySetupFactory;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavTypeFactory = $eavTypeFactory;
        $this->attributeSetManagement = $attributeSetManagement;
        $this->attributeManagement = $attributeManagement;
        $this->file = $file;
        $this->serialize = $serialize;
        $this->hostName = $this->scopeConfig->getValue('firstsection/firstgroup/dbHostName');
        $this->databaseUser = $this->scopeConfig->getValue('firstsection/firstgroup/dbUserName');
        $this->databasPassword = $this->scopeConfig->getValue('firstsection/firstgroup/dbPassword');
        $this->osCommerceVersion = $this->scopeConfig->getValue('firstsection/firstgroup/oscVersion');
        $this->databaseName = $this->scopeConfig->getValue('firstsection/firstgroup/osDbName');
        $this->setup = $setup;
        $this->logger = $logger;
        $this->collectionFactory = $categoryCollection;
        $this->assetRepo = $assetRepo;
        $this->request = $request;
        $this->initDb();
    }

    /**
     * Initiate oscommerce database connection
     *
     * @param  string hostName
     * @param  string databaseName
     * @param string databaseUser
     * @param string databasPassword
     * @return boolean
     */
    public function initDb()
    {

        if ($this->hostName !== null) {
            $db = $this->connectionFactory->create(
                [
                    'host' => $this->hostName,
                    'dbname' => $this->databaseName,
                    'username' => $this->databaseUser,
                    'password' => $this->databasPassword,
                    'active' => '1',
                ]
            );
            $tableToTest = $this->getDbPrefix() . 'categories';
            try {
                $select = $db->select()
                    ->from($tableToTest, 'categories_id');
                if ($results = $db->fetchAll($select)) {
                    return true;
                }
            } catch (\Exception $e) {
                return false;
            }
        }
    }

    /**
     * Setup oscommerce database connection
     *
     * @param  string hostName
     * @param string databaseName
     * @param string databaseUser
     * @param string databasPassword
     * @return connectionFactory database object
     */

    public function newDbConnection()
    {

        if ($this->hostName !== null) {
            $db = $this->connectionFactory->create(
                [
                    'host' => $this->hostName,
                    'dbname' => $this->databaseName,
                    'username' => $this->databaseUser,
                    'password' => $this->databasPassword,
                    'active' => '1',
                ]
            );
            return $db;
        }
    }
/**
 * Get total number of category
 *
 * @return int
 */
    public function getCategoryCount()
    {
        $count = 0;
        if ($this->hostName !== null) {
            $select = $this->newDbConnection()->select()->from($this->getDbPrefix() . 'categories', 'categories_id');
            if ($results = $this->newDbConnection()->fetchAll($select)) {
                return count($results);
            }
        }
        return $count;
    }
    /**
     * set session value for progress counter category
     * @param int $count
     */
    public function setValue($count)
    {
        $this->coreSession->start();
        $this->coreSession->setProgressbar($count);
        $this->coreSession->writeClose(); //IMPORTANT!
    }
    /**
     * Get session value for progress counter category
     * @return int
     */
    public function getValue()
    {
        $this->coreSession->start();
        return $this->coreSession->getProgressbar();
    }
    /**
     * Clean session variable value for progress counter category
     * @return void
     */
    public function unSetValue()
    {
        $this->coreSession->start();
        return $this->coreSession->unsProgressbar();
    }
    /**
     * set session value for progress counter product
     * @param int $count
     */
    public function setProductprogress($count)
    {
        $this->coreSession->start();
        $this->coreSession->setProgressbarproduct($count);
        $this->coreSession->writeClose();
    }
    /**
     * Get session value for progress counter product
     * @return int
     */
    public function getProductprogress()
    {
        $this->coreSession->start();
        return $this->coreSession->getProgressbarproduct();
    }
    /**
     * Clean session variable value for progress counter product
     * @return void
     */
    public function unSetProductprogress()
    {
        $this->coreSession->start();
        return $this->coreSession->unsProgressbarproduct();
    }
    /**
     * set session value for progress counter custom options
     * @param int $count
     */
    public function setOptionValue($count)
    {
        $this->coreSession->start();
        $this->coreSession->setProgressbaroption($count);
        $this->coreSession->writeClose();
    }
    /**
     * Get session value for progress counter product custom options
     * @return int
     */
    public function getOptionValue()
    {
        $this->coreSession->start();
        return $this->coreSession->getProgressbaroption();
    }

    /**
     * Clean session variable value for progress counter  product custom options
     *
     * @return void
     */
    public function unSetProgressbaroption()
    {
        $this->coreSession->start();
        return $this->coreSession->unsProgressbaroption();
    }

    /**
     * Get Oscommerce database prefix from configurations
     *
     * @return string $dbPrefix
     */
    public function getDbPrefix()
    {
        $dbPrefix = $this->scopeConfig->getValue('firstsection/firstgroup/databasePrefex');
        return $dbPrefix;
    }
    /**
     * Get Oscommerce category image path from configurations
     *
     * @return string $categoryImagePath
     */
    public function getCategoryImagePath()
    {
        $categoryImagePath = $this->scopeConfig->getValue('firstsection/firstgroup/categoryImagePath');
        return $categoryImagePath;
    }
    /**
     * Get chunk size from configurations to process
     *
     * @return int $dataChunkSize
     */
    public function getChunkSize()
    {
        $dataChunkSize = $this->scopeConfig->getValue('firstsection/firstgroup/dataChunkSize');
        return $dataChunkSize;
    }
    /**
     * Set image with category
     *
     * @param string $categoryImage
     * @return string $imageName
     */
    public function setImageCategory($categoryImage)
    {
        $imageName = 'dummyimage.png';
        $path = BP . '/pub/media/catalog/category/';
        $params = ['_secure' => $this->request->isSecure()];
        $imgPathDefault = $this->assetRepo->getUrlWithParams(
            'Embraceit_OscommerceToMagento::images/dummyimage.png',
            $params
        );
        if ($categoryImage == '' || $this->getCategoryImagePath() == null || strlen($categoryImage) > 90) {
            $categoryImagePath = $path . $categoryImage;
            if (!$this->file->isExists($path)) {
                $this->file->createDirectory($path);
            }
            $categoryImagePath = $path . $imageName;
            $this->file->changePermissions($path, 0777);
            $this->file->copy($imgPathDefault, $categoryImagePath);
            return $imageName;
        }
        if ($categoryImage != '' && $this->getCategoryImagePath() != null && strlen($categoryImage) < 88) {
            $imgPath = $this->getCategoryImagePath();
            $catImage = $imgPath . $categoryImage;

            if ($this->file->isExists($catImage)) {
                $arrImageStat = $this->file->stat($catImage);
                if (!$this->file->isExists($path)) {
                    $this->file->createDirectory($path);
                }
                $categoryImagePath = $path . $categoryImage;
                if ($arrImageStat['size'] > 0) {
                    $this->file->changePermissions($path, 0777);
                    $this->file->copy($catImage, $categoryImagePath);
                    return $categoryImage;
                } else {
                    return $imageName;
                }
            }
        }
    }
    /**
     * Get Oscommerce product image path from configurations
     *
     * @return string $imgPath
     */
    public function getProductImagePath()
    {
        $imgPath = $this->scopeConfig->getValue('firstsection/firstgroup/productImagePath');
        return $imgPath;
    }
    /**
     * Set image with product
     *
     * @param string $productImage
     * @return string $productImage
     */

    public function setImageProduct($productImage)
    {
        $imageName = 'dummyimage.png';
        $path = BP . '/pub/media/productimages/';
        $relPath = '/pub/media/productimages/';
        $params = ['_secure' => $this->request->isSecure()];
        $imgPathDefault = $this->assetRepo->getUrlWithParams(
            'Embraceit_OscommerceToMagento::images/dummyimage.png',
            $params
        );
        if ($productImage == '' || $this->getProductImagePath() == null || strlen($productImage) > 89) {
            if (!$this->file->isExists($path)) {
                $this->file->createDirectory($path);
            }
            $productImagePath = $path . $imageName;
            $this->file->changePermissions($path, 0777);
            $this->file->copy($imgPathDefault, $productImagePath);
            return $relPath . $imageName;
        }
        if ($productImage != '' && $this->getProductImagePath() != null && strlen($productImage) < 88) {
            $imgPath = $this->getProductImagePath();
            $prodImage = $imgPath . $productImage;

            if ($this->file->isExists($prodImage)) {
                $arrImageStat = $this->file->stat($prodImage);
                if (!$this->file->isExists($path)) {
                    $this->file->createDirectory($path);
                }
                $productImagePath = $path . $productImage;
                if ($arrImageStat['size'] > 0) {
                    $this->file->changePermissions($path, 0777);
                    $this->file->copy($prodImage, $productImagePath);
                    $prodImage = $relPath . $productImage;
                    return $prodImage;
                } else {
                    return $imageName;
                }
            }
        }
    }

    /**
     * Create store view from Oscommerce languges
     *
     */
    public function createStoreViewLng()
    {
        if ($resultsLanguge = $this->newDbConnection()->fetchAll($this->getAllLanguages())) {
            $storesMag = $this->storeManagerInterface->getStores();
            foreach ($resultsLanguge as $lang) {
                //magento store view languge
                $addStore = true;
                foreach ($storesMag as $mStoreLang) {
                    $arrIsAdd = [];
                    if (strtolower($lang['name']) == strtolower($mStoreLang['name'])) {
                        $addStore = false;
                    }
                }

                if ($addStore) {
                    $this->createStoreView('', 1, $lang['name'], $lang['code'], 1, 1, 3);
                }
            }
        }
    }
    /**
     * Add new category Oscommerce database
     *
     * @param array $description
     * @param array $data
     * @return int $catId
     */
    public function saveCategroyFirstTime($description, $data)
    {
        $data['data']['name'] = $description["categories_name"];
        $newCatName = $description["categories_name"];
        $metaTile = $description["meta_title"];
        $metaDes = $description["meta_description"];
        $urlKey = $this->removeAccent(strtolower($description["categories_name"]));
        $urlKey = $this->removeSpecialChar($urlKey);
        $categoryData = $this->categoryFactory->create($data);
        if (preg_match("/[-]$/", $urlKey)) {
            // 'remove dash from string last charactor';
            $urlKey = substr($urlKey, 0, -1);
        };
        if (preg_match("/^[-]/", $urlKey)) {
            //  'remove dash/copyright from string first charactor';
            $urlKey = substr($urlKey, 1);
        };
        $urlKey = $urlKey . '-' . strtolower($description["categories_id"]);
        $categoryData->setCustomAttributes(
            [
                "display_mode" => "PRODUCTS",
                "is_anchor" => 1,
                "meta_title" => $description["meta_title"],
                "meta_description" => $description["meta_description"],
                'meta_keyword' => $description["meta_keywords"],
                'description' => $description["box1_description"],
                'url_key' => $urlKey,
                'category_id' => $description["categories_id"],
            ]
        );
        $checkCat = $this->categoryFactory->create()
            ->getCollection()
            ->addAttributeToFilter('category_id', $description["categories_id"]);
        $parentCatId = $checkCat->getFirstItem()->getParentId();
        if ($parentCatId == '' || $parentCatId == null) {
            $result = $this->repository->save($categoryData);
            $catId = $result->getId();
        } else {
            $catId = $checkCat->getFirstItem()->getEntityId();
        }
        //set default values
        $categoryDef = $this->modelCategory->load($catId);
        $categoryDef->setStoreId(0);
        $categoryDef->setImage($data['data']['image']);
        $categoryDef->save();
        return $catId;
    }
    /**
     * Update category with new translation/view
     *
     * @param array $description
     * @param array $arrData
     */
    public function updateCategory($description, $arrData)
    {
        $categoryTra = $this->modelCategory->load($arrData['data']['category_id'], $arrData['data']['store_id']);
        $categoryTra->setStoreId($arrData['data']['store_id']);
        $categoryTra->setName($description["categories_name"]);
        $categoryTra->setUrlKey(strtolower($arrData['data']['url_key']));
        $categoryTra->setMetaTitle($description["meta_title"]);
        $categoryTra->setMetaDescription($description["meta_description"]);
        $categoryTra->setMetaKeyword($description["meta_keywords"]);
        $categoryTra->setImage($arrData['data']['image']);
        $categoryTra->setDescription($description["box2_description"]);
        $categoryTra->setCategoryId($description["categories_id"]);
        $categoryTra->save();
    }
    /**
     * Get category data from oscommerce database and save or update it
     *
     * @param array $arrDescription
     * @param array $data
     */
    public function getCategoryDiscriptionData($arrDescription, $data)
    {
        $catId = '';
        foreach ($arrDescription as $description) {
            if ($data['data']['parent_id'] == '' || $description["categories_name"] == '') {
                continue;
            }

            // create store view if not exist
            $this->createStoreViewLng();
            //get store ID/languge id
            $storeId = $this->getStoreId($description['language_id']);
            $data['data']['store_id'] = $storeId;
            if ($description['language_id'] == 1) {
                $catId = $this->saveCategroyFirstTime($description, $data);
            } else {
                $urlKey = $this->removeAccent($description["categories_name"]);
                $urlKey = $this->removeSpecialChar($urlKey);

                if (preg_match("/[-]$/", $urlKey)) {
                    // 'remove dash from string last charactor';
                    $urlKey = substr($urlKey, 0, -1);
                };
                if (preg_match("/^[-]$/", $urlKey)) {
                    //  'remove dash/copyright from string first charactor';
                    $urlKey = substr($urlKey, 1);
                };
                $urlKey = $urlKey . '-' . $description["categories_id"];
                $checkCat = $this->collectionFactory->create();
                $checkCat->setStore($storeId);
                $checkCat->addAttributeToFilter('url_key', $urlKey);
                $parentCatId = $checkCat->getFirstItem()->getParentId();
                if ($parentCatId) {
                    continue;
                }
                $data['data']['category_id'] = $catId;
                $data['data']['url_key'] = $urlKey;
                $this->updateCategory($description, $data);
            }
        }
    }
    /**
     * Add category with chunk limit
     *
     * @param int $startLimit
     * @param int $totalLimit
     * @return boolean
     * @throws Exception
     */
    public function addCategory($startLimit, $totalLimit)
    {
        //add languges or store view before adding categories
        if ($results = $this->newDbConnection()->fetchAll($this->getAllCategories($startLimit, $totalLimit))) {
            $nCounter = 0;
            $flag = true;
            foreach ($results as $category) {
                try {
                    $this->unSetValue();
                    $nCounter++;
                    $this->setValue($nCounter);
                    //get category information
                    $categoryParent = $category['parent_id'];
                    $categoryStatus = $category['categories_status'];
                    $categoryInMenu = $category['menu'];
                    $categoryId = $category['categories_id'];
                    $categorySortOrder = $category['sort_order'];
                    //set default image with category if not exist
                    $categoryImageDb = $category['categories_image'];
                    //set category image
                    $categoryImage = $this->setImageCategory($categoryImageDb);
                    switch ($categoryParent) {
                        case 1:
                            //there is no parent with id 1 in magento 2
                            break;
                        case 0:
                            //root category
                            $parent_id = 2;
                            break;
                        default:
                            $catDataParent = $this->categoryFactory->create()
                                ->getCollection()
                                ->addAttributeToFilter('category_id', $categoryParent);
                            $parent_id = $catDataParent->getFirstItem()->getId();
                            break;
                    }
                    $data = [
                        'data' => [
                            "parent_id" => $parent_id,
                            "is_active" => $categoryStatus,
                            "position" => $categorySortOrder,
                            "include_in_menu" => $categoryInMenu,
                            'image' => $categoryImage,
                        ],
                    ];
                    if ($catResults = $this->newDbConnection()->fetchAll($this->getCategoryDescription($categoryId))) {
                        $storeId = '';
                        $this->getCategoryDiscriptionData($catResults, $data);
                    }
                } catch (\Exception $e) {
                    $flag = false;
                    return $e->getMessage();
                }
            }

            return $flag;
        }
    }
    /**
     * Query get all categories of oscommerce with limit
     *
     * @param int $startLimit
     * @param int $totalLimit
     * @return string  $select
     */
    public function getAllCategories($startLimit, $totalLimit)
    {
        $select = $this->newDbConnection()
            ->select()
            ->from($this->getDbPrefix() . 'categories', '*')
            ->order('categories_id', 'ASC');
        //->limit($startLimit, $totalLimit);
        $limit = " LIMIT $startLimit,$totalLimit";
        return $select . $limit;
    }

    /**
     * Query get all oscommcrece languages
     *
     * @return string  $select
     */
    public function getAllLanguages()
    {
        $select = $this->newDbConnection()
            ->select()
            ->from($this->getDbPrefix() . 'languages', '*')
            ->order('languages_id', 'ASC');
        return $select;
    }

    /**
     * Query get oscommcrece language by ID
     *
     * @param int $id
     * @return string  $select
     */
    public function getLanguageById($id)
    {
        $select = $this->newDbConnection()
            ->select()
            ->from($this->getDbPrefix() . 'languages', '*')
            ->where('languages_id = ?', $id);
        return $select;
    }

    /**
     * Query get oscommcrece category detials by ID
     *
     * @param int $id
     * @return string  $select
     */
    public function getCategoryDescription($id)
    {
        $select = $this->newDbConnection()
            ->select('*')
            ->from($this->getDbPrefix() . 'categories_description')
            ->where('categories_id = ?', $id)
            ->order('language_id', 'ASC');
        return $select;
    }

    /**
     * Query get oscommcrece product count
     *
     * @return int  $count
     */
    public function getTotalProductsCount()
    {
        $count = 0;
        if ($this->hostName !== null) {
            $select = $this->newDbConnection()->select()->from($this->getDbPrefix() . 'products', 'products_id');
            if ($results = $this->newDbConnection()->fetchAll($select)) {
                return count($results);
            }
        }

        return $count;
    }
    /**
     * Query get oscommcrece custom option count
     *
     * @return int  $count
     */
    public function getTotalCustomOptionCount()
    {
        $count = 0;
        if ($this->hostName !== null) {
            $customOptionName = $this->getCustomAttributeData();
            $osVersion = $this->getOsVersion();
            $arrCustomOptoin = explode(',', $customOptionName);
            if ($osVersion == '1.0.0') {
                if (isset($arrCustomOptoin[0]) && $arrCustomOptoin[0] != '') {
                    foreach ($arrCustomOptoin as $attribute) {
                        if ($results = $this->newDbConnection()->fetchAll($this->queryCustomOptionsCount($attribute))) {
                            return count($results);
                        }
                    }
                }
            } else {
                //other versions of oscommerce
                return 0;
            }
        }

        return $count;
    }

    /**
     * Add attribute set if not exist
     *
     */

    public function addAttributeSet()
    {
//get attribute sets

        if ($results = $this->newDbConnection()->fetchAll($this->getAllAtrributeSet())) {
            $nCount = 0;
            //Magnet
            foreach ($results as $set) {
                //if attribute set already exist skip
                $arrAtribute = $this->getAttributeByName($set['name']);
                if (isset($arrAtribute['attribute_set_name'])) {
                    continue;
                }

                $entityTypeCode = 'catalog_product';
                $entityType = $this->eavTypeFactory->create()->loadByCode($entityTypeCode);
                $defaultSetId = $entityType->getDefaultAttributeSetId();
                $nCount++;
                $data = [
                    'attribute_set_name' => $set['name'],
                    'entity_type_id' => $entityType->getId(),
                    'sort_order' => 200,
                ];
                $attributeSet = $this->attributeSetFactory->create();
                $attributeSet->setData($data);
                $this->attributeSetManagement->create($entityTypeCode, $attributeSet, $defaultSetId);
            }
        }
    }
    /**
     * Get  attribute by name
     *
     * @param string $name
     * @return array|string
     */

    public function getAttributeByName($name)
    {
        $attributeCollection = $this->eavCollectionFactory->create();
        $attributeSet = $attributeCollection->getItems();
        foreach ($attributeSet as $attSet) {
            if ($name == $attSet->getAttributeSetName()) {
                return $attSet->getData();
            }
        }

        return 'check';
    }
    /**
     * Add products
     *
     * @param int $startLimit
     * @param int $totalLimit
     * @return boolean  $flag
     */
    public function addProducts($startLimit, $totalLimit)
    {
        $this->addAttributeSet();

        if ($results = $this->newDbConnection()->fetchAll($this->getAllProductDetials($startLimit, $totalLimit))) {
            $ncounter = 0;
            $productId = '';
            $productIdProd = '';
            $pImage = '';
            $flag = true;

            try {
                foreach ($results as $product) {
                    //progress bar unset data
                    $this->unSetProductprogress();
                    $ncounter++;
                    $this->setProductprogress($ncounter);
                    //skip and check if this product exist in database
                    $checkSku = 'sku' . '-' . $product['products_id'];
                    $isProductExist = $this->productCollectionFactory->create()
                        ->addAttributeToSelect('*')
                        ->addAttributeToFilter(
                            [
                                ['attribute' => 'sku', 'eq' => trim($checkSku)], // Color filter
                            ]
                        );
                    if ($isProductExist->getFirstItem()->getId()) {
                        //product with the same sku already exist skip to next product;
                        continue;
                    }

                    //attach attribute set
                    $atrType = $product['product_type'];
                    $attributeSetId = $this->addAttributeSetProduct($atrType);
                    //Tax class
                    $txClsId = 0;
                    $this->addTaxClass($product['products_tax_class_id']);
                    $productInfo = $this->productFactory->create(); //productFactory
                    $productInfo->setCustomAttribute('foxseo_discontinued', 0);
                    // attribute set id
                    $productInfo->setAttributeSetId($attributeSetId);
                    // enabled = 1, disabled = 0
                    $productInfo->setStatus($product['products_status']);
                    // visibilty of product, 1 = Not Visible Individually, 2 = Catalog, 3 = Search, 4 = Catalog, Search
                    $productInfo->setVisibility(4);
                    // Tax class id, 0 = None, 2 = Taxable Goods, etc.
                    $productInfo->setTaxClassId($txClsId);
                    // type of product (simple/virtual/downloadable/configurable)
                    $productInfo->setTypeId('simple');
                    // 1 = simple product, 0 = virtual product
                    $productInfo->setProductHasWeight(1);
                    // weight of product
                    $productInfo->setWeight($product['products_weight']);
                    // price of the product
                    $productInfo->setPrice($product['products_price']);
                    // Assign product to Websites
                    $productInfo->setWebsiteIds([1]);
                    $productInfo->setCustomAttribute('supplier_number', $product['supplier_number']);
                    $productInfo->setCustomAttribute('ts_dimensions_width', $product['products_width']);
                    $productInfo->setCustomAttribute('ts_dimensions_height', $product['products_hight']);
                    $productInfo->setCustomAttribute('color', $product['products_color']);
                    $pImage = $product['products_image'];
                    $inStock = 0;
                    $productId = $product['products_id'];
                    if ($product['products_quantity'] > 0) {
                        $inStock = 1;
                    }

                    $productInfo->setStockData(
                        [
                            'use_config_manage_stock' => 0,
                            'manage_stock' => 1,
                            'is_in_stock' => $inStock,
                            'qty' => $product['products_quantity'],
                        ]
                    );
                    //products to categories
                    $this->addProductToCategory($productId);
                    //products to categories
                    $resultsProdDes = $this->newDbConnection()
                        ->fetchAll($this->getProductDescription($product['products_id']));
                    foreach ($resultsProdDes as $proDes) {
                        if ($proDes['products_name'] == '') {
                            continue;
                        }
                        if ($proDes['language_id'] == 1) {
                            $productIdProd = $this->addNewProductData(
                                $proDes,
                                $product['products_id'],
                                $productInfo,
                                $pImage
                            );
                        }

                        //skip product
                        if ($proDes['language_id'] != 1 && $productIdProd == '') {
                            //there is no product id
                            continue;
                        }
                        //update product
                        if ($proDes['language_id'] != 1 && $productIdProd != '') {
                            $this->updateProductData($proDes, $productIdProd);
                        }
                    }
                }

                return $flag;
            } catch (\Exception $e) {
                $flag = false;
                return $e->getMessage();
            }

            return $flag;
        }
    }
    /**
     * Add products to categories
     *
     * @param int $productId
     */
    public function addProductToCategory($productId)
    {
        $arrCatIds = [];
        $resultsProToCat = $this->newDbConnection()->fetchAll($this->productToCat($productId));
        if ($resultsProToCat) {
            //get category id
            foreach ($resultsProToCat as $cat) {
                $catDataParent = $this->categoryFactory->create()
                    ->getCollection()
                    ->addAttributeToFilter('category_id', $cat["categories_id"]);
                $catId = $catDataParent->getFirstItem()->getId();
                $arrCatIds[] = $catId;
            }
        }

        $this->productModel->setCategoryIds($arrCatIds);
    }

    /**
     * Add attribute set name with product
     *
     * @param int $atrType
     * @return int $attributeSetId
     */
    public function addAttributeSetProduct($atrType)
    {
        $resultsAtr = $this->newDbConnection()->fetchAll($this->getAtributeSet($atrType));

        if ($atrType == 0) {
            $atData = $this->getAttributeByName('Default');
            if (isset($atData['attribute_set_id'])) {
                //default attribute set
                $attributeSetId = 4;
            }

            return $attributeSetId;
        } else {
            $resultsAtr = $this->newDbConnection()->fetchAll($this->getAtributeSet($atrType));
            foreach ($resultsAtr as $artType) {
                $atrSetName = $artType['name'];
            }

            $attributeSet = $this->eavCollectionFactory->create()->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                'attribute_set_name',
                "$atrSetName"
            );
            foreach ($attributeSet as $attr) {
                $attributeSetId = $attr->getAttributeSetId();
            }
            return $attributeSetId;
        }
    }
    /**
     * Add tax class to  product
     *
     * @param int $classId
     */
    public function addTaxClass($classId)
    {
        if ($classId == 0) {
            $txClsId = 2;
        } else {
            $txClsId = $classId;
        }
    }

    /**
     * Update product data/translation
     *
     * @param array $proDes
     * @param int $productIdProd
     */

    public function updateProductData($proDes, $productIdProd)
    {
        $storeId = $this->getStoreId($proDes['language_id']);
        $productData = $this->productModel->load($productIdProd);
        $productData->setStoreId($storeId);
        $productData->setName($proDes['products_name']); // name of the product
        $productData->setMetaTitle($proDes["meta_title"]);
        $productData->setMetaDescription($proDes["meta_description"]);
        $productData->setMetaKeyword($proDes["meta_keywords"]);
        $productData->setDescription($proDes['products_description']);
        $productData->save();
    }
    /**
     * Add new product data/translation
     *
     * @param array $proDes
     * @param int $productId
     * @param arry $productInfo
     * @param string $pImage
     * @return int $productIdProd
     */
    public function addNewProductData($proDes, $productId, $productInfo, $pImage)
    {
        $storeId = $this->getStoreId($proDes['language_id']);
        $productInfo->setStoreId($storeId);
        $sku = 'sku' . '-' . $productId;
        $productInfo->setSku($sku);
        $productInfo->setName($proDes['products_name']); // name of the product
        $productInfo->setMetaTitle($proDes["meta_title"]);
        $productInfo->setMetaDescription($proDes["meta_description"]);
        $productInfo->setMetaKeyword($proDes["meta_keywords"]);
        $urlKey = $this->removeAccent($proDes["products_name"]);
        $urlKey = $this->removeSpecialChar($urlKey);

        $productInfo->setDescription($proDes['products_description']);
        //assign product categories
        $productInfo->setCustomAttribute('subtitle', $proDes['subtitle']);
        $productInfo->setCustomAttribute('subtitleposter', $proDes['subtitle']);

        if (preg_match("/[.!?,;:-]$/", $urlKey)) {
            // 'remove dash from string last charactor';
            $urlKey = substr($urlKey, 0, -1);
        };
        if (preg_match("/^[.!?,;:-]$/", $urlKey)) {
            //  'remove dash/copyright from string first charactor';
            $urlKey = substr($urlKey, 1);
        };
        $productInfo->setUrlKey($urlKey); // url key of the product
        $collectionProduct = $this->productCollectionFactory->create()
            ->addAttributeToSelect(['entity_id'])
            ->addAttributeToFilter('url_key', ['like' => "%" . $urlKey . '%']);
        if ($collectionProduct->getFirstItem()->getId()) {
            // url key of the product
            $urlKey = strtolower($urlKey . '-' . $productId);
            $productInfo->setUrlKey($urlKey);
        };

        $collectionProductCheck = $this->productCollectionFactory->create();
        $productcollectionCheck = $collectionProductCheck->addAttributeToSelect('*')
            ->addAttributeToFilter(
                [
                    ['attribute' => 'url_key', 'eq' => trim($urlKey)], // Color filter
                ]
            );

        if ($productcollectionCheck->getFirstItem()->getId()) {
            return $productcollectionCheck->getFirstItem()->getId();
        };

        //add product image
        $productImage = $this->setImageProduct($pImage);
        if (($productImage != '') && ($this->file->isExists(BP . $productImage))) {
            $productInfo
                ->addImageToMediaGallery(BP . $productImage, ['image', 'small_image', 'thumbnail'], false, false);
        }

        $isProduct = $this->productRepository->save($productInfo);
        $productIdProd = $isProduct->getId();
        $prodSku = $isProduct->getSku();
        return $productIdProd;
    }
    /**
     * Get store by id
     *
     * @param int $id
     * @return int $storeId
     */

    public function getStoreId($id)
    {
        $storeId = '';
        $resultLang = $this->newDbConnection()->fetchAll($this->getLanguageById($id));
        $localStores = $this->storeManagerInterface->getStores();
        foreach ($localStores as $mStoreLang) {
            if (strtolower($resultLang[0]['name']) == strtolower($mStoreLang['name'])) {
                $storeId = $mStoreLang['store_id'];
            }
        }
        return $storeId;
    }
    /**
     * Get custom attribute names from configuration
     *
     * @return array $customAtributes
     */
    public function getCustomAttributeData()
    {
        $customAtributes = $this->scopeConfig->getValue('firstsection/firstgroup/customAttribute');
        return $customAtributes;
    }
    /**
     * Get custom attribute title from configuration
     *
     * @return array $customAtributeTitle
     */

    public function getCustomAttributeDataTitle()
    {
        $customAtributeTitle = $this->scopeConfig->getValue('firstsection/firstgroup/customAttributeTitle');
        return $customAtributeTitle;
    }
    /**
     * Get oscommerce version to import from  configuration
     *
     * @return string $osVesionNo
     */

    public function getOsVersion()
    {
        $osVesionNo = $this->scopeConfig->getValue('firstsection/firstgroup/oscVersion');
        return $osVesionNo;
    }
    /**
     * Add custom options with products
     *
     * @param int $startLimit
     * @param int $totalLimit
     */
    public function addCustomOption($startLimit, $totalLimit)
    {
        $productId = '';
        $productIdProd = '';
        $prodSku = '';
        $catId = '';
        $chooseOption = 'Choose size';
        $customOptionName = $this->getCustomAttributeData();
        $customAttributeTitle = $this->getCustomAttributeDataTitle();
        $osVersion = $this->getOsVersion();
        $arrCustomAttributeTitle = explode(',', $customAttributeTitle);
        $arrCustomOptoin = explode(',', $customOptionName);
        //check oscommerce version
        if ($osVersion == '1.0.0') {
            if (isset($arrCustomOptoin[0]) && $arrCustomOptoin[0] != '') {
                foreach ($arrCustomOptoin as $key => $attribute) {
                    return $this->getOptionVersionOne(
                        $attribute,
                        $startLimit,
                        $totalLimit,
                        $arrCustomAttributeTitle[$key]
                    );
                }
            }
        }
        //other version data attribute
        if ($osVersion != '1.0.0') {
            if ($results = $this->newDbConnection()->fetchAll($this->queryCustomProductAttributes())) {
                try {
                    $this->getCustomOptionVersionTwo();
                    $arrValue = [];
                    $optionName = '';
                    $exist = false;
                    foreach ($results as $prod) {
                        $this->getCustomOptionVersionTwo($prod);
                    }

                    return "Customizable options has been added";
                } catch (\Exception $e) {
                    return $e->getMessage();
                }
            }
        }
    }
    /**
     * Set/add  custom options  for version
     *
     * @param string $attribute
     * @param int $startLimit
     * @param int $totalLimit
     * @param string $attributeTitle
     * @return string $message
     * @throws Exception
     */
    public function getOptionVersionOne($attribute, $startLimit, $totalLimit, $attributeTitle = '')
    {
        if ($attributeTitle == '') {
            $attributeTitle = 'Default';
        }

        if ($results = $this->newDbConnection()->fetchAll(
            $this->queryCustomOptions(
                $attribute,
                $startLimit,
                $totalLimit
            )
        )
        ) {
            $nCounter = 0;
            try {
                foreach ($results as $prod) {
                    $nCounter++;
                    $productIdEco = $prod['products_id'];
                    $exist = false;
                    //generate sku
                    $productSku = 'sku-' . $productIdEco;
                    //load product by sky
                    $product = $this->productRepository->get($productSku);
                    $orgPrice = $product->getPrice();
                    $productIdSku = $product->getId();
                    $arrSize = $this->serialize->unserialize($prod[$attribute]);
                    $arrPrice = $this->serialize->unserialize($prod[$attribute . '_prices']);
                    $arrValue = [];
                    $selectedSize = '';
                    foreach ($arrSize as $key => $size) {
                        //clean data
                        $proSize = str_replace('&nbsp;', ' ', $size);
                        if (isset($arrPrice[$proSize])) {
                            //calculate price
                            //optonPrice-orginalPrice = abs(variation price)
                            //39-89=abs(0)
                            //98-65=33
                            $optionPrice = $orgPrice - $arrPrice[$proSize];
                            $selectedSize = $this->checkOptionPrice($optionPrice, $proSize);
                            $arrValue[] = [
                                'title' => $proSize,
                                'price' => abs($optionPrice),
                                'price_type' => 'fixed',
                                'sku' => '',
                                'sort_order' => 0,
                            ];
                        }

                        $options = [

                            [
                                'title' => $attributeTitle,
                                'type' => 'drop_down',
                                'is_require' => 1,
                                'sort_order' => 0,
                                'values' => $arrValue,
                            ],
                        ];
                    }

                    $product->setHasOptions(1);
                    $product->setCanSaveCustomOptions(true);
                    foreach ($product->getOptions() as $opt) {
                        if ($opt['default_title'] == $attributeTitle) {
                            $exist = true;
                        }
                    }

                    if (!$exist) {
                        foreach ($options as $arrayOption) {
                            $option = $this->productOptionFactory->create();
                            $option->setProductId($product->getId())
                                ->setStoreId($product->getStoreId())
                                ->addData($arrayOption);
                            $product->addOption($option);
                        }
                    }

                    $product->setCustomAttribute('poster_size', $selectedSize);
                    $this->productRepository->save($product);

                    $this->setOptionValue($nCounter);
                }
                $message = "Customizable options has been added";
                return $message;
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }
    }
    /**
     * Get custom product option price
     *
     * @param int $optionPrice
     * @param string $proSize
     * @return string $selectedSize
     */
    public function checkOptionPrice($optionPrice, $proSize)
    {
        if (abs($optionPrice) == 0) {
            $selectedSize = $proSize;
        } else {
            $selectedSize = '';
        }

        return $selectedSize;
    }

    /**
     * Add  custom product option for future version
     *
     * @param array $prod
     * @param string $proSize
     * @return string $message
     * @throws Exception
     */
    public function getCustomOptionVersionTwo($prod)
    {
        try {
            //get all options attached with product
            $productIdEco = $prod['products_id'];
            //generate sku
            $productSku = 'sku-' . $productIdEco;
            //load product by sky
            $product = $this->productRepository->get($productSku);
            $orgPrice = $product->getPrice();
            $productIdSku = $product->getId();

            if ($results = $this->newDbConnection()->fetchAll(
                $this->queryGetCustomProductAttributesById($productIdEco)
            )
            ) {
                //
                foreach ($results as $data) {
                    //get option detial with option id
                    $optionId = $data['options_id'];
                    $optionData = $this->newDbConnection()
                        ->fetchAll($this->queryGetOptionDetial($optionId));
                    $optionName = $optionData[0]['products_options_name'];
                    //get option value
                    $resultOptionData = $this->newDbConnection()
                        ->fetchAll($this->queryGetOptionData($optionId, $productIdEco));

                    //get option values
                    foreach ($resultOptionData as $option) {
                        $arrValue[] = [
                            'title' => $option['products_options_values_name'],
                            'price' => $option['options_values_price'],
                            'price_type' => 'fixed',
                            'sku' => '',
                            'sort_order' => 0,
                        ];
                    }

                    $options = [

                        [
                            'title' => $optionName,
                            'type' => 'drop_down',
                            'is_require' => 1,
                            'sort_order' => 0,
                            'values' => $arrValue,
                        ],
                    ];
                }
            }

            $product->setHasOptions(1);
            $product->setCanSaveCustomOptions(true);
            foreach ($product->getOptions() as $opt) {
                if ($opt['default_title'] == $optionName) {
                    $exist = true;
                }
            };

            if (!$exist) {
                foreach ($options as $arrayOption) {
                    $option = $this->productOptionFactory->create();
                    $option->setProductId($product->getId())
                        ->setStoreId($product->getStoreId())
                        ->addData($arrayOption);
                    $product->addOption($option);
                }
            }

            $this->productRepository->save($product);
            $message = "Customizable options has been added";
            return $message;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    /**
     * Query   custom product option by product id
     *
     * @param int $id
     * @return string $select
     */

    public function queryGetCustomProductAttributesById($id)
    {
        $column1 = $this->getDbPrefix() . 'products_attributes.options_id';
        $column2 = $this->getDbPrefix() . 'products_options.products_options_id';
        $select = $this->newDbConnection()
            ->select()
            ->from([$this->getDbPrefix() . 'products_attributes'], ['options_id'])
            ->from([$this->getDbPrefix() . 'products_options'], ['products_options_id'])
            ->where('products_id = ?', $id)
            ->where($column1 . ' = ' . $column2)
            ->group($this->getDbPrefix() . 'products_attributes.options_id');
        return $select;
    }

    /**
     * Query custom product option detial by option id
     *
     * @param int $id
     * @return string $select
     */

    public function queryGetOptionDetial($id)
    {
        $select = $this->newDbConnection()
            ->select()
            ->from([$this->getDbPrefix() . 'products_options'], ['products_options_name'])
            ->where('products_options_id = ?', $id);
        return $select;
    }
    /**
     * Query custom product option detial by option id and product id
     *
     * @param int $optionId
     * @param int $productId
     * @return string $select
     */

    public function queryGetOptionData($optionId, $productId)
    {
        $column1 = $this->getDbPrefix() . 'products_attributes.options_values_id';
        $column2 = $this->getDbPrefix() . 'products_options_values.products_options_values_id';
        $select = $this->newDbConnection()
            ->select()
            ->from([$this->getDbPrefix() . 'products_attributes'])
            ->from([$this->getDbPrefix() . 'products_options_values'])
            ->where($this->getDbPrefix() . 'products_attributes.options_id = ?', $optionId)
            ->where($this->getDbPrefix() . 'products_attributes.products_id = ?', $productId)
            ->where($column1 . '=' . $column2);
        return $select;
    }
    /**
     * Query get custom product option by name and limit
     *
     * @param string $attribute
     * @param int $startLimit
     * @param int $totalLimit
     * @return string $select
     */
    public function queryCustomOptions($attribute, $startLimit, $totalLimit)
    {
        $select = $this->newDbConnection()
            ->select()
            ->from($this->getDbPrefix() . 'products')
            ->where($this->getDbPrefix() . 'products.' . $attribute . '  <> "N;"')
            ->where($this->getDbPrefix() . 'products.' . $attribute . '  <> "" ')
            ->where($this->getDbPrefix() . 'products.' . $attribute . '_prices  <> "s:0:\"\"" ')
            ->order($this->getDbPrefix() . 'products.products_id', 'ASC');
        $limit = " LIMIT $startLimit,$totalLimit";
        return $select . $limit;
    }
    /**
     * Query count total custom product option
     *
     * @param string $attribute
     * @return string $select
     */
    public function queryCustomOptionsCount($attribute)
    {
        $select = $this->newDbConnection()
            ->select()
            ->from($this->getDbPrefix() . 'products')
            ->where($this->getDbPrefix() . 'products.' . $attribute . '  <> "N;"')
            ->where($this->getDbPrefix() . 'products.' . $attribute . '  <> "" ')
            ->where($this->getDbPrefix() . 'products.' . $attribute . '_prices  <> "s:0:\"\"" ')
            ->order($this->getDbPrefix() . 'products.products_id', 'ASC');
        return $select;
    }
    /**
     * Query all custom product attribute
     *
     * @return string $select
     */
    public function queryCustomProductAttributes()
    {
        $select = $this->newDbConnection()
            ->select()
            ->from([$this->getDbPrefix() . 'products_attributes'], ['products_id'])
            ->group('products_id')
            ->order($this->getDbPrefix() . 'products_attributes.products_id', 'ASC');
        return $select;
    }
    /**
     * Query products oscommerce
     *
     * @return string $select
     */
    public function queryProductsInfo()
    {

        $select = $this->newDbConnection()
            ->select()
            ->from($this->getDbPrefix() . 'products')
            ->from($this->getDbPrefix() . 'products_description')
            ->where(
                $this->getDbPrefix()
                . 'products.products_id ='
                . $this->getDbPrefix()
                . 'products_description.products_id'
            )
            ->order($this->getDbPrefix() . 'products.products_id', 'ASC');
        return $select;
    }
    /**
     * Query for internal use/check function
     *
     * @return string $select
     */
    public function printQuery()
    {
        $select = $this->queryProductsInfo();
        return $select;
    }
    /**
     * Query get attribute set by id
     *
     * @return string $select
     */
    public function getAtributeSet($id)
    {
        $select = $this->newDbConnection()
            ->select()
            ->from($this->getDbPrefix() . 'productstype', '*')
            ->where('id=?', $id)
            ->where('language_id=1');
        return $select;
    }
    /**
     * Query get all attribute set
     *
     * @return string $select
     */

    public function getAllAtrributeSet()
    {

        $select = $this->newDbConnection()
            ->select()
            ->from($this->getDbPrefix() . 'productstype', '*')
            ->where($this->getDbPrefix() . 'productstype.language_id=1')
            ->order($this->getDbPrefix() . 'productstype.id', 'ASC');
        return $select;
    }
    /**
     * Query get attribute translation
     *
     * @return string $select
     */
    public function getAttributeTranslationCount()
    {
        $select = $this->newDbConnection()
            ->select()
            ->from($this->getDbPrefix() . 'productstype', 'language_id')
            ->group($this->getDbPrefix() . 'productstype.language_id');
        return $select;
    }
    /**
     * Replace special language charactors with proper alternate
     *
     * @param string $str
     * @return string $replaceValue
     */
    public function removeAccent($str)
    {
        $a = ['À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç',
            'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð',
            'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú',
            'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å',
            'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï',
            'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý',
            'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č',
            'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę',
            'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ',
            'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ',
            'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł',
            'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő',
            'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ',
            'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū',
            'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ',
            'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư',
            'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ',
            'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ'];
        $b = ['A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E',
            'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U',
            'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c',
            'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o',
            'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C',
            'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E',
            'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g',
            'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ',
            'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l',
            'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe',
            'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T',
            't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u',
            'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f',
            'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U',
            'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o'];
        $replaceValue = str_replace($a, $b, $str);
        return $replaceValue;
    }
    /**
     * Get product ot category relateionship by product id
     *
     * @param int $id
     * @return string $select
     */
    public function productToCat($id)
    {
        $select = $this->newDbConnection()
            ->select()
            ->from($this->getDbPrefix() . 'products_to_categories', '*')
            ->where('products_id=?', $id);
        return $select;
    }
    /**
     * Replace special charactors with proper alternate
     *
     * @param string $urlKey
     * @return string $urlKey
     */
    public function removeSpecialChar($urlKey)
    {
        $urlKey = preg_replace('/&/', '', $urlKey);
        $urlKey = preg_replace('/[@]/', '', $urlKey);
        $urlKey = preg_replace('/[!]/', '', $urlKey);
        $urlKey = preg_replace('/[?]/', '', $urlKey);
        $urlKey = preg_replace('/[;]/', '', $urlKey);
        $urlKey = preg_replace('/[:]/', '', $urlKey);
        $urlKey = preg_replace('/[.]/', '', $urlKey);
        $urlKey = preg_replace('/--/', '-', $urlKey);
        $urlKey = preg_replace('/[\/]/', '-', $urlKey);
        $urlKey = preg_replace('/[|]/', '', $urlKey);
        $urlKey = preg_replace('/[½]/', '', $urlKey);
        $urlKey = preg_replace('/[#]/', '', $urlKey);
        $urlKey = preg_replace('/[©]/', '', $urlKey);
        $urlKey = preg_replace('/---/', '-', $urlKey);
        $urlKey = preg_replace('/[(]/', '', $urlKey);
        $urlKey = preg_replace('/[)]/', '', $urlKey);
        $urlKey = preg_replace('/[´]/', '', $urlKey);
        $urlKey = preg_replace('/[`]/', '-', $urlKey);
        $urlKey = preg_replace('/--/', '-', $urlKey);
        $urlKey = preg_replace('/,/', '', $urlKey);
        $urlKey = preg_replace('/[+]/', '-', $urlKey);
        $urlKey = preg_replace('/\s+/', '-', $urlKey);
        $urlKey = preg_replace('/\'/', '-', $urlKey);

        return $urlKey;
    }
    /**
     * Get product information by limit
     *
     * @param int $startLimit
     * @param int $totalLimit
     * @return string $select
     */
    public function getAllProductDetials($startLimit, $totalLimit)
    {
        $select = $this->newDbConnection()
            ->select()
            ->from($this->getDbPrefix() . 'products', '*')
            ->order('products_id', 'ASC');
        $limit = " LIMIT $startLimit,$totalLimit";
        return $select . $limit;
    }

    /**
     * Get product description by id
     *
     * @param int $id
     * @return string $select
     */

    public function getProductDescription($id)
    {
        $select = $this->newDbConnection()
            ->select('*')
            ->from($this->getDbPrefix() . 'products_description')
            ->where('products_id = ?', $id)
            ->order('language_id', 'ASC');
        return $select;
    }

/**
 * Update/create store (frontname: "Store View").
 *
 * @param int $storeId ID to update existing store or 'null' to create new one
 * @param string $name
 * @param string $code
 * @param int $websiteId
 * @param int $groupId (frontname: "Store")
 * @param int $sortOrder
 * @param bool $isActive
 * @return \Magento\Store\Model\Store
 */
    public function createStoreView(
        $storeId = null,
        $isActive = true,
        $name = null,
        $code = null,
        $websiteId = null,
        $groupId = null,
        $sortOrder = null
    ) {

        $stores = $this->storeManagerInterface->getStores();
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->storeModel->load($storeId);
        /* 'code' is required attr. and should be set for existing store */
        $event = $store->getCode() === null ? 'store_add' : 'store_edit';
        foreach ($stores as $mstore) {
            //check if store langue already added
            if ($mstore['code'] == $code) {
                $mstore['store_id'];
            }
        }

        $store->setIsActive($isActive);
        if ($name !== null) {
            $store->setName($name);
        }

        if ($code !== null) {
            $store->setCode($code);
        }

        if ($websiteId !== null) {
            $store->setWebsiteId($websiteId);
        }

        if ($groupId !== null) {
            $store->setGroupId($groupId);
        }

        if ($sortOrder !== null) {
            $store->setSortOrder($sortOrder);
        }

        $store->save();
        return $store;
    }
    /**
     * Get product image path from configurations
     *
     * @return string $productImagePath
     */
    public function getImagePathProduct()
    {
        $productImagePath = $this->scopeConfig->getValue('firstsection/firstgroup/productImagePath');
        return $productImagePath;
    }
    /**
     * Get category image path from configurations
     *
     * @return string $categoryImagePath
     */
    public function getImagePathCategory()
    {
        $categoryImagePath = $this->scopeConfig->getValue('firstsection/firstgroup/categoryImagePath');
        return $categoryImagePath;
    }
    /**
     * Remove all categories from database
     *
     * @return boolean
     * @throws Exception
     */
    public function deleteCategoryData()
    {
        $arrtableNames = ['catalog_category_entity',
            'catalog_category_entity_datetime',
            'catalog_category_entity_decimal',
            'catalog_category_entity_int',
            'catalog_category_entity_text',
            'catalog_category_entity_varchar',
            'catalog_category_product',
            'catalog_category_product_index',
        ];
        $connection = $this->setup->getConnection();
        // truncate table
        $result = '';
        //remove data
        $connection->query('SET FOREIGN_KEY_CHECKS = 0');
        foreach ($arrtableNames as $table) {
            $result = $connection->truncateTable($table);
        }

        $timestamp = date('Y-m-d H:i:s');
        $insertData = [
            [
                'entity_id' => 1,
                'attribute_set_id' => 0,
                'parent_id' => 0,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'path' => 1,
                'position' => 0,
                'level' => 0,
                'children_count' => 1,
            ],
            [
                'entity_id' => 2,
                'attribute_set_id' => 3,
                'parent_id' => 1,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'path' => '1/2',
                'position' => 1,
                'level' => 1,
                'children_count' => 0,
            ],
        ];
        try {
            $connection->beginTransaction();
            $connection->insertMultiple('catalog_category_entity', $insertData);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            return $e;
        }

        $insertDataCe = [
            [
                'value_id' => 1,
                'attribute_id' => 69,
                'store_id' => 0,
                'entity_id' => 1,
                'value' => 1,
            ],
            [
                'value_id' => 2,
                'attribute_id' => 46,
                'store_id' => 0,
                'entity_id' => 2,
                'value' => 1,
            ],
            [
                'value_id' => 3,
                'attribute_id' => 69,
                'store_id' => 0,
                'entity_id' => 2,
                'value' => 1,
            ],
        ];
        try {
            $connection->beginTransaction();
            $connection->insertMultiple('catalog_category_entity_int', $insertDataCe);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            return $e;
        }

        $insertDataVc = [
            [
                'value_id' => 1,
                'attribute_id' => 45,
                'store_id' => 0,
                'entity_id' => 1,
                'value' => 'Root Catalog',
            ],
            [
                'value_id' => 2,
                'attribute_id' => 45,
                'store_id' => 0,
                'entity_id' => 2,
                'value' => 'Default Category',
            ],
        ];
        try {
            $connection->beginTransaction();
            $connection->insertMultiple('catalog_category_entity_varchar', $insertDataVc);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            return $e;
        }

        try {
            $select = $this->newDbConnection()
                ->select()
                ->from('url_rewrite', 'entity_type')
                ->where('entity_type = ?', 'category');
            $IsCategoryType = $connection->fetchRow($select);
            if (isset($IsCategoryType['entity_type'])) {
                $connection->delete('url_rewrite', ["entity_type = 'category'"]);
            }
        } catch (\Exception $e) {
            return $e;
        }

        $connection->query('SET FOREIGN_KEY_CHECKS = 1');
        if ($result) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * Remove all products from database
     *
     * @return boolean
     * @throws Exception
     */
    public function deleteProductData()
    {
        $arrtableNames = ['cataloginventory_stock_item',
            'cataloginventory_stock_status',
            'cataloginventory_stock_status_idx',
            'cataloginventory_stock_status_tmp',
            'catalog_category_product',
            'catalog_category_product_index',
            'catalog_category_product_index_tmp',
            'catalog_compare_item',
            'catalog_product_bundle_option',
            'catalog_product_bundle_option_value',
            'catalog_product_bundle_price_index',
            'catalog_product_bundle_selection',
            'catalog_product_bundle_selection_price',
            'catalog_product_bundle_stock_index',
            'catalog_product_entity',
            'catalog_product_entity_datetime',
            'catalog_product_entity_decimal',
            'catalog_product_entity_gallery',
            'catalog_product_entity_int',
            'catalog_product_entity_media_gallery',
            'catalog_product_entity_media_gallery_value',
            'catalog_product_entity_media_gallery_value_to_entity',
            'catalog_product_entity_media_gallery_value_video',
            'catalog_product_entity_text',
            'catalog_product_entity_tier_price',
            'catalog_product_entity_varchar',
            'catalog_product_index_eav',
            'catalog_product_index_eav_decimal',
            'catalog_product_index_eav_decimal_idx',
            'catalog_product_index_eav_decimal_tmp',
            'catalog_product_index_eav_idx',
            'catalog_product_index_eav_tmp',
            'catalog_product_index_price',
            'catalog_product_index_price_bundle_idx',
            'catalog_product_index_price_bundle_opt_idx',
            'catalog_product_index_price_bundle_opt_tmp',
            'catalog_product_index_price_bundle_sel_idx',
            'catalog_product_index_price_bundle_sel_tmp',
            'catalog_product_index_price_bundle_tmp',
            'catalog_product_index_price_cfg_opt_agr_idx',
            'catalog_product_index_price_cfg_opt_agr_tmp',
            'catalog_product_index_price_cfg_opt_idx',
            'catalog_product_index_price_cfg_opt_tmp',
            'catalog_product_index_price_downlod_idx',
            'catalog_product_index_price_downlod_tmp',
            'catalog_product_index_price_final_idx',
            'catalog_product_index_price_final_tmp',
            'catalog_product_index_price_idx',
            'catalog_product_index_price_opt_agr_idx',
            'catalog_product_index_price_opt_agr_tmp',
            'catalog_product_index_price_opt_idx',
            'catalog_product_index_price_opt_tmp',
            'catalog_product_index_price_tmp',
            'catalog_product_index_tier_price',
            'catalog_product_index_website',
            'catalog_product_link',
            'catalog_product_link_attribute_decimal',
            'catalog_product_link_attribute_int',
            'catalog_product_link_attribute_varchar',
            'catalog_product_option',
            'catalog_product_option_price',
            'catalog_product_option_title',
            'catalog_product_option_type_price',
            'catalog_product_option_type_title',
            'catalog_product_option_type_value',
            'catalog_product_relation',
            'catalog_product_super_attribute',
            'catalog_product_super_attribute_label',
            'catalog_product_super_link',
            'catalog_product_website',
            'catalog_url_rewrite_product_category',
            'downloadable_link',
            'downloadable_link_price',
            'downloadable_link_purchased',
            'downloadable_link_purchased_item',
            'downloadable_link_title',
            'downloadable_sample',
            'downloadable_sample_title',
            'product_alert_price',
            'product_alert_stock',
            'report_compared_product_index',
            'report_viewed_product_aggregated_daily',
            'report_viewed_product_aggregated_monthly',
            'report_viewed_product_aggregated_yearly',
            'report_viewed_product_index',
        ];
        $connection = $this->setup->getConnection();
        // truncate table
        $result = '';
        //remove data
        $connection->query('SET FOREIGN_KEY_CHECKS = 0');

        try {
            foreach ($arrtableNames as $table) {
                $result = $connection->truncateTable($table);
            }
        } catch (\Exception $e) {
            $connection->rollBack();
            return $e;
        }

        try {
            $select = $this->newDbConnection()
                ->select()
                ->from('url_rewrite', 'entity_type')
                ->where('entity_type = ?', 'product');
            $IsCategoryType = $connection->fetchRow($select);
            if (isset($IsCategoryType['entity_type'])) {
                $connection->delete('url_rewrite', ["entity_type = 'product'"]);
            }
        } catch (\Exception $e) {
            return $e;
        }

        $connection->query('SET FOREIGN_KEY_CHECKS = 1');
        if ($result) {
            return true;
        } else {
            return false;
        }
    }
}
