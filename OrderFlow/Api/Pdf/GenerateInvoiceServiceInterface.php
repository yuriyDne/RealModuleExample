<?php
declare(strict_types=1);

namespace Fisha\OrderFlow\Api\Pdf;

use Magento\Sales\Api\Data\OrderInterface;

interface GenerateInvoiceServiceInterface
{
    /**
     * @param OrderInterface $order
     * @param bool $returnAsUrl
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Zend_Pdf_Exception
     */
    public function execute(OrderInterface $order, bool $returnAsUrl = false): string;
}
