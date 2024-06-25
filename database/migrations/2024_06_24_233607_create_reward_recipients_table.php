<?php

use App\Models\DiscoveredItem;
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
        Schema::create('reward_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(DiscoveredItem::class);
            $table->bigInteger('reward');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reward_recipients');
    }
};
