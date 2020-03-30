<?php

class Messages_Action extends Typecho_Widget implements Widget_Interface_Do
{
    public $options;
    public $db;
    public $option;

    public function __construct($request, $response, $params = null)
    {
        parent::__construct($request, $response, $params);
        $this->db = Typecho_Db::get();
        $this->options = Typecho_Widget::widget('Widget_Options');
        $this->option = $this->options->plugin('Messages');
    }

    public function execute()
    {
    }

    public function action()
    {
    }

    public function send()
    {
        try {
            if (time() > $this->request->timestamp + 10) {
                throw new Exception('参数timestamp异常，请检查后重试');
            }
            if (md5($this->option->secret . $this->request->timestamp) != $this->request->sign) {
                throw new Exception('sign illegality');
            }
            $mid = Messages_Plugin::send(
                $this->request->msg,
                $this->request->destroy ?: Messages_Plugin::HOURS,
                $this->request->authorId ?: 1
            );
            $response = array(
                'code' => $mid ? 0 : 100,
                'msg' => $mid ?: "禁止写入"
            );

        } catch (Exception $e) {
            $response = array(
                'code' => 100,
                'msg' => $e->getMessage(),
            );
        }
        $this->response->throwJson($response);
    }

}
