<?php

class AuditLog
{
    public static function record(PDO $pdo, ?int $userId, string $action, string $description, ?string $referenceType = null, ?int $referenceId = null): void
    {
        $stmt = $pdo->prepare('INSERT INTO audit_logs (user_id, action, reference_type, reference_id, description, ip_address, user_agent) VALUES (:user_id, :action, :reference_type, :reference_id, :description, :ip_address, :user_agent)');
        $stmt->execute([
            ':user_id' => $userId,
            ':action' => $action,
            ':reference_type' => $referenceType,
            ':reference_id' => $referenceId,
            ':description' => $description,
            ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);
    }
}
