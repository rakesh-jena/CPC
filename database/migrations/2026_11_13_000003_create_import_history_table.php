<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(){
        Schema::create('import_history', function(Blueprint $table){
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('dataset',60)->index();
            $table->string('filename');
            $table->string('storage_path')->nullable();
            $table->string('file_hash',64)->nullable()->index();
            $table->string('status',30)->default('uploaded')->index();
            $table->unsignedInteger('row_count')->default(0);
            $table->unsignedInteger('created_count')->default(0);
            $table->unsignedInteger('updated_count')->default(0);
            $table->unsignedInteger('skipped_count')->default(0);
            $table->unsignedInteger('error_count')->default(0);
            $table->unsignedInteger('duplicate_count')->default(0);
            $table->text('error_summary')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable()->index();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(){ Schema::dropIfExists('import_history'); }
};
