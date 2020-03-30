<?php

/**
 * 消息推送 - 南博助手
 * @package Messages
 * @author 权那他
 * @version 1.0
 */
class Messages_Plugin implements Typecho_Plugin_Interface
{
    const MINUTE = 60;
    const HOURS = 60 * 60;
    const DAY = 60 * 60 * 24;

    // 激活插件
    public static function activate()
    {
        Helper::addPanel(3, 'Messages/manage/message.php', '消息推送', '消息推送列表', 'administrator');
        Helper::addRoute("messages_send", "/messages/send", "Messages_Action", 'send');
        $db = Typecho_Db::get();
        $prefix = $db->getPrefix();
        $db->query('CREATE TABLE IF NOT EXISTS `' . $prefix . 'messages` (
		  `mid` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `authorId` int(2) DEFAULT 1,
		  `msg` text,
		  `type` int(1) DEFAULT 0,
		  `status` int(1) DEFAULT 1,
		  `created` int(10)	 DEFAULT 0,
		  `destroy` int(10)  DEFAULT 0,
		  PRIMARY KEY (`mid`)
		) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;');

        return _t('插件已经激活！');
    }

    // 禁用插件
    public static function deactivate()
    {
        Helper::removePanel(3, 'Messages/manage/message.php');
        Helper::removeRoute("messages_send");
        return _t('插件已被禁用');
    }

    public static function send($msg = "消息内容为空", $destroy = Messages_Plugin::HOURS, $authorId = 1)
    {
        $config = Typecho_Widget::widget('Widget_Options')->plugin('Messages');
        if ($config->enable == 1) {
            $db = Typecho_Db::get();
            $time = time();
            $data = array(
                'msg' => $msg,
                'authorId' => $authorId,
                'type' => 0,
                'status' => 1,
                'created' => $time,
                'destroy' => $time + $destroy
            );
            $insert = $db->insert('table.messages')->rows($data);
            return $db->query($insert);
        }
        return null;
    }

    public static function delete($mids)
    {
        $db = Typecho_Db::get();
        if (isset($mids) && is_array($mids)) {
            foreach ($mids as $mid) {
                $db->query($db->delete('table.messages')
                    ->where('mid = ?', $mid)
                );
            }
        }
    }

    public static function read($authorId = 1, $size = 10, $page = 1)
    {
        $db = Typecho_Db::get();
        $query = $db->select()->from('table.messages')
            ->where('authorId =? ', $authorId)
            ->page($page, $size);
        $fetchAll = $db->fetchAll($query);
        Messages_Plugin::destroy();
        return $fetchAll;
    }

    public static function destroy($authorId = 1)
    {
        $db = Typecho_Db::get();
        $db->query($db->delete('table.messages')
            ->where('destroy <?', time())
            ->where('authorId =? ', $authorId)
        );
    }


    // 插件配置面板
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $options = Typecho_Widget::widget('Widget_Options');
        $secret = new Typecho_Widget_Helper_Form_Element_Text(
            'secret', null, md5(uniqid(microtime(true), true)),
            'Secret', 'secret，如果你使用接口写入，则需要用到secret来验证权限,接口:' . $options->index . "/messages/send");
        $form->addInput($secret);

        $enable = new Typecho_Widget_Helper_Form_Element_Radio(
            'enable', array(
            '0' => '禁用',
            '1' => '开放',
        ), '1', '使用状态:', '请选择是否开放消息写入能力,关闭后就不会写入了');
        $form->addInput($enable);
    }

    // 个人用户配置面板
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }

    public static function form($action = NULL)
    {
    }
}
