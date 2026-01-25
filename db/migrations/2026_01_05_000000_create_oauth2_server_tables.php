<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOauth2ServerTables extends Migration
{
    use Reference;

    public function up(): void
    {
        // OAuth2 Clients
        $this->schema->create('oauth2_clients', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('identifier', 80)->unique();
            $table->string('name');
            $table->text('secret')->nullable();
            $table->text('redirect_uris');
            $table->string('grants')->default('authorization_code');
            $table->text('scopes')->nullable();
            $table->boolean('confidential')->default(true);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // OAuth2 Client AngelType restrictions (pivot table)
        $this->schema->create('oauth2_client_angel_type', function (Blueprint $table): void {
            $table->increments('id');
            $this->references($table, 'oauth2_clients', 'oauth2_client_id');
            $this->references($table, 'angel_types');
            $table->unique(['oauth2_client_id', 'angel_type_id']);
        });

        // OAuth2 Access Tokens
        $this->schema->create('oauth2_access_tokens', function (Blueprint $table): void {
            $table->string('id', 100)->primary();
            $this->references($table, 'oauth2_clients', 'oauth2_client_id');
            $this->referencesUser($table)->nullable();
            $table->text('scopes')->nullable();
            $table->boolean('revoked')->default(false);
            $table->dateTime('expires_at');
            $table->timestamps();
        });

        // OAuth2 Refresh Tokens
        $this->schema->create('oauth2_refresh_tokens', function (Blueprint $table): void {
            $table->string('id', 100)->primary();
            $table->string('access_token_id', 100);
            $table->boolean('revoked')->default(false);
            $table->dateTime('expires_at');
            $table->timestamps();

            $table->foreign('access_token_id')
                ->references('id')->on('oauth2_access_tokens')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        // OAuth2 Authorization Codes
        $this->schema->create('oauth2_auth_codes', function (Blueprint $table): void {
            $table->string('id', 100)->primary();
            $this->references($table, 'oauth2_clients', 'oauth2_client_id');
            $this->referencesUser($table);
            $table->text('scopes')->nullable();
            $table->boolean('revoked')->default(false);
            $table->dateTime('expires_at');
            $table->text('redirect_uri');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $this->schema->drop('oauth2_auth_codes');
        $this->schema->drop('oauth2_refresh_tokens');
        $this->schema->drop('oauth2_access_tokens');
        $this->schema->drop('oauth2_client_angel_type');
        $this->schema->drop('oauth2_clients');
    }
}
