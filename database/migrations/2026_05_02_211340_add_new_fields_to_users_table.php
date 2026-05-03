<?php
// database/migrations/2024_01_01_000002_add_extra_fields_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Adiciona APENAS os campos que NÃO existem na tabela users original

            // Documentos
            $table->string('cpf', 14)->unique()->nullable()->after('email');
            $table->string('rg', 20)->nullable()->after('cpf');

            // Contato
            $table->string('phone', 20)->nullable()->after('rg');
            $table->string('mobile', 20)->nullable()->after('phone');
            $table->string('alternative_email', 100)->nullable()->after('mobile');

            // Status
            $table->boolean('is_active')->default(true)->after('password');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');

            // Preferências
            $table->string('locale', 5)->default('pt_BR')->after('last_login_ip');
            $table->string('timezone', 50)->default('America/Sao_Paulo')->after('locale');
            $table->json('preferences')->nullable()->after('timezone');

            // Perfil
            $table->string('profile_photo_path', 2048)->nullable()->after('preferences');

            // Notificações
            $table->boolean('email_notifications')->default(true)->after('profile_photo_path');
            $table->boolean('sms_notifications')->default(false)->after('email_notifications');
            $table->boolean('push_notifications')->default(true)->after('sms_notifications');

            // 2FA
            $table->boolean('two_factor_enabled')->default(false)->after('push_notifications');

            // Metadados
            $table->text('notes')->nullable()->after('two_factor_enabled');

            // Soft Deletes (adiciona deleted_at)
            $table->softDeletes()->after('updated_at');

            // Índices
            $table->index('cpf');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'cpf', 'rg', 'phone', 'mobile', 'alternative_email',
                'is_active', 'last_login_at', 'last_login_ip',
                'locale', 'timezone', 'preferences',
                'profile_photo_path', 'email_notifications',
                'sms_notifications', 'push_notifications',
                'two_factor_enabled', 'notes'
            ]);

            $table->dropSoftDeletes();
        });
    }
};
