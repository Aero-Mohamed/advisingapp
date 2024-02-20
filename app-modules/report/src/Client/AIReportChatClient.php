<?php

namespace AdvisingApp\Report\Client;

use Closure;
use Throwable;
use Illuminate\Support\Arr;
use AdvisingApp\Task\Models\Task;
use Illuminate\Support\Facades\DB;
use AdvisingApp\Alert\Models\Alert;
use Illuminate\Support\Facades\Schema;
use AdvisingApp\CareTeam\Models\CareTeam;
use AdvisingApp\Prospect\Models\Prospect;
use AdvisingApp\Interaction\Models\Interaction;
use AdvisingApp\Prospect\Models\ProspectSource;
use AdvisingApp\Prospect\Models\ProspectStatus;
use AdvisingApp\StudentDataModel\Models\Program;
use AdvisingApp\StudentDataModel\Models\Student;
use AdvisingApp\IntegrationAI\Client\AzureOpenAI;
use AdvisingApp\Notification\Models\Subscription;
use Illuminate\Auth\Access\AuthorizationException;
use AdvisingApp\Interaction\Models\InteractionType;
use AdvisingApp\StudentDataModel\Models\Enrollment;
use AdvisingApp\StudentDataModel\Models\Performance;
use AdvisingApp\Interaction\Models\InteractionDriver;
use AdvisingApp\Interaction\Models\InteractionStatus;
use AdvisingApp\Interaction\Models\InteractionOutcome;
use AdvisingApp\Interaction\Models\InteractionCampaign;
use AdvisingApp\Interaction\Models\InteractionRelation;
use AdvisingApp\Report\Settings\ReportAssistantSettings;
use AdvisingApp\Assistant\Services\AIInterface\Enums\AIChatMessageFrom;
use AdvisingApp\Assistant\Services\AIInterface\DataTransferObjects\Chat;

class AIReportChatClient extends AzureOpenAI
{
    public function ask(Chat $chat, ?Closure $callback, int $attempt = 1): string
    {
        if (is_null($this->systemContext)) {
            $this->setSystemContext();
        }

        if ($chat->messages->last()->message && ($attempt === 1)) {
            $this->dispatchPromptInitiatedEvent($chat);
        }

        try {
            $response = $this->client->chat()->create([
                'messages' => $this->formatMessagesFromChat($chat),
                'functions' => [
                    [
                        'name' => 'sql',
                        'description' => 'Get the results of a SQL query',
                        'parameters' => [
                            'type' => 'object',
                            'properties' => [
                                'query' => [
                                    'type' => 'string',
                                    'description' => 'The SQL query to execute.',
                                ],
                            ],
                            'required' => ['query'],
                        ],
                    ],
                ],
            ]);
        } catch (Throwable $exception) {
            if ($attempt >= 3) {
                return "An error occurred while processing this request: {$exception->getMessage()}";
            }

            return $this->ask($chat, $callback, $attempt + 1);
        }

        $response = Arr::first($response->choices);

        if (
            ($response->finishReason === 'function_call') &&
            ($response->message->functionCall->name === 'sql')
        ) {
            $query = json_decode($response->message->functionCall->arguments, associative: true)['query'] ?? null;

            if (blank($query)) {
                return 'An error occurred while processing this request.';
            }

            try {
                $this->canQuery($query, $this->getAuthorizedTables()) || throw new AuthorizationException();

                $results = DB::select($query);
            } catch (AuthorizationException $exception) {
                if ($attempt >= 2) {
                    return 'So sorry, I do not have the data I need to answer that question.';
                }

                return $this->ask($chat, $callback, $attempt + 1);
            } catch (Throwable $exception) {
                if ($attempt >= 3) {
                    return 'You do not appear to have access to the information required to process this request.';
                }

                return $this->ask($chat, $callback, $attempt + 1);
            }

            $chat->messages[] = [
                'from' => AIChatMessageFrom::from($response->message->role),
                'functionCall' => [
                    'name' => $response->message->functionCall->name,
                    'arguments' => $response->message->functionCall->arguments,
                ],
            ];

            $chat->messages[] = [
                'from' => AIChatMessageFrom::Function,
                'name' => 'sql',
                'message' => json_encode($results, JSON_PRETTY_PRINT),
            ];

            return $this->ask($chat, $callback);
        }

        return $response->message->content;
    }

    protected function getAuthorizedTables(): array
    {
        return collect([
            Alert::class,
            CareTeam::class,
            Interaction::class, InteractionCampaign::class, InteractionDriver::class, InteractionOutcome::class, InteractionRelation::class, InteractionStatus::class, InteractionType::class,
            Prospect::class, ProspectSource::class, ProspectStatus::class,
            Student::class, Enrollment::class, Performance::class, Program::class,
            Subscription::class,
            Task::class,
        ])
            ->filter(fn (string $model) => auth()->user()->can('viewAny', $model))
            ->map(fn (string $model): string => app($model)->getTable())
            ->all();
    }

    protected function setSystemContext(): void
    {
        $schema = collect(Schema::getTables())
            ->keyBy('name')
            ->only($this->getAuthorizedTables())
            ->map(fn (array $table): string => "{$table['name']}: " . PHP_EOL . collect(Schema::getColumns($table['name']))
                ->map(fn (array $column): string => "{$table['name']}.{$column['name']} ({$column['type_name']}" . ($column['nullable'] ? ', nullable' : '') . ')')
                ->join(PHP_EOL))
            ->implode(PHP_EOL . PHP_EOL);

        $this->systemContext = (string) str(
            app(ReportAssistantSettings::class)
                ->prompt_system_context,
        )->replace('{{ schema }}', $schema);
    }

    protected function canQuery(string $query, array $authorizedTables): bool
    {
        return collect($this->getConsumedTablesFromQuery($query))
            ->diff($authorizedTables)
            ->isEmpty();
    }

    protected function getConsumedTablesFromQuery(string $query): array
    {
        $tables = [];

        $explain = DB::select("EXPLAIN {$query}");

        foreach ($explain as $explainRow) {
            preg_match_all(
                '/(?<=Scan\son\s)(\w+)\s*?|Scan\susing\s\w+\son\s\K(\w+)\s*?/',
                $explainRow->{'QUERY PLAN'},
                $tableMatches,
                PREG_SET_ORDER,
            );

            if (! empty($tableMatches)) {
                $tables[] = $tableMatches[0][0];
            }
        }

        return collect($tables)->unique()->values()->all();
    }
}
