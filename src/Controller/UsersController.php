<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserProfile;
use App\Model\Constants\UserRole;
use Exception;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Patch;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UsersController extends ApiController {

  /**
   * Create user service.
   *
   * Please use this service to create a user.
   *
   * @OA\RequestBody(
   *   description="User data",
   *   @OA\JsonContent(
   *     type="object",
   *     required={"email", "firstName", "lastName", "password", "role"},
   *     @OA\Property(property="email", type="string", example="john@doe.com", description="User email"),
   *     @OA\Property(property="firstName", type="string", example="John", description="User first name"),
   *     @OA\Property(property="lastName", type="string", example="Doe", description="User last name"),
   *     @OA\Property(property="avatar", type="string", example="https://imgur.com/blablabla", description="User avatar url"),
   *     @OA\Property(property="password", type="string", example="*********", description="User plain password")
   *   )
   * )
   * @OA\Response(
   *     response=201, description="Returns user data",
   *     @OA\JsonContent(
   *         type="object",
   *         @OA\Property(property="id", type="integer", example=1, description="User unique identifier"),
   *         @OA\Property(property="email", type="string", example="test@test.com", description="User email")
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
   * @OA\Tag(name="users")
   * @Security()
   *
   * @Post("/users", name="store_user", options={ "method_prefix" = false })
   */
  public function store(Request $request): JsonResponse {
    try {
      $errors = array_merge(
        $this->validationManager->validate('storeUser'),
        $this->checkDuplicates(User::class, $request, [], 'email')
      );

      if (!empty($errors)) {
        return $this->rest->setResponse($errors, Response::HTTP_BAD_REQUEST);
      }

      // Save new entity in db
      $entity = $this->save(new User(), $request);

      // Response data
      return $this->rest->setResponse([
        'id' => $entity->getId(),
        'email' => $entity->getEmail()
      ]);
    } catch (Exception $e) {
      $this->logAPIError($e->getMessage());

      return $this->rest->setResponse(
        $this->translator->trans('utils.internalServer', locale: $request->getLocale()),
        Response::HTTP_INTERNAL_SERVER_ERROR
      );
    }
  }

  /**
   * Get users service.
   *
   * Please use this service to get a list of users.
   *
   * @OA\Parameter(
   *     name="enabled", in="query", required=true, description="Filter by users status",
   *     @OA\Schema(
   *         type="boolean", default=true
   *     )
   * )
   * @OA\Parameter(
   *     name="q", in="query", description="Search term",
   *     @OA\Schema(
   *         type="string"
   *     )
   * )
   * @OA\Parameter(
   *     name="fields", in="query", description="Fields to return, comma separated values",
   *     @OA\Schema(
   *         type="string", format="field1,field2"
   *     )
   * )
   * @OA\Parameter(
   *     name="sort", in="query", description="Sort options",
   *     @OA\Schema(
   *         type="string", format="fieldName or -fieldName", default="id"
   *     )
   * )
   * @OA\Parameter(
   *     name="offset", in="query", description="Offset for pagination",
   *     @OA\Schema(
   *         type="integer", default="1"
   *     )
   * )
   * @OA\Parameter(
   *     name="limit", in="query", description="Limit for pagination",
   *     @OA\Schema(
   *         type="integer", default="20"
   *     )
   * )
   * @OA\Response(
   *     response=200, description="Returns a list of users",
   *     @OA\JsonContent(
   *         type="array",
   *         @OA\Items(
   *             type="object",
   *             @OA\Property(property="id", type="integer", example=1, description="User unique identifier"),
   *             @OA\Property(property="email", type="string", example="test@test.com", description="User email address also used to authenticate"),
   *             @OA\Property(property="roles", description="User roles", type="array", @OA\Items()),
   *             @OA\Property(property="enabled", type="boolean", example=true, description="User status, if user is active or not"),
   *             @OA\Property(property="firstName", type="string", example="John", description="User first name"),
   *             @OA\Property(property="lastName", type="string", example="Doe", description="User last name"),
   *             @OA\Property(property="avatar", type="string", example="https://imgur.com/blablabla", description="User avatar url"),
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
   * @OA\Tag(name="users")
   *
   * @Get("/secured/users", name="get_users", options={ "method_prefix" = false })
   */
  public function index(Request $request): JsonResponse {
    try {
      // In case user is not admin
      if ($this->isGranted(UserRole::ROLE_ADMIN) === false) {
        return $this->rest->setResponse(
          $this->translator->trans('utils.notAuthorized', locale: $request->getLocale()),
          Response::HTTP_FORBIDDEN
        );
      }

      // Validate request
      $errors = $this->validationManager->validate('getUsers');

      if (!empty($errors)) {
        return $this->rest->setResponse($errors, Response::HTTP_BAD_REQUEST);
      }

      // Set pagination and sorting
      $this->setListingConfigurations($request, $page, $noRecords, $sortField, $sortType);

      $enabled = $this->rest->getBool($request->get('enabled'));
      $q = $request->get('q');

      // Count total items
      $noTotal = $this->em->getRepository(User::class)->getCount($enabled, $q);

      // Get items data
      $users = $this->em->getRepository(User::class)->getAll($page, $noRecords, $sortField, $sortType, $enabled, $q);

      // Filter return fields
      $users = $this->array->filterArrayByKeys($users, $request->get('fields'));

      // Create header link
      $headerLink = $this->setHeaderLink($request, $page, $noRecords, $noTotal);

      // Return response
      return $this->rest->setResponse($users, Response::HTTP_OK, [
        'X-Total-Count' => $noTotal,
        'Link' => $headerLink
      ]);
    } catch (Exception $e) {
      $this->logAPIError($e->getMessage());

      return $this->rest->setResponse(
        $this->translator->trans('utils.internalServer', locale: $request->getLocale()),
        Response::HTTP_INTERNAL_SERVER_ERROR
      );
    }
  }

  /**
   * Get user service.
   *
   * Please use this service to get user details.
   *
   * @OA\Parameter(
   *     name="id", in="path", required=true, description="User unique identifier",
   *     @OA\Schema(
   *         type="integer"
   *     )
   * )
   * @OA\Response(
   *     response=200, description="Returns user details",
   *     @OA\JsonContent(
   *         type="object",
   *         @OA\Property(property="id", type="integer", example=1, description="User unique identifier"),
   *         @OA\Property(property="email", type="string", example="test@test.com", description="User email address also used to authenticate"),
   *         @OA\Property(property="roles", description="User roles", type="array", @OA\Items()),
   *         @OA\Property(property="enabled", type="boolean", example=true, description="User status, if user is active or not"),
   *         @OA\Property(property="firstName", type="string", example="John", description="User first name"),
   *         @OA\Property(property="lastName", type="string", example="Doe", description="User last name"),
   *         @OA\Property(property="avatar", type="string", example="https://imgur.com/blablabla", description="User avatar url"),
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
   * @OA\Tag(name="users")
   *
   * @Get("/secured/users/{id}", name="get_user", options={ "method_prefix" = false })
   */
  public function show(Request $request, ?User $user): JsonResponse {
    try {
      // In case user not found, return error
      if ($user === null) {
        return $this->rest->setResponse(
          $this->translator->trans('utils.notFound', locale: $request->getLocale()),
          Response::HTTP_NOT_FOUND
        );
      }

      // In case user is not admin
      if ($this->isGranted(UserRole::ROLE_ADMIN) === false) {
        return $this->rest->setResponse(
          $this->translator->trans('utils.notAuthorized', locale: $request->getLocale()),
          Response::HTTP_FORBIDDEN
        );
      }

      // Return response
      return $this->rest->setResponse([
        'id' => $user->getId(),
        'email' => $user->getEmail(),
        'roles' => $user->getRoles(),
        'enabled' => $user->isEnabled(),
        'firstName' => $user->getUserProfile() ? $user->getUserProfile()->getFirstName() : '',
        'lastName' => $user->getUserProfile() ? $user->getUserProfile()->getLastName() : '',
        'avatar' => $user->getUserProfile()?->getAvatar(),
      ]);
    } catch (Exception $e) {
      $this->logAPIError($e->getMessage());

      return $this->rest->setResponse(
        $this->translator->trans('utils.internalServer', locale: $request->getLocale()),
        Response::HTTP_INTERNAL_SERVER_ERROR
      );
    }
  }

  /**
   * Get the authenticated user profile.
   *
   * Please use this service to get the authenticated user profile.
   *
   * @OA\Response(
   *     response=200, description="Returns authenticated user details",
   *     @OA\JsonContent(
   *         type="object",
   *         @OA\Property(property="id", type="integer", example=1, description="User unique identifier"),
   *         @OA\Property(property="email", type="string", example="test@test.com", description="User email address also used to authenticate"),
   *         @OA\Property(property="roles", description="User roles", type="array", @OA\Items()),
   *         @OA\Property(property="enabled", type="boolean", example=true, description="User status, if user is active or not"),
   *         @OA\Property(property="firstName", type="string", example="John", description="User first name"),
   *         @OA\Property(property="lastName", type="string", example="Doe", description="User last name"),
   *         @OA\Property(property="avatar", type="string", example="https://imgur.com/blablabla", description="User avatar url"),
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
   * @OA\Tag(name="users")
   *
   * @Get("/secured/authenticated", name="get_authenticated", options={ "method_prefix" = false })
   */
  public function authenticated(Request $request): JsonResponse {
    try {
      $user = $this->getUser();

      // In case user not found, return error
      if ($user === null) {
        return $this->rest->setResponse(
          $this->translator->trans('utils.notFound', locale: $request->getLocale()),
          Response::HTTP_NOT_FOUND
        );
      }

      // Return response
      return $this->rest->setResponse([
        'id' => $user->getId(),
        'email' => $user->getEmail(),
        'roles' => $user->getRoles(),
        'enabled' => $user->isEnabled(),
        'firstName' => $user->getUserProfile() ? $user->getUserProfile()->getFirstName() : '',
        'lastName' => $user->getUserProfile() ? $user->getUserProfile()->getLastName() : '',
        'avatar' => $user->getUserProfile()?->getAvatar(),
      ]);
    } catch (Exception $e) {
      $this->logAPIError($e->getMessage());

      return $this->rest->setResponse(
        $this->translator->trans('utils.internalServer', locale: $request->getLocale()),
        Response::HTTP_INTERNAL_SERVER_ERROR
      );
    }
  }

  /**
   * Edit user service.
   *
   * Please use this service to edit user details.
   *
   * @OA\Parameter(
   *     name="id", in="path", required=true, description="User unique identifier",
   *     @OA\Schema(
   *         type="integer"
   *     )
   * )
   * @OA\RequestBody(
   *   description="User data",
   *   @OA\JsonContent(
   *     type="object",
   *     required={"email", "firstName", "lastName"},
   *     @OA\Property(property="email", type="string", example="john@doe.com", description="User email"),
   *     @OA\Property(property="firstName", type="string", example="John", description="User first name"),
   *     @OA\Property(property="lastName", type="string", example="Doe", description="User last name"),
   *     @OA\Property(property="avatar", type="string", example="https://imgur.com/blablabla", description="User avatar url"),
   *     @OA\Property(property="password", type="string", example="*********", description="User plain password")
   *   )
   * )
   * @OA\Response(
   *     response=200, description="Returns user details",
   *     @OA\JsonContent(
   *         type="object",
   *         @OA\Property(property="id", type="integer", example=1, description="User unique identifier"),
   *         @OA\Property(property="email", type="string", example="test@test.com", description="User email")
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
   * @OA\Tag(name="users")
   *
   * @Put("/secured/users/{id}", name="edit_user", options={ "method_prefix" = false })
   */
  public function edit(Request $request, ?User $user): JsonResponse {
    try {
      // In case user not found, return error
      if ($user === null) {
        return $this->rest->setResponse(
          $this->translator->trans('utils.notFound', locale: $request->getLocale()),
          Response::HTTP_NOT_FOUND
        );
      }

      // In case that auth user, try to edit another user data
      if ($this->getUser()->getUserIdentifier() !== $user->getEmail()) {
        // And that user isn't admin,
        if ($this->isGranted(UserRole::ROLE_ADMIN) === false) {
          return $this->rest->setResponse(
            $this->translator->trans('utils.notAuthorized', locale: $request->getLocale()),
            Response::HTTP_FORBIDDEN
          );
        }
      }

      // Edit data
      $entity = $this->save($user, $request);

      // Response data
      return $this->rest->setResponse([
        'id' => $entity->getId(),
        'email' => $entity->getEmail()
      ]);
    } catch (Exception $e) {
      $this->logAPIError($e->getMessage());

      return $this->rest->setResponse(
        $this->translator->trans('utils.internalServer', locale: $request->getLocale()),
        Response::HTTP_INTERNAL_SERVER_ERROR
      );
    }
  }

  /**
   * Give an admin role to a user.
   *
   * Please use this service to give an admin role to a user.
   *
   * @OA\Parameter(
   *     name="id", in="path", required=true, description="User unique identifier",
   *     @OA\Schema(
   *         type="integer"
   *     )
   * )
   *
   * @OA\Response(
   *     response=200, description="Returns user details",
   *     @OA\JsonContent(
   *         type="object",
   *         @OA\Property(property="id", type="integer", example=1, description="User unique identifier"),
   *         @OA\Property(property="email", type="string", example="test@test.com", description="User email")
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
   * @OA\Tag(name="users")
   *
   * @Patch("/secured/users/grant-admin/{id}", name="patch_admin_user", options={ "method_prefix" = false })
   */
  public function patchAdmin(Request $request, ?User $user): JsonResponse {
    try {
      // In case user not found, return error
      if ($user === null) {
        return $this->rest->setResponse(
          $this->translator->trans('utils.notFound', locale: $request->getLocale()),
          Response::HTTP_NOT_FOUND
        );
      }

      // In case user is not admin
      if ($this->isGranted(UserRole::ROLE_ADMIN) === false) {
        return $this->rest->setResponse(
          $this->translator->trans('utils.notAuthorized', locale: $request->getLocale()),
          Response::HTTP_FORBIDDEN
        );
      }

      // Set a ROLE_ADMIN
      $user->setRoles([UserRole::ROLE_ADMIN, UserRole::ROLE_USER]);

      $this->em->persist($user);
      $this->em->flush();

      return $this->rest->setResponse([
        'id' => $user->getId(),
        'email' => $user->getEmail()
      ]);
    } catch (Exception $e) {
      $this->logAPIError($e->getMessage());

      return $this->rest->setResponse(
        $this->translator->trans('utils.internalServer', locale: $request->getLocale()),
        Response::HTTP_INTERNAL_SERVER_ERROR
      );
    }
  }

  /**
   * Give an admin role to a user.
   *
   * Please use this service to give an admin role to a user.
   *
   * @OA\Parameter(
   *     name="id", in="path", required=true, description="User unique identifier",
   *     @OA\Schema(
   *         type="integer"
   *     )
   * )
   *
   * @OA\Response(
   *     response=200, description="Returns user details",
   *     @OA\JsonContent(
   *         type="object",
   *         @OA\Property(property="id", type="integer", example=1, description="User unique identifier"),
   *         @OA\Property(property="email", type="string", example="test@test.com", description="User email")
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
   * @OA\Tag(name="users")
   *
   * @Patch("/secured/users/grant-instructor/{id}", name="patch_instructor_user", options={ "method_prefix" = false })
   */
  public function patchInstructor(Request $request, ?User $user): JsonResponse {
    try {
      // In case user not found, return error
      if ($user === null) {
        return $this->rest->setResponse(
          $this->translator->trans('utils.notFound', locale: $request->getLocale()),
          Response::HTTP_NOT_FOUND
        );
      }

      // In case user is not admin
      if ($this->isGranted(UserRole::ROLE_ADMIN) === false) {
        return $this->rest->setResponse(
          $this->translator->trans('utils.notAuthorized', locale: $request->getLocale()),
          Response::HTTP_FORBIDDEN
        );
      }

      // Set ROLE_INSTRUCTOR
      $user->setRoles([UserRole::ROLE_INSTRUCTOR, UserRole::ROLE_USER]);

      $this->em->persist($user);
      $this->em->flush();

      return $this->rest->setResponse([
        'id' => $user->getId(),
        'email' => $user->getEmail()
      ]);
    } catch (Exception $e) {
      $this->logAPIError($e->getMessage());

      return $this->rest->setResponse(
        $this->translator->trans('utils.internalServer', locale: $request->getLocale()),
        Response::HTTP_INTERNAL_SERVER_ERROR
      );
    }
  }

  /**
   * Save user into db.
   *
   * @param User $entity
   * @param Request $request
   * @return User
   */
  public function save(User $entity, Request $request): User {
    // Collect password
    $plainPassword = $request->get('password');

    // Set entity data
    $entity->setEmail($request->get('email'));

    // In case we just create a user, set by default a ROLE_USER role
    if ($entity->getId() === null) {
      $entity->setRoles([UserRole::ROLE_USER]);
    }

    $entity->setEnabled(true);

    // In case password was sent, change it
    if ($plainPassword) {
      $entity->setPassword($this->encoder->hashPassword($entity, $plainPassword));
    }

    // Save the entity to db
    $this->em->persist($entity);
    $this->em->flush();

    // Make an empty user profile entity
    $userProfile = new UserProfile();

    // In case user had a user profile, take that entity
    if ($entity->getUserProfile() !== null) {
      $userProfile = $entity->getUserProfile();
    }

    // Setting user profile data
    $userProfile->setUser($entity);
    $userProfile->setFirstName($request->get('firstName'));
    $userProfile->setLastName($request->get('lastName'));
    $userProfile->setAvatar($request->get('avatar'));

    // Saving to db
    $this->em->persist($userProfile);
    $this->em->flush();

    return $entity;
  }
}
