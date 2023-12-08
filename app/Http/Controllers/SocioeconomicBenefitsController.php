<?php

namespace App\Http\Controllers;

use App\Helpers\ControllerHelper;
use App\Helpers\JsonResponseHelper;
use App\Helpers\UploadHelper;
use App\Http\Requests\StoreSocioeconomicBenefitsRequest;
use App\Models\SocioeconomicBenefit;
use App\Resources\SocioeconomicBenefitResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @todo this class should be refactored to use polymorphic relationships.
 * @todo On upgrade to PHP 8, reinstate type declaration on updateAction.
 */
class SocioeconomicBenefitsController extends Controller
{
    public function uploadAction(StoreSocioeconomicBenefitsRequest $request): JsonResponse
    {
        $this->authorize('upload', SocioeconomicBenefit::class);
        $data = $request->json()->all();

        $me = Auth::user();
        $data['upload'] = UploadHelper::findByIdAndValidate(
            $data['upload'],
            UploadHelper::FILES,
            $me->id
        );
        $benefitUpload = new SocioeconomicBenefit();
        $benefitUpload->upload = $data['upload'];
        $benefitUpload->name = $data['name'];

        if (isset($data['programme_id'])) {
            $benefitUpload->programme_id = $data['programme_id'];
        }
        if (isset($data['programme_submission_id'])) {
            $benefitUpload->programme_submission_id = $data['programme_submission_id'];
        }
        if (isset($data['site_submission_id'])) {
            $benefitUpload->site_submission_id = $data['site_submission_id'];
        }
        if (isset($data['site_id'])) {
            $benefitUpload->site_id = $data['site_id'];
        }
        $benefitUpload->saveOrFail();

        return JsonResponseHelper::success(new SocioeconomicBenefitResource($benefitUpload), 200);
    }

    public function updateAction(StoreSocioeconomicBenefitsRequest $request): JsonResponse
    {
        $this->authorize('upload', SocioeconomicBenefit::class);
        $data = $request->json()->all();

        $me = Auth::user();

        if (isset($data['programme_submission_id'])) {
            $benefitUpload = SocioeconomicBenefit::where('programme_submission_id', $data['programme_submission_id'])->first();
        } elseif (isset($data['programme_id'])) {
            $benefitUpload = SocioeconomicBenefit::where('programme_id', $data['programme_id'])->first();
        } elseif (isset($data['site_submission_id'])) {
            $benefitUpload = SocioeconomicBenefit::where('site_submission_id', $data['site_submission_id'])->first();
        } elseif (isset($data['site_id'])) {
            $benefitUpload = SocioeconomicBenefit::where('site_id', $data['site_id'])->first();
        } else {
            throw new ModelNotFoundException();
        }

        if (! isset($benefitUpload)) {
            $benefitUpload = ControllerHelper::callAction('SocioeconomicBenefitsController@uploadAction', $data, new StoreSocioeconomicBenefitsRequest());
            $benefitUpload = new SocioeconomicBenefit((array)$benefitUpload->data);

            return JsonResponseHelper::success(new SocioeconomicBenefitResource($benefitUpload), 200);
        }

        $data['upload'] = UploadHelper::findByIdAndValidate(
            $data['upload'],
            UploadHelper::FILES,
            $me->id
        );

        $benefitUpload->upload = $data['upload'];
        $benefitUpload->name = $data['name'];
        $benefitUpload->saveOrFail();

        return JsonResponseHelper::success(new SocioeconomicBenefitResource($benefitUpload), 200);
    }

    public function downloadTemplateAction(Request $request): BinaryFileResponse
    {
        $this->authorize('yes', 'App\\Models\\Default');

        $filename = 'roles_and_socioeconomic_benefits.xlsx';
        $path = base_path('resources/templates/roles_and_socioeconomic_benefits.xlsx');
        $headers = [
            'Content-Type' => 'application/xlsx',
        ];

        return response()->download($path, $filename, $headers);
    }

    public function downloadSiteSubmissionTemplateAction(Request $request): BinaryFileResponse
    {
        $this->authorize('yes', 'App\\Models\\Default');

        $filename = 'socioeconomics_site.xlsx';
        $path = base_path('resources/templates/socioeconomics_site.xlsx');
        $headers = [
            'Content-Type' => 'application/xlsx',
        ];

        return response()->download($path, $filename, $headers);
    }

    public function downloadProgrammeSubmissionTemplateAction(Request $request): BinaryFileResponse
    {
        $this->authorize('yes', 'App\\Models\\Default');

        $filename = 'socioeconomics_programme.xlsx';
        $path = base_path('resources/templates/socioeconomics_programme_v2.xlsx');
        $headers = [
            'Content-Type' => 'application/xlsx',
        ];

        return response()->download($path, $filename, $headers);
    }

    public function downloadCsvTemplateAction(Request $request): BinaryFileResponse
    {
        $this->authorize('yes', 'App\\Models\\Default');

        $filename = 'roles_and_socioeconomic_benefits.csv';
        $path = base_path('resources/templates/roles_and_socioeconomic_benefits.csv');
        $headers = [
            'Content-Type' => 'text/plain',
        ];

        return response()->download($path, $filename, $headers);
    }
}
