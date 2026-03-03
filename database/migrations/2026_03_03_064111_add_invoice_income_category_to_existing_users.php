<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasTable('category_types') || ! Schema::hasTable('categories')) {
            return;
        }

        $incomeCategoryTypeId = DB::table('category_types')
            ->where('key', 'income')
            ->value('id');

        if ($incomeCategoryTypeId === null) {
            return;
        }

        DB::table('categories')
            ->where('category_type_id', $incomeCategoryTypeId)
            ->where('name', 'Naplata fakture')
            ->update([
                'name' => 'Faktura',
                'updated_at' => now(),
            ]);

        $exists = DB::table('categories')
            ->where('category_type_id', $incomeCategoryTypeId)
            ->where('name', 'Faktura')
            ->exists();

        if (! $exists) {
            $ownerUserId = (int) DB::table('users')->orderBy('id')->value('id');

            if ($ownerUserId === 0) {
                return;
            }

            DB::table('categories')->insert([
                'user_id' => $ownerUserId,
                'category_type_id' => $incomeCategoryTypeId,
                'name' => 'Faktura',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
