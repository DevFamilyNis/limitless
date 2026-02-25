<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('project_color', 32)->nullable()->after('description');
        });

        $projects = DB::table('projects')
            ->select(['id', 'code', 'name', 'project_color'])
            ->get();

        foreach ($projects as $project) {
            if (is_string($project->project_color) && trim($project->project_color) !== '') {
                continue;
            }

            $seed = mb_strtolower(trim(sprintf('%s %s', (string) $project->code, (string) $project->name)));

            $color = match (true) {
                str_contains($seed, 'novi empay'), str_contains($seed, 'novi empaj') => 'slate',
                preg_match('/\bfm\b/i', $seed) === 1 => 'teal',
                str_contains($seed, 'empay') => 'blue',
                default => null,
            };

            if ($color === null) {
                continue;
            }

            DB::table('projects')
                ->where('id', $project->id)
                ->update([
                    'project_color' => $color,
                    'updated_at' => now(),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('project_color');
        });
    }
};
