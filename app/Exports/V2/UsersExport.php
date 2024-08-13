<?php

namespace App\Exports\V2;

use App\Models\V2\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    public function collection(): Collection
    {
        return User::all();
    }

    public function headings(): array
    {
        return [
            'type',
            'job role',
            'first name',
            'last name',
            'email',
            'phone number',
            'organisation',
            'monitoring organisations',
            'verified at',
            'updated at',
            'created at',
        ];
    }

    public function map($user): array
    {
        return [
            $user->primary_role->name,
            $user->job_role,
            $user->first_name,
            $user->last_name,
            $user->email_address,
            $user->phone_number,
            $this->handPrimaryOrganisation($user),
            $this->handMonitoringyOrganisations($user),
            $user->email_address_verified_at,
            $user->updated_at,
            $user->created_at,
        ];
    }

    private function handPrimaryOrganisation(User $user): string
    {
        if (empty($user->organisation)) {
            return '';
        }

        return data_get($user->organisation, 'name', '');
    }

    private function handMonitoringyOrganisations(User $user): string
    {
        $list = $user->organisations()->pluck('name')->toArray();

        return '[' . implode(', ', $list) . ']';
    }
}
