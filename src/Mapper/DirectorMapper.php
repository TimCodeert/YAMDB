<?php

namespace App\Mapper;

use App\Model\Director as DomainDirector;

class DirectorMapper
{
    public function mapToDomain($name): DomainDirector
    {
        return new DomainDirector($name);
    }
}
