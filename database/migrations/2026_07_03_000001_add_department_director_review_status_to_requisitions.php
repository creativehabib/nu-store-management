<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const BaseStatuses = [
        'pending',
        'initiator_checked',
        'ad_approved',
        'dd_approved',
        'director_approved',
        'returned',
        'distributed',
    ];

    private const DepartmentDirectorReview = 'department_director_review';

    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            $this->modifyStatusEnum([
                ...self::BaseStatuses,
                self::DepartmentDirectorReview,
                ...$this->existingStatuses(),
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::table('requisitions')
                ->where('status', self::DepartmentDirectorReview)
                ->update(['status' => 'pending']);

            $this->modifyStatusEnum([
                ...self::BaseStatuses,
                ...$this->existingStatuses(),
            ]);
        }
    }

    /**
     * @param  array<int, string>  $statuses
     */
    private function modifyStatusEnum(array $statuses): void
    {
        $enumValues = collect($statuses)
            ->filter()
            ->unique()
            ->map(fn (string $status): string => DB::getPdo()->quote($status))
            ->implode(', ');

        DB::statement("ALTER TABLE requisitions MODIFY status ENUM({$enumValues}) DEFAULT 'pending'");
    }

    /**
     * @return array<int, string>
     */
    private function existingStatuses(): array
    {
        return DB::table('requisitions')
            ->whereNotNull('status')
            ->distinct()
            ->pluck('status')
            ->all();
    }
};
