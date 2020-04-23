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
            $table->string('phone_number')->after('email')->nullable()->comment('電話番号');
            $table->string('country_code')->after('phone_number')->nullable()->comment('国番号');
            $table->string('authy_id')->after('country_code')->nullable()->comment('Authy ID');
            $table->boolean('sms_verified')->after('sms_verified')->default(false)->comment('SMS認証済み');

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
            $table->dropColumn('phone_number');
            $table->dropColumn('country_code');
            $table->dropColumn('authy_id');
            $table->dropColumn('sms_verified');
        });
    }
}
