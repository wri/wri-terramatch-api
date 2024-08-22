<?php

namespace App\Console\Commands;

use App\Models\V2\User;
use App\Validators\ServiceAccountValidator;
use DateTime;
use DateTimeZone;
use Exception;
use Illuminate\Console\Command;

class CreateServiceAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create-service-account {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a service account with the given email address.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $email = $this->argument('email');

            $apiKey = base64_encode(random_bytes(48));

            $data = [
                'email_address' => $email,
                'api_key' => $apiKey,
            ];
            ServiceAccountValidator::validate('CREATE', $data);

            $data['email_address_verified_at'] = new DateTime('now', new DateTimeZone('UTC'));

            $user = new User($data);
            $user->saveOrFail();

            // TODO Allow other types of service account, when/if necessary.
            $user->assignRole('greenhouse-service-account');

            $this->info("Created service account $email with API Key: $apiKey");

            return 0;

        } catch (Exception $exception) {
            $this->error($exception->getMessage());
            $this->error('Creation failed');

            return -1;
        }
    }
}
