<?php

namespace App\Http\Controllers\V2\Forms;

use App\Http\Controllers\Controller;
use App\Models\V2\Forms\Form;
use App\Models\V2\Forms\FormOptionListOption;
use App\Models\V2\Forms\FormQuestionOption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class FormOptionsLabelController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $this->authorize('listLinkedFields', Form::class);

        if (! empty($request->query('keys'))) {
            $keys = explode(',', $request->query('keys'));
            $collection = $this->getFormOptionListOptions($keys);
            $missingSlugs = $this->getMissingSlugs($keys, $collection);

            if (! empty($missingSlugs)) {
                $additionalCollection = $this->getAdditionalFormQuestionOptions($missingSlugs);
                $collection = $this->mergeCollections($collection, $additionalCollection);
            }

            if (count($collection) > 0) {
                return new JsonResponse(['data' => $collection->values()->toArray()], 200);
            }

            return new JsonResponse(['data' => []], 200);
        }

        return new JsonResponse('No keys provided.', 406);
    }

    public function getFormOptionListOptions(array $keys): Collection
    {
        $options = FormOptionListOption::whereIn('slug', $keys)->get();
        if ($options->isEmpty()) {
            return collect([]);
        }

        return $options->map(function ($item) {
            return [
                'slug' => $item->slug,
                'label' => $item->translated_label,
                'image_url' => $item->image_url,
            ];
        })->unique('slug');
    }

    public function getMissingSlugs(array $keys, Collection $collection): array
    {
        $foundSlugs = $collection->pluck('slug')->toArray();

        return array_diff($keys, $foundSlugs);
    }

    public function getAdditionalFormQuestionOptions(array $missingSlugs): Collection
    {
        $formQuestionOptions = FormQuestionOption::whereIn('slug', $missingSlugs)->get();

        return $formQuestionOptions->flatMap(function ($resource) {
            return [
                $resource->slug => [
                    'slug' => $resource->slug,
                    'label' => $resource->label,
                    'image_url' => $resource->image_url ?? $resource->getFirstMediaUrl('image'),
                ]
            ];
        })->values();
    }

    public function mergeCollections(Collection $collection, Collection $additionalCollection): Collection
    {
        return $collection->merge($additionalCollection);
    }
}
