<?php
// database/migrations/xxxx_xx_xx_create_not_companies_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotCompaniesTable extends Migration
{
    public function up()
    {
        Schema::create('not_companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('sector_id')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('not_companies');
    }
}