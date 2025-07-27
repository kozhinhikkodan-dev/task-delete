<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    //

    public const COMMISSION_REQUIRED_ROLE = 'Tailor';
}
