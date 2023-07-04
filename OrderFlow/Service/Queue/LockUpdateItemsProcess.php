<?php

namespace Fisha\OrderFlow\Service\Queue;

use Fisha\OrderFlow\Model\Config;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;

/**
 * Class LockUpdateItemsProcess
 * @package Fisha\OrderFlow\Service\Queue
 */
class LockUpdateItemsProcess
{
    const MAX_RUN_TIME = "-10min";

    const FILE_NAME = 'fisha_orderflow_run.lock';
    /**
     * @var Filesystem
     */
    protected $fileSystem;
    /**
     * @var Config
     */
    protected $config;
    /**
     * @var File
     */
    protected $file;

    /**
     * LockUpdateItemsProcess constructor.
     *
     * @param Filesystem $fileSystem
     * @param Config $config
     * @param File $file
     * @param DirectoryList $directoryList
     */
    public function __construct(
        Filesystem $fileSystem,
        Config $config,
        File $file
    ) {
        $this->fileSystem = $fileSystem;
        $this->config = $config;
        $this->file = $file;
    }

    /**
     * @return bool
     */
    public function lock(): bool
    {
        $lockFilePath = $this->getAbsolutePath();
        $lockDate = false;
        if ($this->file->isExists($lockFilePath)) {
            $lockDate = $this->file->fileGetContents($lockFilePath);
        }

        if ($lockDate == false
            || $lockDate < strtotime(self::MAX_RUN_TIME)
        ) {
            $this->lockProcess($lockFilePath);
            return true;
        }

        return  false;
    }

    public function unlock()
    {
        $lockFilePath = $this->getAbsolutePath();
        if ($this->file->isExists($lockFilePath)) {
            $this->file->deleteFile($lockFilePath);
        }
    }

    /**
     * @return string
     */
    protected function getAbsolutePath()
    {
        $varDir = $this->fileSystem->getDirectoryRead(DirectoryList::VAR_DIR);
        $lockFilePath = $varDir->getAbsolutePath() . self::FILE_NAME;

        return $lockFilePath;
    }

    /**
     * @param string $lockFilePath
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function lockProcess(string $lockFilePath)
    {
        $fd = $this->file->fileOpen($lockFilePath, 'w');
        $this->file->fileWrite($fd, strtotime('now'));
        $this->file->fileClose($fd);
    }
}
