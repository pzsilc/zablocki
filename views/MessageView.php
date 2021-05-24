<?php
require_once __dir__.'/../engine/view.php';
require_once __dir__.'/../models/Message.php';

class MessageView extends View
{
    public function delete()
    {
        $id = $this->request->get('id');
        $message = Message::get_object_or_404($id);
        $message->delete();
        return $this->redirect('/account');
    }
}

?>