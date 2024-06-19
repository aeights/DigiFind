<?php

use App\Models\LostReport;
use App\Models\PublicationPackage;
use App\Models\TransactionStatus;
use App\Models\User;
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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class);
            $table->foreignIdFor(LostReport::class);
            $table->foreignIdFor(PublicationPackage::class);
            $table->foreignIdFor(TransactionStatus::class)->default(1);
            $table->timestamp('transaction_date');
            $table->timestamp('expired');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
