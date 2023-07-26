<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Assist\Authorization\Models\Concerns\DefinesPermissions;

abstract class BaseModel extends Model
{
    use HasFactory;

    // TODO This is going to change
    use DefinesPermissions;
}
