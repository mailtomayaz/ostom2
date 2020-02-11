<?php
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
use Magento\Framework\App\ResourceConnection\ConnectionFactory;
use Magento\Framework\App\State;
use Magento\framework\Filesystem\Driver\File;
use Magento\Framework\Serialize\Serializer\Serialize;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Store\Model\Store as storeModel;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class ExternalDb
{
    protected $connectionFactory;
    protected $scopeConfig;
    protected $categoryFactory;
    protected $repository;
    protected $modelCategory;
    protected $eavCollectionFactory;
    protected $productModel;
    protected $state;
    protected $productCollection;
    protected $productFactory;
    protected $productCollectionFactory;
    protected $productRepository;
    protected $stockRegistry;
    protected $option;
    protected $modelProductRepostry;
    protected $productOptionFactory;
    protected $storeManagerInterface;
    protected $storeModel;
    protected $coreSession;
    protected $attributeSetFactory;
    protected $eavSetupFactory;
    protected $eavTypeFactory;
    protected $attributeSetManagement;
    protected $attributeManagement;
    protected $file;
    protected $serialize;
    protected $hostName;
    protected $databaseName;
    protected $databaseUser;
    protected $databasPassword;
    protected $osCommerceVersion;
    protected $setup;
    protected $logger;
    protected $categoryCollection;

    /**
     *
     * @var  Magento\Catalog\Setup\CategorySetupFactory
     */
    protected $categorySetupFactory;

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
        CollectionFactory $categoryCollection
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
        $this->hostName = $this->scopeConfig->getValue('firstsection/firstgroup/DbHostName');
        $this->databaseUser = $this->scopeConfig->getValue('firstsection/firstgroup/DbUserName');
        $this->databasPassword = $this->scopeConfig->getValue('firstsection/firstgroup/DbPassword');
        $this->osCommerceVersion = $this->scopeConfig->getValue('firstsection/firstgroup/OscVersion');
        $this->databaseName = $this->scopeConfig->getValue('firstsection/firstgroup/DbName');
        $this->setup = $setup;
        $this->logger = $logger;
        $this->collectionFactory = $categoryCollection;
        $this->initDb();
    }
    public function initDb()
    {
        if ($this->hostName !== null) {
            $db = $this->connectionFactory->create([
                'host' => $this->hostName,
                'dbname' => $this->databaseName,
                'username' => $this->databaseUser,
                'password' => $this->databasPassword,
                'active' => '1',
            ]);
            $tableToTest = $this->getDbPrefix() . 'categories';
            try {
                /* Some logic that could throw an Exception */
                $select = $db->select()
                    ->from($tableToTest, 'categories_id');
                if ($results = $db->fetchAll($select)) {
                    return true;
                }
            } catch (\Exception $e) {
                //$this->logger->critical($e->getMessage());
                return false;
            }
        }
    }
    public function newDbConnection()
    {

        if ($this->hostName !== null) {
            $db = $this->connectionFactory->create([
                'host' => $this->hostName,
                'dbname' => $this->databaseName,
                'username' => $this->databaseUser,
                'password' => $this->databasPassword,
                'active' => '1',
            ]);
            return $db;
        }
    }
