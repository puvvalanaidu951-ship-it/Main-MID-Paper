<?php

class Notification
{
    public static function send(PDO $pdo, ?int $userId, string $message, string $category = 'system'): void
    {
        $stmt = $pdo->prepare('INSERT INTO notifications (user_id, message, category) VALUES (:user_id, :message, :category)');
        $stmt->execute([
            ':user_id' => $userId,
            ':message' => $message,
            ':category' => $category,
        ]);
    }

    public static function unreadCount(PDO $pdo, int $userId): int
    {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0');
        $stmt->execute([':user_id' => $userId]);
        return (int)$stmt->fetchColumn();
    }

    public static function fetchAll(PDO $pdo, int $userId): array
    {
        $stmt = $pdo->prepare('SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC');
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public static function markAsRead(PDO $pdo, int $notificationId, int $userId): void
    {
        $stmt = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :user_id');
        $stmt->execute([':id' => $notificationId, ':user_id' => $userId]);
    }
}
