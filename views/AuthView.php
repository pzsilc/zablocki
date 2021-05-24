<?php
require_once __dir__.'/../engine/view.php';
require_once __dir__.'/../models/User.php';
require_once __dir__.'/../traits/ExternalDatabase.php';

class AuthView extends View
{
    use ExternalDatabase;

    public function login()
    {
        $error = false;
        
        if($this->request->session('import_auth')){
            return $this->redirect('/');
        }

        if($this->request->method === 'POST')
        {
            $email = $this->request->post('email');
            $token = $this->request->post('token');
            $sql  ="SELECT * FROM people WHERE email='$email' AND token='$token'";
            $res = $this->external_query($sql);
            if($res){
                $user = User::filter([['external_user_id', '=', $res[0]['id']]])[0];
                $user->recent_login_at = date('Y-m-d H:i:s');
                $user->save();
                $this->request->set_session('import_auth', (object)[
                    'id' => $user->id,
                    'email' => $res[0]['email'],
                    'fname' => $res[0]['first_name'],
                    'lname' => $res[0]['last_name'],
                    'role' => (object)(array)$user->get_role()
                ]);
                $this->add_message('success', 'Zalogowałeś się');
                return $this->redirect('/');
            }
            $error = true;
            $this->add_message('error', 'Dane są nieprawdiłowe');
        }
        return $this->render('auth.login', ['error' => $error]);
    }

    public function logout()
    {
        if($this->request->session('import_auth'))
            $this->request->unset_session('import_auth');
        return $this->redirect('/');
    }
}

?>