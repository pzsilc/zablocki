<?php

require_once __dir__.'/../engine/model.php';
require_once __dir__.'/../engine/fields.php';
require_once __dir__.'/../models/UserRole.php';
require_once __dir__.'/../traits/ExternalDatabase.php';

class User extends Model
{
    const TABLE = 'users';
    use ExternalDatabase;

    public function __construct()
    {
        $this->recent_login_at = DateTimeField::init('recent_login_at', ['required' => false]);
        $this->external_user_id = IntegerField::init('external_user_id');
        $this->role_id = ForeignField::init('role_id', UserRole::class, ['default' => 1]);
        $this->messages_allow = BooleanField::init('messages_allow', ['default' => true]);
    }

    public function get_external_user()
    {
        $res = $this->external_query("SELECT id, email, last_name, first_name FROM people WHERE active=1 AND id=$this->external_user_id");
        return count($res) ? (object)$res[0] : null;
    }

    public function get_role()
    {
        return UserRole::get($this->role_id);
    }
}

?>