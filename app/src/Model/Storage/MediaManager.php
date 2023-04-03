<?php

namespace App\Model\Storage;

use App\Entity\Media;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Model\Utility\StringUtility;
use Symfony\Contracts\Translation\TranslatorInterface;

class MediaManager {

  private array $allowedExtensions = [
    'jpg', 'png', 'gif', 'jpeg', 'tiff', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'jpg', 'png', 'tiff', 'jpeg'
  ];

  public function __construct(
    private                                 $project,
    private readonly TranslatorInterface    $translator,
    private readonly EntityManagerInterface $em
  ) {

  }

  /**
   * Check file extension
   *
   * @param string $extension
   * @return bool
   */
  private function isFileValid(string $extension): bool {
    return in_array(strtolower($extension), $this->allowedExtensions, true);
  }

  /**
   * Save file to server
   *
   * @param UploadedFile $file
   * @param string $type
   * @param Request $request
   * @return array
   */
  public function save(UploadedFile $file, string $type, Request $request): array {
    if ($this->isFileValid($file->getClientOriginalExtension())) {
      // Generate a random name for file
      $fileName = (new StringUtility())->generateUuid(true) . '.' . $file->getClientOriginalExtension();

      // Move that file
      $file->move($this->project['full_upload_dir'] . $type . '/', $fileName);

      $host = $request->getSchemeAndHttpHost();
      $fileUrl = $host . $this->project['public_upload_dir'] . $type . '/' . $fileName;

      // Save it do db
      $media = new Media();
      $media->setName($fileName);
      $media->setUrl($fileUrl);
      $media->setType($type);

      $this->em->persist($media);
      $this->em->flush();

      // Return name
      return [
        'id' => $media->getId(),
        'fileName' => $fileName,
        'fileUrl' => $fileUrl
      ];
    }

    return [];
  }
}
