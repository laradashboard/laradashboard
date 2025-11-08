<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\CampaignStatus;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('email_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('subject');
            $table->string('preheader')->nullable();
            $table->longText('body_html')->nullable();
            $table->longText('content_text')->nullable();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->enum('status', CampaignStatus::getValues())->default(CampaignStatus::DRAFT);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('total_recipients')->default(0);
            $table->integer('sent_count')->default(0);
            $table->integer('delivered_count')->default(0);
            $table->integer('opened_count')->default(0);
            $table->integer('clicked_count')->default(0);
            $table->integer('bounced_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->integer('unsubscribed_count')->default(0);
            $table->string('from_email');
            $table->string('from_name')->nullable();
            $table->string('reply_to_name')->nullable();
            $table->string('reply_to_email')->nullable();
            $table->json('settings')->nullable(); // Campaign settings like tracking, etc.
            $table->json('filters')->nullable(); // Contact filters for recipient selection
            $table->boolean('track_opens')->default(true);
            $table->boolean('track_clicks')->default(true);
            $table->boolean('use_custom_from')->default(false);
            $table->boolean('use_utm_parameters')->default(false);
            $table->json('contact_group_ids')->nullable();
            $table->json('contact_group_excluded_ids')->nullable();
            $table->json('contact_tag_ids')->nullable();
            $table->json('contact_tag_excluded_ids')->nullable();
            $table->string('campaign_source')->nullable();
            $table->string('campaign_medium')->nullable();
            $table->string('campaign_name')->nullable();
            $table->string('campaign_term')->nullable();
            $table->string('campaign_content')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('template_id')->references('id')->on('email_templates')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_campaigns');
    }
};