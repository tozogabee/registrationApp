<?php
class UserDto {
    public $id;
    public $email;
    public $nickname;
    public $birthDate;
    public $passwordHash;


    public function __construct($id, $email, $nickname, $birthDate, $passwordHash) {
        $this->id = $id;
        $this->email = $email;
        $this->nickname = $nickname;
        $this->birthDate = $birthDate;
        $this->passwordHash = $passwordHash;
    }

    public function toArray() {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'nickname' => $this->nickname,
            'birth_date' => $this->birthDate,
            'passwordHash' => $this->passwordHash
        ];
    }
}
?>
