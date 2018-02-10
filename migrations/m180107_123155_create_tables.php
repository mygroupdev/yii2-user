<?php

use yii\db\Migration;

/**
 * Class m180107_123155_create_tables
 */
class m180107_123155_create_tables extends Migration
{
    public function up()
    {
        $this->createTable('user', [
            'id'            => $this->primaryKey(),
            'username'      => $this->string(25)->unique(),
            'email'         => $this->string(255)->unique(),
            'phone'         => $this->string(20)->unique(),
            'password_hash' => $this->string(60)->notNull(),
            'auth_key'      => $this->string(32)->notNull(),
            'sing_up_ip'    => $this->string(45)->notNull(),
            'role'          => $this->integer()->defaultValue(null),
            'status'        => $this->integer(6)->defaultValue(0),
            'last_sign_in'  => $this->integer()->defaultValue(null),
            'confirmed_at'  => $this->integer()->defaultValue(null),
            'blocked_at'    => $this->integer()->defaultValue(null),
            'created_at'    => $this->integer()->notNull(),
            'updated_at'    => $this->integer()->notNull(),
        ]);

        $this->createTable('token', [
            'user_id'    => $this->integer()->notNull(),
            'code'       => $this->string(32)->notNull(),
            'type'       => $this->smallInteger()->notNull(),
            'created_at' => $this->integer()->notNull(),
        ]);
        $this->createIndex('{{%token_unique}}', '{{%token}}', ['user_id', 'code', 'type'], true);
        $this->addForeignKey('{{%fk_user__token}}', '{{%token}}', 'user_id', '{{%user}}', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('{{%fk_user__token}}', '{{%token}}');
        $this->dropIndex('{{%token_unique}}', '{{%token}}');
        $this->dropTable('token');

        $this->dropTable('user');
    }
}
