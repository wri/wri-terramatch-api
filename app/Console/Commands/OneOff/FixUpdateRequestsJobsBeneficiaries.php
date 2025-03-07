<?php

namespace App\Console\Commands\OneOff;

use App\Console\Commands\Traits\Abortable;
use App\Console\Commands\Traits\AbortException;
use App\Models\V2\Forms\Form;
use App\Models\V2\Forms\FormQuestion;
use App\Models\V2\Forms\FormSection;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\UpdateRequests\UpdateRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class FixUpdateRequestsJobsBeneficiaries extends Command
{
    use Abortable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:fix-update-requests-jobs-beneficiaries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the change request data for update requests that were in progress when jobs and beneficiaries data was moved to demographics';

    protected const DEMOGRAPHICS_LINKED_FIELDS = [
        'jobs' => [ 'full-time' => 'pro-rep-full-time-jobs', 'part-time' => 'pro-rep-part-time-jobs'],
        'volunteers' => ['volunteer' => 'pro-rep-volunteers'],
    ];

    protected const DEMOGRAPHICS_MAPPING = [
        'jobs' => [
            'full-time' => [
                'gender' => [
                    'male' => 'pro-rep-ft-men',
                    'female' => 'pro-rep-ft-women',
                    'non-binary' => 'pro-rep-ft-other',
                ],
                'age' => [
                    'youth' => 'pro-rep-ft-youth',
                    'non-youth' => 'pro-rep-ft-non-youth',
                ],
                'total' => 'pro-rep-ft-total',
            ],
            'part-time' => [
                'gender' => [
                    'male' => 'pro-rep-pt-men',
                    'female' => 'pro-rep-pt-women',
                    'non-binary' => 'pro-rep-pt-other',
                ],
                'age' => [
                    'youth' => 'pro-rep-pt-youth',
                    'non-youth' => 'pro-rep-pt-non-youth',
                ],
                'total' => 'pro-rep-pt-total',
            ],
        ],
        'volunteers' => [
            'volunteer' => [
                'gender' => [
                    'male' => 'pro-rep-volunteer-men',
                    'female' => 'pro-rep-volunteer-women',
                    'non-binary' => 'pro-rep-volunteer_other',
                ],
                'age' => [
                    'youth' => 'pro-rep-volunteer-youth',
                    'non-youth' => 'pro-rep-volunteer-non-youth',
                ],
                'caste' => [
                    'marginalized' => 'pro-rep-volunteer_scstobc',
                ],
                'total' => 'pro-rep-volunteer-total',
            ],
        ],
    ];

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->executeAbortableScript(function () {
            $this->ensureFormQuestionsDeleted();
            $this->processUpdateRequests();
        });
    }

    private function processUpdateRequests(): void
    {
        // This is the list of all questions related to our linked field keys of concern that were removed since the release
        // and therefore could have data set in an affected update request.
        $questionIds = FormQuestion::withTrashed()->where('deleted_at', '>', '2025-03-05')->whereIn('linked_field_key', $this->allOldLinkedFieldKeys())->pluck("uuid");

        $updateRequests = UpdateRequest::whereNot('status', 'approved')
            ->where('updaterequestable_type', ProjectReport::class)
            ->where(function ($query) use ($questionIds) {
                $questionIds->each(function ($id) use ($query) {
                    $query->orWhereNotNull("content->$id");
                });
            });
        $count = (clone $updateRequests)->count();
        $this->info("\n Processing individual update requests...");
        $this->withProgressBar($count, function ($bar) use ($updateRequests) {
            $updateRequests->each(function ($updateRequest, $index) use ($bar){
                $this->processUpdateRequest($updateRequest);
                $bar->advance();
            });
        });
    }

    /**
     * @throws AbortException
     */
    private function processUpdateRequest($updateRequest): void
    {
        $projectReport = $updateRequest->updaterequestable;
        if ($projectReport == null) return;

        $sections = $projectReport->getForm()->sections()->withTrashed()->get();
        $relevantQuestions = collect();

        foreach ($sections as $section) {
            foreach ($section->questions()->withTrashed()->get() as $question) {
                $relevantQuestions->push($this->getRelevantQuestions($question));
            }
        }
        $relevantQuestions = $relevantQuestions
            ->flatten()
            ->filter()
            ->mapToGroups(fn ($q) => [ $q->linked_field_key => $q->uuid])
            ->map(
              function ($uuids, $linkedFieldKey) use ($updateRequest) {
                $unique = $uuids->unique();
                if ($unique->count() > 1) {
                    $encodedIds = json_encode($uuids);
                    $this->abort("Found multiple question UUIDs for a single linked field: [$updateRequest->uuid, $linkedFieldKey, $encodedIds}]");
                }

                return $unique->first();
            });

        $requiresSave = false;
        $content = $updateRequest->content;
        foreach (self::DEMOGRAPHICS_MAPPING as $demographicType => $demographics) {
            foreach ($demographics as $collection => $fieldMapping) {
                $demographic = $this->generateDemographicDefinition($collection, $fieldMapping, $updateRequest, $relevantQuestions);
                if ($demographic != null) {
                    $demographicKey = self::DEMOGRAPHICS_LINKED_FIELDS[$demographicType][$collection];
                    $questionUuid = $this->getQuestionUuid($updateRequest->updaterequestable->getForm(), $demographicKey);
                    $this->assert($questionUuid != null, "demographics question not found [$updateRequest->uuid, $demographicKey]");

                    data_set($content, $questionUuid, [$demographic]);
                    $requiresSave = true;
                }
            }
        }
        if ($requiresSave) {
            $updateRequest->update(['content' => $content]);
        }
    }

    private function getRelevantQuestions($question): Collection
    {
        $questions = collect();
        if ($this->allOldLinkedFieldKeys()->contains($question->linked_field_key)) {
            $questions->push($question);
        }

        $children = $question->children()->withTrashed()->get();
        if (! empty($children)) {
            foreach ($children as $child) {
                $questions->push($this->getRelevantQuestions($child));
            }
        }

        return $questions;
    }

    private function generateDemographicDefinition($collection, $fieldMapping, $updateRequest, $questionUuidMapping): ?array
    {
        $entries = collect();
        $total = 0;
        foreach ($fieldMapping as $type => $subtypes) {
            if ($type == "total") {
                $questionUuid = data_get($questionUuidMapping, $subtypes);
                if (! empty($questionUuid)) {
                    $total = data_Get($updateRequest->content, $questionUuid, $total);
                }
            } else {
                foreach ($subtypes as $subtype => $linkedFieldKey) {
                    $questionUuid = data_get($questionUuidMapping, $linkedFieldKey);
                    if (empty($questionUuid)) continue;

                    $value = data_get($updateRequest->content, $questionUuid);
                    if ($value != null) {
                        $entries->push([
                            'type' => $type,
                            'subtype' => $subtype,
                            'name' => null,
                            'amount' => $value
                        ]);
                    }
                }
            }
        }

        if ($entries->count() == 0 && $total == 0) return null;

        // Balance gender (and age in non-HBF frameworks) against the reported total. When doing both gender and
        // age, take the max value from the reported total, the gender total and the age total and make sure
        // both gender and age reach that value.
        $genderTotal = $entries->filter(fn ($entry) => $entry['type'] == "gender")->sum("amount");
        $ageTotal = $entries->filter(fn ($entry) => $entry['type'] == "age")->sum("amount");
        $framework = $updateRequest->updaterequestable->framework_key;
        $missingGender = 0;
        $missingAge = 0;
        if ($framework == "hbf") {
            $missingGender = max(0, $total - $genderTotal);
        } else {
            $targetTotal = max($genderTotal, $ageTotal, $total);
            $missingGender = $targetTotal - $genderTotal;
            $missingAge = $targetTotal - $ageTotal;
        }

        if ($missingGender > 0) {
            $entries->push([
                'type' => 'gender',
                'subtype' => 'unknown',
                'name' => null,
                'amount' => $missingGender
            ]);
        }
        if ($missingAge > 0) {
            $entries->push([
                'type' => 'age',
                'subtype' => 'unknown',
                'name' => null,
                'amount' => $missingAge
            ]);
        }

        return [
            'collection' => $collection,
            'demographics' => $entries,
        ];
    }

    private function getQuestionUuid($formElement, $linkedFieldKey): ?string {
        if (get_class($formElement) == Form::class) {
            foreach ($formElement->sections as $section) {
                $uuid = $this->getQuestionUuid($section, $linkedFieldKey);
                if ($uuid != null) return $uuid;
            }
        } else if (get_class($formElement) == FormSection::class) {
            foreach ($formElement->questions as $question) {
                $uuid = $this->getQuestionUuid($question, $linkedFieldKey);
                if ($uuid != null) return $uuid;
            }
        } else if (get_class($formElement) == FormQuestion::class) {
            if ($formElement->linked_field_key == $linkedFieldKey) return $formElement->uuid;
            foreach ($formElement->children as $question) {
                $uuid = $this->getQuestionUuid($question, $linkedFieldKey);
                if ($uuid != null) return $uuid;
            }
        }

        return null;
    }


    private $relevantFieldKeys;
    private function allOldLinkedFieldKeys()
    {
        if ($this->relevantFieldKeys != null) return $this->relevantFieldKeys;
        return $this->relevantFieldKeys = collect(self::DEMOGRAPHICS_MAPPING)->values()->flatten()->values()->flatten()->values()->flatten();
    }

    private function ensureFormQuestionsDeleted()
    {
        $fieldKeys = $this->allOldLinkedFieldKeys();
        $this->info('Looking for form questions to delete: ' . json_encode($fieldKeys, JSON_PRETTY_PRINT));
        FormQuestion::whereIn('linked_field_key', $fieldKeys)
            ->get()
            ->each(function ($q) {
                // In this case, the parent still exists so we want to keep the question intact.
                if ($q->parent_id == null && $q->parent != null) {
                    return;
                }

                $this->info("Removing form question: [$q->uuid, $q->linked_field_key]");
                $q->delete();
            });
    }
}
