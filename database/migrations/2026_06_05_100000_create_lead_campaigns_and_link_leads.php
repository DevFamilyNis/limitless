<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('name');
        });

        $campaignId = DB::table('lead_campaigns')->insertGetId([
            'name' => 'EmPay 2.0',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('lead_campaign_id')->nullable()->after('id');
        });

        DB::table('leads')->update(['lead_campaign_id' => $campaignId]);

        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('lead_campaign_id')->nullable(false)->change();
            $table->foreign('lead_campaign_id')
                ->references('id')
                ->on('lead_campaigns')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['lead_campaign_id']);
            $table->dropColumn('lead_campaign_id');
        });

        Schema::dropIfExists('lead_campaigns');
    }
};
