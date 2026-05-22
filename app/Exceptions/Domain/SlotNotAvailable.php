<?php

namespace App\Exceptions\Domain;

class SlotNotAvailable extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Ce créneau n\'est plus disponible.');
    }
}
