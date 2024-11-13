<?php

namespace App\Console\Commands;

use App\Models\V2\Projects\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Models\Traits\SaveAuditStatusTrait;
use App\Models\V2\User;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class BulkApproveProjectPolygonsCommand extends Command
{
  use SaveAuditStatusTrait;

  protected $signature = 'bulk-approve-project-polygons {file}';
  protected $description = 'Bulk approve site polygons for projects listed in a CSV file';

  public function handle(): void
  {
      $filePath = $this->argument('file');

      if (! File::exists($filePath)) {
          $this->error("CSV file not found at {$filePath}");
          return;
      }
      $userEmail = 'noah.maghsadi@wri.org';
      $user = User::where('email_address', $userEmail)->first();
      Auth::login($user);
      $data = array_map('str_getcsv', file($filePath));
      $header = array_shift($data);
      $output = new ConsoleOutput();
      $progressBar = new ProgressBar($output, count($data));
      $progressBar->setFormat('Processing: %current% [%bar%] %percent:3s%%');

      $progressBar->start();

      $polygonsChanged = [];

      foreach ($data as $row) {
          $uuid = $row[0];
          $project = Project::isUuid($uuid)->first();
          $this->info("\nProcessing project " . $uuid);
          if ($project) {
              foreach ($project->sitePolygons as $sitePolygon) {
                  $sitePolygon->status = 'approved';
                  $sitePolygon->save();

                  $this->saveAuditStatus(get_class($sitePolygon), $sitePolygon->id, $sitePolygon->status, 'Approved via bulk command', 'status');

                  $polygonsChanged[] = $sitePolygon;
              }
          }

          $progressBar->advance();
      }

      $progressBar->finish();

      // Print summary of the number of polygons approved
      $this->info("\n" . count($polygonsChanged) . ' polygons were updated.');
  }
}
