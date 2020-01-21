<?php
namespace Embraceit\OscommerceToMagento\Setup;

use Embraceit\OscommerceToMagento\Model\ExternalDb as ModelExternalDb;
use Magento\Catalog\Model\Category;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;
    /**
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    /**
     * @var modelExternalDb
     */
    protected $modelExternalDb;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ModelExternalDb $modelExternalDb
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->modelExternalDb = $modelExternalDb;
    }
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            Category::ENTITY,
            'category_id',
            [
                'type' => 'varchar',
                'label' => 'Category Id',
                'input' => 'text',
                'required' => false,
                'sort_order' => 100,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'General Information',
            ]
        );

        //add attribute sets
        //$this->modelExternalDb->addAttributeSet();
    }
}
