<?php
declare(strict_types=1);

namespace Modera\SalesfrontConnector\Model;

use InvalidArgumentException;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\DriverInterface;

class JwtManagement
{
    /**
     * @var Json
     */
    private $json;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * @param Json $json
     * @param DirectoryList $directoryList
     * @param DriverInterface $driver
     */
    public function __construct(
        Json $json,
        DirectoryList $directoryList,
        DriverInterface $driver
    ) {
        $this->json = $json;
        $this->directoryList = $directoryList;
        $this->driver = $driver;
    }

    /**
     * Converts JWT string into array.
     *
     * @param string $jwt The JWT
     *
     * @return bool
     * @throws FileSystemException
     */
    public function validate(string $jwt): bool
    {
        if (empty($jwt)) {
            throw new InvalidArgumentException('JWT is empty');
        }

        $parts = explode('.', $jwt);
        if (count($parts) != 3) {
            throw new InvalidArgumentException('Wrong number of segments in JWT');
        }

        [$headB64, $payloadB64, $signatureB64] = $parts;
        $payloadJson = $this->urlSafeB64Decode($payloadB64);
        $payload = $this->json->unserialize($payloadJson);

        if (!$this->validateByKey($headB64, $payloadB64, $signatureB64)
//            || !$this->validateByTime($payload['exp'])
        ) {
            return false;
        }

        return true;
    }

    /**
     * Validate by key
     *
     * @param string $head
     * @param string $payload
     * @param string $signature
     *
     * @return bool
     * @throws FileSystemException
     */
    private function validateByKey(string $head, string $payload, string $signature): bool
    {
        $publicKey = $this->getPublicKeyContent();
        $signature = $this->urlSafeB64Decode($signature);
        $data = utf8_decode($head . '.' . $payload);

        return openssl_verify($data, $signature, $publicKey, OPENSSL_ALGO_SHA256) === 1;
    }

    /**
     * Validate by time
     *
     * @param int $expireTimestamp
     *
     * @return bool
     */
    private function validateByTime(int $expireTimestamp): bool
    {
        return $expireTimestamp > time();
    }

    /**
     * Retrieve public file content
     *
     * @return false|string
     * @throws FileSystemException
     */
    private function getPublicKeyContent()
    {
        $publicKeyPath = $this->directoryList->getPath('app') . DS . 'code' . DS . 'Modera'
            . DS . 'SalesfrontConnector' . DS . 'etc' . DS . 'public.pem';

        return $this->driver->fileGetContents($publicKeyPath);
    }

    /**
     * Decode a string with URL-safe Base64.
     *
     * @param string $input A Base64 encoded string
     *
     * @return string
     */
    private function urlSafeB64Decode(string $input): string
    {
        return base64_decode(
            str_pad(strtr($input, '-_', '+/'), strlen($input) % 4, '=', STR_PAD_RIGHT)
        );
    }
}
