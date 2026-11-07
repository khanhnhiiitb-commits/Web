<?php
namespace App\Models;

use App\Core\Model;

class UserModel extends Model
{
    // Tìm tài khoản theo email
    public function findByEmail($email)
    {
        $sql = "SELECT * FROM users WHERE email = :email";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':email' => strtolower(trim($email))
        ]);

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    // Tạo tài khoản mới
    public function createUser($data)
    {
        $hash = password_hash(
            $data['password'],
            PASSWORD_DEFAULT
        );

        $sql = "INSERT INTO users
                (full_name, email, password_hash, phone, address, role, status)
                VALUES
                (:full_name, :email, :password_hash, :phone, :address, 'member', 1)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':full_name' => $data['full_name'],
            ':email' => strtolower(trim($data['email'])),
            ':password_hash' => $hash,
            ':phone' => $data['phone'] ?? null,
            ':address' => $data['address'] ?? null
        ]);
    }
}
