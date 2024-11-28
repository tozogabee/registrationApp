<?php
class UserDto {
    public $id;
    public $email;
    public $nickname;
    public $birthDate;
    public $passwordHash;
    public $isLogged;


    public function __construct($id, $email, $nickname, $birthDate, $passwordHash, $isLogged = 0) {
        $this->id = $id;
        $this->email = $email;
        $this->nickname = $nickname;
        $this->birthDate = $birthDate;
        $this->passwordHash = $passwordHash;
        $this->isLogged = $isLogged;
    }

    public function toArray() {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'nickname' => $this->nickname,
            'birth_date' => $this->birthDate,
            'passwordHash' => $this->passwordHash,
            'is_logged' => $this->isLogged
        ];
    }
}
?>
