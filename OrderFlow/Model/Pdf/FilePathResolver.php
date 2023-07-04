<?php

namespace Fisha\OrderFlow\Model\Pdf;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class FilePathResolver
 * @package Fisha\OrderFlow\Model\Pdf
 */
class FilePathResolver
{
    const INVOICES = 'invoices';
    const CREDIT_MEMO = 'creditmemo';

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * DirectoryResolver constructor.
     *
     * @param DirectoryList $directoryList
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        DirectoryList $directoryList,
        StoreManagerInterface $storeManager
    ) {
        $this->directoryList = $directoryList;
        $this->storeManager = $storeManager;
    }

    /**
     * @param Order $order
     * @param bool $forWebUsage
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getInvoicePath(Order $order, bool $forWebUsage = false): string
    {
        $fileName = 'sales_order_invoice_' . $order->getIncrementId() . '_' .time().'.pdf';
        return $forWebUsage
            ? $this->directoryList->getPath(DirectoryList::PUB) . DIRECTORY_SEPARATOR . self::INVOICES . DIRECTORY_SEPARATOR . $fileName
            : $this->getDirectoryPath(self::INVOICES) . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * @param string $fileName
     * @return string
     */
    public function getInvoiceUrl(string $fileName): string
    {
        return $this->storeManager->getStore()->getBaseUrl().
            $this->directoryList->getUrlPath(DirectoryList::PUB) . '/' . self::INVOICES . '/' . $fileName;
    }

    /**
     * @param string $fileName
     * @return string
     */
    public function getCreditMemoUrl(string $fileName): string
    {
        return $this->storeManager->getStore()->getBaseUrl().
            $this->directoryList->getUrlPath(DirectoryList::PUB) . '/' . self::CREDIT_MEMO . '/' . $fileName;
    }

    /**
     * @param Order $order
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getCreditMemoPath(Order $order)
    {
        $fileName = 'sales_order_creditmemo_' . $order->getIncrementId() . '_' .time().'.pdf';
        return $this->getDirectoryPath(self::CREDIT_MEMO) . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * @param string $dirName
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function getDirectoryPath(string $dirName)
    {
        $absolutePath = $this->directoryList->getPath(DirectoryList::PUB) . DIRECTORY_SEPARATOR . $dirName;
        if (!is_dir($absolutePath)) {
            if (!@mkdir($absolutePath, 0777, true)) {
                throw new \UnexpectedValueException(
                    "Unable to create {$absolutePath}"
                );
            }
        }

        return $absolutePath;
    }
}
