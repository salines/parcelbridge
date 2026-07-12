<?php
declare(strict_types=1);

namespace App\Service;

use finfo;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Validate invoice uploads using server-detected file content.
 */
class InvoiceUploadValidator
{
    /**
     * @var array<string, string>
     */
    private const MIME_EXTENSIONS = [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    ];

    /**
     * Return trusted MIME type and extension, or null for an unsupported file.
     *
     * @param \Psr\Http\Message\UploadedFileInterface $file Uploaded file
     * @return array{mime_type: string, extension: string}|null
     */
    public function inspect(UploadedFileInterface $file): ?array
    {
        $source = $file->getStream()->getMetadata('uri');
        if (!is_string($source) || $source === '' || !is_file($source)) {
            return null;
        }

        $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($source);
        if (!is_string($mimeType) || !isset(self::MIME_EXTENSIONS[$mimeType])) {
            return null;
        }

        return [
            'mime_type' => $mimeType,
            'extension' => self::MIME_EXTENSIONS[$mimeType],
        ];
    }
}
