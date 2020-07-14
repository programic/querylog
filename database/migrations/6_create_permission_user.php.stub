<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermissionUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permission_user', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');

            // Nullable is for direct permissions
            $table->unsignedBigInteger('role_user_id')->nullable();

            $table->string('target_path')->nullable();
            $table->string("target_type")->nullable();
            $table->unsignedBigInteger('target_id')->nullable();

            // Causer calculated from the inheritance table
            $table->unsignedBigInteger('causer_id')->nullable();
            $table->string('permission_name');
            $table->unsignedInteger('depth');

            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users');

            $table->foreign('role_user_id')
                ->references('id')
                ->on('role_user')
                ->onDelete('cascade');

            $table->foreign('causer_id')
                ->references('id')
                ->on('permission_user')
                ->onDelete('cascade');

            $table->foreign('permission_name')
                ->references('name')
                ->on('permissions')
                ->onDelete('cascade');

            $table->index('target_path');
            $table->index(['target_type', 'target_id'], 'target');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permission_user');
    }
}
