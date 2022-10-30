<?php

use think\migration\Migrator;
use think\migration\db\Column;

class RechargeSetting extends Migrator
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
    // 充值设置
    public function change()
    {
        $table = $this->table('recharge_setting', ['comment' => '充值设置', 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
        $table
            ->addColumn('recharge', 'integer', ['limit' => 20, 'default' => 0, 'comment' => '充值金额'])
            ->addColumn('giving', 'integer', ['limit' => 10, 'default' => 0, 'comment' => '赠送金额'])
            ->addColumn('sort', 'integer', ['limit' => 20, 'default' => 0, 'comment' => '排序'])
            ->addColumn('create_time', 'integer', ['limit' => 10, 'default' => 0, 'comment' => '创建时间'])
            ->addColumn('update_time', 'integer', ['limit' => 10, 'default' => 0, 'comment' => '更新时间'])
            ->addColumn('delete_time', 'integer', ['limit' => 10, 'default' => 0, 'comment' => '删除时间'])
            ->create();
    }
}
