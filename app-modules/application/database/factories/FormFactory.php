<?php

/*
<COPYRIGHT>

Copyright © 2022-2023, Canyon GBS LLC

All rights reserved.

This file is part of a project developed using Laravel, which is an open-source framework for PHP.
Canyon GBS LLC acknowledges and respects the copyright of Laravel and other open-source
projects used in the development of this solution.

This project is licensed under the Affero General Public License (AGPL) 3.0.
For more details, see https://github.com/canyongbs/assistbycanyongbs/blob/main/LICENSE.

Notice:
- The copyright notice in this file and across all files and applications in this
 repository cannot be removed or altered without violating the terms of the AGPL 3.0 License.
- The software solution, including services, infrastructure, and code, is offered as a
 Software as a Service (SaaS) by Canyon GBS LLC.
- Use of this software implies agreement to the license terms and conditions as stated
 in the AGPL 3.0 License.

For more information or inquiries please visit our website at
https://www.canyongbs.com or contact us via email at legal@canyongbs.com.

</COPYRIGHT>
*/

namespace Assist\Form\Database\Factories;

use Illuminate\Support\Str;
use Assist\Form\Models\Form;
use Assist\Form\Models\FormField;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Form>
 */
class FormFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'description' => fake()->sentences(asText: true),
            'embed_enabled' => fake()->boolean(),
            'allowed_domains' => [fake()->domainName()],
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Form $form) {
            if ($form->fields()->doesntExist()) {
                $form->fields()->createMany(FormField::factory()->count(3)->make()->toArray());

                $form->content = [
                    'type' => 'doc',
                    'content' => $form->fields->map(fn (FormField $field): array => [
                        'type' => 'tiptapBlock',
                        'attrs' => [
                            'id' => $field->id,
                            'type' => $field->type,
                            'data' => [
                                'label' => $field->label,
                                'isRequired' => $field->is_required,
                                ...$field->config,
                            ],
                        ],
                    ])->all(),
                ];
                $form->save();
            }

            if ($form->submissions()->doesntExist()) {
                for ($i = 0; $i < rand(1, 3); $i++) {
                    $submission = $form->submissions()->create();

                    foreach ($form->fields as $field) {
                        $submission->fields()->attach(
                            $field,
                            ['id' => Str::orderedUuid(), 'response' => fake()->words(rand(1, 10), true)],
                        );
                    }
                }
            }
        });
    }
}
