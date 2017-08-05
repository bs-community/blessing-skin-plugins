<?php
/**
 * @Author: printempw
 * @Date:   2017-01-08 10:00:47
 * @Last Modified by:   printempw
 * @Last Modified time: 2017-01-08 10:03:16
 */

namespace DataIntegration;

use Option;
use App\Services\Database;
use InvalidArgumentException;

class Form
{
    public static function getAll()
    {
        $forms = [];

        $forms['config'] = Option::form('config', '对接配置', function ($form) {

            $form->select('da_adapter',  '数据对接适配器')
                    ->option('',         '不进行数据对接')
                    ->option('Authme',   'Authme')
                    ->option('Crazy',    'CrazyLogin')
                    ->option('BeeLogin', 'BeeLogin')
                    ->option('Discuz',   'Discuz')
                    ->option('Phpwind',  'Phpwind');

            $form->text('da_columns[username]', '用户名字段');
            $form->text('da_columns[password]', '密码字段');
            $form->text('da_columns[ip]',       '注册 IP 字段');

            $form->checkbox('da_verbose_log', '日志记录')->label('记录详细日志（调试时请开启）');

            $form->select('da_duplicated_prefer', '重复处理')
                    ->option('target', '用目标程序上的用户数据覆盖皮肤站')
                    ->option('skin',   '用皮肤站用户数据覆盖目标程序')
                    ->description('此项选择后，同名用户的密码同步将以你选择的那一方为准，另一方的密码将被覆盖');

            $form->checkbox('da_bilateral', '双向同步')->label('将皮肤站用户同步至目标程序');

        })->handle();

        $forms['connection'] = Option::form('connection', '数据库连接配置', function ($form) {

            $form->text('da_connection[host]',     '目标数据库地址')->hint('跨数据库主机进行对接可能会有延迟，敬请知悉。');
            $form->text('da_connection[port]',     '端口');
            $form->text('da_connection[database]', '数据库名');
            $form->text('da_connection[username]', '用户名');
            $form->text('da_connection[password]', '密码');
            $form->text('da_connection[table]',    '用户数据表名');

        })->handle()->always(function($form) {
            $conn = unserialize(option('da_connection'));

            try {
                $db = new Database($conn);

                if (!$db->hasTable($conn['table'])) {
                    $form->addMessage("数据表 [{$conn['table']}] 不存在", 'warning');
                } else {
                    $form->addMessage('目标数据库连接正常。', 'success');
                }

            } catch (InvalidArgumentException $e) {
                $form->addMessage('无法连接至 MySQL 服务器，请检查你的配置：'.$e->getMessage(), 'warning');
            }
        });

        return $forms;
    }

}
