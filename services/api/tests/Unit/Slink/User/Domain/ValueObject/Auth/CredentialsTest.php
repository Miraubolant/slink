<?php

declare(strict_types=1);

namespace Unit\Slink\User\Domain\ValueObject\Auth;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\User\Domain\ValueObject\Auth\Credentials;
use Slink\User\Domain\ValueObject\Auth\HashedPassword;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\Username;
use function password_hash;

final class CredentialsTest extends TestCase {

  #[Test]
  public function itCreatesFromCredentialsObjects(): void {
    $email = Email::fromString('test@example.com');
    $username = Username::fromString('testuser');
    $password = HashedPassword::encode('password123');

    $credentials = Credentials::fromCredentials($email, $username, $password);

    $this->assertInstanceOf(Credentials::class, $credentials);
    $this->assertSame($email, $credentials->email);
    $this->assertSame($username, $credentials->username);
    $this->assertSame($password, $credentials->password);
  }

  #[Test]
  public function itCreatesFromPayload(): void {
    $hashedPassword = password_hash('password123', PASSWORD_BCRYPT, ['cost' => 12]);
    $payload = [
      'email' => 'test@example.com',
      'username' => 'testuser',
      'password' => $hashedPassword
    ];

    $credentials = Credentials::fromPayload($payload);

    $this->assertInstanceOf(Credentials::class, $credentials);
    $this->assertEquals('test@example.com', $credentials->email->toString());
    $this->assertEquals('testuser', $credentials->username->toString());
    $this->assertEquals($hashedPassword, $credentials->password->toString());
  }

  #[Test]
  public function itCreatesFromPlainCredentials(): void {
    $credentials = Credentials::fromPlainCredentials(
      'test@example.com',
      'testuser',
      'password123'
    );

    $this->assertInstanceOf(Credentials::class, $credentials);
    $this->assertEquals('test@example.com', $credentials->email->toString());
    $this->assertEquals('testuser', $credentials->username->toString());
    $this->assertTrue($credentials->password->match('password123'));
  }

  #[Test]
  public function itCreatesWithDifferentCredentials(): void {
    $credentials1 = Credentials::fromPlainCredentials(
      'user1@example.com',
      'user1',
      'password1'
    );

    $credentials2 = Credentials::fromPlainCredentials(
      'user2@example.com',
      'user2',
      'password2'
    );

    $this->assertNotEquals($credentials1->email->toString(), $credentials2->email->toString());
    $this->assertNotEquals($credentials1->username->toString(), $credentials2->username->toString());
    $this->assertFalse($credentials1->password->match('password2'));
    $this->assertFalse($credentials2->password->match('password1'));
  }

  #[Test]
  public function itHandlesComplexCredentials(): void {
    $credentials = Credentials::fromPlainCredentials(
      'complex.email+tag@sub.domain.com',
      'complex_user-name.123',
      'C0mpl3x.P@ssw0rd!'
    );

    $this->assertInstanceOf(Credentials::class, $credentials);
    $this->assertEquals('complex.email+tag@sub.domain.com', $credentials->email->toString());
    $this->assertEquals('complex_user-name.123', $credentials->username->toString());
    $this->assertTrue($credentials->password->match('C0mpl3x.P@ssw0rd!'));
  }
}
