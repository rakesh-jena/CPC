<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeaderRelationshipsTable extends Migration
{
    public function up()
    {
        Schema::create('leader_relationships', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('leader_id_1', 191);
            $table->string('leader_id_2', 191);
            $table->unsignedInteger('relationship_score')->default(0);
            $table->unsignedInteger('evidence_categories')->default(0);
            $table->text('relationship_types')->nullable();
            $table->unsignedInteger('total_evidence_records')->default(0);
            $table->unsignedInteger('career_evidence_records')->default(0);
            $table->unsignedInteger('education_evidence_records')->default(0);
            $table->longText('evidence_details')->nullable();

            // Preserve the names/positions captured in the source export as a
            // fallback/audit snapshot; current profile data comes from leaders.
            $table->text('leader_1_name')->nullable();
            $table->text('leader_1_chinese_name')->nullable();
            $table->text('leader_1_position')->nullable();
            $table->text('leader_1_rank')->nullable();
            $table->text('leader_2_name')->nullable();
            $table->text('leader_2_chinese_name')->nullable();
            $table->text('leader_2_position')->nullable();
            $table->text('leader_2_rank')->nullable();

            $table->timestamps();

            $table->unique(
                ['leader_id_1', 'leader_id_2'],
                'leader_relationships_pair_unique'
            );
            $table->index(['leader_id_1', 'relationship_score'], 'lr_leader1_score_idx');
            $table->index(['leader_id_2', 'relationship_score'], 'lr_leader2_score_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('leader_relationships');
    }
}
