<?php

/*
<COPYRIGHT>

    Copyright © 2022-2023, Canyon GBS LLC. All rights reserved.

    Advising App™ is licensed under the Elastic License 2.0. For more details,
    see https://github.com/canyongbs/advisingapp/blob/main/LICENSE.

    Notice:

    - You may not provide the software to third parties as a hosted or managed
      service, where the service provides users with access to any substantial set of
      the features or functionality of the software.
    - You may not move, change, disable, or circumvent the license key functionality
      in the software, and you may not remove or obscure any functionality in the
      software that is protected by the license key.
    - You may not alter, remove, or obscure any licensing, copyright, or other notices
      of the licensor in the software. Any use of the licensor’s trademarks is subject
      to applicable law.
    - Canyon GBS LLC respects the intellectual property rights of others and expects the
      same in return. Canyon GBS™ and Advising App™ are registered trademarks of
      Canyon GBS LLC, and we are committed to enforcing and protecting our trademarks
      vigorously.
    - The software solution, including services, infrastructure, and code, is offered as a
      Software as a Service (SaaS) by Canyon GBS LLC.
    - Use of this software implies agreement to the license terms and conditions as stated
      in the Elastic License 2.0.

    For more information or inquiries please visit our website at
    https://www.canyongbs.com or contact us via email at legal@canyongbs.com.

</COPYRIGHT>
*/

namespace App\Actions\Finders;

use ReflectionClass;
use App\Models\BaseModel;
use Illuminate\Support\Collection;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Eloquent\Model;
use AdvisingApp\Authorization\Models\Concerns\DefinesPermissions;

class ApplicationModels
{
    public function all(): Collection
    {
        return collect(File::allFiles(app_path()))
            ->map(function ($item) {
                $path = $item->getRelativePathName();
                $class = sprintf(
                    '\%s%s',
                    Container::getInstance()->getNamespace(),
                    strtr(substr($path, 0, strrpos($path, '.')), '/', '\\')
                );

                return $class;
            })
            ->filter(function ($class) {
                $isModel = false;

                if (class_exists($class)) {
                    $reflection = new ReflectionClass($class);
                    $isModel = ($reflection->isSubclassOf(Model::class) || $reflection->isSubclassOf(BaseModel::class)) &&
                    ! $reflection->isAbstract();
                }

                return $isModel;
            });
    }

    public function implementingPermissions(): Collection
    {
        return $this->all()->filter(function ($class) {
            $implementsPermissions = false;

            $reflection = new ReflectionClass($class);
            $parentClass = $reflection->getParentClass();

            if (in_array(DefinesPermissions::class, $reflection->getTraitNames()) || in_array(DefinesPermissions::class, $parentClass->getTraitNames())) {
                $implementsPermissions = true;
            }

            return $implementsPermissions;
        });
    }
}