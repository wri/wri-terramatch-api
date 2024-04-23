<?php

use App\Exceptions\Terrafund\InvalidMorphableModelException;
use App\Helpers\UploadHelper;
use App\Models\Programme;
use App\Models\Site;
use App\Models\SiteSubmission;
use App\Models\Submission;
use App\Models\Terrafund\TerrafundNursery;
use App\Models\Terrafund\TerrafundNurserySubmission;
use App\Models\Terrafund\TerrafundProgramme;
use App\Models\Terrafund\TerrafundProgrammeSubmission;
use App\Models\Terrafund\TerrafundSite;
use App\Models\Terrafund\TerrafundSiteSubmission;
use Illuminate\Support\Str;

function explode_pop(string $delimiter, string $string): string
{
    $parts = explode($delimiter, $string);

    return array_pop($parts);
}

function explode_shift(string $delimiter, string $string): string
{
    $parts = explode($delimiter, $string);

    return array_shift($parts);
}

function arr_uv(array $array): array
{
    return array_values(array_unique($array));
}

function arr_dv(array $array1, $array2): array
{
    return array_values(array_diff($array1, $array2));
}

/**
 * This function takes an exception's trace and returns the controller and
 * action which threw it (as an array where the first element is the controller
 * and the second is the action). If a controller and action cannot be found
 * then an array of nulls is returned.
 *
 * Rather than returning after the first match all matches are recorded and only
 * the last match is returned. This means if one controller calls another (which
 * does happen with drafts) the controller and action invoked by the HTTP kernel
 * are returned.
 */
function get_controller_and_action_from_trace(array $stack): array
{
    $matches = [];
    foreach ($stack as $trace) {
        if (key_exists('class', $trace) && key_exists('function', $trace)) {
            if (Str::startsWith($trace['class'], 'App\\Http\\Controllers\\')) {
                $classParts = explode('\\', $trace['class']);
                $matches[] = [array_pop($classParts), $trace['function']];
            }
        }
    }
    if (count($matches) > 0) {
        return array_pop($matches);
    } else {
        return [null, null];
    }
}

/**
 * This function will take a morphable model, and return it
 * For example, if a polymorphic relationship's type is
 * 'programme' then it will return the programme with
 * the corresponding ID
 */
function getTerrafundModelDataFromMorphable(string $type, int $id)
{
    switch ($type) {
        case 'programme':
            return [
                'model' => TerrafundProgramme::findOrFail($id),
                'files' => UploadHelper::FILES_PDF,
            ];
        case 'nursery':
            return [
                'model' => TerrafundNursery::findOrFail($id),
                'files' => UploadHelper::IMAGES_VIDEOS,
            ];
        case 'site':
            return [
                'model' => TerrafundSite::findOrFail($id),
                'files' => UploadHelper::IMAGES_VIDEOS,
            ];
        case 'nursery_submission':
            return [
                'model' => TerrafundNurserySubmission::findOrFail($id),
                'files' => UploadHelper::IMAGES_VIDEOS,
            ];
        case 'site_submission':
            return [
                'model' => TerrafundSiteSubmission::findOrFail($id),
                'files' => UploadHelper::IMAGES_VIDEOS,
            ];
        case 'programme_submission':
            return [
                'model' => TerrafundProgrammeSubmission::findOrFail($id),
                'files' => [
                    'photos' => UploadHelper::IMAGES_VIDEOS,
                    'other_additional_documents' => UploadHelper::FILES_IMAGES,
                ],
            ];
        default:
            throw new InvalidMorphableModelException();
    }
}

function getDataFromMorphable(string $type, int $id)
{
    $all = array_merge(UploadHelper::FILES, UploadHelper::IMAGES, UploadHelper::VIDEOS);
    switch ($type) {
        case 'programme':
            return [
                'model' => Programme::findOrFail($id),
                'files' => $all,
            ];
        case 'site':
        case 'control_site':
            return [
                'model' => Site::findOrFail($id),
                'files' => $all,
            ];
        case 'site_submission':
        case 'control_site_submission':
            return [
                'model' => SiteSubmission::findOrFail($id),
                'files' => $all,
            ];
        case 'submission':
        case 'programme_submission':
            return [
                'model' => Submission::findOrFail($id),
                'files' => $all,
            ];
        default:
            throw new InvalidMorphableModelException();
    }
}

/**
 * This function will take a User and assign a Spatie role to it based on its 'role' property.
 * This is a super hacky and we will need to rework how roles are assigned to users
 */
function assignSpatieRole($user)
{
    switch ($user->role) {
        case 'user':
            $user->assignRole('project-developer');

            break;
        case 'admin':
            $user->assignRole('admin-ppc');

            break;
        case 'terrafund_admin':
            $user->assignRole('admin-terrafund');

            break;
        case 'service':
            $user->assignRole('greenhouse-service-account');

            // no break
        case 'project-developer':
        case 'funder':
        case 'government':
            $user->assignRole($user->role);

            break;
    }
}
