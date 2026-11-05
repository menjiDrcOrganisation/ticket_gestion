<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_groups', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->json('members');
            $table->timestamps();
        });

        Schema::create('notification_campaigns', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('subject')->nullable();
            $table->text('message');
            $table->json('channels');
            $table->string('trigger')->default('manual');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('event_id')->nullable()->constrained('evenements')->nullOnDelete();
            $table->foreignId('notification_group_id')->nullable()->constrained('notification_groups')->nullOnDelete();
            $table->string('source_key')->nullable()->unique();
            $table->timestamps();
        });

        Schema::create('notification_recipients', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('notification_campaign_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('email_status')->default('pending');
            $table->string('sms_status')->default('pending');
            $table->text('email_error')->nullable();
            $table->text('sms_error')->nullable();
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamp('sms_sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_recipients');
        Schema::dropIfExists('notification_campaigns');
        Schema::dropIfExists('notification_groups');
    }
};
