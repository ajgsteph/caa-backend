<?php

namespace App\Actions\Certificate;

use Illuminate\Support\Facades\DB;

class GenerateUniqueNumberAction
{
    public function execute(): string
    {
        $year = (int) date('Y');

        return DB::transaction(function () use ($year): string {
            $row = DB::table('certificate_sequences')
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if ($row === null) {
                DB::table('certificate_sequences')->insert([
                    'year' => $year,
                    'last_number' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $next = 1;
            } else {
                $next = $row->last_number + 1;
                DB::table('certificate_sequences')
                    ->where('year', $year)
                    ->update([
                        'last_number' => $next,
                        'updated_at' => now(),
                    ]);
            }

            return sprintf('CAA-%d-%04d', $year, $next);
        });
    }
}
