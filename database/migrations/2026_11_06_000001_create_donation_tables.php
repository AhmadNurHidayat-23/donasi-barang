<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donations', function (Blueprint $t) {
            $t->id();
            $t->uuid('slug')->unique();
            $t->foreignId('user_id')->constrained();
            $t->string('title');
            $t->text('description');
            $t->string('category');
            $t->string('condition');
            $t->string('city');
            $t->string('photo');
            $t->string('status')->default('available');
            $t->foreignId('recipient_id')->nullable()->constrained('users');
            $t->text('pickup_note')->nullable();
            $t->boolean('donor_confirmed')->default(false);
            $t->boolean('recipient_confirmed')->default(false);
            $t->timestamp('completed_at')->nullable();
            $t->timestamps();
        });
        Schema::create('donation_requests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('donation_id')->constrained();
            $t->foreignId('user_id')->constrained();
            $t->text('reason');
            $t->string('status')->default('pending');
            $t->timestamps();
            $t->unique(['donation_id', 'user_id']);
        });
        Schema::create('donation_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('donation_id')->constrained();
            $t->foreignId('actor_id')->constrained('users');
            $t->string('description');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['donation_events', 'donation_requests', 'donations'] as $n) {
            Schema::dropIfExists($n);
        }
    }
};
