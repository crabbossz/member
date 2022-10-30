<?php

use think\migration\Migrator;
use think\migration\db\Column;

class Member extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    // 会员信息
    public function change()
    {
        $table = $this->table('member', ['comment' => '会员', 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
        $table
            ->addColumn('avatar', 'string', ['limit' => 255, 'default' => '/static/index/images/avatar.png', 'comment' => '头像'])
            ->addColumn('nickname', 'string', ['limit' => 30, 'default' => '', 'comment' => '昵称'])
            ->addColumn('code', 'string', ['limit' => 255, 'default' => '', 'comment' => '会员码'])
            ->addColumn('mobile', 'string', ['limit' => 11, 'default' => '', 'comment' => '手机号'])
            ->addColumn('birthday', 'integer', ['limit' => 10, 'default' => 0, 'comment' => '生日'])
            ->addColumn('integral', 'integer', ['limit' => 10, 'default' => 0, 'comment' => '积分'])
            ->addColumn('balance', 'integer', ['limit' => 20, 'default' => 0, 'comment' => '余额'])
            ->addColumn('business', 'string', ['limit' => 20, 'default' => '', 'comment' => '业务员'])
            ->addColumn('create_time', 'integer', ['limit' => 10, 'default' => 0, 'comment' => '创建时间'])
            ->addColumn('update_time', 'integer', ['limit' => 10, 'default' => 0, 'comment' => '更新时间'])
            ->addColumn('delete_time', 'integer', ['limit' => 10, 'default' => 0, 'comment' => '删除时间'])
            ->create();
    }
}
