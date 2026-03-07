<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Convert existing single-string image_path values to JSON arrays
        // SQLite stores TEXT regardless of declared type, so no schema change needed —
        // Laravel's array cast on the model handles the JSON encode/decode.
        $rows = DB::table('modeler_requests')->whereNotNull('image_path')->get();
        foreach ($rows as $row) {
            // Skip if already a valid JSON array
            $decoded = json_decode($row->image_path);
            if (is_array($decoded)) {
                continue;
            }
            // Wrap old string value in a JSON array
            DB::table('modeler_requests')
                ->where('id', $row->id)
                ->update(['image_path' => json_encode([$row->image_path])]);
        }
    }

    public function down(): void
    {
        // Convert JSON arrays back to single string
        $rows = DB::table('modeler_requests')->whereNotNull('image_path')->get();
        foreach ($rows as $row) {
            $decoded = json_decode($row->image_path, true);
            if (is_array($decoded) && count($decoded) > 0) {
                DB::table('modeler_requests')
                    ->where('id', $row->id)
                    ->update(['image_path' => $decoded[0]]);
            }
        }
    }
};
