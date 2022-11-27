<?php
declare(strict_types=1);

namespace App\Bundle\TestBundle\Messenger\Message;

class ExchangeMessage
{

    private $fileName;

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * @param mixed $fileName
     */
    public function setFileName($fileName): void
    {
        $this->fileName = $fileName;
    }

}