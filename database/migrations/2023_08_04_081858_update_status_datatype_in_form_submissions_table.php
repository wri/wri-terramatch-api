<?php

use App\Models\V2\Forms\FormSubmission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateStatusDatatypeInFormSubmissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->string('status', 50)->default(FormSubmission::STATUS_AWAITING_APPROVAL)->change();
        });
    }
}
