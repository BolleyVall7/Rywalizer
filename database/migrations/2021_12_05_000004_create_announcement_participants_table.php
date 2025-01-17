<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnnouncementParticipantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('announcement_participants', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->unsignedMediumInteger('user_id')->nullable();
            $table->unsignedInteger('announcement_seat_id');
            $table->unsignedInteger('announcement_payment_id')->nullable();
            $table->unsignedSmallInteger('joining_status_id')->comment('Status dołączenia, np. zatwierdzone, odrzucone etc.');
            $table->timestamps();
        });

        Schema::table('announcement_participants', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('announcement_seat_id')->references('id')->on('announcement_seats');
            $table->foreign('announcement_payment_id')->references('id')->on('announcement_payments');
            $table->foreign('joining_status_id')->references('id')->on('default_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('announcement_participants');
    }
}
