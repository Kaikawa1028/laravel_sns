<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PrepareUsersTableForSmsVerify extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone_number')->nullable()->comment('電話番号');
            $table->string('country_code')->nullable()->comment('国番号');
            $table->string('authy_id')->nullable()->comment('Authy ID');
            $table->boolean('sms_verified')->default(false)->comment('SMS認証済み');
        });
    }
}
