<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDefaultTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('default_types', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedTinyInteger('default_type_name_id');
            $table->string('name', 50)->nullable();
            $table->string('description_simple', 250)->nullable();
            $table->string('description_perfect', 250)->nullable();
            $table->string('description_future', 250)->nullable();
            $table->unsignedSmallInteger('icon_id')->nullable();
            $table->unsignedMediumInteger('creator_id')->nullable();
            $table->unsignedMediumInteger('editor_id')->nullable();
            $table->boolean('is_active');
            $table->timestamps();
        });

        Schema::table('default_types', function (Blueprint $table) {
            $table->foreign('default_type_name_id')->references('id')->on('default_type_names');
            $table->foreign('icon_id')->references('id')->on('icons')->nullOnDelete();
            $table->foreign('creator_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('editor_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('gender_id')->references('id')->on('default_types');
            $table->foreign('role_id')->references('id')->on('default_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('default_types');
    }
}
