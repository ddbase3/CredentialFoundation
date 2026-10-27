<?php declare(strict_types=1);

namespace CredentialFoundation\Test\Dto;

use CredentialFoundation\Dto\CredentialSubject;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class CredentialSubjectTest extends TestCase {

	public function testSeparatesServiceIdentityFromUser(): void {
		$service = new CredentialSubject(CredentialSubject::TYPE_SERVICE, 'contourz');
		self::assertSame('service', $service->getType());
		self::assertSame('contourz', $service->getId());
		self::assertTrue($service->isService());
		self::assertFalse($service->isUser());
		self::assertSame(['type' => 'service', 'id' => 'contourz'], $service->toArray());
	}

	public function testRejectsInvalidServiceSubject(): void {
		$this->expectException(InvalidArgumentException::class);
		new CredentialSubject(CredentialSubject::TYPE_SERVICE, 'Admin');
	}
}
