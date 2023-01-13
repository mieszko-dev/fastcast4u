<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->boolean('email_marketing_consent')->default(false);
            $table->timestamp('email_marketing_consent_at')->nullable();
            $table->timestamp('email_marketing_consent_revoked_at')->nullable();
            $table->boolean('phone_marketing_consent')->default(false);
            $table->timestamp('phone_marketing_consent_at')->nullable();
            $table->timestamp('phone_marketing_consent_revoked_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('details');
    }
};
