<?php

namespace Fisha\OrderFlow\Setup\Patch\Data;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchInterface;

class FixPrioEmailTemplates implements DataPatchInterface
{
    const TEMPLATE_ID_TO_FILE = [
        23 => 'order_invoice.html',
    ];

    /**
     * @var AdapterInterface
     */
    protected AdapterInterface $dbConnection;

    /**
     * FixPrioEmailTemplates constructor.
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->dbConnection = $resourceConnection->getConnection();
    }

    public function apply()
    {
        $templatesDir = $this->getEmailTemplatesDir();
        $updateData = [];
        foreach (static::TEMPLATE_ID_TO_FILE as $templateId => $fileName) {
            $filePath = $templatesDir . $fileName;
            if (!is_file($filePath)) {
                throw new \LogicException('Cannot find email template ' . $filePath);
            }
            $updateData[] = [
                'template_id' => $templateId,
                'template_text' => file_get_contents($filePath)
            ];
        }

        $tableName = $this->dbConnection->getTableName('email_template');
        $this->dbConnection->insertOnDuplicate($tableName, $updateData, ['template_text']);
    }

    protected function getEmailTemplatesDir()
    {
        return __DIR__.'/email_templates/';
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
