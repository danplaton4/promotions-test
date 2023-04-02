<?php

namespace App\Controller;

use App\Entity\Media;
use App\Entity\User;
use Exception;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MediaController extends ApiController {

  /**
   * Upload media.
   *
   * Please use this service to upload files.
   *
   * @OA\RequestBody(
   *     @OA\MediaType(
   *         mediaType="multipart/form-data",
   *         @OA\Schema(
   *             type="object", required={"files[]", "type"},
   *             @OA\Property(
   *                 property="files[]",
   *                 type="array",
   *                 description="Files to be uploaded",
   *                 @OA\Items(format="binary")
   *             ),
   *             @OA\Property(property="type", type="string", enum={"avatar"}, description="Upload type")
   *         )
   *     )
   * )
   * @OA\Response(
   *     response=200, description="Returns a list of files",
   *     @OA\JsonContent(
   *         type="array",
   *         @OA\Items(
   *             type="object",
   *             @OA\Property(property="id", type="integer", example=1, description="Media unique identifier"),
   *             @OA\Property(property="fileName", type="string", example="filename.extension", description="File name"),
   *             @OA\Property(property="fileUrl", type="string", example="https://urlforavatar.com/get.php", description="File URL"),
   *         )
   *     )
   * )
   * @OA\Response(
   *     response=400, description="Invalid input"
   * )
   * @OA\Response(
   *     response=401, description="Unauthorized access"
   * )
   * @OA\Response(
   *     response=404, description="Resource not found"
   * )
   * @OA\Response(
   *     response=500, description="Internal server error"
   * )
   * @OA\Tag(name="media")
   *
   * @Post("/secured/media/upload", name="media_upload", options={ "method_prefix" = false })
   */
  public function upload(Request $request): JsonResponse {
    try {
      // Validate request parameters
      $errors = $this->validationManager->validate('mediaUpload');

      if (!empty($errors)) {
        return $this->rest->setResponse($errors, Response::HTTP_BAD_REQUEST);
      }

      $files = $request->files->get('files');

      // In no files were sent
      if (empty($files)) {
        return $this->rest->setResponse(
          $this->translator->trans('utils.badRequest'),
          Response::HTTP_BAD_REQUEST
        );
      }

      $response = [];
      foreach ($files as $file) {
        $response[] = $this->mediaManager->save($file, $request->get('type'), $request);
      }

      return $this->rest->setResponse($response);
    } catch (Exception $e) {
      $this->logAPIError($e->getMessage());

      return $this->rest->setResponse(
        $this->translator->trans('media.error', locale: $request->getLocale()) . '|' . $e->getMessage(),
        Response::HTTP_INTERNAL_SERVER_ERROR
      );
    }
  }
}
