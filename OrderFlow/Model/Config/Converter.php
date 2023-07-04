<?php

namespace Fisha\OrderFlow\Model\Config;

class Converter implements \Magento\Framework\Config\ConverterInterface
{
    const KEY_NAME = 'processStatus';

    const INT_ARGUMENTS = [
        'attemptsCount',
        'nextRun',
    ];

    public function convert($source)
    {
        $statusList = $source->getElementsByTagName('status');
        $result = [];
        $iterator = 0;
        foreach ($statusList as $status) {
            foreach ($status->childNodes as $statusInfo) {
                $key = $statusInfo->localName;
                $textContent = $statusInfo->textContent;
                if ($key) {
                    $value = $this->getTypedValue($key, $textContent);
                    $result[$iterator][$key] = $value;
                }
            }
            $iterator++;
        }
        return ['status_list' => $result];
    }

    protected function getTypedValue(string $key, string $textContent)
    {
        if (in_array($key, self::INT_ARGUMENTS)) {
            return (int) $textContent;
        }

        return $textContent;
    }
}
