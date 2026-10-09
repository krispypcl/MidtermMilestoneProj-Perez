<?php
declare(strict_types=1);

/*
 * User.php
 * A class that groups the database work for member accounts:
 * checking usernames, creating accounts, and checking logins.
 */

class User
{
    // [STORED CONNECTION]
    // The object keeps the PDO connection so every method can use it.
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // [CHECK IF USERNAME IS TAKEN]
    public function exists(string $username): bool
    {
        $stmt = $this->pdo->prepare('SELECT id FROM users WHERE username = :username');
        $stmt->execute([':username' => $username]);

        return $stmt->fetch() !== false;
    }

    // [CREATE ACCOUNT]
    // password_hash() turns the password into a one-way scrambled string.
    // Even we cannot read the real password from the database.
    public function register(string $username, string $password): void
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->pdo->prepare(
            'INSERT INTO users (username, password_hash) VALUES (:username, :hash)'
        );
        $stmt->execute([':username' => $username, ':hash' => $hash]);
    }

    // [CHECK LOGIN]
    // password_verify() compares the typed password with the saved hash.
    // Returns the user row if correct, or null if the username/password is wrong.
    public function login(string $username, string $password): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, username, password_hash FROM users WHERE username = :username'
        );
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        if ($user === false || !password_verify($password, $user['password_hash'])) {
            return null;
        }

        return $user;
    }
}
