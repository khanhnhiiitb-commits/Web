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
            'email' => $email
        ]);

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    // Tạo tài khoản mới
    
public function createUser($data)
{
    $password = $data['password'];

    // Mã hóa mật khẩu
    $hash = password_hash($password, PASSWORD_BCRYPT);

    $sql = "INSERT INTO users
            (full_name, email, password_hash, phone, address, role, status)
            VALUES
            (:full_name, :email, :password_hash, :phone, :address, 'member', 1)";

    $stmt = $this->db->prepare($sql);

    return $stmt->execute([
        'full_name' => $data['full_name'],
        'email' => $data['email'],
        'password_hash' => $hash,
        'phone' => $data['phone'] ?? null,
        'address' => $data['address'] ?? null
    ]);
}


    // Tìm tài khoản theo ID
    public function findById($id)
    {
        $sql = "SELECT id, full_name, email, phone, address, role
                FROM users
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'id' => $id
        ]);

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    // Cập nhật hồ sơ cá nhân
    public function updateProfile($id, $data)
    {
        $sql = "UPDATE users
                SET full_name = :full_name,
                    phone = :phone,
                    address = :address
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'full_name' => $data['full_name'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'id' => $id
        ]);
    }

    // Đổi mật khẩu
    public function updatePassword($id, $newPassword)
    {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);

        $sql = "UPDATE users
                SET password_hash = :password_hash
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'password_hash' => $hash,
            'id' => $id
        ]);
    }
    
    // Lấy danh sách tất cả người dùng
    public function getAllUsers()
    {
        $sql = "SELECT id, full_name, email, phone, role, status, created_at
                FROM users
                ORDER BY id DESC";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Cập nhật trạng thái tài khoản
    public function updateStatus($id, $status)
    {
        $sql = "UPDATE users
                SET status = :status
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'status' => $status,
            'id' => $id
        ]);
    }

}
