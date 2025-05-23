<?php
// database/migrations/xxxx_xx_xx_xxxxxx_make_institution_id_nullable_in_graduate_educations.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('graduate_educations', function (Blueprint $table) {
            $table->unsignedBigInteger('institution_id')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('graduate_educations', function (Blueprint $table) {
            $table->unsignedBigInteger('institution_id')->nullable(false)->change();
        });
    }
};