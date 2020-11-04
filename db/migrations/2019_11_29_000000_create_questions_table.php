<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Schema\Blueprint;
use stdClass;

/**
 * This migration creates the "questions" table and migrates the existing "Questions" records.
 */
class CreateQuestionsTable extends Migration
{
    use ChangesReferences;
    use Reference;

    /**
     * @return void
     */
    public function up(): void
    {
        $hasPreviousQuestionsTable = $this->schema->hasTable('Questions');

        if ($hasPreviousQuestionsTable) {
            // Rename because some SQL DBMS handle identifiers case insensitive
            $this->schema->rename('Questions', 'PreviousQuestions');
        }

        $this->createNewQuestionsTable();

        if ($hasPreviousQuestionsTable) {
            $this->copyPreviousToNewQuestionsTable();
            $this->changeReferences(
                'PreviousQuestions',
                'QID',
                'questions',
                'id'
            );
            $this->schema->drop('PreviousQuestions');
        }
    }

    /**
     * @return void
     */
    public function down(): void
    {
        // Rename as some SQL DBMS handle identifiers case insensitive
        $this->schema->rename('questions', 'new_questions');

        $this->createPreviousQuestionsTable();
        $this->copyNewToPreviousQuestionsTable();
        $this->changeReferences(
            'new_questions',
            'id',
            'Questions',
            'QID'
        );

        $this->schema->drop('new_questions');
    }

    /**
     * @return void
     */
    private function createNewQuestionsTable(): void
    {
        $this->schema->create(
            'questions',
            function (Blueprint $table) {
                $table->increments('id');
                $this->referencesUser($table);
                $table->text('text');
                $table->text('answer')
                    ->nullable();
                $this->references($table, 'users', 'answerer_id')
                    ->nullable();
            }
        );
    }

    /**
     * @return void
     */
    private function copyPreviousToNewQuestionsTable(): void
    {
        $connection = $this->schema->getConnection();
        /** @var stdClass[] $previousQuestionsRecords */
        $previousQuestionsRecords = $connection
            ->table('PreviousQuestions')
            ->get();

        foreach ($previousQuestionsRecords as $previousQuestionRecord) {
            $connection->table('questions')->insert([
                'id'          => $previousQuestionRecord->QID,
                'user_id'     => $previousQuestionRecord->UID,
                'text'        => $previousQuestionRecord->Question,
                'answerer_id' => $previousQuestionRecord->AID,
                'answer'      => $previousQuestionRecord->Answer,
            ]);
        }
    }

    /**
     * @return void
     */
    private function createPreviousQuestionsTable(): void
    {
        $this->schema->create(
            'Questions',
            function (Blueprint $table) {
                $table->increments('QID');
                $this->references($table, 'users', 'UID');
                $table->text('Question');
                $this->references($table, 'users', 'AID')
                    ->nullable();
                $table->text('Answer')
                    ->nullable();
            }
        );
    }

    /**
     * @return void
     */
    private function copyNewToPreviousQuestionsTable(): void
    {
        $connection = $this->schema->getConnection();
        /** @var Collection|stdClass[] $questionRecords */
        $questionRecords = $connection
            ->table('new_questions')
            ->get();

        foreach ($questionRecords as $questionRecord) {
            $connection->table('Questions')->insert([
                'QID'      => $questionRecord->id,
                'UID'      => $questionRecord->user_id,
                'Question' => $questionRecord->text,
                'AID'      => $questionRecord->answerer_id,
                'Answer'   => $questionRecord->answer,
            ]);
        }
    }
}
