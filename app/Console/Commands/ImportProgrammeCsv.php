<?php

namespace App\Console\Commands;

use App\Helpers\ControllerHelper;
use App\Models\Organisation;
use App\Models\OrganisationVersion;
use App\Models\Programme;
use App\Models\ProgrammeTreeSpecies;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use League\Csv\Reader;

class ImportProgrammeCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import-programme-csv {filename}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import a CSV of programmes';

    public function handle(): int
    {
        $csv = Reader::createFromPath(base_path('imports/') . $this->argument('filename'), 'r');
        $csv->setHeaderOffset(0);
        $records = $csv->getRecords();

        foreach ($records as $record) {
            // find or create an organisation
            $organisationVersion = OrganisationVersion::where('name', $record['organisation'])
                ->first();

            // find if a system user already exists
            $user = User::where('email_address', 'server+' . preg_replace('/[^A-Za-z0-9]/', '', $record['organisation']) . '@3sidedcube.com')
                ->first();

            if (! $user) {
                // create a system user so we can manage it, then log in
                $user = ControllerHelper::callAction('UsersController@createAction', [
                    'first_name' => 'System',
                    'last_name' => 'User',
                    'email_address' => 'server+' . preg_replace('/[^A-Za-z0-9]/', '', $record['organisation']) . '@3sidedcube.com',
                    'password' => Hash::make(Str::random(8)),
                    'job_role' => 'Automated User',
                    'facebook' => null,
                    'twitter' => null,
                    'linkedin' => null,
                    'instagram' => null,
                    'phone_number' => '00000',
                ])->data;
            }

            Auth::onceUsingId($user->id);

            if (! $organisationVersion) {
                $organisation = ControllerHelper::callAction('OrganisationsController@createAction', [
                    'name' => $record['organisation'],
                    'description' => 'Automatically Created Organisation',
                    'address_1' => 'Automatically generated address',
                    'address_2' => null,
                    'city' => 'Automatically generated city',
                    'state' => null,
                    'zip_code' => null,
                    'country' => 'US',
                    'phone_number' => '00000',
                    'website' => null,
                    'facebook' => null,
                    'twitter' => null,
                    'linkedin' => null,
                    'instagram' => null,
                    'avatar' => null,
                    'cover_photo' => null,
                    'video' => null,
                    'founded_at' => null,
                    'type' => 'other',
                    'category' => 'both',
                ])->data->data;
            } else {
                $organisation = Organisation::where('id', $organisationVersion->organisation_id)->first();
                $user->organisation_id = $organisation->id;
            }

            $programme = new Programme();
            $programme->organisation_id = $organisation->id;
            $programme->name = $record['name'];
            $programme->framework_id = 1; // PPC
            $programme->boundary_geojson = $record['boundary geojson'];
            $programme->country = $record['country code'];
            $programme->continent = $record['continent'];
            $programme->saveOrFail();

            $programme->aim()->create([
                'programme_id' => $programme->id,
                'year_five_trees' => $record['aim year five trees'] ?? null,
                'restoration_hectares' => $record['aim restoration hectares'] ?? null,
                'survival_rate' => $record['aim survival rate'] ?? null,
                'year_five_crown_cover' => $record['aim year five crown cover'] ?? null,
            ]);

            foreach (explode('//', $record['tree species']) as $treeSpeciesName) {
                $species = new ProgrammeTreeSpecies();
                $species->programme_id = $programme->id;
                $species->name = $treeSpeciesName;
                $species->saveOrFail();
            }
            $this->info('Programme ' . $record['name'] . ' created.');
        }

        return 0;
    }
}
