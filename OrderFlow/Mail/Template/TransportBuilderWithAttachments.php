<?php

namespace Fisha\OrderFlow\Mail\Template;

use Zend\Mail\Message;
use Zend\Mime\Mime;
use Zend\Mime\Part;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\Template\TransportBuilder;

/**
 * Class TransportBuilderWithAttachments
 * @package Fisha\OrderFlow\Mail\Template
 */
class TransportBuilderWithAttachments extends TransportBuilder
{
    /**
     * @var Part[]
     */
    protected array $parts = [];

    /**
     * @param string $filePath
     * @param string $fileType
     */
    public function addAttachment(string $filePath, string $fileType)
    {
        $fileName = basename($filePath);
        $fileContent = file_get_contents($filePath);

        $part = new Part();
        $part->setType($fileType)
            ->setContent($fileContent)
            ->setDisposition(\Zend_Mime::DISPOSITION_ATTACHMENT)
            ->setEncoding(\Zend_Mime::ENCODING_BASE64)
            ->setFileName($fileName);

        $this->parts[] = $part;
    }

    /**
     * @return $this|TransportBuilder
     * @throws LocalizedException
     */
    protected function prepareMessage()
    {
        parent::prepareMessage();

        if (count($this->parts)) {
            /** @var ss **/
            $message = $this->message;
            $body = Message::fromString($message->getRawMessage())->getBody();
            $body = \Zend_Mime_Decode::decodeQuotedPrintable($body);
            $part = new Part($body);
            $part->setCharset('utf-8');
            $part->setEncoding(Mime::ENCODING_BASE64);
            $part->setEncoding(Mime::ENCODING_QUOTEDPRINTABLE);
            $part->setDisposition(Mime::DISPOSITION_INLINE);
            $part->setType(Mime::TYPE_HTML);
            array_unshift($this->parts, $part);

            $bodyPart = new \Zend\Mime\Message();
            $bodyPart->setParts($this->parts);
            $message->setBody($bodyPart);

            $this->parts = [];
        }
        return $this;
    }
}
