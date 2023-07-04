<?php
namespace Fisha\OrderFlow\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class StopProcessingReason implements OptionSourceInterface
{
    const NO_REASON = 0;
    const MAX_ATTEMPTS_COUNT_REACHED = 1;
    const FAILED_PROCESSOR_STATUS = 4;
    const UNKNOWN_REASON = 100;

    public function toOptionArray()
    {
        return [
            [
                'value' => self::NO_REASON,
                'label' => '-',
            ],
            [
                'value' => self::MAX_ATTEMPTS_COUNT_REACHED,
                'label' => __('Max attempts count reached)'),
            ],
            [
                'value' => self::FAILED_PROCESSOR_STATUS,
                'label' => __('Failed Processor Status')
            ],
            [
                'value' => self::UNKNOWN_REASON,
                'label' => __('Unknown Reason')
            ]
        ];
    }


}