/**
 * Undocumented function
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
    public function setValue($count)
    {
        $this->coreSession->start();
        $this->coreSession->setProgressbar($count);
        $this->coreSession->writeClose(); //IMPORTANT!
    }
    public function getValue()
    {
        $this->coreSession->start();
        return $this->coreSession->getProgressbar();
    }

    public function unSetValue()
    {
        $this->coreSession->start();
        return $this->coreSession->unsProgressbar();
    }

    public function setProductprogress($count)
    {
        $this->coreSession->start();
        $this->coreSession->setProgressbarproduct($count);
        $this->coreSession->writeClose();
    }
    public function getProductprogress()
    {
        $this->coreSession->start();
        return $this->coreSession->getProgressbarproduct();
    }

    public function unSetProductprogress()
    {
        $this->coreSession->start();
        return $this->coreSession->unsProgressbarproduct();
    }

    public function setOptionValue($count)
    {
        $this->coreSession->start();
        $this->coreSession->setProgressbaroption($count);
        $this->coreSession->writeClose();
    }
    public function getOptionValue()
    {
        $this->coreSession->start();
        return $this->coreSession->getProgressbaroption();
    }

    public function unSetProgressbaroption()
    {
        $this->coreSession->start();
        return $this->coreSession->unsProgressbaroption();
    }

    public function getDbPrefix()
    {
        $dbPrefix = $this->scopeConfig->getValue('firstsection/firstgroup/databasePrefex');
        return $dbPrefix;
    }
    public function getCategoryImagePath()
    {
        return $this->scopeConfig->getValue('firstsection/firstgroup/categoryImagePath');
    }
    public function getChunkSize()
    {
        return $this->scopeConfig->getValue('firstsection/firstgroup/dataChunkSize');
    }
    public function setImageCategory($categoryImage)
    {
        $imageName = '';
        if ($categoryImage == '' || $this->getCategoryImagePath() == null) {
            $imageName = $categoryImage = 'dummyimage.png';
            $imgPath = BP . '/app/code/Embraceit/OscommerceToMagento/view/adminhtml/web/images/';
            //in case composer install
            if (!$this->file->isExists($imgPath)) {
                $imgPath = BP . '/vendor/embraceit/oscommerce-to-magento/view/adminhtml/web/images/';
            }
        } else {
            $imgPath = $this->getCategoryImagePath();
        }
        $path = BP . '/pub/media/catalog/category';
        if (!$this->file->isExists($path)) {
            $path = BP . '/pub/media/catalog/category';
            $this->file->createDirectory($path);
        }
        $imageName = $categoryImage;
        $newPath = '';
        $imagePath = $imgPath . $categoryImage;
        $newPath = '/pub/media/catalog/category/';
        $categoryImage = $newPath . $categoryImage;
        $this->file->changePermissions(BP . '/pub/media/catalog/', 0777);
        $copied = $this->file->copy($imagePath, BP . $categoryImage);
        return $imageName;
    }
    public function getProductImagePath()
    {
        $imgPath = $this->scopeConfig->getValue('firstsection/firstgroup/productImagePath');
        return $imgPath;
    }
    public function setImageProduct($productImage)
    {
        $imageName = '';
        if ($productImage == '' || $this->getProductImagePath() == null) {
            $imageName = $productImage = 'dummyimage.png';
            //menual install path
            $imgPath = BP . '/app/code/Embraceit/OscommerceToMagento/view/adminhtml/web/images/';
            //in case composer install
            if (!$this->file->isExists($imgPath)) {
                $imgPath = BP . '/vendor/embraceit/oscommerce-to-magento/view/adminhtml/web/images/';
            }
        } else {
            $imgPath = $this->getProductImagePath();
        }
        $path = BP . '/pub/media/productimages/';
        if (!$this->file->isExists($path)) {
            $path = BP . '/pub/media/productimages/';
            $this->file->createDirectory($path);
        }
        $imageName = $productImage;
        $newPath = '';
        $imagePath = $imgPath . $productImage;
        $newPath = '/pub/media/productimages/';
        $productImage = $newPath . $productImage;
        $this->file->changePermissions(BP . '/pub/media/productimages/', 0777);
        $copied = $this->file->copy($imagePath, BP . $productImage);
        return $productImage;
    }

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
    public function saveCategroyFirstTime($description, $data)
    {
        $data['data']['name'] = $description["categories_name"];
        $newCatName = $description["categories_name"];
        $metaTile = $description["meta_title"];
        $metaDes = $description["meta_description"];
        $urlKey = $this->removeAccent(strtolower($description["categories_name"]));
        $urlKey = $this->removeSpecialChar($urlKey);
        $categoryData = $this->categoryFactory->create($data);
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
        if ($parentCatId == null || $parentCatId == '') {
            $result = $this->repository->save($categoryData);
            $catId = $result->getId();
        } else {
            //echo "dont save";

            $catId = $checkCat->getFirstItem()->getEntityId();
        }
        //set default values
        $categoryDef = $this->modelCategory->load($catId);
        $categoryDef->setStoreId(0);
        $categoryDef->setImage($data['data']['image']);
        $categoryDef->save();

        return $catId;
    }
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
     * Undocumented function
     *
     * @return void
     */
    public function addCategory($startLimit, $totalLimit)
    {
        //add languges or store view before adding categories
        if ($results = $this->newDbConnection()->fetchAll($this->getAllCategories($startLimit, $totalLimit))) {
            $nCount = 0;
            $catId = '';
            $nCounter = 0;
            $flag = true;
            foreach ($results as $category) {
                try {
                    $this->unSetValue();
                    $nCounter++;
                    $this->setValue($nCounter);

                    // if ($nCounter > 500) {
                    //     die('stop and  check');
                    // }
                    //$nCounter = 0;
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
                            //'store_id' => $storeId
                        ],
                    ];
                    if ($catResults = $this->newDbConnection()->fetchAll($this->getCategoryDescription($categoryId))) {
                        $storeId = '';
                        $this->getCategoryDiscriptionData($catResults, $data);
                    }
                } catch (\Exception $e) {
                    // $this->logger->critical($e->getMessage());
                    $flag = false;
                    return $e->getMessage();
                }
            }
            return $flag;
        }
    }
    //getAllCategories($startLimit, $totalLimit)
    //get all categoryies query
    public function getAllCategories($startLimit, $totalLimit)
    {
        $select = $this->newDbConnection()
            ->select()
            ->from($this->getDbPrefix() . 'categories', '*')
            ->order('categories_id', 'ASC');
        //->limit($startLimit, $totalLimit);
        $limit = " LIMIT $startLimit,$totalLimit";
        //$select
        return $select . $limit;
    }

    //get all languges query
    public function getAllLanguages()
    {
        $select = $this->newDbConnection()
            ->select()
            ->from($this->getDbPrefix() . 'languages', '*')
            ->order('languages_id', 'ASC');
        return $select;
    }

    //get langure by id
    //get all languges query
    public function getLanguageById($id)
    {
        $select = $this->newDbConnection()
            ->select()
            ->from($this->getDbPrefix() . 'languages', '*')
            ->where('languages_id = ?', $id);
        return $select;
    }

    //get all categories description by ID
    public function getCategoryDescription($id)
    {
        $select = $this->newDbConnection()
            ->select('*')
            ->from($this->getDbPrefix() . 'categories_description')
            ->where('categories_id = ?', $id)
            ->order('language_id', 'ASC');
        return $select;
    }

    //get total products
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
    //get total custom option count
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
                        if ($results = $this->newDbConnection()->fetchAll($this->queryCustomOptions($attribute))) {
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
//attribute set add

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
//get attributeby name
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
    //add products
    public function addProducts()
    {
        $this->addAttributeSet();

        if ($results = $this->newDbConnection()->fetchAll($this->getAllProductDetials())) {
            $ncounter = 0;
            $nCounter = 0;
            $catId = '';
            $productId = '';
            $productIdProd = '';
            $prodSku = '';
            $pImage = '';
            $sku = '';
            try {
                foreach ($results as $product) {
                    //progress bar unset data
                    $this->unSetProductprogress();
                    // if ($ncounter > 50) {
                    //     die('stop annd check');
                    // }
                    //skip and check if this product exist in database
                    $ncounter++;
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

                        $sku = '';
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

                    $this->setProductprogress($ncounter);
                }
                //add here
                return "Products has been added";
            } catch (\Exception $e) {
                //$this->logger->critical($e->getMessage());
                return $e->getMessage();
            }
        }
    }

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

    //add attribute set
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
            foreach ($attributeSet as $attr):
                $attributeSetId = $attr->getAttributeSetId();
            endforeach;
            return $attributeSetId;
        }
    }
    //add tax class
    public function addTaxClass($classId)
    {
        if ($classId == 0) {
            $txClsId = 2;
        } else {
            $txClsId = $classId;
        }
    }

