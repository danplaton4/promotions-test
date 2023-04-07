<?php

namespace App\Model\Validation;

use App\Model\Constant\ValidationType;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ValidationManager {

  private ValidatorInterface $validator;
  private ?Request $request;
  private array $errorMessages;
  private TranslatorInterface $translator;

  /**
   * @param ValidatorInterface $validator
   * @param RequestStack $requestStack
   * @param TranslatorInterface $translator
   */
  public function __construct(
    ValidatorInterface  $validator,
    RequestStack        $requestStack,
    TranslatorInterface $translator
  ) {
    $this->validator = $validator;
    $this->request = $requestStack->getCurrentRequest();
    $this->errorMessages = array();
    $this->translator = $translator;
  }

  /**
   * @param string $resource
   * @param string|null $userRole
   * @return array
   * @throws Exception
   */
  public function validate(string $resource, string $userRole = null): array {
    $validationData = ValidationRules::get($resource, $userRole);

    if (empty($validationData)) {
      return array();
    }

    foreach ($validationData as $key => $data) {
      $extra = !empty($data['extra']) ? $data['extra'] : array();
      $requestItem = $this->request->get($key);

      if (str_contains($key, '.')) {
        $parts = explode('.', $key);
        $requestItem = $this->request->get(array_shift($parts));

        foreach ($parts as $part) {
          if (!is_array($requestItem) || !$part) {
            $requestItem = null;

            break;
          }

          $requestItem = $requestItem[$part] ?? null;
        }
      }

      $this->validateItem($data['label'], $key, $requestItem, $data['rules'], $extra);
    }

    return $this->errorMessages;
  }

  /**
   * @param string $requestName
   * @param string $requestFieldName
   * @param $requestItem
   * @param array $validationType
   * @param array $extra
   * @return void
   */
  private function validateItem(
    string $requestName,
    string $requestFieldName,
           $requestItem,
    array  $validationType = [],
    array  $extra = []
  ): void {
    foreach ($validationType as $type) {
      switch ($type) {
        case ValidationType::REQUIRED:
          if ($requestItem !== false) {
            $notBlankConstraint = new Assert\NotBlank();
            $errors = $this->validator->validate($requestItem, $notBlankConstraint);
            $errorsCount = count($errors);

            if ($errorsCount > 0) {
              $this->errorMessages[$requestFieldName] = $this->translator->trans('validation.required', ['field' => $requestName], locale: $this->request->getLocale());
            }

            if (!empty($extra) && !isset($extra['min'], $extra['max'])) {
              $choiceConstraint = new Assert\Choice($extra);
              $errors = $this->validator->validate($requestItem, $choiceConstraint);
              $errorsCount = count($errors);

              if ($errorsCount > 0) {
                $this->errorMessages[$requestFieldName] = $this->translator->trans('validation.invalid', ['field' => $requestName, 'values' => implode(", ", $extra)], locale: $this->request->getLocale());
              }
            }
          }

          break;

        case ValidationType::EMAIL:
          $emailConstraint = new Assert\Email();
          $errors = $this->validator->validate($requestItem, $emailConstraint);
          $errorsCount = count($errors);

          if ($errorsCount > 0) {
            $this->errorMessages[$requestFieldName] = $this->translator->trans('validation.email', ['field' => $requestName], locale: $this->request->getLocale());
          }

          break;

        case ValidationType::DATETIME:
          $dateTimeConstraint = new Assert\DateTime();
          $errors = $this->validator->validate($requestItem, $dateTimeConstraint);
          $errorsCount = count($errors);

          if ($errorsCount > 0) {
            $this->errorMessages[$requestFieldName] = $this->translator->trans('validation.datetime', ['field' => $requestName], locale: $this->request->getLocale());
          }

          break;

        case ValidationType::DATE:
          $dateConstraint = new Assert\Date();
          $errors = $this->validator->validate($requestItem, $dateConstraint);
          $errorsCount = count($errors);

          if ($errorsCount > 0) {
            $this->errorMessages[$requestFieldName] = $this->translator->trans('validation.date', ['field' => $requestName], locale: $this->request->getLocale());
          }

          break;

        case ValidationType::TIME:
          $timeConstraint = new Assert\Time();
          $requestItem = $requestItem ? $requestItem . ':00' : $requestItem;
          $errors = $this->validator->validate($requestItem, $timeConstraint);
          $errorsCount = count($errors);

          if ($errorsCount > 0) {
            $this->errorMessages[$requestFieldName] = $this->translator->trans('validation.time', ['field' => $requestName], locale: $this->request->getLocale());

          }

          break;

        case ValidationType::INTEGER:
          $regexConstraint = new Assert\Regex('/^\d+$/');
          $errors = $this->validator->validate($requestItem, $regexConstraint);
          $errorsCount = count($errors);

          if ($errorsCount > 0) {
            $this->errorMessages[$requestFieldName] = $this->translator->trans('validation.integer', ['field' => $requestName], locale: $this->request->getLocale());

          }
          break;
        case ValidationType::NUMERIC:
          $integerConstraint = new Assert\Type('numeric');
          $errors = $this->validator->validate($requestItem, $integerConstraint);
          $errorsCount = count($errors);

          if ($errorsCount > 0) {
            $this->errorMessages[$requestFieldName] = $this->translator->trans('validation.numeric', ['field' => $requestName], locale: $this->request->getLocale());
          }
          break;
        case ValidationType::ZIP:
          $regexConstraint = new Assert\Regex('/^\d{5}(?:[-\s]\d{4})?$/');
          $errors = $this->validator->validate($requestItem, $regexConstraint);
          $errorsCount = count($errors);

          if ($errorsCount > 0) {
            $this->errorMessages[$requestFieldName] = $this->translator->trans('validation.zip', ['field' => $requestName], locale: $this->request->getLocale());
          }

          break;

        case ValidationType::BOOLEAN:
          $identicalConstraint = new Assert\Type('bool');
          $requestItem = $requestItem === 'true' ? true : false;
          $errors1 = $this->validator->validate($requestItem, $identicalConstraint);
          $errors1Count = count($errors1);

          $choiceConstraint = new Assert\Choice(array("0", "1"));
          $errors2 = $this->validator->validate($requestItem, $choiceConstraint);
          $errors2Count = count($errors2);

          if ($errors1Count > 0 && $errors2Count > 0) {
            $this->errorMessages[$requestFieldName] = $this->translator->trans('validation.bool', ['field' => $requestName], locale: $this->request->getLocale());
          }

          break;

        case ValidationType::PASSWORD:
          $regexConstraint = new Assert\Regex('/^(?=.*?[A-Z])(?=(.*[a-z]){1,})(?=(.*[\d]){1,})(?=(.*[\W]){1,})(?!.*\s).{8,}$/');
          $errors = $this->validator->validate($requestItem, $regexConstraint);
          $errorsCount = count($errors);

          if ($errorsCount > 0) {
            $this->errorMessages[$requestFieldName] = $this->translator->trans('validation.password', locale: $this->request->getLocale());
          }

          break;

        case ValidationType::LATITUDE:
          $regexConstraint = new Assert\Regex('/^(\+|-)?(?:90(?:(?:\.0{1,7})?)|(?:[0-9]|[1-8][0-9])(?:(?:\.[0-9]{1,7})?))$/');
          $errors = $this->validator->validate($requestItem, $regexConstraint);
          $errorsCount = count($errors);

          if ($errorsCount > 0) {
            $this->errorMessages[$requestFieldName] = $this->translator->trans('validation.latitude', ['field' => $requestName], locale: $this->request->getLocale());
          }

          break;

        case ValidationType::LONGITUDE:
          $regexConstraint = new Assert\Regex('/^(\+|-)?(?:180(?:(?:\.0{1,7})?)|(?:[0-9]|[1-9][0-9]|1[0-7][0-9])(?:(?:\.[0-9]{1,7})?))$/');
          $errors = $this->validator->validate($requestItem, $regexConstraint);
          $errorsCount = count($errors);

          if ($errorsCount > 0) {
            $this->errorMessages[$requestFieldName] = $this->translator->trans('validation.longitude', ['field' => $requestName], locale: $this->request->getLocale());
          }

          break;

        case ValidationType::HEX_COLOR:
          $regexConstraint = new Assert\Regex('/#([a-fA-F0-9]{3}){1,2}\b/');
          $errors = $this->validator->validate($requestItem, $regexConstraint);
          $errorsCount = count($errors);

          if ($errorsCount > 0) {
            $this->errorMessages[$requestFieldName] = $this->translator->trans('validation.hex', ['field' => $requestName], locale: $this->request->getLocale());
          }

          break;

        case ValidationType::URL:
          $urlConstraint = new Assert\Url();
          $errors = $this->validator->validate($requestItem, $urlConstraint);
          $errorsCount = count($errors);

          if ($errorsCount > 0) {
            $this->errorMessages[$requestFieldName] = $this->translator->trans('validation.url', ['field' => $requestName], locale: $this->request->getLocale());
          }

          break;

        case ValidationType::RANGE:
          $rangeConstraint = new Assert\Range($extra);
          $errors = $this->validator->validate($requestItem, $rangeConstraint);
          $errorsCount = count($errors);

          if ($errorsCount > 0) {
            $this->errorMessages[$requestFieldName] = $this->translator->trans('validation.range', ['field' => $requestName, 'min' => $extra['min'], 'max' => $extra['max']], locale: $this->request->getLocale());
          }

          break;

        case ValidationType::CHOICE:
          $choiceConstraint = new Assert\Choice($extra);
          $errors = $this->validator->validate($requestItem, $choiceConstraint);
          $errorsCount = count($errors);

          if ($errorsCount > 0) {
            $this->errorMessages[$requestFieldName] = $this->translator->trans('validation.choice', ['field' => $requestName, 'values' => implode(", ", $extra)], locale: $this->request->getLocale());
          }

          break;

        case ValidationType::CHOICE_MULTIPLE:
          $this->validateMultipleChoice($requestItem, $extra, $requestFieldName, $requestName);

          break;

        case ValidationType::CHOICE_MULTIPLE_COMMA:
          if (!is_string($requestItem) && !is_null($requestItem)) {
            $this->errorMessages[$requestFieldName] = $this->translator->trans('validation.string', ['field' => $requestName], locale: $this->request->getLocale());
          } else {
            $explodedRequestItems = $requestItem ? preg_split('/\s*,\s*/', $requestItem, -1, PREG_SPLIT_NO_EMPTY) : [];

            foreach ($explodedRequestItems as $explodedRequestItem) {
              $choiceConstraint = new Assert\Choice($extra);
              $errors = $this->validator->validate($explodedRequestItem, $choiceConstraint);
              $errorsCount = count($errors);

              if ($errorsCount > 0) {
                $this->errorMessages[$requestFieldName] = $this->translator->trans('validation.choice', ['field' => $requestName, 'values' => implode(", ", $extra)], locale: $this->request->getLocale());

                break;
              }
            }
          }

          break;

        case ValidationType::CONDITION:
          if (!is_array($extra['conditionField']['value'])) {
            $constraint = new Assert\IdenticalTo(array('value' => $extra['conditionField']['value']));
          } else {
            $constraint = new Assert\Choice($extra['conditionField']['value']);
          }

          $errorsCondition = $this->validator->validate($this->request->get($extra['conditionField']['name']), $constraint);
          $errorsConditionCount = count($errorsCondition);

          if ($errorsConditionCount === 0) {
            $extraToValidate = isset($extra['extra']) ? $extra['extra'] : array();
            $this->validateItem($requestName, $requestFieldName, $requestItem, $extra['rules'], $extraToValidate);
          }

          break;

        case ValidationType::ARRAY_INTEGER:
          $regexConstraint = [
            new Assert\Type('array'),
            new Assert\All([
              new Assert\Regex('/^\d+$/')
            ])
          ];

          $errors = $this->validator->validate($requestItem, $regexConstraint);

          if (count($errors) > 0) {
            $this->errorMessages[$requestFieldName] = $this->translator->trans('validation.arrayInt', ['field' => $requestName], locale: $this->request->getLocale());
          }

          break;

        case ValidationType::ARRAY_NOT_EMPTY:
          $regexConstraint = [
            new Assert\Type('array'),
            new Assert\Count(['min' => 1])
          ];

          $errors = $this->validator->validate($requestItem, $regexConstraint);

          if (count($errors) > 0) {
            $this->errorMessages[$requestFieldName] = $this->translator->trans('validation.arrayNotEmpty', ['field' => $requestName], locale: $this->request->getLocale());
          }

          break;
      }
    }
  }

  private function validateMultipleChoice(mixed $requestItem, mixed $extra, string $requestFieldName, string $requestName): void {
    if (is_array($requestItem)) {
      foreach ($requestItem as $requestItemPart) {
        $choiceConstraint = new Assert\Choice($extra);
        $errors = $this->validator->validate($requestItemPart, $choiceConstraint);
        $errorsCount = count($errors);

        if ($errorsCount > 0) {
          $this->errorMessages[$requestFieldName] = $this->translator->trans('validation.choice', ['field' => $requestName, 'values' => implode(", ", $extra)], locale: $this->request->getLocale());

          break;
        }
      }
    }
  }
}
