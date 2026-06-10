<?php

/**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * https://www.d3data.de
 *
 * @copyright (C) D3 Data Development (Inh. Thomas Dartsch)
 * @author    D3 Data Development - Daniel Seifert <info@shopmodule.com>
 * @link      https://www.oxidmodule.com
 */

declare(strict_types=1);

namespace D3\Totp\Tests\Unit\Application\Model;

use OTPHP\OTPInterface;
use OTPHP\TOTP;
use OTPHP\TOTPInterface;

class TOTPMock implements TOTPInterface
{
    public function verify(string $otp, ?int $input = null, ?int $window = null): bool
    {
        return true;
    }

    public static function createFromSecret(string $secret): OTPInterface
    {
        return new TOTP('abc');
    }

    public static function generate(): OTPInterface
    {
        return new TOTP('abc');
    }

    public function setSecret(string $secret): void
    {
    }

    public function withSecret(string $secret): OTPInterface
    {
        return new TOTP('abc');
    }

    public function setDigits(int $digits): void
    {
    }

    public function withDigits(int $digits): OTPInterface
    {
        return new TOTP('abc');
    }

    public function setDigest(string $digest): void
    {
    }

    public function withDigest(string $digest): OTPInterface
    {
        return new TOTP('abc');
    }

    public function at(int $input): string
    {
        return 'abc';
    }

    public function getSecret(): string
    {
        return 'abc';
    }

    public function setLabel(string $label): void
    {
    }

    public function withLabel(string $label): OTPInterface
    {
        return new TOTP('abc');
    }

    public function getLabel(): null|string
    {
        return 'abc';
    }

    public function getIssuer(): ?string
    {
        return "Issuer";
    }

    public function setIssuer(string $issuer): void
    {
    }

    public function withIssuer(string $issuer): OTPInterface
    {
        return new TOTP('abc');
    }

    public function isIssuerIncludedAsParameter(): bool
    {
        return true;
    }

    public function setIssuerIncludedAsParameter(bool $issuer_included_as_parameter): void
    {
    }

    public function withIssuerIncludedAsParameter(bool $issuer_included_as_parameter): OTPInterface
    {
        return new TOTP('abc');
    }

    public function getDigits(): int
    {
        return 1;
    }

    public function getDigest(): string
    {
        return 'abc';
    }

    public function getParameter(string $parameter): mixed
    {
        return 'abc';
    }

    public function hasParameter(string $parameter): bool
    {
        return true;
    }

    public function getParameters(): array
    {
        return [];
    }

    public function setParameter(string $parameter, mixed $value): void
    {
    }

    public function withParameter(string $parameter, mixed $value): OTPInterface
    {
        return new TOTP('abc');
    }

    public function getProvisioningUri(): string
    {
        return "abc";
    }

    public function getQrCodeUri(string $uri, string $placeholder): string
    {
        return "abc";
    }

    public static function create(?string $secret = null, int $period = self::DEFAULT_PERIOD, string $digest = self::DEFAULT_DIGEST, int $digits = self::DEFAULT_DIGITS): TOTPInterface
    {
        return new TOTP('abc');
    }

    public function setPeriod(int $period): void
    {
    }

    public function withPeriod(int $period): TOTPInterface
    {
        return new TOTP('abc');
    }

    public function setEpoch(int $epoch): void
    {
    }

    public function withEpoch(int $epoch): TOTPInterface
    {
        return new TOTP('abc');
    }

    public function now(): string
    {
        return "2026";
    }

    public function getPeriod(): int
    {
        return 1;
    }

    public function expiresIn(): int
    {
        return 1;
    }

    public function getEpoch(): int
    {
        return 1;
    }
}
