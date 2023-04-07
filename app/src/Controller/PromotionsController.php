<?php

namespace App\Controller;

use App\Entity\Partner;
use App\Entity\Promotion;
use App\Entity\User;
use App\Entity\Winning;
use DateTime;
use Doctrine\DBAL\LockMode;
use Exception;
use FOS\RestBundle\Controller\Annotations\Get;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PromotionsController extends ApiController {

  /**
   * Play in a promotion service.
   *
   * Please use this service to play in a promotion.
   *
   * @OA\Parameter(ref="#/components/parameters/Accept-Language")
   * @OA\Parameter(
   *     name="id", in="path", required=true, description="Promotion unique identifier",
   *     @OA\Schema(
   *         type="integer"
   *     )
   * )
   * @OA\Response(
   *     response=200, description="Returns the promotion data",
   *     @OA\JsonContent(
   *         type="object",
   *         @OA\Property(property="id", type="integer", example=1, description="Promotion unique identifier"),
   *         @OA\Property(property="name", type="string", example="Promotion name", description="Promotion name"),
   *         @OA\Property(property="startDate", type="string", example="2023-04-03", description="Promotion start date"),
   *         @OA\Property(property="prize", description="Prize data",
   *             type="object",
   *             @OA\Property(property="name", type="string", example="Prize name", description="Prize name"),
   *             @OA\Property(property="code", type="string", example="pr1", description="Prize code")
   *         ),
   *         @OA\Property(property="partner", description="Partner data",
   *             type="object",
   *             @OA\Property(property="name", type="string", example="Partner name", description="Partner name"),
   *             @OA\Property(property="code", type="string", example="pt1", description="Partner code"),
   *             @OA\Property(property="url", type="string", example="www.partner1.com", description="Partner website")
   *         ),
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
   * @OA\Tag(name="promotions")
   *
   * @Get("/secured/promotions/{id}/play", name="get_promotion_play", options={ "method_prefix" = false })
   */
  public function play(Request $request, ?Promotion $promotion): JsonResponse {
    // Begin the transaction in order the LockMode to work to prevent concurrency issues
    $this->em->beginTransaction();

    try {
      // In case a promotion was not found, just return an error message
      if (!$promotion) {
        return $this->rest->setResponse(
          $this->translator->trans('utils.notFound', locale: $request->getLocale()),
          Response::HTTP_NOT_FOUND
        );
      }

      // Get current authenticated user
      /** @var User $user */
      $user = $this->getUser();

      // Get current date
      $currentDate = new DateTime();

      // Validate the current game
      $errors = $this->promotionService->validate($user, $promotion, $currentDate);

      // In case errors were found, return them as response.
      if (!empty($errors)) {
        return $this->rest->setResponse($errors, Response::HTTP_BAD_REQUEST);
      }

      // Get a random prize based on the promotion
      $prize = $this->promotionService->getRandomPrize($promotion, $currentDate);

      // In case no prize was found, return error message
      if (!$prize) {
        return $this->rest->setResponse(
          $this->translator->trans('promotions.noPrizesLeft', locale: $request->getLocale()),
          Response::HTTP_NOT_FOUND
        );
      }

      // Lock this prize, to deal with the concurrency issues
      $this->em->lock($prize, LockMode::PESSIMISTIC_WRITE);

      // Create new winning entry
      $winning = new Winning();
      $winning->setUser($user);
      $winning->setPromotion($promotion);
      $winning->setPrize($prize);
      $winning->setDate($currentDate);

      // Save winning
      $this->em->persist($winning);

      // Set that this prize was won already
      $prize->setIsWon(true);

      // Save prize
      $this->em->persist($prize);

      // Commit & flush the transaction
      $this->em->commit();
      $this->em->flush();

      // Find the prize's partner
      $partner = $this->em->getRepository(Partner::class)->findOneBy(['code' => $prize->getPartnerCode()]);

      // Return promotion data response
      return $this->rest->setResponse(
        $this->promotionService->getPromotionResponse($prize, $partner, $request->getLocale())
      );
    } catch (Exception $e) {
      // Rollback the transaction if an error has happened
      $this->em->rollback();

      // Log the message
      $this->logAPIError($e->getMessage());

      return $this->rest->setResponse(
        $this->translator->trans('utils.internalServer', locale: $request->getLocale()),
        Response::HTTP_INTERNAL_SERVER_ERROR
      );
    }
  }

  /**
   * Check a promotion service.
   *
   * Please use this service to check a promotion.
   *
   * @OA\Parameter(ref="#/components/parameters/Accept-Language")
   * @OA\Parameter(
   *     name="id", in="query", description="Promotion unique identifier",
   *     @OA\Schema(
   *         type="integer"
   *     )
   * )
   * @OA\Response(
   *     response=200, description="Returns the promotion data",
   *     @OA\JsonContent(
   *         type="array",
   *         @OA\Items(
   *             @OA\Property(property="id", type="integer", example=1, description="Promotion unique identifier"),
   *             @OA\Property(property="name", type="string", example="Promotion name", description="Promotion name"),
   *             @OA\Property(property="startDate", type="string", example="2023-04-03", description="Promotion start date"),
   *             @OA\Property(property="prize", description="Prize data",
   *                 type="object",
   *                 @OA\Property(property="name", type="string", example="Prize name", description="Prize name"),
   *                 @OA\Property(property="code", type="string", example="pr1", description="Prize code")
   *             ),
   *             @OA\Property(property="partner", description="Partner data",
   *                 type="object",
   *                 @OA\Property(property="name", type="string", example="Partner name", description="Partner name"),
   *                 @OA\Property(property="code", type="string", example="pt1", description="Partner code"),
   *                 @OA\Property(property="url", type="string", example="www.partner1.com", description="Partner website")
   *             ),
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
   * @OA\Tag(name="promotions")
   *
   * @Get("/secured/promotions/check", name="get_promotion_check", options={ "method_prefix" = false })
   */
  public function check(Request $request): JsonResponse {
    try {
      // Initialize a default search criteria
      $criteria = [
        'user' => $this->getUser()
      ];

      // Check if was requested to look for wins in a promotion
      $id = $request->get('id');

      if ($id) {
        $promotion = $this->em->getRepository(Promotion::class)->find($id);

        // In case a promotion was not found, just return an error message
        if (!$promotion) {
          return $this->rest->setResponse(
            $this->translator->trans('utils.notFound', locale: $request->getLocale()),
            Response::HTTP_NOT_FOUND
          );
        }

        // Add promotion to search criteria
        $criteria['promotion'] = $promotion;
      }

      // Check if user had any wins
      $wins = $this->em->getRepository(Winning::class)->findBy($criteria);

      // Loop tru all wins and build up the response
      $response = [];

      foreach ($wins as $win) {
        $prize = $win->getPrize();
        $partner = $this->em->getRepository(Partner::class)->findOneBy(['code' => $prize->getPartnerCode()]);
        $response[] = $this->promotionService->getPromotionResponse($prize, $partner, $request->getLocale());
      }

      return $this->rest->setResponse($response);
    } catch (Exception $e) {
      // Log the message
      $this->logAPIError($e->getMessage());

      return $this->rest->setResponse(
        $this->translator->trans('utils.internalServer', locale: $request->getLocale()),
        Response::HTTP_INTERNAL_SERVER_ERROR
      );
    }
  }
}
