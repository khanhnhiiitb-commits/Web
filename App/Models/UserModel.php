<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class UserModel extends Model
{
    protected string $table = 'users';
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

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("CALL nttd_sp_register_user(?, ?, ?, ?, ?)");
            try {
                $created = $stmt->execute([
                    $data['full_name'], strtolower(trim($data['email'])), $hash,
                    $data['phone'] ?? null, $data['address'] ?? null
                ]);
            } finally {
                $stmt->closeCursor();
            }
            if (!$created) {
                $this->db->rollBack();
                return false;
            }
            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }
    // Tìm tài khoản theo ID
    public function findById($id)
    {
        $sql = "SELECT id, full_name, email, phone, address, role, status
                FROM users WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Cập nhật thông tin cá nhân
    public function updateProfile($id, $data)
    {
        $sql = "UPDATE users
                SET full_name = :full_name,
                    phone = :phone,
                    address = :address
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':full_name' => $data['full_name'],
            ':phone' => $data['phone'] ?? null,
            ':address' => $data['address'] ?? null,
            ':id' => $id
        ]);
    }

    // Đổi mật khẩu
    public function updatePassword($id, $newPassword)
    {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);

        $sql = "UPDATE users
                SET password_hash = :password_hash
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':password_hash' => $hash,
            ':id' => $id
        ]);
    }

    // Lấy danh sách người dùng cho Admin
    public function getAllUsers()
    {
        $sql = "SELECT id, full_name, email, phone, role, status, created_at
                FROM users ORDER BY id DESC";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Khóa hoặc mở khóa tài khoản
    public function updateStatus($id, $status): bool
    {
        if ((int)$id <= 0 || !in_array((int)$status, [0, 1], true)) {
            return false;
        }
        // Chuyển việc cập nhật trạng thái + thông báo vào 1 transaction.
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('SELECT id, status FROM users WHERE id = ? FOR UPDATE');
            $stmt->execute([(int)$id]);
            $old = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            if (!$old || (int)$old['status'] === (int)$status) {
                $this->db->rollBack();
                return false;
            }
            $stmt = $this->db->prepare('CALL nttd_sp_toggle_user_status(?, ?)');
            try {
                $stmt->execute([(int)$id, (int)$status]);
            } finally {
                $stmt->closeCursor();
            }
            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }
    
}
