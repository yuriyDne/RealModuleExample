<?php
declare(strict_types=1);

namespace Fisha\OrderFlow\Service\Pdf;

use Fisha\OrderFlow\Api\Pdf\GenerateInvoiceServiceInterface;
use Fisha\OrderFlow\Model\Pdf\FilePathResolver;
use Magento\Framework\Exception\FileSystemException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Pdf\Invoice;
use Zend_Pdf_Exception;

class GenerateInvoiceService implements GenerateInvoiceServiceInterface
{
    /**
     * @var FilePathResolver
     */
    protected FilePathResolver $filePathResolver;

    /**
     * @var Invoice
     */
    protected Invoice $pdfInvoice;

    /**
     * @param FilePathResolver $filePathResolver
     * @param Invoice $pdfInvoice
     */
    public function __construct(
        FilePathResolver $filePathResolver,
        Invoice $pdfInvoice
    ) {
        $this->filePathResolver = $filePathResolver;
        $this->pdfInvoice = $pdfInvoice;
    }

    /**
     * @param OrderInterface $order
     * @param bool $returnAsUrl
     * @return string
     * @throws FileSystemException
     * @throws Zend_Pdf_Exception
     */
    public function execute(OrderInterface $order, bool $returnAsUrl = false): string
    {
        $invoices = $order->getInvoiceCollection();
        $returnInvoices = [];

        foreach ($invoices as $invoice) {
            $returnInvoices[]  = $invoice;
        }

        $pdf = $this->pdfInvoice->getPdf($returnInvoices);
        $invoicePath = $this->filePathResolver->getInvoicePath($order, $returnAsUrl);
        $pdf->save($invoicePath);

        return $returnAsUrl
            ? $this->filePathResolver->getInvoiceUrl(basename($invoicePath))
            : $invoicePath;
    }
}
