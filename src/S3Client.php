<?php

namespace grptx;

use Aws\Exception\AwsException;
use Aws\Result;
use yii\base\Component;

class S3Client extends Component
{
    /** @var \Aws\S3\S3Client */
    private $S3Client;

    public $key;

    public $secret;

    public $region;

    public $version = 'latest';

    public $endpoint;

    public $profile = 'default';

    public $use_path_style_endpoint = true;

    public $defaultBucket;

    public function init()
    {
        parent::init();
        if (!$this->S3Client) {
            $this->getS3Client();
        }
    }


    /**
     * @return \Aws\S3\S3Client
     */
    public function getS3Client(): \Aws\S3\S3Client
    {
        if (!$this->S3Client) {
            $this->S3Client = new \Aws\S3\S3Client([
                'credentials' => [
                    'key' => $this->key ?? '',
                    'secret' => $this->secret ?? '',
                ],
                'region' => $this->region ?? '',
                'version' => $this->version ?? 'latest',
                'endpoint' => $this->endpoint ?? '',
                'use_path_style_endpoint' => true,
            ]);
        }

        return $this->S3Client;
    }

    /**
     * @param string $localObjectPath
     * @param string|null $storageSavePath
     * @param string|null $bucket
     * @return Result|bool
     */
    public function putObjectByPath(string $localObjectPath, string $storageSavePath = null, string $bucket = null)
    {
        if (is_null($bucket)) {
            $bucket = $this->defaultBucket;
        }

        if (empty($bucket)) {
            return false;
        }

        if ($storageSavePath === null) {
            $storageSavePath = $localObjectPath;
        }

        try {
            $storageSavePath = $this->formatStorageSavePath($storageSavePath);

            $result = $this->S3Client->putObject([
                'Bucket' => $bucket,
                'Key' => $storageSavePath,
                'SourceFile' => $localObjectPath
            ]);

            return $result;
        } catch (AwsException $awsException) {
            return false;
        }
    }

    /**
     * @param string $content
     * @param string $storageSavePath
     * @param string $bucket
     * @return Result|bool
     */
    public function putObjectByContent(string $content, string $storageSavePath, string $bucket = null)
    {
        if (is_null($bucket)) {
            $bucket = $this->defaultBucket;
        }

        if (empty($bucket)) {
            return false;
        }

        try {
            $storageSavePath = $this->formatStorageSavePath($storageSavePath);

            $result = $this->S3Client->putObject([
                'Bucket' => $bucket,
                'Key' => $storageSavePath,
                'Body' => $content
            ]);

            return $result;
        } catch (AwsException $awsException) {
            return false;
        }
    }

    /**
     * @param string $storageSavePath
     * @param string|null $localSaveAsPath
     * @param string|null $bucket
     * @return bool|mixed
     */
    public function getObject(string $storageSavePath, string $localSaveAsPath = null, string $bucket = null)
    {
        if (is_null($bucket)) {
            $bucket = $this->defaultBucket;
        }

        if (empty($bucket)) {
            return false;
        }

        try {
            $param = [
                'Bucket' => $bucket,
                'Key' => $storageSavePath,
            ];
            if (!is_null($localSaveAsPath)) {
                $param = [
                    'SaveAs' => $localSaveAsPath
                ];
            }

            $result = $this->S3Client->getObject($param);
            return $result['Body'];
        } catch (AwsException $awsException) {
            return false;
        }

    }

    /**
     * @param string $storageSavePath
     * @return string
     * @author klinson <klinson@163.com>
     */
    private function formatStorageSavePath(string $storageSavePath)
    {
        return trim($storageSavePath, '/');
    }
}