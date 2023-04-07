<?php

namespace App\Controller;

use App\Model\Service\PromotionService;
use App\Model\Storage\MediaManager;
use App\Model\Utility\ArrayUtility;
use App\Model\Utility\RESTUtility;
use App\Model\Utility\StringUtility;
use App\Model\Validation\ValidationManager;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ApiController extends AbstractFOSRestController {
  public function __construct(
    protected JWTTokenManagerInterface     $jwtManager,
    protected RefreshTokenManagerInterface $refreshTokenManager,
    protected ValidationManager            $validationManager,
    protected UserPasswordHasherInterface  $encoder,
    public RESTUtility                     $rest,
    protected StringUtility                $string,
    protected ArrayUtility                 $array,
    protected LoggerInterface              $logger,
    protected EntityManagerInterface       $em,
    protected TranslatorInterface          $translator,
    protected MediaManager                 $mediaManager,
    protected TokenStorageInterface        $tokenStorage,
    protected PromotionService             $promotionService
  ) {
  }

  /**
   *  Set listing configuration for pagination and sorting
   *
   * @param Request $request
   * @param int|null $page
   * @param int|null $noRecords
   * @param string|null $sortField
   * @param string|null $sortType
   * @return void
   */
  protected function setListingConfigurations(
    Request $request,
    ?int    &$page,
    ?int    &$noRecords,
    ?string &$sortField,
    ?string &$sortType
  ): void {
    $limit = (int)$request->get('limit') !== 0 ?
      $request->get('limit') :
      $this->getParameter('project')['pagination']['perPage'];

    $page = (int)$request->get('offset') ? (int)$request->get('offset') - 1 : 0;
    $noRecords = $limit === -1 ? PHP_INT_MAX : $limit;

    $sort = $request->get('sort');
    $sortFields = explode('-', $sort);

    $sortField = $sortFields[1] ?? ($sortFields[0] ?: 'id');
    $defaultSortOrder = $sortFields[0] ? 'ASC' : 'DESC';
    $sortType = isset($sortFields[1]) ? 'DESC' : $defaultSortOrder;
  }

  /**
   * Set header link for pagination
   *
   * @param Request $request
   * @param int $page
   * @param int $noRecords
   * @param int $noTotal
   * @param array $params
   * @return string
   */
  protected function setHeaderLink(
    Request $request,
    int     $page,
    int     $noRecords,
    int     $noTotal,
    array   $params = []
  ): string {
    // Get current url
    $url = $this->generateUrl($request->get('_route'), $params, UrlGeneratorInterface::ABSOLUTE_URL);

    // Get last offset
    $lastOffset = ceil($noTotal / $noRecords);

    // First, last, prev and next link
    $firstLink = $url . '?offset=1&limit=' . $noRecords;
    $lastLink = $url . '?offset=' . $lastOffset . '&limit=' . $noRecords;
    $prevLink = $nextLink = null;

    if ($page + 2 <= $lastOffset) {
      $nextLink = $url . '?offset=' . ($page + 2) . '&limit=' . $noRecords;
    }

    if ($page >= 1) {
      $prevLink = $url . '?offset=' . $page . '&limit=' . $noRecords;
    }

    // Header link
    $headerLink = '<' . $firstLink . '>; rel="first", <' . $lastLink . '>; rel="last"';

    if ($prevLink) {
      $headerLink .= ', <' . $prevLink . '>; rel="prev"';
    }

    if ($nextLink) {
      $headerLink .= ', <' . $nextLink . '>; rel="next"';
    }

    return $headerLink;
  }

  /**
   * Log API errors
   *
   * @param $message
   * @return void
   */
  protected function logAPIError($message): void {
    $this->logger->error($message);
  }

  protected function checkDuplicates(
    $entityRepository,
    $request,
    $excludeIds = [],
    $attribute = 'name',
    $addConditions = [],
  ): array {
    $value = $request->get($attribute);

    $conditions = [$attribute => $value];

    foreach ($addConditions as $addCondition) {
      $conditions[$addCondition] = $request->get($addCondition);
    }

    // Get entities
    $entities = $this->em->getRepository($entityRepository)->findBy($conditions);

    $hasDuplicate = false;

    foreach ($entities as $entity) {
      if (!in_array($entity->getId(), $excludeIds)) {
        $hasDuplicate = true;

        break;
      }
    }

    if ($hasDuplicate) {
      return [
        $attribute => $this->translator->trans('duplicated', ['attribute' => $attribute], locale: $request->getLocale())
      ];
    }

    return [];
  }

  /**
   * Decode the user's current token data
   *
   * @return array|false
   * @throws JWTDecodeFailureException
   */
  public function getTokenData(): bool|array {
    return $this->jwtManager->decode($this->tokenStorage->getToken());
  }
}
