<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacilityPlaceBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('facility_place_bookings', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->unsignedMediumInteger('user_id')->nullable();
            $table->unsignedMediumInteger('facility_place_id');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->unsignedInteger('transaction_id')->nullable();
            $table->unsignedTinyInteger('booking_status_id')->default(1);
            $table->timestamps();
        });

        Schema::table('facility_place_bookings', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('facility_place_id')->references('id')->on('facility_places');
            $table->foreign('transaction_id')->references('id')->on('transactions');
            $table->foreign('booking_status_id')->references('id')->on('booking_statuses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('facility_place_bookings');
    }
}