//update product data

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
        $productInfo->setUrlKey($urlKey); // url key of the product
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

        $collectionProduct = $this->productCollectionFactory->create()
        //->getCollection()
            ->addAttributeToSelect(['entity_id'])
            ->addAttributeToFilter('url_key', ['like' => "%" . $urlKey . '%']);
        //$query = $collectionProduct->getSelect()->__toString();
        if ($collectionProduct->getFirstItem()->getId()) {
            // url key of the product
            $urlKey = strtolower($urlKey . '-' . $productId);
            $productInfo->setUrlKey($urlKey);
            //$this->logger->info($urlKey, $proDes);
            //$this->logger->info($urlKey . '--in first condiation--');
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
        if ($productImage != '') {
            $productInfo
                ->addImageToMediaGallery(BP . $productImage, ['image', 'small_image', 'thumbnail'], false, false);
        }
        $isProduct = $this->productRepository->save($productInfo);
        $productIdProd = $isProduct->getId();
        $prodSku = $isProduct->getSku();
        return $productIdProd;
    }
//get storeID

    public function getStoreId($id)
    {
        $storeId = '';
        $resultLang = $this->newDbConnection()->fetchAll($this->getLanguageById($id));
        //end language check
        $localStores = $this->storeManagerInterface->getStores();
        foreach ($localStores as $mStoreLang) {
            if (strtolower($resultLang[0]['name']) == strtolower($mStoreLang['name'])) {
                //$addStore = false;
                //echo "in matching";
                $storeId = $mStoreLang['store_id'];
            }
        }
        return $storeId;
    }
    public function getCustomAttributeData()
    {
        return $this->scopeConfig->getValue('firstsection/firstgroup/customAttribute');
    }
    public function getOsVersion()
    {
        return $this->scopeConfig->getValue('firstsection/firstgroup/OscVersion');
    }
