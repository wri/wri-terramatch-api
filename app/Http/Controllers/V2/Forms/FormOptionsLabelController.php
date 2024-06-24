<?php

namespace App\Http\Controllers\V2\Forms;

use App\Http\Controllers\Controller;
use App\Models\V2\Forms\Form;
use App\Models\V2\Forms\FormOptionListOption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FormOptionsLabelController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $this->authorize('listLinkedFields', Form::class);

        if (! empty($request->query('keys'))) {
            $collection = FormOptionListOption::whereIn('slug', explode(',', $request->query('keys')))->get();

            $list = [];
            foreach ($collection as $item) {
                $list[] = [
                    'slug' => $item->slug,
                    'label' => $item->translated_label,
                    'image_url' => $item->image_url,
                ];
            }

            if (count($list) > 0) {
                return new JsonResponse(['data' => $list], 200);
            }

            return new JsonResponse(['data' => []], 200);
        }

        return new JsonResponse('No keys provided.', 406);
    }
}
