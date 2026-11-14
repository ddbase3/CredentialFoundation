<?php declare(strict_types=1);

/***********************************************************************
 * This file is part of CredentialFoundation for BASE3 Framework.
 *
 * CredentialFoundation defines reusable credential and encryption contracts.
 * It supplies DTOs and stateless signing support for BASE3 integrations.
 * Credential storage and management belong to implementation plugins.
 *
 * Developed by Daniel Dahme
 * Licensed under GPL-3.0
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 *
 * https://base3.de
 * https://github.com/ddbase3/CredentialFoundation
 **********************************************************************/

namespace Base3\CredentialFoundation\Test\Dto;

use Base3\CredentialFoundation\Dto\CredentialSubject;
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