//add customizable options
    public function addCustomOption()
    {
        $productId = '';
        $productIdProd = '';
        $prodSku = '';
        $catId = '';

        $chooseOption = 'Choose size';
        $customOptionName = $this->getCustomAttributeData();
        $osVersion = $this->getOsVersion();

        $arrCustomOptoin = explode(',', $customOptionName);
        if ($osVersion == '1.0.0') {
            if (isset($arrCustomOptoin[0]) && $arrCustomOptoin[0] != '') {
                foreach ($arrCustomOptoin as $attribute) {
                    return $this->getOptionVersionOne($attribute);
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
                    //$this->logger->critical($e->getMessage());
                    return $e->getMessage();
                }
            }
        }
    }
    //
    public function getOptionVersionOne($attribute)
    {
        if ($results = $this->newDbConnection()->fetchAll($this->queryCustomOptions($attribute))) {
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
                                'title' => $chooseOption,
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
                        if ($opt['default_title'] == $chooseOption) {
                            $exist = true;
                            //"product option already added";
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
                return "Customizable options has been added";
            } catch (\Exception $e) {
                //$this->logger->critical($e->getMessage());
                return $e->getMessage();
            }
        }
    }
    public function checkOptionPrice($optionPrice,$proSize)
    {
        if (abs($optionPrice) == 0) {
            $selectedSize = $proSize;
        } else {
            $selectedSize = '';
        }
        return $selectedSize;
    }
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
            return "Customizable options has been added";
        } catch (\Exception $e) {
            //$this->logger->critical($e->getMessage());
            return $e->getMessage();
        }
    }
    //get product option by product id

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

    //get product option by product id

    public function queryGetOptionDetial($id)
    {
        $select = $this->newDbConnection()
            ->select()
            ->from([$this->getDbPrefix() . 'products_options'], ['products_options_name'])
            ->where('products_options_id = ?', $id);
        return $select;
    }
    //get option details

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

    public function queryCustomOptions($attribute)
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

    public function queryCustomProductAttributes()
    {
        $select = $this->newDbConnection()
            ->select()
            ->from([$this->getDbPrefix() . 'products_attributes'], ['products_id'])
            ->group('products_id')
            ->order($this->getDbPrefix() . 'products_attributes.products_id', 'ASC');
        return $select;
    }

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
    public function printQuery()
    {
        return $this->queryProductsInfo();
    }
    public function getAtributeSet($id)
    {
        $select = $this->newDbConnection()
            ->select()
            ->from($this->getDbPrefix() . 'productstype', '*')
            ->where('id=?', $id)
            ->where('language_id=1');
        return $select;
    }
    public function getAllAtrributeSet()
    {

        $select = $this->newDbConnection()
            ->select()
            ->from($this->getDbPrefix() . 'productstype', '*')
            ->where($this->getDbPrefix() . 'productstype.language_id=1')
            ->order($this->getDbPrefix() . 'productstype.id', 'ASC');
        return $select;
    }

    public function getAttributeTranslationCount()
    {
        $select = $this->newDbConnection()
            ->select()
            ->from($this->getDbPrefix() . 'productstype', 'language_id')
            ->group($this->getDbPrefix() . 'productstype.language_id');
        return $select;
    }
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
        return str_replace($a, $b, $str);
    }
    public function productToCat($id)
    {
        $select = $this->newDbConnection()
            ->select()
            ->from($this->getDbPrefix() . 'products_to_categories', '*')
            ->where('products_id=?', $id);
        return $select;
    }
    public function removeSpecialChar($urlKey)
    {
        $urlKey = preg_replace('/&/', '', $urlKey);
        $urlKey = preg_replace('/\s+/', '-', $urlKey);
        $urlKey = preg_replace('/,/', '', $urlKey);
        $urlKey = preg_replace('/[+]/', '-', $urlKey);
        $urlKey = preg_replace('/\'/', '-', $urlKey);
        $urlKey = preg_replace('/[:]/', '-', $urlKey);
        $urlKey = preg_replace('/[.]/', '-', $urlKey);
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
        return $urlKey;
    }
    public function getAllProductDetials()
    {
        $select = $this->newDbConnection()
            ->select()
            ->from($this->getDbPrefix() . 'products', '*')
            ->order('products_id', 'ASC');
        return $select;
    }
    //get all categories description by ID
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
        //$store = $objectManager->create(\Magento\Store\Model\Store::class);
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

    public function getImagePathProduct()
    {
        return $this->scopeConfig->getValue('firstsection/firstgroup/productImagePath');
    }
    public function getImagePathCategory()
    {
        return $this->scopeConfig->getValue('firstsection/firstgroup/categoryImagePath');
    }

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
        $insertData = array(
            array(
                'entity_id' => 1,
                'attribute_set_id' => 0,
                'parent_id' => 0,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'path' => 1,
                'position' => 0,
                'level' => 0,
                'children_count' => 1,
            ),
            array(
                'entity_id' => 2,
                'attribute_set_id' => 3,
                'parent_id' => 1,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'path' => '1/2',
                'position' => 1,
                'level' => 1,
                'children_count' => 0,
            ),
        );
        try {
            $connection->beginTransaction();
            $connection->insertMultiple('catalog_category_entity', $insertData);
            $connection->commit();
        } catch (\Exception $e) {

            $connection->rollBack();
            return $e;
        }

        $insertDataCe = array(
            array(
                'value_id' => 1,
                'attribute_id' => 69,
                'store_id' => 0,
                'entity_id' => 1,
                'value' => 1,
            ),
            array(
                'value_id' => 2,
                'attribute_id' => 46,
                'store_id' => 0,
                'entity_id' => 2,
                'value' => 1,
            ),
            array(
                'value_id' => 3,
                'attribute_id' => 69,
                'store_id' => 0,
                'entity_id' => 2,
                'value' => 1,
            ),
        );
        try {
            $connection->beginTransaction();
            $connection->insertMultiple('catalog_category_entity_int', $insertDataCe);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            return $e;
        }

        $insertDataVc = array(
            array(
                'value_id' => 1,
                'attribute_id' => 45,
                'store_id' => 0,
                'entity_id' => 1,
                'value' => 'Root Catalog',
            ),
            array(
                'value_id' => 2,
                'attribute_id' => 45,
                'store_id' => 0,
                'entity_id' => 2,
                'value' => 'Default Category',
            ),
        );
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
            return $e;
            $connection->rollBack();

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
