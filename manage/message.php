<?php
include 'common.php';
include 'header.php';
include 'menu.php';
require_once __DIR__ . '/../Messages_Page.php';
?>
<?php

$db = Typecho_Db::get();
$prefix = $db->getPrefix();
$request = Typecho_Request::getInstance();

$type = $request->get('type', 1);
$pagenum = $request->get('page', 1);
$filter = $request->get('filter', 'all');

$query = $db->select('table.messages.mid',
    'table.messages.authorId',
    'table.messages.msg',
    'table.messages.status',
    'table.messages.created',
    'table.messages.destroy',
    'table.users.screenName',
    'table.users.mail')->from('table.messages');
$query->join('table.users', 'table.messages.authorId = table.users.uid', Typecho_Db::LEFT_JOIN);
$query = $query->order('created', Typecho_Db::SORT_DESC);
$query = $query->page($pagenum, 20);
$rowGoods = $db->fetchAll($query);

$filterOptions = $request->get($filter);

$filterArr = array(
    'filter' => $filter,
    $filter => $filterOptions
);
$qcount = $db->select('count(1) AS count')->from('table.messages');

$page = new Messages_Page(20, $db->fetchAll($qcount)[0]['count'], $pagenum, 10,
    array_merge($filterArr, array(
        'panel' => 'Message/manage/message.php',
        'action' => 'logs',
        'type' => $type,
    )));

?>
<div class="main">
    <div class="body container">
        <?php include 'page-title.php'; ?>
        <div class="row typecho-page-main" role="main">

            <div class="col-mb-12">
                <ul class="typecho-option-tabs fix-tabs clearfix">
                    <li class="current"><a href="#">消息推送列表</a></li>
                    <li><a href="<?php $options->adminUrl('options-plugin.php?config=Messages') ?>">插件设置</a></li>
                </ul>
            </div>

            <div class="col-mb-12 typecho-list">

                <div class="col-mb-12 col-tb-12 row" role="main">
                    <form method="post" name="manage_posts" class="operate-form">
                        <div class="typecho-table-wrap">
                            <table class="typecho-list-table">
                                <colgroup>
                                    <col width="10%"/>
                                    <col width="10%"/>
                                    <col width="50%"/>
                                    <col width="15%"/>
                                    <col width="15%"/>
                                </colgroup>
                                <thead>
                                <tr>
                                    <th><?php _e('mid'); ?></th>
                                    <th><?php _e('创建人'); ?></th>
                                    <th><?php _e('推送内容'); ?></th>
                                    <th><?php _e('创建时间'); ?></th>
                                    <th><?php _e('摧毁时间'); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                foreach ($rowGoods as $value) {
                                    ?>
                                    <tr id="<?php echo $value["mid"]; ?>">
                                        <td><?php echo $value["mid"]; ?></td>
                                        <td><?php echo $value["screenName"]; ?></td>
                                        <td><?php echo $value["msg"]; ?></td>
                                        <td><?php echo date('Y-m-d H:i:s', $value["created"]); ?></td>
                                        <td><?php echo date('Y-m-d H:i:s', $value["destroy"]); ?></td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </form>

                    <div class="typecho-list-operate clearfix">
                        <ul class="typecho-pager">
                            <?php echo $page->show(); ?>
                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?php
include 'copyright.php';
?>
