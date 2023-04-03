<?php

namespace App\Model\Utility;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;


class RESTUtility {

  /**
   * Get params from raw json body
   *
   * @param Request $request
   * @return mixed
   */
  public function getBodyParams(Request $request): mixed {
    $content = $request->getContent();

    return json_decode($content);
  }

  /**
   * Get all params from form type
   *
   * @param Request $request
   * @return object
   */
  public function getFormParams(Request $request): object {
    return (object)$request->request->all();
  }

  /**
   * Get all query params
   *
   * @param Request $request
   * @return object
   */
  public function getQueryParams(Request $request): object {
    return (object)$request->query->all();
  }

  /**
   * Set json response
   *
   * @param $msg
   * @param int $code
   * @param array $headers
   * @return JsonResponse
   */
  public function setResponse($msg, int $code = Response::HTTP_OK, array $headers = array()): JsonResponse {
    // Get all header params
    $headerParams = getallheaders();

    if (isset($headerParams['X-Bounce-Params'])) {
      $headers['X-Bounce-Params'] = $headerParams['X-Bounce-Params'];
    }

    $successCodes = array(
      Response::HTTP_OK,
      Response::HTTP_CREATED,
      Response::HTTP_NO_CONTENT
    );

    if (!in_array($code, $successCodes)) {
      if (is_array($msg)) {
        foreach ($msg as $key => $value) {
          $messages = explode('|', $value);
          $errorMessages[$key] = $messages[0];
          $internalMessages[$key] = $messages[1];
        }

        $response = array(
          'errors' => array(
            'userMessage' => $errorMessages,
            'internalMessage' => $internalMessages
          )
        );
      } else {
        $errorMessages = explode('|', $msg);
        $response = array(
          'errors' => array(
            'userMessage' => $errorMessages[0],
            'internalMessage' => $errorMessages[1]
          )
        );
      }
    } else {
      $response = $msg;
    }

    return new JsonResponse($response, $code, $headers);
  }

  /**
   * Returns a bool value based on a GET parameter.
   *
   * @param mixed $parameter
   *
   * @return bool
   */
  public function getBool(mixed $parameter): bool {
    return in_array($parameter, ['1', 1, 'true', true], true);
  }
}
