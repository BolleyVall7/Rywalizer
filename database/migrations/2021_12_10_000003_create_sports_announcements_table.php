<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSportsAnnouncementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('sports_announcements', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->unsignedTinyInteger('sport_id');
            $table->unsignedSmallInteger('sports_facility_id')->nullable();
            $table->unsignedSmallInteger('sports_announcement_partner_id')->nullable();
            $table->unsignedTinyInteger('sports_announcement_type_id');
            $table->unsignedTinyInteger('minimum_skill_level_id')->nullable();
            $table->unsignedTinyInteger('gender_type_id')->nullable();
            $table->unsignedTinyInteger('age_category_id')->nullable();
            $table->unsignedTinyInteger('minimal_age')->nullable();
            $table->unsignedTinyInteger('maximum_age')->nullable();
            $table->unsignedTinyInteger('game_variant_id');
            $table->unsignedMediumInteger('front_picture_id')->nullable();
            $table->unsignedMediumInteger('background_picture_id')->nullable();
            $table->unsignedMediumInteger('ticket_price');
            $table->string('description', 2668)->nullable(); // Kodowane natywnie
            $table->unsignedTinyInteger('current_participants_number')->default(0);
            $table->unsignedTinyInteger('maximum_participants_number');
            $table->boolean('is_active')->default(0);
            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->timestamps();
        });

        Schema::table('sports_announcements', function (Blueprint $table) {
            $table->foreign('sport_id')->references('id')->on('sports');
            $table->foreign('sports_facility_id')->references('id')->on('sports_facilities')->nullOnDelete();
            $table->foreign('sports_announcement_partner_id')->references('id')->on('sports_announcement_partners')->nullOnDelete();
            $table->foreign('sports_announcement_type_id')->references('id')->on('sports_announcement_types');
            $table->foreign('minimum_skill_level_id')->references('id')->on('minimum_skill_levels');
            $table->foreign('gender_type_id')->references('id')->on('gender_types');
            $table->foreign('age_category_id')->references('id')->on('age_categories');
            $table->foreign('game_variant_id')->references('id')->on('game_variants');
            $table->foreign('front_picture_id')->references('id')->on('pictures')->nullOnDelete();
            $table->foreign('background_picture_id')->references('id')->on('pictures')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('sports_announcements');
    }
}
