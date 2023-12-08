<?php

namespace App\Http\Controllers\V2\User;

use App\Exports\V2\UsersExport;
use App\Http\Controllers\Controller;
use App\Models\V2\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdminExportUsersController extends Controller
{
    public function __invoke(Request $request): BinaryFileResponse
    {
        $this->authorize('export', User::class);

        $filename = 'users(' . now()->format('d-m-Y-H-i'). ').csv';

        return (new UsersExport())->download($filename, Excel::CSV)->deleteFileAfterSend(true);
    }
}
