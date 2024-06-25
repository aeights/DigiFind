<?php

use App\Models\LostReport;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('discovered_items', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(LostReport::class);
            $table->foreignId('discoverer_id');
            $table->text('description');
            $table->timestamp('discovered_date');
            $table->string('village_code');
            $table->string('location_detail');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discovered_items');
    }
};
