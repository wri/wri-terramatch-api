<?php

namespace App\Http\Controllers\V2\Forms;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Forms\LinkedFieldListingRequest;
use App\Http\Resources\V2\Forms\FormOptionListOptionResource;
use App\Http\Resources\V2\General\ListingResource;
use App\Models\V2\Forms\Form;
use App\Models\V2\Forms\FormOptionList;
use Illuminate\Http\Resources\Json\ResourceCollection;

class LinkedFieldListingsController extends Controller
{
    public function __invoke(LinkedFieldListingRequest $linkedFieldListingRequest): ResourceCollection
    {
        $this->authorize('listLinkedFields', Form::class);


        $config = config('wri.linked-fields', []);
        $includes = ['fields', 'file-collections', 'relations'];
        $list = [];

        foreach (data_get($config, 'models', []) as $modelKey => $model) {
            $modelSuffix = ' (' . data_get($model, 'label') . ')';
            foreach ($includes as $section) {
                foreach (data_get($model, $section, []) as $fieldKey => $value) {
                    $label = data_get($value, 'label', 'unknown');
                    $inputType = data_get($value, 'input_type');
                    $optionListKey = data_get($value, 'option_list_key');
                    $options = [];
                    $multichoice = data_get($value, 'multichoice');
                    $collection = data_get($value, 'collection');

                    if ($optionListKey) {
                        $formOptionList = FormOptionList::where('key', $optionListKey)->first();
                        if ($formOptionList) {
                            $options = FormOptionListOptionResource::collection($formOptionList->options);
                        }
                    }

                    $list[] = [
                        'uuid' => $fieldKey,
                        'name' => $label . $modelSuffix,
                        'label' => $label,
                        'model_key' => $modelKey,
                        'input_type' => $inputType,
                        'option_list_key' => $optionListKey,
                        'options' => $options,
                        'multichoice' => $multichoice,
                        'collection' => $collection,
                    ];
                }
            }
        }

        if ($linkedFieldListingRequest->form_types) {
            $list = array_filter($list, fn ($listItem) => in_array($listItem['model_key'], $linkedFieldListingRequest->form_types));
        }

        return ListingResource::collection($list);
    }
}
