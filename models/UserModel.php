<?php


class UserModel extends Model {

    /**
     * Vérifie si un titre identique existe déjà
     * @param $id    Titre du code ADE
     * @return bool     Renvoie vrai s'il y a un doublon
     */
    protected function checkIfDoubleUserID($id){
        $var = 0;
        $req = $this->getDb()->prepare('SELECT * FROM code_delete_account WHERE ID_user =:ID_user');
        $req->bindValue(':ID_user', $id);
        $req->execute();
        while ($data = $req->fetch(PDO::FETCH_ASSOC)){
            $var = $var + 1;
        }
        if ($var > 0)
            return true;
        else
            return false;

        $req->closeCursor();
    }

    public function createRandomCode($userID){
        if(! ($this->checkIfDoubleUserID($userID))){
            $req = $this->getDb()->prepare('INSERT INTO code_delete_account (ID_user, Code) 
                                         VALUES (:ID_user, :code)');

            $code = wp_generate_password();

            $req->bindParam(':ID_user', $userID);
            $req->bindParam(':code', $code);

            $req->execute();

            return $code;
        }
        else{
            $code = $this->modifyCode($userID);
            return $code;
        }
    }

    public function getAll($table)
    {
        return parent::getAll($table); // TODO: Change the autogenerated stub
    }

    public function modifyCode($id_user){
        $req = $this->getDb()->prepare('UPDATE code_delete_account SET Code=:code WHERE ID_user=:id_user');
        $code = wp_generate_password();
        $req->bindParam(':id_user', $id_user);
        $req->bindParam(':code', $code);
        $req->execute();
        return $code;
    }

    public function getCode($userID){
        $var = [];
        $req = $this->getDb()->prepare('SELECT * FROM code_delete_account WHERE ID_user = :UserID');
        $req->bindParam(':UserID', $userID);
        $req->execute();
        while ($data = $req->fetch(PDO::FETCH_ASSOC)) {
            $var[] = $data;
        }
        return $var;
        $req->closeCursor();
    }

    public function deleteCode($UserID){
        $req = $this->getDb()->prepare('DELETE FROM code_delete_account WHERE ID_user = :userID');
        $req->bindValue(':userID', $UserID);

        $req->execute();
    }

    public function modifyMyCodes($id, $login, $codes) {
        $this->modifyUser($id, $login, $codes);
    }

    protected function verifyTuple($login){
        $var = 0;
        $req = $this->getDb()->prepare('SELECT * FROM wp_users WHERE user_login =:login');
        $req->bindValue(':login', $login);
        $req->execute();
        while ($data = $req->fetch(PDO::FETCH_ASSOC)) {
            $var = $var + 1;
        }
        if ($var > 0) {
            return true;
        } else {
            return false;
        }
        $req->closeCursor();
    }

    public function verifyMail($mail) {
        $var = 0;
        $req = $this->getDb()->prepare('SELECT * FROM wp_users WHERE user_email =:mail');
        $req->bindValue(':mail', $mail);
        $req->execute();
        while ($data = $req->fetch(PDO::FETCH_ASSOC)) {
            $var = $var + 1;
        }
        if ($var > 0) {
            return false;
        } else {
            return true;
        }
        $req->closeCursor();
    }

    public function verifyLogin($login) {
        $var = 0;
        $req = $this->getDb()->prepare('SELECT * FROM wp_users WHERE user_login =:login');
        $req->bindValue(':login', $login);
        $req->execute();
        while ($data = $req->fetch(PDO::FETCH_ASSOC)) {
            $var = $var + 1;
        }
        if ($var > 0) {
            return false;
        } else {
            return true;
        }
        $req->closeCursor();
    }

    protected function verifyNoDouble($email, $login){
        $var = 0;
        $req = $this->getDb()->prepare('SELECT * FROM wp_users WHERE user_email =:mail OR user_login =:login');
        $req->bindValue(':mail', $email);
        $req->bindValue(':login', $login);
        $req->execute();
        while ($data = $req->fetch(PDO::FETCH_ASSOC)) {
            $var = $var + 1;
        }
        if ($var > 0) {
            return false;
        } else {
            return true;
        }
        $req->closeCursor();
    }

    protected function insertUser($login, $pwd, $role, $email, $code = array()){
        if ($this->verifyNoDouble($email, $login)){
            $req = $this->getDb()->prepare('INSERT INTO wp_users (user_login, user_pass, code,
                                      user_nicename, user_email, user_url, user_registered, user_activation_key,
                                      user_status, display_name) 
                                         VALUES (:login, :pwd, :code, :name, :email, :url, NOW(), :key, :status, :displayname)');

            $nul = " ";
            $zero = "0";

            $serCode = serialize($code);
            $req->bindParam(':login', $login);
            $req->bindParam(':pwd', $pwd);
            $req->bindParam(':code', $serCode);
            $req->bindParam(':name', $login);
            $req->bindParam(':email', $email);
            $req->bindParam(':url', $nul);
            $req->bindParam(':key', $nul);
            $req->bindParam(':status', $zero);
            $req->bindParam(':displayname', $login);

            $req->execute();

            $capa = 'wp_capabilities';
            $size = strlen($role);
            $role = 'a:1:{s:'.$size.':"'.$role.'";b:1;}';

            $id = $this->getDb()->lastInsertId();

            $req = $this->getDb()->prepare('INSERT INTO wp_usermeta(user_id, meta_key, meta_value) VALUES (:id, :capabilities, :role)');

            $req->bindParam(':id', $id);
            $req->bindParam(':capabilities', $capa);
            $req->bindParam(':role', $role);

            $req->execute();

            $level = "wp_user_level";

            $req = $this->getDb()->prepare('INSERT INTO wp_usermeta(user_id, meta_key, meta_value) VALUES (:id, :level, :value)');

            $req->bindParam(':id', $id);
            $req->bindParam(':level', $level);
            $req->bindParam(':value', $zero);

            $req->execute();

            return true;
        }
        else{
            return false;
        }
    }

    protected function modifyUser($id, $login, $codes){
        if ($this->verifyTuple($login)) {
            $req = $this->getDb()->prepare('UPDATE wp_users SET code = :codes
                                            WHERE ID=:id');
            $serCode = serialize($codes);
            $req->bindParam(':id', $id);
            $req->bindParam(':codes', $serCode);

            $req->execute();

            return true;
        }
        else {
            return false;
        }
    }

    public function getUsersByRole($role){
        $req = $this->getDb()->prepare('SELECT * FROM wp_users user, wp_usermeta meta WHERE user.ID = meta.user_id AND meta.meta_value =:role 
                                        ORDER BY user.code, user.user_login');
        $size = strlen($role);
        $role = 'a:1:{s:'.$size.':"'.$role.'";b:1;}';
        $req->bindParam(':role', $role);
        $req->execute();
        while ($data = $req->fetch()) {
            $var[] = $data;
        }
        return $var;
        $req->closeCursor();
    }

    public function getUserByLogin($login){
        $req = $this->getDb()->prepare('SELECT * FROM wp_users WHERE user_login = :login');
        $req->bindParam(':login', $login);
        $req->execute();
        while ($data = $req->fetch()) {
            $var[] = $data;
        }
        return $var;
        $req->closeCursor();
    }

}